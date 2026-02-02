<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== MENAMBAHKAN INDEX KE TABEL LHGK ===\n\n";

try {
    $connection = DB::connection('dashboard_phinnisi');
    
    // Check existing indexes
    echo "Index yang sudah ada:\n";
    $existingIndexes = $connection->select("SHOW INDEX FROM lhgk");
    foreach ($existingIndexes as $idx) {
        echo "  - {$idx->Key_name} on {$idx->Column_name}\n";
    }
    echo "\n";
    
    // Add indexes
    echo "Menambahkan index baru...\n";
    
    // Index for PERIODE
    try {
        $connection->statement('ALTER TABLE lhgk ADD INDEX idx_periode (PERIODE)');
        echo "  ✓ Index idx_periode berhasil ditambahkan\n";
    } catch (\Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "  ⚠ Index idx_periode sudah ada\n";
        } else {
            echo "  ✗ Error: " . $e->getMessage() . "\n";
        }
    }
    
    // Index for NM_BRANCH
    try {
        $connection->statement('ALTER TABLE lhgk ADD INDEX idx_nm_branch (NM_BRANCH)');
        echo "  ✓ Index idx_nm_branch berhasil ditambahkan\n";
    } catch (\Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "  ⚠ Index idx_nm_branch sudah ada\n";
        } else {
            echo "  ✗ Error: " . $e->getMessage() . "\n";
        }
    }
    
    // Composite index for PERIODE and NM_BRANCH
    try {
        $connection->statement('ALTER TABLE lhgk ADD INDEX idx_periode_branch (PERIODE, NM_BRANCH)');
        echo "  ✓ Index idx_periode_branch berhasil ditambahkan\n";
    } catch (\Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "  ⚠ Index idx_periode_branch sudah ada\n";
        } else {
            echo "  ✗ Error: " . $e->getMessage() . "\n";
        }
    }
    
    // Index for NM_PERS_PANDU
    try {
        $connection->statement('ALTER TABLE lhgk ADD INDEX idx_nm_pers_pandu (NM_PERS_PANDU)');
        echo "  ✓ Index idx_nm_pers_pandu berhasil ditambahkan\n";
    } catch (\Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "  ⚠ Index idx_nm_pers_pandu sudah ada\n";
        } else {
            echo "  ✗ Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== INDEX SETELAH DITAMBAHKAN ===\n";
    $newIndexes = $connection->select("SHOW INDEX FROM lhgk");
    foreach ($newIndexes as $idx) {
        echo "  - {$idx->Key_name} on {$idx->Column_name}\n";
    }
    
    echo "\n✓ Selesai! Query akan lebih cepat sekarang.\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
