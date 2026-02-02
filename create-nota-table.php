<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Creating nota table...\n\n";
    
    DB::statement("
        CREATE TABLE IF NOT EXISTS nota (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            NM_PERS_PANDU VARCHAR(255) NULL,
            NM_BRANCH TEXT NULL,
            PENDAPATAN_PANDU DECIMAL(15, 2) NULL,
            PENDAPATAN_TUNDA DECIMAL(15, 2) NULL,
            NM_KAPAL VARCHAR(255) NULL,
            JN_KAPAL VARCHAR(255) NULL,
            KP_GRT DECIMAL(15, 2) NULL,
            PILOT_DEPLOY VARCHAR(255) NULL,
            mulai_pelaksanaan VARCHAR(255) NULL,
            selesai_pelaksanaan VARCHAR(255) NULL,
            REALISAS_PILOT_VIA VARCHAR(255) NULL,
            PERIODE VARCHAR(255) NULL,
            TGL_PMT VARCHAR(255) NULL,
            JAM_PMT VARCHAR(255) NULL,
            PNK VARCHAR(255) NULL,
            NO_NOTA VARCHAR(255) NULL,
            TGL_NOTA DATE NULL,
            STATUS_NOTA VARCHAR(255) NULL,
            KETERANGAN TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "✅ Table 'nota' created successfully!\n";
    
    // Verify table exists
    $tables = DB::select("SHOW TABLES LIKE 'nota'");
    if (count($tables) > 0) {
        echo "✅ Verified: Table 'nota' exists in database\n";
        
        // Show columns
        $columns = DB::select("SHOW COLUMNS FROM nota");
        echo "\nColumns in 'nota' table:\n";
        foreach ($columns as $column) {
            echo "  - {$column->Field} ({$column->Type})\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
