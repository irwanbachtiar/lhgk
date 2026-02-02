<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Changing NM_BRANCH column type to TEXT...\n";
    
    DB::statement('ALTER TABLE lhgk MODIFY COLUMN NM_BRANCH TEXT NULL');
    
    echo "✅ Column NM_BRANCH successfully changed to TEXT!\n";
    echo "Now it can handle unlimited text length.\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
