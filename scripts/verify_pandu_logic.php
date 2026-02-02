<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$periode = $argv[1] ?? '12-2025';

echo "=== VERIFIKASI LOGIC TOTAL PANDU ===\n";
echo "Periode: {$periode}\n\n";

// Global total (apa yang seharusnya ditampilkan)
echo "1. GLOBAL TOTAL (dari seluruh tabel pandu_prod filtered by INVOICE_DATE)\n";
$globalPandu = DB::connection('dashboard_phinnisi')->table('pandu_prod')
    ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
    ->sum('REVENUE');
echo "   Total Pandu: " . number_format($globalPandu, 0, ',', '.') . "\n\n";

// Regional breakdown
echo "2. BREAKDOWN PER WILAYAH (sum per NAME_BRANCH)\n";

$regionalGroups = [
    'WILAYAH 1' => [
        'REGIONAL 1 BELAWAN',
        'REGIONAL 1 DUMAI',
        'REGIONAL 1 KUALA TANJUNG',
        'REGIONAL 1 LHOKSEUMAWE',
        'REGIONAL 1 MALAHAYATI',
        'REGIONAL 1 PANJANG',
        'REGIONAL 1 PALEMBANG',
        'REGIONAL 1 PANGKAL BALAM',
        'REGIONAL 1 PEKANBARU',
        'REGIONAL 1 TELUK BAYUR',
        'REGIONAL 1 JAMBI'
    ],
    'WILAYAH 2' => [
        'REGIONAL 2 BENGKULU',
        'REGIONAL 2 BANTEN',
        'REGIONAL 2 CIREBON',
        'REGIONAL 2 JAKARTA',
        'REGIONAL 2 PALEMBANG',
        'REGIONAL 2 PANJANG',
        'REGIONAL 2 PONTIANAK',
        'REGIONAL 2 SEMARANG',
        'REGIONAL 2 TANJUNG PRIOK',
        'REGIONAL 2 TANJUNG PERAK',
        'REGIONAL 2 TELUK BAYUR'
    ],
    'WILAYAH 3' => [
        'REGIONAL 3 AMBON',
        'REGIONAL 3 BANJARMASIN',
        'REGIONAL 3 BALIKPAPAN',
        'REGIONAL 3 BAUBAU',
        'REGIONAL 3 BENOA',
        'REGIONAL 3 BITUNG',
        'REGIONAL 3 GORONTALO',
        'REGIONAL 3 KENDARI',
        'REGIONAL 3 KUPANG',
        'REGIONAL 3 MAKASSAR',
        'REGIONAL 3 PALANGKARAYA',
        'REGIONAL 3 PANTOLOAN',
        'REGIONAL 3 PONTIANAK',
        'REGIONAL 3 SAMPIT',
        'REGIONAL 3 TERNATE',
        'REGIONAL 3 TARAKAN'
    ],
    'WILAYAH 4' => [
        'REGIONAL 4 AMBON',
        'REGIONAL 4 BENOA',
        'REGIONAL 4 BIAK',
        'REGIONAL 4 BITUNG',
        'REGIONAL 4 FAK FAK',
        'REGIONAL 4 GORONTALO',
        'REGIONAL 4 JAYAPURA',
        'REGIONAL 4 KENDARI',
        'REGIONAL 4 KUPANG',
        'REGIONAL 4 LEMBAR',
        'REGIONAL 4 MAUMERE',
        'REGIONAL 4 MAKASSAR',
        'REGIONAL 4 MANOKWARI',
        'REGIONAL 4 MERAUKE',
        'REGIONAL 4 PANTOLOAN',
        'REGIONAL 4 PALANGKARAYA',
        'REGIONAL 4 PONTIANAK',
        'REGIONAL 4 SAMPIT',
        'REGIONAL 4 SANANA',
        'REGIONAL 4 SORONG',
        'REGIONAL 4 TERNATE',
        'REGIONAL 4 TAHUNA',
        'REGIONAL 4 TARAKAN',
        'REGIONAL 4 TIMIKA',
        'REGIONAL 4 TUAL',
        'REGIONAL 4 WAINGAPU',
        'REGIONAL 4 BULI',
        'REGIONAL 4 DOBO',
        'REGIONAL 4 SAU SAU'
    ]
];

$totalRegional = 0;

foreach ($regionalGroups as $wilayah => $branches) {
    $panduRevenue = DB::connection('dashboard_phinnisi')->table('pandu_prod')
        ->whereIn('NAME_BRANCH', $branches)
        ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
        ->sum('REVENUE');
    
    echo "   {$wilayah}: " . number_format($panduRevenue, 0, ',', '.') . "\n";
    $totalRegional += $panduRevenue;
}

echo "\n   TOTAL REGIONAL: " . number_format($totalRegional, 0, ',', '.') . "\n\n";

// Difference
$diff = $globalPandu - $totalRegional;
echo "3. PERBEDAAN (Global - Regional)\n";
echo "   Difference: " . number_format($diff, 0, ',', '.') . "\n";

if ($diff != 0) {
    echo "\n4. ANALISIS PERBEDAAN\n";
    
    // Check records with NULL or empty NAME_BRANCH
    $nullBranch = DB::connection('dashboard_phinnisi')->table('pandu_prod')
        ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
        ->where(function($q) {
            $q->whereNull('NAME_BRANCH')->orWhere('NAME_BRANCH', '');
        })
        ->sum('REVENUE');
    
    echo "   Revenue dengan NAME_BRANCH NULL/empty: " . number_format($nullBranch, 0, ',', '.') . "\n";
    
    // Check for branches not in our regional groups
    $allDefinedBranches = array_merge(...array_values($regionalGroups));
    $otherBranches = DB::connection('dashboard_phinnisi')->table('pandu_prod')
        ->whereRaw("DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, '%d-%m-%Y'), '%m-%Y') = ?", [$periode])
        ->whereNotNull('NAME_BRANCH')
        ->where('NAME_BRANCH', '!=', '')
        ->whereNotIn('NAME_BRANCH', $allDefinedBranches)
        ->select('NAME_BRANCH', DB::raw('SUM(REVENUE) as total'))
        ->groupBy('NAME_BRANCH')
        ->get();
    
    if ($otherBranches->count() > 0) {
        echo "\n   Cabang yang TIDAK ada di regional groups:\n";
        foreach ($otherBranches as $branch) {
            echo "      - {$branch->NAME_BRANCH}: " . number_format($branch->total, 0, ',', '.') . "\n";
        }
    }
}

echo "\n=== KESIMPULAN ===\n";
echo "Logic DashboardWilayahController:\n";
echo "- Menghitung \$totalPandu = SUM(REVENUE) dari seluruh pandu_prod (filter INVOICE_DATE saja)\n";
echo "- Nilai yang ditampilkan di dashboard: " . number_format($globalPandu, 0, ',', '.') . "\n";
echo "- Ini adalah TOTAL KESELURUHAN tanpa filter wilayah/branch\n";

return 0;
