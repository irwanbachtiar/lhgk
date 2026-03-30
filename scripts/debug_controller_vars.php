<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\TrafikController;

echo "=== Debug Controller Variables ===\n\n";

// Test 1: periode='all', wilayah='all'
echo "Test 1: periode='all', wilayah='all'\n";
$request1 = Request::create('/trafik', 'GET', ['periode' => 'all', 'wilayah' => 'all']);
$controller1 = new TrafikController();
$response1 = $controller1->index($request1);
$data1 = $response1->getData();

echo "  totalCall: " . ($data1['totalCall'] ?? 'NOT SET') . "\n";
echo "  totalGt: " . ($data1['totalGt'] ?? 'NOT SET') . "\n";
echo "  rows count: " . (isset($data1['rows']) ? $data1['rows']->count() : 'NOT SET') . "\n";

// Test 2: periode='01-2026', wilayah='all'
echo "\nTest 2: periode='01-2026', wilayah='all'\n";
$request2 = Request::create('/trafik', 'GET', ['periode' => '01-2026', 'wilayah' => 'all']);
$controller2 = new TrafikController();
$response2 = $controller2->index($request2);
$data2 = $response2->getData();

echo "  totalCall: " . ($data2['totalCall'] ?? 'NOT SET') . "\n";
echo "  totalGt: " . ($data2['totalGt'] ?? 'NOT SET') . "\n";
echo "  rows count: " . (isset($data2['rows']) ? $data2['rows']->count() : 'NOT SET') . "\n";

echo "\n=== Comparison ===\n";
if (($data1['totalCall'] ?? 0) == ($data2['totalCall'] ?? 0)) {
    echo "⚠️  WARNING: totalCall is SAME for both tests!\n";
    echo "    This means periode filter is NOT working in controller.\n";
} else {
    echo "✅ totalCall is DIFFERENT - periode filter is working!\n";
}

echo "\nDone.\n";
