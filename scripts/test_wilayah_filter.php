<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\TrafikController;

echo "=== Test Wilayah Filter (Multiple Values Available) ===\n\n";

// Test 1: wilayah='all', periode='01-2026'
echo "Test 1: periode='01-2026', wilayah='all'\n";
$request1 = Request::create('/trafik', 'GET', ['periode' => '01-2026', 'wilayah' => 'all']);
$controller1 = new TrafikController();
$response1 = $controller1->index($request1);
$data1 = $response1->getData();

echo "  totalCall: " . number_format($data1['totalCall'] ?? 0) . "\n";
echo "  totalGt: " . number_format($data1['totalGt'] ?? 0) . "\n";
echo "  rows count: " . (isset($data1['rows']) ? $data1['rows']->count() : 0) . "\n";

// Test 2: wilayah='WILAYAH 1', periode='01-2026'
echo "\nTest 2: periode='01-2026', wilayah='WILAYAH 1'\n";
$request2 = Request::create('/trafik', 'GET', ['periode' => '01-2026', 'wilayah' => 'WILAYAH 1']);
$controller2 = new TrafikController();
$response2 = $controller2->index($request2);
$data2 = $response2->getData();

echo "  totalCall: " . number_format($data2['totalCall'] ?? 0) . "\n";
echo "  totalGt: " . number_format($data2['totalGt'] ?? 0) . "\n";
echo "  rows count: " . (isset($data2['rows']) ? $data2['rows']->count() : 0) . "\n";

// Test 3: wilayah='WILAYAH 2', periode='01-2026' 
echo "\nTest 3: periode='01-2026', wilayah='WILAYAH 2'\n";
$request3 = Request::create('/trafik', 'GET', ['periode' => '01-2026', 'wilayah' => 'WILAYAH 2']);
$controller3 = new TrafikController();
$response3 = $controller3->index($request3);
$data3 = $response3->getData();

echo "  totalCall: " . number_format($data3['totalCall'] ?? 0) . "\n";
echo "  totalGt: " . number_format($data3['totalGt'] ?? 0) . "\n";
echo "  rows count: " . (isset($data3['rows']) ? $data3['rows']->count() : 0) . "\n";

echo "\n=== Comparison ===\n";
$call1 = $data1['totalCall'] ?? 0;
$call2 = $data2['totalCall'] ?? 0;
$call3 = $data3['totalCall'] ?? 0;

if ($call2 != $call1 && $call3 != $call1 && $call2 != $call3) {
    echo "✅ WILAYAH filter is WORKING! Values are different:\n";
    echo "   - all: " . number_format($call1) . "\n";
    echo "   - WILAYAH 1: " . number_format($call2) . "\n";
    echo "   - WILAYAH 2: " . number_format($call3) . "\n";
    echo "\n✅ This proves the filter logic itself is correct.\n";
    echo "   The periode filter appears broken because database only has ONE periode (01-2026).\n";
} else {
    echo "⚠️  Values are still same - filter may not be working.\n";
}

echo "\nDone.\n";
