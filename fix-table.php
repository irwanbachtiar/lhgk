<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    echo "Checking table structure...\n";
    
    // Get current columns
    $columns = Schema::getColumnListing('lhgk');
    echo "Current columns: " . implode(', ', $columns) . "\n\n";
    
    $fixed = [];
    
    // Add mulai_pelaksanaan if not exists
    if (!in_array('mulai_pelaksanaan', $columns)) {
        DB::statement('ALTER TABLE lhgk ADD COLUMN mulai_pelaksanaan VARCHAR(255) NULL AFTER PILOT_DEPLOY');
        echo "✓ Added column: mulai_pelaksanaan\n";
        $fixed[] = 'mulai_pelaksanaan';
    } else {
        echo "✓ Column already exists: mulai_pelaksanaan\n";
    }
    
    // Add selesai_pelaksanaan if not exists
    if (!in_array('selesai_pelaksanaan', $columns)) {
        DB::statement('ALTER TABLE lhgk ADD COLUMN selesai_pelaksanaan VARCHAR(255) NULL AFTER mulai_pelaksanaan');
        echo "✓ Added column: selesai_pelaksanaan\n";
        $fixed[] = 'selesai_pelaksanaan';
    } else {
        echo "✓ Column already exists: selesai_pelaksanaan\n";
    }
    
    // Add TGL_PMT if not exists
    if (!in_array('TGL_PMT', $columns)) {
        DB::statement('ALTER TABLE lhgk ADD COLUMN TGL_PMT VARCHAR(255) NULL AFTER PERIODE');
        echo "✓ Added column: TGL_PMT\n";
        $fixed[] = 'TGL_PMT';
    } else {
        echo "✓ Column already exists: TGL_PMT\n";
    }
    
    // Add JAM_PMT if not exists (after TGL_PMT)
    if (!in_array('JAM_PMT', $columns)) {
        DB::statement('ALTER TABLE lhgk ADD COLUMN JAM_PMT VARCHAR(255) NULL AFTER TGL_PMT');
        echo "✓ Added column: JAM_PMT\n";
        $fixed[] = 'JAM_PMT';
    } else {
        echo "✓ Column already exists: JAM_PMT\n";
    }
    
    // Add PNK if not exists (after JAM_PMT)
    if (!in_array('PNK', $columns)) {
        DB::statement('ALTER TABLE lhgk ADD COLUMN PNK VARCHAR(255) NULL AFTER JAM_PMT');
        echo "✓ Added column: PNK\n";
        $fixed[] = 'PNK';
    } else {
        echo "✓ Column already exists: PNK\n";
    }
    
    // Get updated columns
    $updatedColumns = Schema::getColumnListing('lhgk');
    echo "\nUpdated columns: " . implode(', ', $updatedColumns) . "\n";
    
    if (count($fixed) > 0) {
        echo "\n✅ Table structure fixed! Added columns: " . implode(', ', $fixed) . "\n";
    } else {
        echo "\n✅ Table structure is already correct!\n";
    }
    
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
