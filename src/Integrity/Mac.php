<?php

namespace Mobbex\Integrity;

/**
 * The two canonical attestation checks.
 *
 * Contract with the verification service: both implementations must produce the
 * same byte for the same input. Every plugin sends both values, because they
 * cover different things: the spec MAC is over ranges the server draws per
 * request, so nothing is precomputable; the global sum is over the whole
 * release, which is what makes detection deterministic — without it a modified
 * file the draw did not happen to touch would pass.
 *
 *     key = raw(nonce)
 *
 *     // 1. spec check
 *     msg = prefix . "\n" . platform . "\n" . version . "\n"
 *         . per file, IN SPEC ORDER:
 *               path_utf8 . 0x00 . uint64BE(offset) . uint64BE(requested_length) . read_bytes
 *     mac = hex(HMAC-SHA256(key, msg))
 *
 *     // 2. global check
 *     content_digest = SHA256( per file of global.files, SORTED BY PATH:
 *               path_utf8 . 0x00 . uint64BE(bytes_read) . bytes )
 *     msg    = prefix . "\n" . "GLOBAL\n" . platform . "\n" . version . "\n" . raw(content_digest)
 *     global = hex(HMAC-SHA256(key, msg))
 *
 * A divergence here looks exactly like a real tamper in production — every
 * merchant breaks at once and the investigation starts in the wrong place — so
 * this class has no network, filesystem-layout or platform dependency beyond the
 * root directory it is handed, and is covered by the golden vectors shared with
 * the verification service in tests/mac_vectors.json.
 *
 * Compatible with PHP 5.6: plugins run on PrestaShop 1.6, WooCommerce on old
 * hosts, and so on.
 */
class Mac
{
    /** Bytes read per iteration. Never allocate a buffer from a requested length. */
    const CHUNK_SIZE = 65536;

    /**
     * Compute the spec check over the ranges the server drew.
     *
     * Streams the message through hash_update instead of building it in memory:
     * a spec may legitimately request a length larger than the file (the golden
     * vectors do), and fread() allocates its buffer from the *requested* length.
     * Doing that naively exhausts memory_limit, which in PHP is a fatal error no
     * try/catch can recover from — it would kill the checkout request.
     *
     * @param string $prefix   Protocol prefix, e.g. "MBX-ATTEST-v1".
     * @param string $nonceHex Challenge nonce, hex encoded. Used as the HMAC key.
     * @param string $platform Repo name of the plugin, e.g. "prestashop".
     * @param string $version  Plugin version, matching the published release tag.
     * @param array  $files    [['p' => string, 'o' => int, 'l' => int], ...] IN ORDER.
     * @param string $rootDir  Plugin installation directory. Spec paths are relative to it.
     *
     * @return string Hex MAC.
     *
     * @throws \Mobbex\Exception When the nonce is not valid hex.
     */
    public static function computeSpec($prefix, $nonceHex, $platform, $version, $files, $rootDir)
    {
        $context = hash_init('sha256', HASH_HMAC, self::decodeNonce($nonceHex));

        hash_update($context, $prefix . "\n" . $platform . "\n" . $version . "\n");

        $root = rtrim($rootDir, '/');

        foreach ($files as $file) {
            $path   = isset($file['p']) ? $file['p'] : '';
            $offset = isset($file['o']) ? $file['o'] : 0;
            $length = isset($file['l']) ? $file['l'] : 0;

            // The uint64 carries the REQUESTED length, never the length read:
            // using the read length would let an attacker truncate a file by
            // declaring a shorter range.
            hash_update($context, $path);
            hash_update($context, "\0");
            hash_update($context, self::uint64BE($offset));
            hash_update($context, self::uint64BE($length));

            self::updateWithRange($context, self::resolve($root, $path), $offset, $length);
        }

        return hash_final($context);
    }

