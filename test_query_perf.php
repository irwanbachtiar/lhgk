<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$periode = '03-2026';

// Test: Simple count query first
echo "Test 1: Simple branch count...\n";
$count = DB::connection('dashboard_phinnisi')->table('lhgk')
    ->where('PERIODE', $periode)
    ->count();
echo "Total rows for period: " . $count . "\n\n";

// Test 2: Group by branch
echo "Test 2: Count per branch...\n";
$groupBy = DB::connection('dashboard_phinnisi')->table('lhgk')
    ->where('PERIODE', $periode)
    ->selectRaw("NM_BRANCH, COUNT(*) as cnt")
    ->groupBy('NM_BRANCH')
    ->get();
echo "Branches: " . count($groupBy) . "\n\n";

// Test 3: Simple aggregation
echo "Test 3: Simple aggregation with calculations...\n";
$start = microtime(true);
$result = DB::connection('dashboard_phinnisi')->table('lhgk')
    ->where('PERIODE', $periode)
    ->selectRaw("
        NM_BRANCH,
        COUNT(*) as jumlah_gerakan,
        SUM(CASE WHEN UPPER(REALISAS_PILOT_VIA) = 'WEB' THEN 1 ELSE 0 END) as web
    ")
    ->groupBy('NM_BRANCH')
    ->limit(5)
    ->get();
$elapsed = microtime(true) - $start;
echo "Result rows: " . count($result) . " (took " . round($elapsed, 2) . "s)\n";
if (count($result) > 0) {
    echo "First result: " . json_encode($result[0]) . "\n";
}
