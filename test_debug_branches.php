<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$periode = '03-2026';

$branches = DB::connection('dashboard_phinnisi')->table('lhgk')
    ->select('NM_BRANCH')
    ->where('PERIODE', $periode)
    ->whereNotNull('NM_BRANCH')
    ->where('NM_BRANCH', '!=', '')
    ->distinct()
    ->orderBy('NM_BRANCH')
    ->pluck('NM_BRANCH')
    ->toArray();

echo "Branches found: " . count($branches) . "\n";
if (count($branches) > 0) {
    echo "First 3 branches:\n";
    var_dump(array_slice($branches, 0, 3));
} else {
    echo "No branches found for period $periode\n";
}

// Check lhgkStats
$lhgkStats = DB::connection('dashboard_phinnisi')->table('lhgk')
    ->where('PERIODE', $periode)
    ->whereIn('NM_BRANCH', array_slice($branches, 0, 3))
    ->selectRaw("
        NM_BRANCH,
        COUNT(*) as jumlah_gerakan
    ")
    ->groupBy('NM_BRANCH')
    ->get();

echo "\nlhgkStats:\n";
var_dump($lhgkStats);
