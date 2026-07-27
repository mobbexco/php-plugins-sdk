<?php

namespace Mobbex\Integrity;

/**
 * Challenge-response integrity attestation.
 *
 * Generic for every platform. A plugin enables it with one line, after
 * \Mobbex\Platform::init():
 *
 *     \Mobbex\Integrity\Attestation::init('prestashop', dirname(__FILE__));
 *
 * The first argument is the plugin's GitHub repo name and the second its
 * installation directory. Nothing else is platform specific: this class
 * registers itself as a header provider and attaches x-integrity-* to every
 * checkout request \Mobbex\Api makes.
 *
 * Flow: ask the API for a challenge, then attach two values — an HMAC over the
 * byte ranges the server drew for this request, and a sum over the whole
 * release. The server recomputes both from the published artifact and refuses
 * the checkout when they differ.
 *
 * Both are needed. The drawn ranges make the value impossible to precompute,
 * which a static checksum never was: it could be copied straight out of the
 * public repo. The global sum makes detection deterministic, since a modified
 * file the draw did not happen to touch would otherwise pass.
 *
 * Nothing here may break a payment. Every path is wrapped and degrades quietly.
 *
 * Compatible with PHP 5.6.
 */
class Attestation
{
    /**
     * Protocol prefix. Deliberately hardcoded rather than read from the
     * response: rotating it to v2 is meant to invalidate evasion code that
     * hardcoded the protocol, and a plugin that took the server's word for it
     * would hand that rotation to the attacker too.
     */
    const PROTOCOL_PREFIX = 'MBX-ATTEST-v1';

    /** Budget for reading the whole tree for the global sum, in seconds. */
    const GLOBAL_BUDGET = 2;

    /** Longest shop URL worth sending. Past this the header is omitted, never cut. */
    const MAX_URL_LENGTH = 512;

    /** Plugin repo name, e.g. "prestashop". Must match the key the server expects. */
    public static $platform;

    /** Plugin installation directory. Spec paths are relative to it. */
    public static $rootDir;

    /** Plugin version. Must match the published release tag. */
    public static $version;

    /** Checkout page URL: a string, a callable returning one, or null. */
    public static $shopUrl;

    /** Whether the shop URL was already resolved this request. */
    protected static $urlResolved = false;

    /** Result of that resolution. Null is a valid outcome, hence the flag above. */
    protected static $resolvedUrl;

    /**
     * Enable attestation for this plugin.
     *
     * @param string               $platform Plugin repo name, e.g. "prestashop".
     * @param string               $rootDir  Plugin installation directory.
     * @param string|null          $version  Defaults to \Mobbex\Platform::$version.
     * @param string|callable|null $shopUrl  Checkout page URL, or a callable returning
     *                                       it. A callable is preferable: init() runs on
     *                                       every request while the header is only sent
     *                                       when a checkout is created, and resolving the
     *                                       URL can touch the platform's router. Null
     *                                       falls back to the shop host.
     *
     * @return void
     */
    public static function init($platform, $rootDir, $version = null, $shopUrl = null)
    {
        self::$platform = $platform;
        self::$rootDir  = rtrim($rootDir, '/');
        self::$version  = $version !== null ? $version : \Mobbex\Platform::$version;
        self::$shopUrl  = $shopUrl;

        // A new configuration invalidates whatever was resolved before.
        self::$urlResolved = false;
        self::$resolvedUrl = null;

        \Mobbex\Platform::addHeaderProvider([__CLASS__, 'getHeaders']);
    }

    /**
     * Header provider. Called by \Mobbex\Api with the request data.
     *
     * Never throws: it runs inside the request path, and an exception escaping
     * here would take the payment down instead of degrading the attestation.
     *
     * @param array $data Request data from \Mobbex\Api::request().
     *
     * @return string[]
     */
    public static function getHeaders($data = [])
    {
        try {
            if (!self::attests($data))
                return [];

            $challenge = Challenge::fetch(self::$platform, self::$version);

            if (!is_array($challenge))
                return self::degraded('challenge_unavailable');

            return self::headersFor($challenge);
        } catch (\Exception $e) {
            \Mobbex\Platform::log('error', 'Integrity > getHeaders', $e->getMessage());

            return self::degraded('exception');
        } catch (\Throwable $e) {
            // PHP 7+ only. \Error does not extend \Exception, so without this an
            // internal error would escape into the request path and break the
            // payment. The class does not exist on 5.6, where this never matches.
            \Mobbex\Platform::log('error', 'Integrity > getHeaders | Fatal', $e->getMessage());

            return self::degraded('fatal');
        }
    }

