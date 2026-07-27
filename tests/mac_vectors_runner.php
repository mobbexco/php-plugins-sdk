<?php

/**
 * Golden vector runner for the integrity checks.
 *
 * Runs \Mobbex\Integrity\Mac against the cross-language contract in
 * mac_vectors.json, the same file the verification service's Go test consumes. The vectors were
 * produced by an independent reference implementation in Python written from the
 * spec text, so agreement means the protocol is implementable without ambiguity,
 * not just that two ports of the same code agree.
 *
 * This is the check that matters: if these values diverge from Go's, every
 * merchant breaks at once and the symptom is indistinguishable from a real
 * tamper.
 *
 * Deliberately dependency free, unlike the PHPUnit suite. PHPUnit 9 needs PHP
 * 7.3+, but plugins run on PHP 5.6 — and the 32 bit integer path is exactly
 * where a divergence would hide. This runner covers those legs.
 *
 * Usage: php tests/mac_vectors_runner.php
 */

require_once __DIR__ . '/../src/Exception.php';
require_once __DIR__ . '/../src/Integrity/Mac.php';

use Mobbex\Integrity\Mac;

/**
 * Materialize a vector's "disk" map into a fresh temporary directory.
 *
 * A path absent from the map is a file that does not exist, which is a case the
 * protocol has to handle, so absence is represented by absence.
 *
 * @param array $disk Map of relative path => base64 content.
 *
 * @return string Root directory.
 */
function mbxMakeTree($disk)
{
    $root = sys_get_temp_dir() . '/mbx-mac-' . uniqid('', true);

    mkdir($root, 0777, true);

    foreach ($disk as $path => $base64) {
        $target = $root . '/' . $path;
        $dir    = dirname($target);

        if (!is_dir($dir))
            mkdir($dir, 0777, true);

        file_put_contents($target, base64_decode($base64));
    }

    return $root;
}

/**
 * Remove a directory tree.
 *
 * @param string $path
 *
 * @return void
 */
function mbxRemoveTree($path)
{
    if (!is_dir($path))
        return;

    foreach (scandir($path) as $entry) {
        if ($entry === '.' || $entry === '..')
            continue;

        $child = $path . '/' . $entry;

        is_dir($child) ? mbxRemoveTree($child) : unlink($child);
    }

    rmdir($path);
}

/**
 * uint64 big endian for the oracle. Not pack('J'), which does not exist on 32
 * bit PHP — the very build this runner also has to pass on. Every value used
 * here is far below 2^32, so the high word is always zero.
 *
 * @param int $number
 *
 * @return string 8 bytes.
 */
function mbxTestUint64BE($number)
{
    return pack('NN', 0, $number);
}

/**
 * Obviously correct, obviously slow. Independent of the streaming logic.
 *
 * @param string $path
 * @param int    $offset
 * @param int    $length 0 means to end of file.
 *
 * @return string
 */
function mbxNaiveRead($path, $offset, $length)
{
    if (!is_file($path) || !is_readable($path))
        return '';

    $content = file_get_contents($path);

    if ($content === false || $offset >= strlen($content))
        return '';

    return $length > 0 ? substr($content, $offset, $length) : substr($content, $offset);
}

$file = __DIR__ . '/mac_vectors.json';
$data = is_file($file) ? json_decode(file_get_contents($file), true) : null;

if (empty($data['vectors'])) {
    fwrite(STDERR, "could not read vectors from $file\n");
    exit(1);
}

$failures = 0;

echo 'PHP ' . PHP_VERSION . ' (' . (PHP_INT_SIZE * 8) . " bit)\n";
echo count($data['vectors']) . " vectors\n\n";

foreach ($data['vectors'] as $vector) {
    $root = mbxMakeTree($vector['disk']);

    // For the global check, "disk" doubles as the manifest: the global sum
    // covers exactly those paths. On a real install this list arrives as
    // global.files in the challenge response — never a directory walk.
    $globalFiles = array_keys($vector['disk']);

    try {
        $got = [
            'mac' => Mac::computeSpec(
                $vector['prefix'],
                $vector['nonce'],
                $vector['platform'],
                $vector['version'],
                $vector['spec'],
                $root
            ),
        ];

        $got += Mac::computeGlobal(
            $vector['prefix'],
            $vector['nonce'],
            $vector['platform'],
            $vector['version'],
            $globalFiles,
            $root
        );
    } catch (\Exception $e) {
        $got = ['mac' => 'EXCEPTION: ' . $e->getMessage()];
    }

    mbxRemoveTree($root);

    $wrong = [];

    foreach (['mac', 'content_digest', 'global'] as $what) {
        $actual = isset($got[$what]) ? $got[$what] : '(missing)';

        if ($actual !== $vector[$what])
            $wrong[$what] = $actual;
    }

    if (!$wrong) {
        echo '  ok    ' . str_pad($vector['name'], 32) . " mac+content_digest+global\n";
        continue;
    }

    $failures++;

    echo '  FAIL  ' . $vector['name'] . "\n";
    echo '        why: ' . $vector['why'] . "\n";

    foreach ($wrong as $what => $actual) {
        echo '        ' . str_pad($what, 15) . ' expected ' . $vector[$what] . "\n";
        echo '        ' . str_pad('', 15) . ' got      ' . $actual . "\n";
    }
}