    /**
     * Compute the global check over the whole release.
     *
     * Reads every path in $paths — never a directory walk. A merchant's install
     * holds files the release does not ship: PrestaShop generates config.xml
     * inside the module folder, WordPress plugins collect caches and .htaccess.
     * Walking the directory would make the global sum fail on every legitimate
     * install rather than on the tampered ones. Whatever else is on disk is
     * simply ignored.
     *
     * The digest is recomputed on every request by design. Caching it per
     * version would turn it back into a fixed, precomputable value — the exact
     * static checksum this protocol replaces.
     *
     * @param string     $prefix   Protocol prefix.
     * @param string     $nonceHex Challenge nonce, hex encoded. Same key as the spec check.
     * @param string     $platform Repo name of the plugin.
     * @param string     $version  Plugin version.
     * @param array      $paths    global.files from the challenge. Order is irrelevant: sorted here.
     * @param string     $rootDir  Plugin installation directory.
     * @param float|null $deadline microtime(true) past which to give up. Null for no limit.
     *
     * @return array|null ['content_digest' => hex, 'global' => hex], or null if the deadline passed.
     *
     * @throws \Mobbex\Exception When the nonce is not valid hex.
     */
    public static function computeGlobal($prefix, $nonceHex, $platform, $version, $paths, $rootDir, $deadline = null)
    {
        $key   = self::decodeNonce($nonceHex);
        $paths = array_values($paths);

        // Path order defines the message, not the order the server sent. Byte
        // wise comparison, which for UTF-8 is also code point order — the same
        // thing the Go and Python references do.
        sort($paths, SORT_STRING);

        $context = hash_init('sha256');
        $root    = rtrim($rootDir, '/');

        foreach ($paths as $path) {
            // Reading the whole tree is normally 1-2 ms from page cache, but
            // plugins also run on shared hosting over NFS. Giving up beats
            // stalling a payment: the caller omits the global value and the
            // server sees global:missing rather than a tamper.
            //
            // Checked on every file, not every N: a manifest of a few very large
            // files would otherwise blow the budget without ever looking. One
            // microtime() call is nothing next to reading a file.
            if ($deadline !== null && microtime(true) > $deadline)
                return null;

            $full = self::resolve($root, $path);

            // The uint64 here carries the size actually READ, unlike the spec
            // check where it is the size requested. A missing file is 0.
            $size = $full === null ? 0 : self::readableSize($full);

            hash_update($context, $path);
            hash_update($context, "\0");
            hash_update($context, self::uint64BE($size));

            self::updateWithRange($context, $full, 0, $size);
        }

        // Raw 32 bytes into the message, never the 64 char hex.
        $digest = hash_final($context, true);

        return [
            'content_digest' => bin2hex($digest),
            'global'         => hash_hmac(
                'sha256',
                $prefix . "\n" . "GLOBAL\n" . $platform . "\n" . $version . "\n" . $digest,
                $key
            ),
        ];
    }

    /**
     * Resolve a spec path against the install directory.
     *
     * @param string $root Install directory, without trailing slash.
     * @param string $path Relative path from the spec.
     *
     * @return string|null Absolute path, or null when the path is not allowed.
     */
    protected static function resolve($root, $path)
    {
        if (!self::isSafePath($path))
            return null;

        return $root . '/' . $path;
    }

    /**
     * Whether a spec path is allowed to be read.
     *
     * Paths come from the challenge response, so they are input from outside.
     * A path escaping the install directory would make the plugin hash files it
     * has no business reading — and that hash travels back to us, which turns a
     * compromised endpoint into a slow exfiltration primitive. Absolute paths and
     * any `..` segment are refused.
     *
     * A refused path contributes zero bytes, exactly like a missing file, so the
     * value simply fails to match. Failing closed is the right outcome: the
     * server never sends such a path.
     *
     * @param string $path Relative path from the spec.
     *
     * @return bool
     */
    protected static function isSafePath($path)
    {
        if (!is_string($path) || $path === '')
            return false;

        // Absolute, or a Windows drive or UNC prefix.
        if ($path[0] === '/' || $path[0] === '\\' || preg_match('#^[a-zA-Z]:#', $path))
            return false;

        foreach (preg_split('#[/\\\\]#', $path) as $segment) {
            if ($segment === '..')
                return false;
        }

        return true;
    }

