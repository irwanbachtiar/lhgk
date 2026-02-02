<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$periode = $argv[1] ?? '12-2024';
$limit = 50;

echo "Periode: {$periode}\n\n";

// Raw sum
$raw = DB::connection('dashboard_phinnisi')->table('pandu_prod')
    ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
    ->select(DB::raw('SUM(REVENUE) as total'), DB::raw('COUNT(*) as cnt'))
    ->first();

$rawTotal = $raw->total ?? 0;
$rawRows = $raw->cnt ?? 0;

echo "Raw SUM(REVENUE): " . number_format($rawTotal, 0, ',', '.') . " (rows: {$rawRows})\n";

// Grouped by BILLING using MAX(REVENUE)
$grouped = DB::connection('dashboard_phinnisi')->table(DB::raw('(SELECT BILLING, MAX(REVENUE) as REVENUE, MAX(INVOICE_DATE) as INVOICE_DATE FROM dashboard_phinnisi.pandu_prod GROUP BY BILLING) as pandu'))
    ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
    ->select(DB::raw('SUM(REVENUE) as total'), DB::raw('COUNT(*) as bills'))
    ->first();

$groupTotal = $grouped->total ?? 0;
$billCount = $grouped->bills ?? 0;

echo "Grouped SUM (by BILLING, MAX(REVENUE)): " . number_format($groupTotal, 0, ',', '.') . " (distinct bills: {$billCount})\n\n";

// Difference
$diff = $rawTotal - $groupTotal;
echo "Difference (raw - grouped): " . number_format($diff, 0, ',', '.') . "\n\n";

// Show top duplicated BILLINGs (those with count>1) with sums
echo "Top duplicated BILLING (count >1)\n";
$dup = DB::connection('dashboard_phinnisi')->table('pandu_prod')
    ->select('BILLING', DB::raw('COUNT(*) as cnt'), DB::raw('SUM(REVENUE) as total'))
    ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
    ->groupBy('BILLING')
    ->having('cnt', '>', 1)
    ->orderByDesc('total')
    ->limit($limit)
    ->get();

foreach ($dup as $row) {
    echo str_pad($row->BILLING, 30) . ' cnt=' . str_pad($row->cnt, 4) . ' total=' . str_pad(number_format($row->total,0,',','.'), 20, ' ', STR_PAD_LEFT) . "\n";
}

// Show top bills by MAX(REVENUE) (grouped)
echo "\nTop bills by MAX(REVENUE) (grouped)\n";
$top = DB::connection('dashboard_phinnisi')->table(DB::raw('(SELECT BILLING, MAX(REVENUE) as REVENUE, MAX(INVOICE_DATE) as INVOICE_DATE FROM dashboard_phinnisi.pandu_prod GROUP BY BILLING) as pandu'))
    ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
    ->orderByDesc('REVENUE')
    ->limit($limit)
    ->get();

foreach ($top as $row) {
    echo str_pad($row->BILLING, 30) . ' ' . str_pad(number_format($row->REVENUE,0,',','.'), 20, ' ', STR_PAD_LEFT) . "\n";
}

// Additional diagnostics: totals without date filter and sample INVOICE_DATE values
echo "\n--- Diagnostics: overall table totals (no date filter) ---\n";
$overall = DB::connection('dashboard_phinnisi')->table('pandu_prod')
    ->select(DB::raw('COUNT(*) as cnt'), DB::raw('SUM(REVENUE) as total'))
    ->first();
echo "Overall rows: " . ($overall->cnt ?? 0) . "\n";
echo "Overall SUM(REVENUE): " . number_format($overall->total ?? 0, 0, ',', '.') . "\n\n";

echo "--- Sample distinct INVOICE_DATE values (top 50 by count) ---\n";
$dates = DB::connection('dashboard_phinnisi')->table('pandu_prod')
    ->select('INVOICE_DATE', DB::raw('COUNT(*) as cnt'))
    ->groupBy('INVOICE_DATE')
    ->orderByDesc('cnt')
    ->limit(50)
    ->get();

foreach ($dates as $d) {
    echo str_pad($d->INVOICE_DATE, 30) . ' cnt=' . $d->cnt . "\n";
}

return 0;
