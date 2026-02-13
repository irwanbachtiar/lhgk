<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

// Bootstrap the application (register providers)
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get DB manager
$db = $app->make('db');

try {
    $schema = $db->connection('dashboard_phinnisi')->getSchemaBuilder();
    if (! $schema->hasTable('sharing_regional')) {
        echo "TABLE_NOT_FOUND\n";
        exit(0);
    }

    $cols = $schema->getColumnListing('sharing_regional');
    echo "COLUMNS:\n";
    print_r($cols);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