    /**
     * Size of a readable file, or 0 when it is missing or unreadable.
     *
     * @param string $path
     *
     * @return int
     */
    protected static function readableSize($path)
    {
        if (!is_file($path) || !is_readable($path))
            return 0;

        $size = @filesize($path);

        return $size === false ? 0 : $size;
    }

    /**
     * Decode the hex nonce into the raw HMAC key.
     *
     * @param string $nonceHex
     *
     * @return string Raw bytes.
     *
     * @throws \Mobbex\Exception When the value is not valid hex.
     */
    protected static function decodeNonce($nonceHex)
    {
        if (!is_string($nonceHex) || $nonceHex === '' || strlen($nonceHex) % 2 !== 0 || !ctype_xdigit($nonceHex))
            throw new \Mobbex\Exception('Mobbex integrity error: invalid nonce');

        return pack('H*', $nonceHex);
    }

    /**
     * Feed a file range into the hash context.
     *
     * Protocol rules, all of them intentional:
     *   - a missing or unreadable file contributes 0 bytes, never an error;
     *   - an offset at or past EOF contributes 0 bytes;
     *   - a range overflowing EOF contributes whatever is there (short read).
     *
     * In every one of those cases the value will not match, which is exactly the
     * behaviour we want: a tampered install must fail verification, not crash.
     *
     * @param resource  $context
     * @param string    $path
     * @param int|float $offset
     * @param int|float $length Requested length. 0 means "to end of file".
     *
     * @return void
     */
    protected static function updateWithRange($context, $path, $offset, $length)
    {
        if ($path === null || !is_file($path) || !is_readable($path))
            return;

        $size = @filesize($path);

        if ($size === false || $offset >= $size)
            return;

        // Clamp to what the file actually holds *before* reading. This is what
        // keeps a spec asking for 8 GiB from allocating 8 GiB.
        $available = $size - $offset;
        $remaining = $length > 0 ? min($length, $available) : $available;

        $handle = @fopen($path, 'rb');

        if ($handle === false)
            return;

        // Only reached when $offset < $size, so the seek target always fits an int.
        if ($offset > 0 && fseek($handle, (int) $offset) !== 0) {
            fclose($handle);
            return;
        }

        while ($remaining > 0) {
            $chunk = fread($handle, (int) min($remaining, self::CHUNK_SIZE));

            if ($chunk === false || $chunk === '')
                break;

            hash_update($context, $chunk);
            $remaining -= strlen($chunk);
        }

        fclose($handle);
    }

    /**
     * Encode a value as an 8 byte big endian unsigned integer.
     *
     * pack('N') is 32 bits and would silently truncate the offsets past 2^32
     * that the spec is allowed to use. pack('J') is the right tool but only
     * exists from PHP 5.6.3, so the result is validated rather than assumed.
     *
     * @param int|float $number
     *
     * @return string 8 bytes.
     */
    protected static function uint64BE($number)
    {
        if (PHP_INT_SIZE >= 8) {
            $packed = @pack('J', (int) $number);

            if (is_string($packed) && strlen($packed) === 8)
                return $packed;
        }

        // 32 bit PHP: json_decode already widened anything past PHP_INT_MAX to
        // float, so shifts and pack() would overflow. Build the bytes by hand,
        // which stays exact for every value a spec can carry.
        $bytes     = '';
        $remaining = (float) $number;

        for ($i = 0; $i < 8; $i++) {
            $bytes     = chr((int) fmod($remaining, 256.0)) . $bytes;
            $remaining = floor($remaining / 256.0);
        }

        return $bytes;
    }
}
