<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Updating lhgk table columns ===\n\n";

// Check what needs to be updated
echo "Checking records that need updates...\n";
$needRangeGT = DB::connection('dashboard_phinnisi')->table('lhgk')->where(function($q) { $q->whereNull('RANGE_GT')->orWhere('RANGE_GT', ''); })->whereNotNull('KP_GRT')->count();
$needRangeLabel = DB::connection('dashboard_phinnisi')->table('lhgk')->where(function($q) { $q->whereNull('RANGE_GT_LABEL')->orWhere('RANGE_GT_LABEL', ''); })->whereNotNull('KP_GRT')->count();
$needJenisKapal = DB::connection('dashboard_phinnisi')->table('lhgk')->where(function($q) { $q->whereNull('JENIS_KAPAL_DARI_BENDERA')->orWhere('JENIS_KAPAL_DARI_BENDERA', ''); })->whereNotNull('KD_BENDERA')->count();
$needPanduClean = DB::connection('dashboard_phinnisi')->table('lhgk')->where(function($q) { $q->whereNull('TOTAL_PENDAPATAN_PANDU_CLEAN')->orWhere('TOTAL_PENDAPATAN_PANDU_CLEAN', 0); })->whereNotNull('PENDAPATAN_PANDU')->where('PENDAPATAN_PANDU', '>', 0)->count();
$needTundaClean = DB::connection('dashboard_phinnisi')->table('lhgk')->where(function($q) { $q->whereNull('TOTAL_PENDAPATAN_TUNDA_CLEAN')->orWhere('TOTAL_PENDAPATAN_TUNDA_CLEAN', 0); })->whereNotNull('PENDAPATAN_TUNDA')->where('PENDAPATAN_TUNDA', '>', 0)->count();

echo "Records need RANGE_GT update: " . number_format($needRangeGT) . "\n";
echo "Records need RANGE_GT_LABEL update: " . number_format($needRangeLabel) . "\n";
echo "Records need JENIS_KAPAL_DARI_BENDERA update: " . number_format($needJenisKapal) . "\n";
echo "Records need TOTAL_PENDAPATAN_PANDU_CLEAN update: " . number_format($needPanduClean) . "\n";
echo "Records need TOTAL_PENDAPATAN_TUNDA_CLEAN update: " . number_format($needTundaClean) . "\n\n";

// Auto proceed without confirmation for automation
echo "Proceeding with updates...\n\n";

echo "\n=== 1. Updating RANGE_GT ===\n";
$ranges = [
    ['min' => 0, 'max' => 3500, 'range' => '0-3500 GT', 'label' => 'A'],
    ['min' => 3501, 'max' => 8000, 'range' => '3501-8000 GT', 'label' => 'B'],
    ['min' => 8001, 'max' => 14000, 'range' => '8001-14000 GT', 'label' => 'C'],
    ['min' => 14001, 'max' => 18000, 'range' => '14001-18000 GT', 'label' => 'D'],
    ['min' => 18001, 'max' => 26000, 'range' => '18001-26000 GT', 'label' => 'E'],
    ['min' => 26001, 'max' => 40000, 'range' => '26001-40000 GT', 'label' => 'F'],
    ['min' => 40001, 'max' => 75000, 'range' => '40001-75000 GT', 'label' => 'G']
];

$totalUpdated = 0;
foreach ($ranges as $r) {
    echo "Updating range {$r['range']}...\n";
    $updated = DB::connection('dashboard_phinnisi')->table('lhgk')
        ->where(function($q) { $q->whereNull('RANGE_GT')->orWhere('RANGE_GT', ''); })
        ->whereBetween('KP_GRT', [$r['min'], $r['max']])
        ->update(['RANGE_GT' => $r['range'], 'RANGE_GT_LABEL' => $r['label']]);
    echo "  Updated: " . number_format($updated) . " records\n";
    $totalUpdated += $updated;
}

// >75000
echo "Updating range >75000 GT...\n";
$updated = DB::connection('dashboard_phinnisi')->table('lhgk')
    ->where(function($q) { $q->whereNull('RANGE_GT')->orWhere('RANGE_GT', ''); })
    ->where('KP_GRT', '>', 75000)
    ->update(['RANGE_GT' => '>75000 GT', 'RANGE_GT_LABEL' => 'H']);
echo "  Updated: " . number_format($updated) . " records\n";
$totalUpdated += $updated;

echo "Total RANGE_GT updated: " . number_format($totalUpdated) . "\n\n";

echo "=== 2. Updating JENIS_KAPAL_DARI_BENDERA ===\n";

// Update KAPAL NASIONAL (KD_BENDERA = 'ID' or 'IDN' or 'INDONESIA')
echo "Updating KAPAL NASIONAL...\n";
$updated = DB::connection('dashboard_phinnisi')->table('lhgk')
    ->where(function($q) { $q->whereNull('JENIS_KAPAL_DARI_BENDERA')->orWhere('JENIS_KAPAL_DARI_BENDERA', ''); })
    ->where(function($q) {
        $q->where('KD_BENDERA', 'ID')
          ->orWhere('KD_BENDERA', 'IDN')
          ->orWhere('KD_BENDERA', 'INDONESIA');
    })
    ->update(['JENIS_KAPAL_DARI_BENDERA' => 'KAPAL NASIONAL']);
