<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$periode = $argv[1] ?? '12-2024';

echo "Periode: {$periode}\n\n";

$pandu = DB::connection('dashboard_phinnisi')->table('pandu_prod')
    ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
    ->sum('REVENUE');

$tunda = DB::connection('dashboard_phinnisi')->table('tunda_prod')
    ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
    ->sum('REVENUE');

echo "Total Pandu: " . number_format($pandu, 0, ',', '.') . "\n";
echo "Total Tunda: " . number_format($tunda, 0, ',', '.') . "\n";
echo "Total Gabungan: " . number_format($pandu + $tunda, 0, ',', '.') . "\n\n";

echo "--- Pandu per branch (top 50) ---\n";
$perBranch = DB::connection('dashboard_phinnisi')->table('pandu_prod')
    ->select('NAME_BRANCH', DB::raw('SUM(REVENUE) as total'))
    ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
    ->groupBy('NAME_BRANCH')
    ->orderByDesc('total')
    ->limit(50)
    ->get();

foreach ($perBranch as $row) {
    $name = $row->NAME_BRANCH ?? '(NULL)';
    echo str_pad($name, 40) . ' ' . str_pad(number_format($row->total, 0, ',', '.'), 20, ' ', STR_PAD_LEFT) . "\n";
}

echo "\n--- Revenue where NAME_BRANCH is NULL or empty ---\n";
$missing = DB::connection('dashboard_phinnisi')->table('pandu_prod')
    ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
    ->where(function($q){ $q->whereNull('NAME_BRANCH')->orWhere('NAME_BRANCH', ''); })
    ->sum('REVENUE');

echo "Missing NAME_BRANCH revenue: " . number_format($missing, 0, ',', '.') . "\n";

return 0;