echo "\n";

// The 32 bit branch of uint64BE is the one CI never reaches: every runner is 64
// bit, so pack('J') always wins and the hand rolled fallback ships unexercised.
// A wrong byte there would only surface on a merchant's 32 bit host, as a
// mismatch reported as tampering.
//
// Expected encodings are written out literally rather than derived from
// pack('J'): that function does not exist on 32 bit PHP, and an oracle calling
// the same builtin the code calls proves nothing.
echo "uint64BE fallback (32 bit branch)\n";

$encode = new ReflectionMethod('Mobbex\Integrity\Mac', 'uint64BE');

// Required up to PHP 8.0, a no-op after 8.1 and deprecated from 8.5.
if (PHP_VERSION_ID < 80100)
    $encode->setAccessible(true);

$values = [
    [0,             '0000000000000000'],
    [1,             '0000000000000001'],
    [255,           '00000000000000ff'],
    [256,           '0000000000000100'],
    [65535,         '000000000000ffff'],
    [2147483647,    '000000007fffffff'],   // 2^31 - 1, where pack('N') starts to matter
    [2147483648,    '0000000080000000'],
    [4294967295,    '00000000ffffffff'],   // 2^32 - 1
    [4294967296,    '0000000100000000'],   // 2^32, the golden vector offset
    [8589934592,    '0000000200000000'],   // 2^33, the golden vector length
    [1099511627776, '0000010000000000'],   // 2^40
];

foreach ($values as $case) {
    list($value, $expected) = $case;

    $shipped = bin2hex($encode->invoke(null, $value));

    if ($shipped === $expected) {
        echo '  ok    ' . $value . ' => ' . $expected . "\n";
        continue;
    }

    $failures++;

    echo '  FAIL  ' . $value . "\n";
    echo '        expected: ' . $expected . "\n";
    echo '        got:      ' . $shipped . "\n";
}

echo "\n";

// Regression: a spec may legitimately request a length far larger than the file.
// fread() sizes its buffer from the REQUESTED length, so reading the range
// naively allocates that much — 8 GiB here — and blows memory_limit. In PHP that
// is a fatal error, not an exception: no try/catch recovers, and the request
// dies. The read must be clamped to the file before any allocation.
//
// The golden vectors do not cover this: their large-length case also has an
// offset past EOF, so it returns before reading anything.
echo "huge requested length does not exhaust memory\n";

$previousLimit = ini_get('memory_limit');
ini_set('memory_limit', '32M');

$root    = mbxMakeTree(['views/js/front.js' => base64_encode("window.mobbex=function(){return 1};\n")]);
$nonce   = '00112233445566778899aabbccddeeff00112233445566778899aabbccddeeff';
$hugeLen = 8589934592;   // 2^33

// Independent oracle: the message the protocol defines, built by hand. The
// uint64 carries the REQUESTED length while the bytes are only what the file
// holds — that asymmetry is the rule being verified.
$expectedMac = hash_hmac(
    'sha256',
    "MBX-ATTEST-v1\nprestashop\n5.1.0\n"
        . 'views/js/front.js' . "\0"
        . pack('H*', '0000000000000000')      // uint64BE(0)
        . pack('H*', '0000000200000000')      // uint64BE(2^33)
        . "window.mobbex=function(){return 1};\n",
    pack('H*', $nonce)
);

$actualMac = Mac::computeSpec(
    'MBX-ATTEST-v1',
    $nonce,
    'prestashop',
    '5.1.0',
    [['p' => 'views/js/front.js', 'o' => 0, 'l' => $hugeLen]],
    $root
);

mbxRemoveTree($root);
ini_set('memory_limit', $previousLimit);

if ($actualMac === $expectedMac) {
    echo '  ok    l=' . $hugeLen . " over a 36 byte file, under a 32M limit\n";
} else {
    $failures++;

    echo "  FAIL  huge length regression\n";
    echo '        expected: ' . $expectedMac . "\n";
    echo '        got:      ' . $actualMac . "\n";
}

echo "\n";

// Differential test over a synthetic tree built to cross chunk boundaries.
//
// Every golden vector uses files of a few dozen bytes, so the 64 KiB chunking
// loop never crosses a boundary and the shared contract cannot catch an
// off-by-one there. Neither can this repository's own source: its largest file
// is around 11 KB. So the tree is generated — one file spanning four chunks,
// binary content including NUL bytes, and a UTF-8 path — and the shipped
// streaming code is required to agree with a naive read-it-all oracle.
echo "differential vs naive oracle, over a synthetic multi-chunk tree\n";

