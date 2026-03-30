<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Comprehensive Periode Filter Test ===\n\n";

$conn = DB::connection('dashboard_phinnisi');
$table = 'trafik';

// Determine table
try {
    $schema = $conn->getSchemaBuilder();
    if (! $schema->hasTable('trafik')) {
        if ($schema->hasTable('trafik_rkap_realisasi')) {
            $table = 'trafik_rkap_realisasi';
        }
    }
} catch (Exception $e) {
}

echo "Using table: $table\n\n";

// 1. Check all available periods
echo "1. Available Periods:\n";
try {
    $periods = $conn->table($table)
        ->select('periode')
        ->groupBy('periode')
        ->orderBy('periode', 'desc')
        ->pluck('periode')
        ->toArray();
    
    foreach ($periods as $p) {
        $count = $conn->table($table)->where('periode', $p)->count();
        $callSum = $conn->table($table)->where('periode', $p)->sum('`Call`');
        echo "   - $p: $count rows, Call sum: " . number_format($callSum) . "\n";
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n2. Test Filter Logic:\n";

// Test with periode='all' (should return all)
echo "\n   Test A: periode='all', wilayah='all'\n";
try {
    $q = $conn->table($table);
    // Mimic controller logic
    $selectedPeriode = 'all';
    $selectedWilayah = 'all';
    
    if ($selectedPeriode != 'all' || $selectedWilayah != 'all') {
        if ($selectedWilayah != 'all') {
            $q->where('wilayah', $selectedWilayah);
        }
        if ($selectedPeriode != 'all') {
            $q->where('periode', $selectedPeriode);
        }
        $count = $q->count();
        $callSum = $q->sum('`Call`');
        echo "      Result: $count rows, Call sum: " . number_format($callSum) . "\n";
    } else {
        echo "      Result: No query executed (both filters are 'all')\n";
    }
} catch (Exception $e) {
    echo "      Error: " . $e->getMessage() . "\n";
}

// Test with specific periode
echo "\n   Test B: periode='01-2026', wilayah='all'\n";
try {
    $q = $conn->table($table);
    $selectedPeriode = '01-2026';
    $selectedWilayah = 'all';
    
    if ($selectedPeriode != 'all' || $selectedWilayah != 'all') {
        if ($selectedWilayah != 'all') {
            $q->where('wilayah', $selectedWilayah);
        }
        if ($selectedPeriode != 'all') {
            $q->where('periode', $selectedPeriode);
        }
        $count = $q->count();
        $callSum = $q->sum('`Call`');
        echo "      Result: $count rows, Call sum: " . number_format($callSum) . "\n";
    }
} catch (Exception $e) {
    echo "      Error: " . $e->getMessage() . "\n";
}

// Test with different periode if available
if (count($periods) > 1) {
    $periode2 = $periods[1];
    echo "\n   Test C: periode='$periode2', wilayah='all'\n";
    try {
        $q = $conn->table($table);
        $selectedPeriode = $periode2;
        $selectedWilayah = 'all';
        
        if ($selectedPeriode != 'all' || $selectedWilayah != 'all') {
            if ($selectedWilayah != 'all') {
                $q->where('wilayah', $selectedWilayah);
            }
            if ($selectedPeriode != 'all') {
                $q->where('periode', $selectedPeriode);
            }
            $count = $q->count();
            $callSum = $q->sum('`Call`');
            echo "      Result: $count rows, Call sum: " . number_format($callSum) . "\n";
        }
    } catch (Exception $e) {
        echo "      Error: " . $e->getMessage() . "\n";
    }
}

// Test combination
echo "\n   Test D: periode='01-2026', wilayah='WILAYAH 1'\n";
try {
    $q = $conn->table($table);
    $selectedPeriode = '01-2026';
    $selectedWilayah = 'WILAYAH 1';
    
    if ($selectedPeriode != 'all' || $selectedWilayah != 'all') {
        if ($selectedWilayah != 'all') {
            $q->where('wilayah', $selectedWilayah);
        }
        if ($selectedPeriode != 'all') {
            $q->where('periode', $selectedPeriode);
        }
        $count = $q->count();
        $callSum = $q->sum('`Call`');
        echo "      Result: $count rows, Call sum: " . number_format($callSum) . "\n";
    }
} catch (Exception $e) {
    echo "      Error: " . $e->getMessage() . "\n";
}

echo "\n3. Check View Rendering:\n";
echo "   Running e2e tests...\n";

// Test e2e with different periodes
$tests = [
    ['periode' => 'all', 'wilayah' => 'all', 'desc' => 'Both all'],
    ['periode' => '01-2026', 'wilayah' => 'all', 'desc' => 'Periode only'],
];

if (count($periods) > 1) {
    $tests[] = ['periode' => $periods[1], 'wilayah' => 'all', 'desc' => 'Different periode'];
}

foreach ($tests as $test) {
    echo "\n   Test: " . $test['desc'] . " (periode={$test['periode']}, wilayah={$test['wilayah']})\n";
    $cmd = 'php scripts/e2e_trafik.php ' . escapeshellarg($test['periode']) . ' ' . escapeshellarg($test['wilayah']);
    exec($cmd, $output, $code);
    
    if ($code === 0) {
        // Check output file for KPI values
        $html = @file_get_contents(__DIR__ . '/e2e_trafik_output.html');
        if ($html) {
            preg_match('/<div class="kpi-value">Call: ([^<]+)<\/div>/', $html, $callMatch);
            preg_match('/<div class="kpi-value text-primary">GT: ([^<]+)<\/div>/', $html, $gtMatch);
            $callValue = $callMatch[1] ?? 'NOT FOUND';
            $gtValue = $gtMatch[1] ?? 'NOT FOUND';
            echo "      KPI: Call=$callValue, GT=$gtValue\n";
        }
    } else {
        echo "      Error: e2e script failed\n";
    }
}

echo "\n=== Conclusion ===\n";
echo "If Call/GT values are DIFFERENT between periode='all' and periode='01-2026',\n";
echo "then the periode filter is WORKING.\n";
echo "If values are SAME, the periode filter is NOT WORKING.\n";
