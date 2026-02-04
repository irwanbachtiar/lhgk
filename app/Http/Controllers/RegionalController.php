<?php
//tes git
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegionalController extends Controller
{private function getRegionalGroups()
    {
        return [
            'WILAYAH 1' => [
                'REGIONAL 1 BELAWAN',
                'REGIONAL 1 PANGKALAN SUSU',
                'REGIONAL 1 TEMBILAHAN',
                'REGIONAL 1 TANJUNG BALAI KARIMUN',
                'REGIONAL 1 PEKANBARU',
                'REGIONAL 1 SUNGAI PAKNING',
                'REGIONAL 1 LHOKSEUMAWE',
                'REGIONAL 1 MALAHAYATI',
                'REGIONAL 1 SIBOLGA',
                'REGIONAL 1 TANJUNG PINANG',
                'REGIONAL 1 DUMAI'
            ],
            'WILAYAH 2' => [
                'REGIONAL 2 BANTEN',
                'REGIONAL 2 CIREBON',
                'REGIONAL 2 TELUK BAYUR',
                'REGIONAL 2 PALEMBANG',
                'REGIONAL 2 JAMBI',
                'REGIONAL 2 TANJUNG PRIOK',
                'REGIONAL 2 TANJUNG PANDAN',
                'REGIONAL 2 PANGKAL BALAM',
                'REGIONAL 2 PONTIANAK',
                'REGIONAL 2 PANJANG',
                'REGIONAL 2 BENGKULU'
            ],
            'WILAYAH 3' => [
                'REGIONAL 3 BATANG',
                'REGIONAL 3 BENOA',
                'REGIONAL 3 SAMPIT',
                'REGIONAL 3 BANJARMASIN',
                'REGIONAL 3 KUMAI',
                'REGIONAL 3 TANJUNG INTAN',
                'REGIONAL 3 CELUKAN BAWANG',
                'REGIONAL 3 BUNATI & SATUI',
                'REGIONAL 3 BATULICIN',
                'REGIONAL 3 KOTABARU',
                'REGIONAL 3 MEKARPUTIH',
                'REGIONAL 3 LEMBAR',
                'REGIONAL 3 TENAU KUPANG',
                'REGIONAL 3 TANJUNG WANGI',
                'REGIONAL 3 TANJUNG PERAK',
                'REGIONAL 3 TANJUNG EMAS'
            ],
            'WILAYAH 4' => [
                'REGIONAL 4 AMAMAPARE',
                'REGIONAL 4 TANJUNG SANTAN',
                'REGIONAL 4 SANGKULIRANG',
                'REGIONAL 4 SANGATTA',
                'REGIONAL 4 BONTANG',
                'REGIONAL 4 UNIT INDOMINCO',
                'REGIONAL 4 BIAK',
                'REGIONAL 4 LUWUK',
                'REGIONAL 4 TANAH GROGOT',
                'REGIONAL 4 AMURANG',
                'REGIONAL 4 TANJUNG REDEB',
                'REGIONAL 4 BALIKPAPAN',
                'REGIONAL 4 MANOKWARI',
                'REGIONAL 4 TERNATE',
                'REGIONAL 4 FAKFAK',
                'REGIONAL 4 TOLITOLI',
                'REGIONAL 4 SORONG',
                'REGIONAL 4 JAYAPURA',
                'REGIONAL 4 GORONTALO',
                'REGIONAL 4 PAREPARE',
                'REGIONAL 4 AMBON',
                'REGIONAL 4 MERAUKE',
                'REGIONAL 4 PANTOLOAN',
                'REGIONAL 4 BITUNG',
                'REGIONAL 4 NUNUKAN',
                'REGIONAL 4 TARAKAN',
                'REGIONAL 4 SAMARINDA',
                'REGIONAL 4 KENDARI',
                'REGIONAL 4 MAKASSAR'
            ],
            'JAI' => [
                'JAI AREA IV STS MUSI',
                'JAI BAYAH',
                'JAI LAIWUI',
                'JAI NUSANTARA REGAS',
                'JAI PATIMBAN',
                'KANCI I',
                'KANCI II'
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

        // Only load data if period is selected
        if ($selectedPeriode != 'all') {
            // Get only WILAYAH branches (exclude JAI)
            $wilayahBranches = collect($regionalGroups)
                ->except('JAI')
                ->flatten()
                ->toArray();
            
            // Calculate totals for WILAYAH only (exclude JAI)
            $totalPandu = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                ->whereIn('NAME_BRANCH', $wilayahBranches)
                ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                ->sum('REVENUE');

            $totalTundaRevenue = DB::connection('dashboard_phinnisi')->table('tunda_prod')
                ->whereIn('NAME_BRANCH', $wilayahBranches)
                ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                ->sum('REVENUE');
            
            // Calculate total transaksi for WILAYAH only (exclude JAI)
            $totalTransaksi = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                ->select('BILLING')
                ->whereIn('NAME_BRANCH', $wilayahBranches)
                ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                ->distinct()
                ->count('BILLING');
            
            foreach ($regionalGroups as $wilayah => $branches) {
                // Get pandu revenue - sum all revenue based on invoice date period
                $panduRevenue = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                    ->whereIn('NAME_BRANCH', $branches)
                    ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                    ->sum('REVENUE');

                // Get tunda revenue per wilayah - sum REVENUE directly
                $tundaRevenue = DB::connection('dashboard_phinnisi')->table('tunda_prod')
                    ->whereIn('NAME_BRANCH', $branches)
                    ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                    ->sum('REVENUE');

                // Get transaction count (distinct BILLING from pandu_prod)
                $wilayahTransaksi = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                    ->select('BILLING')
                    ->whereIn('NAME_BRANCH', $branches)
                    ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                    ->distinct()
                    ->count('BILLING');

                $regionalData[$wilayah] = [
                    'pandu_revenue' => $panduRevenue ?? 0,
                    'tunda_revenue' => $tundaRevenue ?? 0,
                    'total_revenue' => (($panduRevenue ?? 0) + ($tundaRevenue ?? 0)),
                    'total_transaksi' => $wilayahTransaksi
                ];
            }
            
            // Calculate DELEGATION specific totals (from WILAYAH branches only, exclude JAI)
            $delegationData = [];
            $delegations = ['PELINDO', 'SPJM', 'JAI'];
            
            foreach ($delegations as $delegation) {
                $delPandu = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                    ->whereIn('NAME_BRANCH', $wilayahBranches)
                    ->where('DELEGATION', $delegation)
                    ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                    ->sum('REVENUE');
                
                $delTunda = DB::connection('dashboard_phinnisi')->table('tunda_prod')
                    ->whereIn('NAME_BRANCH', $wilayahBranches)
                    ->where('DELEGATION', $delegation)
                    ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                    ->sum('REVENUE');
                
                $delTransaksi = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                    ->select('BILLING')
                    ->whereIn('NAME_BRANCH', $wilayahBranches)
                    ->where('DELEGATION', $delegation)
                    ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                    ->distinct()
                    ->count('BILLING');
                
                $delegationData[$delegation] = [
                    'pandu' => $delPandu ?? 0,
                    'tunda' => $delTunda ?? 0,
                    'transaksi' => $delTransaksi
                ];
            }
            
            // Calculate JAI specific totals
            $jaiBranches = $regionalGroups['JAI'] ?? [];
            $jaiTotalPandu = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                ->whereIn('NAME_BRANCH', $jaiBranches)
                ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                ->sum('REVENUE');
            
            $jaiTotalTunda = DB::connection('dashboard_phinnisi')->table('tunda_prod')
                ->whereIn('NAME_BRANCH', $jaiBranches)
                ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                ->sum('REVENUE');
            
            $jaiTotalTransaksi = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                ->select('BILLING')
                ->whereIn('NAME_BRANCH', $jaiBranches)
                ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                ->distinct()
                ->count('BILLING');
        } else {
            $totalPandu = 0;
            $totalTundaRevenue = 0;
            $totalTransaksi = 0;
            $jaiTotalPandu = 0;
            $jaiTotalTunda = 0;
            $jaiTotalTransaksi = 0;
            $delegationData = [];
        }

        return view('regional-revenue', compact(
            'periods',
            'selectedPeriode',
            'regionalData',
            'regionalGroups',
            'totalTundaRevenue',
            'totalPandu',
            'totalTransaksi',
            'jaiTotalPandu',
            'jaiTotalTunda',
            'jaiTotalTransaksi',
            'delegationData'
        ));
    }
}
