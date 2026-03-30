<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\TrafikController;

$periode = '01-2026';
$wilayah = 'all';

$request = Request::create('/trafik', 'GET', ['periode' => $periode, 'wilayah' => $wilayah]);

$controller = new TrafikController();
$response = $controller->index($request);

// Get view data
$viewData = $response->getData();

echo "=== Debug TrafikData ===\n\n";
echo "trafikData exists: " . (isset($viewData['trafikData']) ? 'YES' : 'NO') . "\n";

if (isset($viewData['trafikData'])) {
    $trafikData = $viewData['trafikData'];
    echo "trafikData type: " . gettype($trafikData) . "\n";
    echo "trafikData count: " . (is_array($trafikData) ? count($trafikData) : 'N/A') . "\n";
    
    if (is_array($trafikData) && count($trafikData) > 0) {
        echo "\nWilayah keys:\n";
        foreach (array_keys($trafikData) as $wil) {
            echo "  - $wil\n";
            if (isset($trafikData[$wil]['dalam_negeri']['locations'])) {
                $dnCount = count($trafikData[$wil]['dalam_negeri']['locations']);
                echo "    Dalam Negeri locations: $dnCount\n";
            }
            if (isset($trafikData[$wil]['luar_negeri']['locations'])) {
                $lnCount = count($trafikData[$wil]['luar_negeri']['locations']);
                echo "    Luar Negeri locations: $lnCount\n";
            }
        }
        
        echo "\nSample data (first wilayah):\n";
        $firstWil = array_key_first($trafikData);
        echo json_encode($trafikData[$firstWil], JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "trafikData is EMPTY\n";
    }
}

echo "\nOther variables:\n";
echo "totalCall: " . ($viewData['totalCall'] ?? 'not set') . "\n";
echo "totalGt: " . ($viewData['totalGt'] ?? 'not set') . "\n";
echo "selectedPeriode: " . ($viewData['selectedPeriode'] ?? 'not set') . "\n";
echo "selectedWilayah: " . ($viewData['selectedWilayah'] ?? 'not set') . "\n";
