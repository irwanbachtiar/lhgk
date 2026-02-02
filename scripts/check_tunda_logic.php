<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$periode = $argv[1] ?? '12-2025';
$cabang = $argv[2] ?? null;

echo "=== CEK LOGIC PENDAPATAN TUNDA ===\n";
echo "Periode: {$periode}\n";
if ($cabang) {
    echo "Cabang: {$cabang}\n";
}
echo "\n";

// 1. Global total (untuk regional-revenue dan dashboard-wilayah)
echo "1. GLOBAL TOTAL TUNDA (tanpa filter cabang)\n";
$globalTunda = DB::connection('dashboard_phinnisi')->table('tunda_prod')
    ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
    ->sum('REVENUE');

$globalTundaRows = DB::connection('dashboard_phinnisi')->table('tunda_prod')
    ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
    ->count();

echo "   Total REVENUE: " . number_format($globalTunda, 0, ',', '.') . "\n";
echo "   Total Rows: {$globalTundaRows}\n\n";

if ($cabang) {
    // 2. Filtered by cabang (untuk monitoring-nota)
    echo "2. FILTERED BY CABANG (untuk monitoring-nota)\n";
    
    // Method A: Direct filter on tunda_prod.NAME_BRANCH
    echo "   A. Filter langsung tunda_prod.NAME_BRANCH:\n";
    $tundaBranchDirect = DB::connection('dashboard_phinnisi')->table('tunda_prod')
        ->where('NAME_BRANCH', $cabang)
        ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
        ->sum('REVENUE');
    
    $tundaBranchDirectRows = DB::connection('dashboard_phinnisi')->table('tunda_prod')
        ->where('NAME_BRANCH', $cabang)
        ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
        ->count();
    
    echo "      Total: " . number_format($tundaBranchDirect, 0, ',', '.') . " ({$tundaBranchDirectRows} rows)\n\n";
    
    // Method B: Join with pandu_prod (current NotaController logic)
    echo "   B. Join dengan pandu_prod (logic NotaController sekarang):\n";
    $tundaJoin = DB::connection('dashboard_phinnisi')->table('tunda_prod')
        ->join('pandu_prod', 'tunda_prod.BILLING', '=', 'pandu_prod.BILLING')
        ->where('pandu_prod.NAME_BRANCH', $cabang)
        ->whereRaw("DATE_FORMAT(STR_TO_DATE(tunda_prod.INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
        ->sum('tunda_prod.REVENUE');
    
    $tundaJoinRows = DB::connection('dashboard_phinnisi')->table('tunda_prod')
        ->join('pandu_prod', 'tunda_prod.BILLING', '=', 'pandu_prod.BILLING')
        ->where('pandu_prod.NAME_BRANCH', $cabang)
        ->whereRaw("DATE_FORMAT(STR_TO_DATE(tunda_prod.INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
        ->count();
    
    echo "      Total: " . number_format($tundaJoin, 0, ',', '.') . " ({$tundaJoinRows} rows)\n\n";
    
    $diff = $tundaBranchDirect - $tundaJoin;
    if ($diff != 0) {
        echo "   Perbedaan (Direct - Join): " . number_format($diff, 0, ',', '.') . "\n\n";
    }
}

// 3. Check if tunda_prod has NAME_BRANCH column
echo "3. CEK STRUKTUR TABEL tunda_prod\n";
$columns = DB::connection('dashboard_phinnisi')->select("SHOW COLUMNS FROM tunda_prod");
$hasNameBranch = false;
echo "   Kolom yang ada:\n";
foreach ($columns as $col) {
    echo "      - {$col->Field} ({$col->Type})\n";
    if (strtoupper($col->Field) == 'NAME_BRANCH') {
        $hasNameBranch = true;
    }
}

if (!$hasNameBranch) {
    echo "\n   ⚠️  PERHATIAN: tunda_prod TIDAK memiliki kolom NAME_BRANCH!\n";
    echo "   Harus join dengan pandu_prod untuk filter per cabang.\n";
}

echo "\n=== KESIMPULAN ===\n";
if ($cabang) {
    echo "Untuk monitoring-nota dengan filter cabang:\n";
    if ($hasNameBranch) {
        echo "- Bisa filter langsung: " . number_format($tundaBranchDirect, 0, ',', '.') . "\n";
    }
    echo "- Logic sekarang (join pandu): " . number_format($tundaJoin, 0, ',', '.') . "\n";
} else {
    echo "Untuk regional-revenue dan dashboard-wilayah (tanpa filter cabang):\n";
    echo "- Total global tunda: " . number_format($globalTunda, 0, ',', '.') . "\n";
}

return 0;
