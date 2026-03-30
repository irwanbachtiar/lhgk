<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/summary', 'GET');
$response = $kernel->handle($request);
$html = $response->getContent();

// Cek apakah JSON mengandung scientific notation (bug angka besar)
if (preg_match('/\d+e\+\d+/i', $html)) {
    echo "⚠ WARNING: Ada scientific notation di HTML (e+10 etc) - masalah angka besar!\n";
} else {
    echo "✓ Tidak ada scientific notation\n";
}

// Tampilkan JSON wilayahData
preg_match('/const wilayahData = (.+?);[\r\n]/s', $html, $m);
if ($m) echo "wilayahData: " . substr($m[1], 0, 500) . "\n\n";

// Tampilkan JSON summary
preg_match('/var summary = (.+?);[\r\n]/s', $html, $m2);
if ($m2) echo "summary JSON: " . substr($m2[1], 0, 500) . "\n\n";

// Cek segmentWilayahData marine
preg_match('/const marineWilayah = (.+?);[\r\n]/s', $html, $m3);
if ($m3) echo "marineWilayah: " . substr($m3[1], 0, 500) . "\n\n";

// Cek equipment JSON
preg_match('/const equipmentDataObj = (.+?);[\r\n]/s', $html, $m4);
if ($m4) echo "equipmentDataObj: " . substr($m4[1], 0, 500) . "\n\n";

// Cek apakah ada error PHP di output
if (strpos($html, 'ErrorException') !== false || strpos($html, 'ParseError') !== false) {
    echo "⚠ Ada PHP error di HTML!\n";
    preg_match('/(ErrorException|ParseError).{0,500}/s', $html, $err);
    if ($err) echo strip_tags($err[0]) . "\n";
} else {
    echo "✓ Tidak ada PHP error di HTML\n";
}
