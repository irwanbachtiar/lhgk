<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "Checking pandu_prod table structure...\n\n";
    
    // Get existing columns
    $columns = Schema::getColumnListing('pandu_prod');
    echo "Existing columns: " . implode(', ', $columns) . "\n\n";
    
    // Define required columns
    $requiredColumns = [
        'NM_PERS_PANDU' => 'VARCHAR(255) NULL',
        'NM_BRANCH' => 'TEXT NULL',
        'PENDAPATAN_PANDU' => 'DECIMAL(15, 2) NULL',
        'PENDAPATAN_TUNDA' => 'DECIMAL(15, 2) NULL',
        'NM_KAPAL' => 'VARCHAR(255) NULL',
        'JN_KAPAL' => 'VARCHAR(255) NULL',
        'KP_GRT' => 'DECIMAL(15, 2) NULL',
        'PILOT_DEPLOY' => 'VARCHAR(255) NULL',
        'mulai_pelaksanaan' => 'VARCHAR(255) NULL',
        'selesai_pelaksanaan' => 'VARCHAR(255) NULL',
        'REALISAS_PILOT_VIA' => 'VARCHAR(255) NULL',
        'PERIODE' => 'VARCHAR(255) NULL',
        'TGL_PMT' => 'VARCHAR(255) NULL',
        'JAM_PMT' => 'VARCHAR(255) NULL',
        'PNK' => 'VARCHAR(255) NULL',
        'NO_NOTA' => 'VARCHAR(255) NULL',
        'TGL_NOTA' => 'DATE NULL',
        'STATUS_NOTA' => 'VARCHAR(255) NULL',
        'KETERANGAN' => 'TEXT NULL'
    ];
    
    $added = [];
    $afterColumn = 'id';
    
    foreach ($requiredColumns as $columnName => $columnType) {
        if (!in_array($columnName, $columns)) {
            echo "Adding column: $columnName...\n";
            DB::statement("ALTER TABLE pandu_prod ADD COLUMN $columnName $columnType AFTER $afterColumn");
            $added[] = $columnName;
        } else {
            echo "✓ Column exists: $columnName\n";
        }
        $afterColumn = $columnName;
    }
    
    echo "\n";
    if (count($added) > 0) {
        echo "✅ Added " . count($added) . " columns: " . implode(', ', $added) . "\n";
    } else {
        echo "✅ All required columns already exist!\n";
    }
    
    // Show updated columns
    $updatedColumns = Schema::getColumnListing('pandu_prod');
    echo "\nUpdated columns (" . count($updatedColumns) . "): " . implode(', ', $updatedColumns) . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