echo "  Updated: " . number_format($updated) . " records\n";

// Update KAPAL ASING (KD_BENDERA not ID/IDN/INDONESIA and not empty)
echo "Updating KAPAL ASING...\n";
$updated = DB::connection('dashboard_phinnisi')->table('lhgk')
    ->where(function($q) { $q->whereNull('JENIS_KAPAL_DARI_BENDERA')->orWhere('JENIS_KAPAL_DARI_BENDERA', ''); })
    ->whereNotNull('KD_BENDERA')
    ->where('KD_BENDERA', '!=', '')
    ->where('KD_BENDERA', '!=', 'ID')
    ->where('KD_BENDERA', '!=', 'IDN')
    ->where('KD_BENDERA', '!=', 'INDONESIA')
    ->update(['JENIS_KAPAL_DARI_BENDERA' => 'KAPAL ASING']);
echo "  Updated: " . number_format($updated) . " records\n\n";

echo "=== 3. Updating TOTAL_PENDAPATAN_PANDU_CLEAN ===\n";
echo "Copying from PENDAPATAN_PANDU...\n";

// Use raw update for better performance
$updated = DB::connection('dashboard_phinnisi')->statement("
    UPDATE lhgk 
    SET TOTAL_PENDAPATAN_PANDU_CLEAN = PENDAPATAN_PANDU 
    WHERE (TOTAL_PENDAPATAN_PANDU_CLEAN IS NULL OR TOTAL_PENDAPATAN_PANDU_CLEAN = 0)
    AND PENDAPATAN_PANDU IS NOT NULL 
    AND PENDAPATAN_PANDU > 0
");
echo "  Updated successfully\n\n";

echo "=== 4. Updating TOTAL_PENDAPATAN_TUNDA_CLEAN ===\n";
echo "Copying from PENDAPATAN_TUNDA...\n";

// Use raw update for better performance
$updated = DB::connection('dashboard_phinnisi')->statement("
    UPDATE lhgk 
    SET TOTAL_PENDAPATAN_TUNDA_CLEAN = PENDAPATAN_TUNDA 
    WHERE (TOTAL_PENDAPATAN_TUNDA_CLEAN IS NULL OR TOTAL_PENDAPATAN_TUNDA_CLEAN = 0)
    AND PENDAPATAN_TUNDA IS NOT NULL 
    AND PENDAPATAN_TUNDA > 0
");
echo "  Updated successfully\n\n";

echo "=== Update Complete ===\n";
echo "Verifying final state...\n";

$stillEmptyRange = DB::connection('dashboard_phinnisi')->table('lhgk')->where(function($q) { $q->whereNull('RANGE_GT')->orWhere('RANGE_GT', ''); })->whereNotNull('KP_GRT')->count();
$stillEmptyLabel = DB::connection('dashboard_phinnisi')->table('lhgk')->where(function($q) { $q->whereNull('RANGE_GT_LABEL')->orWhere('RANGE_GT_LABEL', ''); })->whereNotNull('KP_GRT')->count();
$stillEmptyJenis = DB::connection('dashboard_phinnisi')->table('lhgk')->where(function($q) { $q->whereNull('JENIS_KAPAL_DARI_BENDERA')->orWhere('JENIS_KAPAL_DARI_BENDERA', ''); })->whereNotNull('KD_BENDERA')->count();
$stillEmptyPandu = DB::connection('dashboard_phinnisi')->table('lhgk')->where(function($q) { $q->whereNull('TOTAL_PENDAPATAN_PANDU_CLEAN')->orWhere('TOTAL_PENDAPATAN_PANDU_CLEAN', 0); })->whereNotNull('PENDAPATAN_PANDU')->where('PENDAPATAN_PANDU', '>', 0)->count();
$stillEmptyTunda = DB::connection('dashboard_phinnisi')->table('lhgk')->where(function($q) { $q->whereNull('TOTAL_PENDAPATAN_TUNDA_CLEAN')->orWhere('TOTAL_PENDAPATAN_TUNDA_CLEAN', 0); })->whereNotNull('PENDAPATAN_TUNDA')->where('PENDAPATAN_TUNDA', '>', 0)->count();

echo "Records still empty RANGE_GT: " . number_format($stillEmptyRange) . "\n";
echo "Records still empty RANGE_GT_LABEL: " . number_format($stillEmptyLabel) . "\n";
echo "Records still empty JENIS_KAPAL_DARI_BENDERA: " . number_format($stillEmptyJenis) . "\n";
echo "Records still empty TOTAL_PENDAPATAN_PANDU_CLEAN: " . number_format($stillEmptyPandu) . "\n";
echo "Records still empty TOTAL_PENDAPATAN_TUNDA_CLEAN: " . number_format($stillEmptyTunda) . "\n";
