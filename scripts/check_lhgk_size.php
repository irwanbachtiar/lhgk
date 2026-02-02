<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK SIZE TABEL LHGK ===\n\n";

try {
    // Count rows in lhgk table
    $count = DB::connection('dashboard_phinnisi')->table('lhgk')->count();
    echo "Total rows dalam tabel lhgk: " . number_format($count) . "\n\n";
    
    // Get distinct periods
    $periods = DB::connection('dashboard_phinnisi')->table('lhgk')
        ->select('PERIODE')
        ->whereNotNull('PERIODE')
        ->where('PERIODE', '!=', '')
        ->groupBy('PERIODE')
        ->orderByRaw("STR_TO_DATE(CONCAT('01-', PERIODE), '%d-%m-%Y') DESC")
        ->limit(10)
        ->pluck('PERIODE');
    
    echo "10 Periode terakhir:\n";
    foreach ($periods as $periode) {
        $count = DB::connection('dashboard_phinnisi')->table('lhgk')
            ->where('PERIODE', $periode)
            ->count();
        echo "  - {$periode}: " . number_format($count) . " rows\n";
    }
    
    echo "\n";
    
    // Get distinct branches count
    $branchCount = DB::connection('dashboard_phinnisi')->table('lhgk')
        ->select('NM_BRANCH')
        ->whereNotNull('NM_BRANCH')
        ->where('NM_BRANCH', '!=', '')
        ->distinct()
        ->count();
    
    echo "Total cabang berbeda: " . number_format($branchCount) . "\n";
    
    // Check indexes
    echo "\n=== CEK INDEX TABEL LHGK ===\n";
    $indexes = DB::connection('dashboard_phinnisi')->select("SHOW INDEX FROM lhgk");
    
    if (empty($indexes)) {
        echo "PERINGATAN: Tidak ada index pada tabel lhgk!\n";
        echo "Ini bisa menyebabkan query lambat.\n";
    } else {
        foreach ($indexes as $index) {
            echo "  - {$index->Key_name} on {$index->Column_name}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
