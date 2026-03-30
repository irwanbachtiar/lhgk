<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Debug KPI Query ===\n\n";

$conn = DB::connection('dashboard_phinnisi');
$table = 'trafik';

// Determine table to use
try {
    $schema = $conn->getSchemaBuilder();
    if (! $schema->hasTable('trafik')) {
        if ($schema->hasTable('trafik_rkap_realisasi')) {
            $table = 'trafik_rkap_realisasi';
        } elseif ($schema->hasTable('trafik_rekap_realisasi')) {
            $table = 'trafik_rekap_realisasi';
        }
    }
} catch (Exception $e) {
}

echo "Using table: $table\n\n";

// Test KPI query
$selectedPeriode = '01-2026';
$selectedWilayah = 'all';

echo "Filter: periode=$selectedPeriode, wilayah=$selectedWilayah\n\n";

$kpiQuery = $conn->table($table);
if ($selectedWilayah != 'all') {
    $kpiQuery->where('wilayah', $selectedWilayah);
}
if ($selectedPeriode != 'all') {
    $kpiQuery->where('periode', $selectedPeriode);
}

try {
    echo "Running query...\n";
    $kpiTotals = $kpiQuery->selectRaw('SUM(`Call`) as total_call, SUM(`GT`) as total_gt')->first();
    echo "Result:\n";
    echo "  total_call: " . ($kpiTotals->total_call ?? 'NULL') . "\n";
    echo "  total_gt: " . ($kpiTotals->total_gt ?? 'NULL') . "\n";
    
    $totalCall = $kpiTotals->total_call ?? 0;
    $totalGt = $kpiTotals->total_gt ?? 0;
    
    echo "\nFinal KPI values:\n";
    echo "  totalCall: " . number_format($totalCall) . "\n";
    echo "  totalGt: " . number_format($totalGt) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nDone.\n";
