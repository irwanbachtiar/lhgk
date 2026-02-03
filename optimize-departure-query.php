<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Checking and creating indexes for departure delay query optimization...\n";
    echo "========================================================================\n\n";
    
    // Check existing indexes on lhgk table
    $indexes = DB::connection('dashboard_phinnisi')->select('SHOW INDEX FROM lhgk');
    
    $existingIndexes = [];
    foreach($indexes as $index) {
        $existingIndexes[] = $index->Key_name;
    }
    
    echo "Existing indexes: " . implode(', ', array_unique($existingIndexes)) . "\n\n";
    
    // Create composite index for departure delay query if not exists
    $indexName = 'idx_departure_delay';
    if (!in_array($indexName, $existingIndexes)) {
        echo "Creating index: $indexName...\n";
        DB::connection('dashboard_phinnisi')->statement(
            "CREATE INDEX $indexName ON lhgk(GERAKAN, PERIODE, NM_BRANCH, INVOICE_DATE, SELESAI_PELAKSANAAN)"
        );
        echo "✓ Index created successfully!\n";
    } else {
        echo "✓ Index $indexName already exists\n";
    }
    
    echo "\n";
    
    // Test query performance
    echo "Testing query performance...\n";
    $start = microtime(true);
    
    $count = DB::connection('dashboard_phinnisi')
        ->table('lhgk')
        ->whereRaw("LOWER(GERAKAN) = 'departure'")
        ->whereNotNull('INVOICE_DATE')
        ->whereNotNull('SELESAI_PELAKSANAAN')
        ->where('INVOICE_DATE', '!=', '')
        ->where('SELESAI_PELAKSANAAN', '!=', '')
        ->whereRaw('DATEDIFF(STR_TO_DATE(INVOICE_DATE, "%d-%m-%Y"), STR_TO_DATE(SELESAI_PELAKSANAAN, "%d-%m-%Y")) > 2')
        ->count();
    
    $duration = round((microtime(true) - $start) * 1000, 2);
    
    echo "✓ Query executed in {$duration}ms\n";
    echo "✓ Found {$count} records with departure delay > 2 days\n";
    
    echo "\n========================================================================\n";
    echo "Optimization complete!\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