    /**
     * Build the attested headers for an already fetched challenge.
     *
     * Split from getHeaders() so the successful path is reachable without a
     * network call: it is the branch that actually has to produce the right
     * bytes, and testing only the degraded path would leave it unverified.
     *
     * @param array $challenge From \Mobbex\Integrity\Challenge::fetch().
     *
     * @return string[]
     *
     * @throws \Mobbex\Exception When the nonce is not valid hex.
     */
    public static function headersFor($challenge)
    {
        $mac = Mac::computeSpec(
            self::PROTOCOL_PREFIX,
            $challenge['nonce'],
            self::$platform,
            self::$version,
            $challenge['files'],
            self::$rootDir
        );

        $global = Mac::computeGlobal(
            self::PROTOCOL_PREFIX,
            $challenge['nonce'],
            self::$platform,
            self::$version,
            $challenge['globalFiles'],
            self::$rootDir,
            microtime(true) + self::GLOBAL_BUDGET
        );

        $headers = [
            'x-integrity-mode: challenge',
            'x-integrity-challenge: ' . self::clean($challenge['id']),
            'x-integrity-mac: ' . $mac,
            'x-integrity-platform: ' . self::clean(self::$platform),
            'x-integrity-version: ' . self::clean(self::$version),
        ];

        // Already normalized and validated, so it never goes through clean():
        // stripping bytes out of a URL would silently produce a different one.
        $shopUrl = self::shopUrl();

        if ($shopUrl !== null)
            $headers[] = 'x-integrity-url: ' . $shopUrl;

        if ($global === null) {
            // Documented degradation: send the spec MAC alone and let the server
            // record global:missing. Slow disk is not tampering.
            \Mobbex\Platform::log('error', 'Integrity > headersFor | Global sum exceeded its budget');

            return $headers;
        }

        $headers[] = 'x-integrity-global: ' . $global['global'];

        return $headers;
    }

    /**
     * Checkout page URL for this shop, resolved at most once per request.
     *
     * Resolving once is not an optimization, it is what keeps the class's whole
     * invariant intact. degraded() is called from the catch block of
     * getHeaders(); if the callable is what threw, resolving again in there
     * would throw inside an exception handler and propagate out — breaking the
     * payment, which is the one thing that may never happen. The failure is
     * cached too, which is why there is a separate flag: null is a valid result.
     *
     * @return string|null Normalized URL, or null when there is none to send.
     */
    protected static function shopUrl()
    {
        if (self::$urlResolved)
            return self::$resolvedUrl;

        self::$urlResolved = true;
        self::$resolvedUrl = null;

        try {
            $raw = self::$shopUrl;

            // is_callable() FIRST, then string. The natural WooCommerce value is
            // 'wc_get_checkout_url', which is both — checked the other way round
            // the header would carry that literal text instead of a URL. No URL
            // can look like a callable: a PHP function name holds neither ':'
            // nor '/'.
            if (is_callable($raw))
                $raw = call_user_func($raw);

            // Nothing configured, or a callable that could not work it out (no
            // context yet, a cron, the back office) falls back to the shop host,
            // which is better than nothing. A value that is present but invalid
            // does NOT fall back: "I don't know" and "here is a bad URL" deserve
            // different answers, and the second one is dropped.
            //
            // The scheme is added only here, because Platform::$domain is a bare
            // host. Anything the plugin supplies must carry its own scheme —
            // otherwise a stray word like 'checkout' would become
            // https://checkout, which FILTER_VALIDATE_URL happily accepts.
            if (!is_string($raw) || $raw === '')
                $raw = 'https://' . \Mobbex\Platform::$domain;

            self::$resolvedUrl = self::normalizeUrl($raw);
        } catch (\Exception $e) {
            \Mobbex\Platform::log('debug', 'Integrity > shopUrl | Could not resolve', $e->getMessage());
        } catch (\Throwable $e) {
            // PHP 7+ only; not defined on 5.6, where this never matches.
            \Mobbex\Platform::log('debug', 'Integrity > shopUrl | Fatal resolving', $e->getMessage());
        }

        return self::$resolvedUrl;
    }

