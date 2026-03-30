<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Verify Realisasi vs Anggaran Data ===\n\n";

$conn = DB::connection('dashboard_phinnisi');
$table = 'trafik_rkap_realisasi';
$periode = '01-2026';

// Test query with jenis separation
$grouped = $conn->table($table)
    ->select('wilayah', 'pelayaran', 'lokasi', 'jenis',
             DB::raw('SUM(`Call`) as total_call'), 
             DB::raw('SUM(`GT`) as total_gt'))
    ->where('periode', $periode)
    ->groupBy('wilayah', 'pelayaran', 'lokasi', 'jenis')
    ->orderBy('wilayah')
    ->orderBy('pelayaran')
    ->orderBy('lokasi')
    ->orderBy('jenis')
    ->get();

echo "Total rows: " . $grouped->count() . "\n\n";

$currentWil = null;
foreach ($grouped as $row) {
    if ($currentWil !== $row->wilayah) {
        if ($currentWil !== null) echo "\n";
        $currentWil = $row->wilayah;
        echo "=== {$row->wilayah} ===\n";
    }
    
    echo "  {$row->pelayaran} | {$row->lokasi} | {$row->jenis}: Call={$row->total_call}, GT={$row->total_gt}\n";
}

// Summary by jenis
echo "\n\n=== Summary by Jenis ===\n";
$summary = $conn->table($table)
    ->select('jenis', DB::raw('SUM(`Call`) as total_call'), DB::raw('SUM(`GT`) as total_gt'))
    ->where('periode', $periode)
    ->groupBy('jenis')
    ->get();

foreach ($summary as $row) {
    echo "{$row->jenis}: Call=" . number_format($row->total_call) . ", GT=" . number_format($row->total_gt) . "\n";
}
