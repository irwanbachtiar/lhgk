<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$periode = $argv[1] ?? '12-2025';

echo "Testing DashboardWilayahController logic output\n";
echo "Periode: {$periode}\n\n";

// Simulate exactly what the controller does
$selectedPeriode = $periode;

// Calculate global totals based on INVOICE_DATE (sum across whole table for the periode)
if ($selectedPeriode != 'all') {
    $totalPandu = DB::connection('dashboard_phinnisi')->table('pandu_prod')
        ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
        ->sum('REVENUE');

    $totalTundaRevenue = DB::connection('dashboard_phinnisi')->table('tunda_prod')
        ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
        ->sum('REVENUE');
} else {
    $totalPandu = 0;
    $totalTundaRevenue = 0;
}

echo "Controller Output:\n";
echo "  \$totalPandu = " . number_format($totalPandu, 0, ',', '.') . "\n";
echo "  \$totalTundaRevenue = " . number_format($totalTundaRevenue, 0, ',', '.') . "\n";
echo "  \$totalAll = " . number_format($totalPandu + $totalTundaRevenue, 0, ',', '.') . "\n\n";

// Check if there's a different query that might be running
echo "Checking alternative queries that might produce 116.181.029.458:\n\n";

// Maybe it's filtering by specific branches?
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

$allBranches = array_merge(...array_values($regionalGroups));

// Sum only branches in regional groups
$sumInGroups = DB::connection('dashboard_phinnisi')->table('pandu_prod')
    ->whereIn('NAME_BRANCH', $allBranches)
    ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
    ->sum('REVENUE');

echo "1. Sum with branches IN regional groups: " . number_format($sumInGroups, 0, ',', '.') . "\n";

// Check if grouped by BILLING
$groupedByBilling = DB::connection('dashboard_phinnisi')
    ->table(DB::raw('(SELECT BILLING, MAX(REVENUE) as REVENUE, MAX(INVOICE_DATE) as INVOICE_DATE FROM dashboard_phinnisi.pandu_prod GROUP BY BILLING) as pandu'))
    ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
    ->sum('REVENUE');

echo "2. Sum with GROUP BY BILLING: " . number_format($groupedByBilling, 0, ',', '.') . "\n";

// Check both filters
$groupedAndFiltered = DB::connection('dashboard_phinnisi')
    ->table(DB::raw('(SELECT BILLING, MAX(REVENUE) as REVENUE, MAX(INVOICE_DATE) as INVOICE_DATE, MAX(NAME_BRANCH) as NAME_BRANCH FROM dashboard_phinnisi.pandu_prod GROUP BY BILLING) as pandu'))
    ->whereIn('NAME_BRANCH', $allBranches)
    ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
    ->sum('REVENUE');

echo "3. Sum with GROUP BY BILLING + branch filter: " . number_format($groupedAndFiltered, 0, ',', '.') . "\n";

echo "\nIf value shown is 116.181.029.458, it might be from:\n";
echo "- Browser cache (hard refresh: Ctrl+Shift+R)\n";
echo "- Laravel view cache (run: php artisan view:clear)\n";
echo "- Using old controller code before our fixes\n";

return 0;