    /**
     * Normalize a shop URL to scheme + host + path, or reject it.
     *
     * Rejects rather than repairs, throughout. A mangled URL is worse than no
     * URL: stripping the non-ASCII from https://tiendañ.com/pedido yields
     * https://tienda.com/pedido, which is valid, plausible and a different real
     * site — the crawler would go and inspect an innocent third party. Same
     * reasoning for the length cap: truncating produces a valid-looking wrong
     * path, so it is dropped instead.
     *
     * Encoding it properly would need idn_to_ascii, which depends on ext/intl
     * and is not guaranteed on a merchant's host. This is telemetry, so a wrong
     * URL costs more than a missing one.
     *
     * @param mixed $url
     *
     * @return string|null
     */
    protected static function normalizeUrl($url)
    {
        if (!is_string($url) || $url === '')
            return null;

        // Refuse anything outside printable ASCII instead of stripping it. This
        // is also what rules out the CR and LF that would inject headers.
        if (preg_match('/[^\x20-\x7E]/', $url))
            return null;

        $parts = @parse_url($url);

        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host']))
            return null;

        $scheme = strtolower($parts['scheme']);

        // Keeps javascript: and file:///etc/passwd out of our logs and database.
        // Necessary, not sufficient: http://169.254.169.254/ passes this and is
        // stopped by the crawler's SSRF guard, not here.
        if ($scheme !== 'http' && $scheme !== 'https')
            return null;

        // Query string and fragment are dropped: they carry cart and session
        // tokens we neither need nor want to store. The port is kept — dropping
        // it would point the crawler at a different service.
        $normalized = $scheme . '://' . $parts['host']
            . (isset($parts['port']) ? ':' . $parts['port'] : '')
            . (isset($parts['path']) ? $parts['path'] : '');

        if (strlen($normalized) > self::MAX_URL_LENGTH)
            return null;

        return filter_var($normalized, FILTER_VALIDATE_URL) === false ? null : $normalized;
    }

    /**
     * Whether this request should carry an attestation.
     *
     * Only checkout creation. Attesting every call would multiply the cost for
     * no gain, and the challenge request itself must never be attested.
     *
     * @param array $data
     *
     * @return bool
     */
    protected static function attests($data)
    {
        if (empty(self::$platform) || empty(self::$rootDir) || empty(self::$version))
            return false;

        return !empty($data['uri']) && strpos($data['uri'], 'checkout') !== false;
    }

    /**
     * Degraded headers, used when no challenge could be obtained.
     *
     * The server reads mode "static" as "let it through and count it as
     * degraded". No checksum travels: a precomputable digest is exactly what
     * this protocol replaces, and the server verifies the challenge values.
     *
     * @param string $reason
     *
     * @return string[]
     */
    protected static function degraded($reason)
    {
        $headers = [
            'x-integrity-mode: static',
            'x-integrity-platform: ' . self::clean(self::$platform),
            'x-integrity-version: ' . self::clean(self::$version),
            'x-integrity-error: ' . self::clean($reason),
        ];

        // Sent here too, and it matters most here: a shop that degrades
        // persistently is the one we most want to go and look at. Safe to call
        // from the catch block because the resolution is cached, failure
        // included — see shopUrl().
        $shopUrl = self::shopUrl();

        if ($shopUrl !== null)
            $headers[] = 'x-integrity-url: ' . $shopUrl;

        return $headers;
    }

    /**
     * Make a value safe to place in a header.
     *
     * The challenge id comes straight from the API response, so it reaches a
     * header without ever being inspected. Stripping everything outside
     * printable ASCII removes the CR and LF that would let a crafted response
     * inject headers of its own.
     *
     * @param string $value
     *
     * @return string
     */
    protected static function clean($value)
    {
        return preg_replace('/[^\x20-\x7E]/', '', (string) $value);
    }
}
