<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Updating RANGE_GT column in lhgk table ===\n\n";

// Check records that need update
$needUpdate = DB::connection('dashboard_phinnisi')
    ->table('lhgk')
    ->where(function($q) {
        $q->whereNull('RANGE_GT')->orWhere('RANGE_GT', '');
    })
    ->whereNotNull('KP_GRT')
    ->count();

echo "Records that need RANGE_GT update: " . number_format($needUpdate) . "\n\n";

if ($needUpdate == 0) {
    echo "No records to update!\n";
    exit;
}

$confirm = readline("Do you want to proceed with the update? (yes/no): ");
if (strtolower($confirm) !== 'yes') {
    echo "Update cancelled.\n";
    exit;
}

echo "\nStarting update...\n";

$ranges = [
    ['min' => 0, 'max' => 3500, 'label' => '0-3500 GT'],
    ['min' => 3501, 'max' => 8000, 'label' => '3501-8000 GT'],
    ['min' => 8001, 'max' => 14000, 'label' => '8001-14000 GT'],
    ['min' => 14001, 'max' => 18000, 'label' => '14001-18000 GT'],
    ['min' => 18001, 'max' => 26000, 'label' => '18001-26000 GT'],
    ['min' => 26001, 'max' => 40000, 'label' => '26001-40000 GT'],
    ['min' => 40001, 'max' => 75000, 'label' => '40001-75000 GT']
];

$totalUpdated = 0;

foreach ($ranges as $range) {
    echo "Updating KP_GRT between {$range['min']} and {$range['max']}...\n";
    
    $updated = DB::connection('dashboard_phinnisi')
        ->table('lhgk')
        ->where(function($q) {
            $q->whereNull('RANGE_GT')->orWhere('RANGE_GT', '');
        })
        ->whereBetween('KP_GRT', [$range['min'], $range['max']])
        ->update(['RANGE_GT' => $range['label']]);
    
    echo "  Updated: " . number_format($updated) . " records\n";
    $totalUpdated += $updated;
}

// Update >75000
echo "Updating KP_GRT > 75000...\n";
$updated = DB::connection('dashboard_phinnisi')
    ->table('lhgk')
    ->where(function($q) {
        $q->whereNull('RANGE_GT')->orWhere('RANGE_GT', '');
    })
    ->where('KP_GRT', '>', 75000)
    ->update(['RANGE_GT' => '>75000 GT']);

echo "  Updated: " . number_format($updated) . " records\n";
$totalUpdated += $updated;

echo "\n=== Update Complete ===\n";
echo "Total records updated: " . number_format($totalUpdated) . "\n";

// Verify
$stillEmpty = DB::connection('dashboard_phinnisi')
    ->table('lhgk')
    ->where(function($q) {
        $q->whereNull('RANGE_GT')->orWhere('RANGE_GT', '');
    })
    ->whereNotNull('KP_GRT')
    ->count();

echo "Records still empty: " . number_format($stillEmpty) . "\n";
