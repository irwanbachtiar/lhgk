<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Testing NEW Comparison Query ===\n\n";

$table = 'trafik_rkap_realisasi';
$periode = '01-2026';

echo "Table: $table\n";
echo "Periode: $periode\n\n";

// Test 1: Get all data for period
echo "1. All data for periode $periode:\n";
try {
    $totalCall = DB::connection('dashboard_phinnisi')
        ->table($table)
        ->where('periode', $periode)
        ->sum('Call');
    $totalGt = DB::connection('dashboard_phinnisi')
        ->table($table)
        ->where('periode', $periode)
        ->sum('GT');
    
    echo "   Total Call: " . number_format($totalCall) . "\n";
    echo "   Total GT: " . number_format($totalGt) . "\n";
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// Test 2: Get REALISASI data
echo "\n2. REALISASI data for periode $periode:\n";
try {
    $realCall = DB::connection('dashboard_phinnisi')
        ->table($table)
        ->where('periode', $periode)
        ->where('jenis', 'REALISASI')
        ->sum('Call');
    $realGt = DB::connection('dashboard_phinnisi')
        ->table($table)
        ->where('periode', $periode)
        ->where('jenis', 'REALISASI')
        ->sum('GT');
    
    echo "   Realisasi Call: " . number_format($realCall) . "\n";
    echo "   Realisasi GT: " . number_format($realGt) . "\n";
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// Test 3: Get ANGGARAN data
echo "\n3. ANGGARAN data for periode $periode:\n";
try {
    $budgetCall = DB::connection('dashboard_phinnisi')
        ->table($table)
        ->where('periode', $periode)
        ->where('jenis', 'ANGGARAN')
        ->sum('Call');
    $budgetGt = DB::connection('dashboard_phinnisi')
        ->table($table)
        ->where('periode', $periode)
        ->where('jenis', 'ANGGARAN')
        ->sum('GT');
    
    echo "   Anggaran Call: " . number_format($budgetCall) . "\n";
    echo "   Anggaran GT: " . number_format($budgetGt) . "\n";
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// Test 4: Calculate variance
echo "\n4. Variance Analysis:\n";
if (isset($realCall) && isset($budgetCall)) {
    $callVariance = $realCall - $budgetCall;
    $gtVariance = $realGt - $budgetGt;
    
    $callPercent = $budgetCall > 0 ? round(($callVariance / $budgetCall) * 100, 2) : 0;
    $gtPercent = $budgetGt > 0 ? round(($gtVariance / $budgetGt) * 100, 2) : 0;
    
    echo "   Call Variance: " . number_format($callVariance) . " ($callPercent%)\n";
    echo "   GT Variance: " . number_format($gtVariance) . " ($gtPercent%)\n";
}

// Test 5: Group by jenis to verify data structure
echo "\n5. Grouping by jenis:\n";
try {
    $grouped = DB::connection('dashboard_phinnisi')
        ->table($table)
        ->select('jenis', DB::raw('SUM(`Call`) as total_call'), DB::raw('SUM(`GT`) as total_gt'), DB::raw('COUNT(*) as row_count'))
        ->where('periode', $periode)
        ->groupBy('jenis')
        ->get();
    
    foreach ($grouped as $row) {
        echo "   Jenis: {$row->jenis}\n";
        echo "      Total Call: " . number_format($row->total_call) . "\n";
        echo "      Total GT: " . number_format($row->total_gt) . "\n";
        echo "      Row Count: {$row->row_count}\n\n";
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
