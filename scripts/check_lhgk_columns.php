<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK KOLOM TABEL LHGK ===\n\n";

try {
    $columns = DB::connection('dashboard_phinnisi')->select("SHOW COLUMNS FROM lhgk");
    
    echo "Kolom-kolom yang ada di tabel lhgk:\n";
    foreach ($columns as $col) {
        echo "  - {$col->Field} ({$col->Type})\n";
    }
    
    echo "\n=== CEK DATA SAMPLE ===\n";
    
    // Check if columns exist
    $columnsToCheck = ['range_gt', 'RANGE_GT', 'jenis_kapal_dari_bendera', 'JENIS_KAPAL_DARI_BENDERA', 
                       'total_pendapatan_pandu_clean', 'TOTAL_PENDAPATAN_PANDU_CLEAN',
                       'total_pendapatan_tunda_clean', 'TOTAL_PENDAPATAN_TUNDA_CLEAN'];
    
    foreach ($columnsToCheck as $colName) {
        $exists = false;
        foreach ($columns as $col) {
            if (strtolower($col->Field) == strtolower($colName)) {
                echo "\n✓ Kolom '{$colName}' ditemukan sebagai '{$col->Field}'\n";
                
                // Get sample data
                $sample = DB::connection('dashboard_phinnisi')->table('lhgk')
                    ->select($col->Field)
                    ->whereNotNull($col->Field)
                    ->where($col->Field, '!=', '')
                    ->limit(5)
                    ->get();
                
                if ($sample->count() > 0) {
                    echo "  Sample data:\n";
                    foreach ($sample as $s) {
                        echo "    - " . $s->{$col->Field} . "\n";
                    }
                }
                
                $exists = true;
                break;
            }
        }
        
        if (!$exists && in_array($colName, ['range_gt', 'jenis_kapal_dari_bendera', 'total_pendapatan_pandu_clean', 'total_pendapatan_tunda_clean'])) {
            echo "\n✗ Kolom '{$colName}' TIDAK ditemukan\n";
        }
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
