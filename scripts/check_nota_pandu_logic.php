<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$periode = $argv[1] ?? '12-2025';
$cabang = $argv[2] ?? 'REGIONAL 4 TOLITOLI';

echo "=== CEK LOGIC PENDAPATAN PANDU - MONITORING NOTA ===\n";
echo "Periode: {$periode}\n";
echo "Cabang: {$cabang}\n\n";

// Method 1: Raw SUM (langsung sum REVENUE tanpa grouping)
echo "1. RAW SUM (langsung sum kolom REVENUE)\n";
$rawSum = DB::connection('dashboard_phinnisi')->table('pandu_prod')
    ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
    ->where('NAME_BRANCH', $cabang)
    ->sum('REVENUE');

$rawCount = DB::connection('dashboard_phinnisi')->table('pandu_prod')
    ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
    ->where('NAME_BRANCH', $cabang)
    ->count();

echo "   Total REVENUE: " . number_format($rawSum, 0, ',', '.') . "\n";
echo "   Total Rows: {$rawCount}\n\n";

// Method 2: Grouped by BILLING (current logic in NotaController)
echo "2. GROUPED BY BILLING (logic NotaController - MAX(REVENUE) per BILLING)\n";
$groupedSum = DB::connection('dashboard_phinnisi')
    ->table(DB::raw('(SELECT BILLING, MAX(REVENUE) as REVENUE, MAX(INVOICE_DATE) as INVOICE_DATE, MAX(NAME_BRANCH) as NAME_BRANCH FROM dashboard_phinnisi.pandu_prod GROUP BY BILLING) as pandu'))
    ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
    ->where('NAME_BRANCH', $cabang)
    ->sum('REVENUE');

$distinctBilling = DB::connection('dashboard_phinnisi')->table('pandu_prod')
    ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
    ->where('NAME_BRANCH', $cabang)
    ->distinct('BILLING')
    ->count('BILLING');

echo "   Total REVENUE: " . number_format($groupedSum, 0, ',', '.') . "\n";
echo "   Distinct BILLING: {$distinctBilling}\n\n";

// Difference
$diff = $rawSum - $groupedSum;
echo "3. PERBEDAAN\n";
echo "   Raw - Grouped: " . number_format($diff, 0, ',', '.') . "\n\n";

if ($diff != 0) {
    echo "4. ANALISIS DUPLIKASI\n";
    
    // Find duplicated BILLINGs
    $duplicates = DB::connection('dashboard_phinnisi')->table('pandu_prod')
        ->select('BILLING', DB::raw('COUNT(*) as cnt'), DB::raw('SUM(REVENUE) as total_sum'), DB::raw('MAX(REVENUE) as max_revenue'))
        ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
        ->where('NAME_BRANCH', $cabang)
        ->groupBy('BILLING')
        ->having('cnt', '>', 1)
        ->orderByDesc('total_sum')
        ->limit(20)
        ->get();
    
    if ($duplicates->count() > 0) {
        echo "   Ditemukan " . $duplicates->count() . " BILLING dengan duplikasi (top 20):\n\n";
        echo "   " . str_pad("BILLING", 25) . " " . str_pad("Count", 8) . " " . str_pad("SUM", 20) . " " . str_pad("MAX", 20) . "\n";
        echo "   " . str_repeat("-", 75) . "\n";
        
        foreach ($duplicates as $dup) {
            echo "   " . str_pad($dup->BILLING, 25) . " " . str_pad($dup->cnt, 8) . " " 
                . str_pad(number_format($dup->total_sum, 0, ',', '.'), 20) . " "
                . str_pad(number_format($dup->max_revenue, 0, ',', '.'), 20) . "\n";
        }
    } else {
        echo "   Tidak ada BILLING dengan duplikasi.\n";
    }
}

echo "\n=== KESIMPULAN ===\n";
echo "Logic NotaController saat ini:\n";
echo "- Menggunakan GROUP BY BILLING + MAX(REVENUE)\n";
echo "- Menghindari double-counting dari baris duplikat per BILLING\n";
echo "- Nilai yang ditampilkan: " . number_format($groupedSum, 0, ',', '.') . "\n\n";
echo "Jika ingin menampilkan RAW SUM (tanpa grouping):\n";
echo "- Langsung SUM(REVENUE) dari tabel\n";
echo "- Nilai yang akan ditampilkan: " . number_format($rawSum, 0, ',', '.') . "\n";

return 0;
