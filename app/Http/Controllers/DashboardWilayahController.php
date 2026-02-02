<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardWilayahController extends Controller
{
    private function getRegionalGroups()
    {
        return [
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
    }

    public function index(Request $request)
    {
        $selectedPeriode = $request->get('periode', 'all');
        
        // Get available periods from INVOICE_DATE
        $periods = DB::connection('dashboard_phinnisi')->table('pandu_prod')
            ->selectRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') as periode')
            ->whereNotNull('INVOICE_DATE')
            ->where('INVOICE_DATE', '!=', '')
            ->groupBy('periode')
            ->orderByRaw('STR_TO_DATE(CONCAT(\'01-\', periode), \'%d-%m-%Y\') DESC')
            ->pluck('periode');
        
        $regionalGroups = $this->getRegionalGroups();
        $regionalData = [];
        
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
        
        // Get data for each region
        foreach ($regionalGroups as $wilayah => $branches) {
            // Get pandu revenue - sum all revenue based on invoice date period
            if ($selectedPeriode != 'all') {
                $panduRevenue = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                    ->whereIn('NAME_BRANCH', $branches)
                    ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                    ->sum('REVENUE');
                
                // Get tunda revenue per wilayah
                $tundaRevenue = DB::connection('dashboard_phinnisi')->table('tunda_prod')
                    ->whereIn('NAME_BRANCH', $branches)
                    ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                    ->sum('REVENUE');
                
                // Count transaksi pandu
                $transaksiPandu = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                    ->select('BILLING')
                    ->whereIn('NAME_BRANCH', $branches)
                    ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                    ->distinct()
                    ->count('BILLING');
                
                // Count transaksi tunda
                $transaksiTunda = DB::connection('dashboard_phinnisi')->table('tunda_prod')
                    ->select('BILLING')
                    ->whereIn('NAME_BRANCH', $branches)
                    ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                    ->distinct()
                    ->count('BILLING');
                
                $totalTransaksi = $transaksiPandu + $transaksiTunda;
            } else {
                $panduRevenue = 0;
                $tundaRevenue = 0;
                $totalTransaksi = 0;
            }
            
            $regionalData[$wilayah] = [
                'pandu_revenue' => $panduRevenue ?? 0,
                'tunda_revenue' => $tundaRevenue ?? 0,
                'total_revenue' => ($panduRevenue ?? 0) + ($tundaRevenue ?? 0),
                'total_transaksi' => $totalTransaksi
            ];
        }
        
        return view('dashboard-wilayah', compact(
            'periods',
            'selectedPeriode',
            'regionalData',
            'regionalGroups',
            'totalTundaRevenue',
            'totalPandu'
        ));
    }
}
