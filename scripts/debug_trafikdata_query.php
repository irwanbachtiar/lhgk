<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Debug trafikData Query ===\n\n";

$conn = DB::connection('dashboard_phinnisi');
$table = 'trafik_rkap_realisasi';
$selectedPeriode = '01-2026';
$selectedWilayah = 'all';

echo "Table: $table\n";
echo "selectedPeriode: $selectedPeriode\n";
echo "selectedWilayah: $selectedWilayah\n\n";

// Test the query that builds trafikData
try {
    $query = $conn->table($table)
        ->select('wilayah', 'pelayaran', 'lokasi', 
                 DB::raw('SUM(`Call`) as total_call'), 
                 DB::raw('SUM(`GT`) as total_gt'))
        ->when($selectedWilayah != 'all', function($q) use ($selectedWilayah) {
            return $q->where('wilayah', $selectedWilayah);
        })
        ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
            return $q->where('periode', $selectedPeriode);
        })
        ->groupBy('wilayah', 'pelayaran', 'lokasi');
    
    echo "SQL: " . $query->toSql() . "\n\n";
    
    $grouped = $query->get();
    
    echo "Result count: " . $grouped->count() . "\n\n";
    
    if ($grouped->count() > 0) {
        echo "First 5 rows:\n";
        foreach ($grouped->take(5) as $row) {
            echo "  - Wilayah: {$row->wilayah}, Pelayaran: {$row->pelayaran}, Lokasi: {$row->lokasi}, Call: {$row->total_call}, GT: {$row->total_gt}\n";
        }
    } else {
        echo "No results returned!\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