$big     = 'assets/big.bin';
$bigSize = 4 * Mac::CHUNK_SIZE + 1234;   // deliberately not a chunk multiple

// Deterministic pseudo-binary content: every byte value appears, including NUL.
$bigContent = '';

for ($i = 0; $i < $bigSize; $i++)
    $bigContent .= chr(($i * 31 + ($i >> 8)) % 256);

$disk = [
    $big                              => base64_encode($bigContent),
    'small.txt'                       => base64_encode("tiny\n"),
    'empty.txt'                       => base64_encode(''),
    'nested/deep/ñandú.tpl'           => base64_encode("<div>ñandú</div>\n"),
    'exactly-one-chunk.bin'           => base64_encode(str_repeat('A', Mac::CHUNK_SIZE)),
];

$treeRoot  = mbxMakeTree($disk);
$realPaths = array_keys($disk);

sort($realPaths, SORT_STRING);

$spec = [
    ['p' => $big,                    'o' => 0,      'l' => 0],        // whole file, four chunks
    ['p' => $big,                    'o' => 65530,  'l' => 20],       // straddles the first boundary
    ['p' => $big,                    'o' => 131072, 'l' => 200],      // starts exactly on a boundary
    ['p' => $big,                    'o' => 0,      'l' => 65536],    // exactly one chunk
    ['p' => 'exactly-one-chunk.bin', 'o' => 0,      'l' => 0],        // file length == chunk size
    ['p' => 'empty.txt',             'o' => 0,      'l' => 0],        // zero bytes
    ['p' => 'nested/deep/ñandú.tpl', 'o' => 3,      'l' => 5],        // UTF-8 path, partial
    ['p' => $big,                    'o' => 10,     'l' => 999999],   // overruns EOF
    ['p' => 'no/such/file.php',      'o' => 0,      'l' => 100],      // missing
];

$msg = "MBX-ATTEST-v1\nprestashop\n5.1.0\n";

foreach ($spec as $f) {
    $msg .= $f['p'] . "\0"
        . mbxTestUint64BE($f['o'])
        . mbxTestUint64BE($f['l'])
        . mbxNaiveRead($treeRoot . '/' . $f['p'], $f['o'], $f['l']);
}

$naiveMac  = hash_hmac('sha256', $msg, pack('H*', $nonce));
$digestMsg = '';

foreach ($realPaths as $path) {
    $content    = mbxNaiveRead($treeRoot . '/' . $path, 0, 0);
    $digestMsg .= $path . "\0" . mbxTestUint64BE(strlen($content)) . $content;
}

$naiveDigest = hash('sha256', $digestMsg, true);
$naiveGlobal = hash_hmac(
    'sha256',
    "MBX-ATTEST-v1\nGLOBAL\nprestashop\n5.1.0\n" . $naiveDigest,
    pack('H*', $nonce)
);

$shippedMac    = Mac::computeSpec('MBX-ATTEST-v1', $nonce, 'prestashop', '5.1.0', $spec, $treeRoot);
$shippedGlobal = Mac::computeGlobal('MBX-ATTEST-v1', $nonce, 'prestashop', '5.1.0', $realPaths, $treeRoot);

$comparisons = [
    'spec mac'       => [$shippedMac, $naiveMac],
    'content_digest' => [$shippedGlobal['content_digest'], bin2hex($naiveDigest)],
    'global'         => [$shippedGlobal['global'], $naiveGlobal],
];

foreach ($comparisons as $what => $pair) {
    if ($pair[0] === $pair[1]) {
        echo '  ok    ' . str_pad($what, 15) . ' ' . $pair[0] . "\n";
        continue;
    }

    $failures++;

    echo '  FAIL  ' . $what . "\n";
    echo '        shipped: ' . $pair[0] . "\n";
    echo '        naive:   ' . $pair[1] . "\n";
}

// The deadline is what keeps slow disk from stalling a payment. It must abort
// rather than throw, and must not change the result when it is not hit.
$expired = Mac::computeGlobal('MBX-ATTEST-v1', $nonce, 'prestashop', '5.1.0', $realPaths, $treeRoot, microtime(true) - 1);

if ($expired === null) {
    echo "  ok    expired deadline aborts and returns null\n";
} else {
    $failures++;

    echo "  FAIL  expired deadline should return null\n";
}

mbxRemoveTree($treeRoot);

echo '  (' . count($realPaths) . ' files, largest ' . $big . " at $bigSize bytes = "
    . round($bigSize / Mac::CHUNK_SIZE, 2) . " chunks)\n\n";

if ($failures > 0) {
    echo "$failures check(s) FAILED\n";
    exit(1);
}

echo 'all ' . count($data['vectors']) . ' vectors (mac + content_digest + global), '
    . count($values) . " encodings, the memory regression and the differential pass\n";
exit(0);
