<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Debug Trafik periode filter ===\n\n";

$conn = DB::connection('dashboard_phinnisi');
// choose the table similarly to the controller: prefer 'trafik', else 'trafik_rkap_realisasi', else 'trafik_rekap_realisasi'
$table = 'trafik';
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
    // ignore
}

echo "Using table: $table\n\n";

// detect if tabel punya bulan+tahun
$hasBulan = true;
try {
    $test = $conn->table($table)->select('bulan','tahun')->limit(1)->first();
    if (! $test) $hasBulan = false;
} catch (Exception $e) {
    $hasBulan = false;
}

echo "hasBulan: " . ($hasBulan ? 'yes' : 'no') . "\n\n";

// list available periode (first 5)
if ($hasBulan) {
    $periods = $conn->table($table)
        ->selectRaw("CONCAT(LPAD(bulan,2,'0'), '-', tahun) as periode_label")
        ->whereNotNull('bulan')
        ->whereNotNull('tahun')
        ->groupBy('periode_label')
        ->orderByRaw("STR_TO_DATE(CONCAT('01-', periode_label), '%d-%m-%Y') DESC")
        ->pluck('periode_label')
        ->toArray();
} else {
    $periods = $conn->table($table)
        ->select('periode')
        ->whereNotNull('periode')
        ->where('periode', '!=', '')
        ->groupBy('periode')
        ->orderByRaw("STR_TO_DATE(CONCAT('01-', periode), '%d-%m-%Y') DESC")
        ->pluck('periode')
        ->toArray();
}

echo "Available periods (first 10):\n";
foreach (array_slice($periods, 0, 10) as $p) {
    echo " - $p\n";
}

if (count($periods) === 0) {
    echo "No periods found.\n";
    exit(0);
}

$samplePeriod = $periods[0];

// pick a sample wilayah (first) and also test 'all'
$allWilayah = [];
try {
    $allWilayah = $conn->table($table)
        ->select('wilayah')
        ->whereNotNull('wilayah')
        ->where('wilayah', '!=', '')
        ->groupBy('wilayah')
        ->orderBy('wilayah')
        ->pluck('wilayah')
        ->toArray();
} catch (Exception $e) {
}

$sampleWilayah = $allWilayah[0] ?? null;

echo "\nTesting samplePeriod=$samplePeriod with wilayah=all and wilayah=" . ($sampleWilayah ?? 'NULL') . "\n\n";

function runTest($conn, $table, $selectedPeriode, $selectedWilayah, $hasBulan) {
    $q = $conn->table($table);
    if ($selectedWilayah != 'all' && $selectedWilayah !== null) {
        $q->where('wilayah', $selectedWilayah);
    }

    if ($selectedPeriode != 'all') {
        if ($hasBulan) {
            $parts = explode('-', $selectedPeriode);
            if (count($parts) === 2) {
                list($m, $y) = $parts;
                $m = ltrim($m, '0');
                $q->where('bulan', $m)->where('tahun', $y);
            } else {
                echo "  [WARN] periode format unexpected: $selectedPeriode\n";
            }
        } else {
            $q->where('periode', $selectedPeriode);
        }
    }

    $count = $q->count();
    echo "  -> count = $count\n";
    if ($count > 0) {
        $row = $q->limit(1)->first();
        echo "  -> sample row keys: " . implode(', ', array_keys((array)$row)) . "\n";
    }
}

// Test 1: periode + wilayah=all
runTest($conn, $table, $samplePeriod, 'all', $hasBulan);

// Test 2: periode + specific wilayah (if available)
if ($sampleWilayah) runTest($conn, $table, $samplePeriod, $sampleWilayah, $hasBulan);

// Test 3: wilayah only (no periode)
if ($sampleWilayah) runTest($conn, $table, 'all', $sampleWilayah, $hasBulan);

echo "\nDone.\n";
