<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/summary', 'GET');
$response = $kernel->handle($request);
$status = $response->getStatusCode();
echo "HTTP Status: $status\n";
$content = $response->getContent();
if ($status !== 200) {
    echo substr($content, 0, 3000);
} else {
    // Cari apakah ada data yang dirender
    if (strpos($content, 'Rp 45') !== false) {
        echo "✓ Data marine (Rp 45 miliar) MUNCUL di HTML\n";
    } else {
        echo "✗ Data marine TIDAK muncul\n";
    }
    if (strpos($content, '45,600,000,000') !== false || strpos($content, '45.600.000.000') !== false) {
        echo "✓ Angka 45.600.000.000 ditemukan\n";
    } else {
        echo "✗ Angka 45.600.000.000 tidak ditemukan\n";
    }
    // Ambil bagian HTML yang relevan
    preg_match('/Total Marine.*?<\/div>/s', $content, $m);
    if ($m) echo "Sample: " . substr($m[0], 0, 200) . "\n";
    echo "Page size: " . strlen($content) . " bytes\n";
}
