<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SummaryController extends Controller
{
    public function index(Request $request)
    {
        $selectedPeriode = $request->get('periode', 'all');

        // Generate periods for last 24 months
        $periods = [];
        for ($i = 0; $i < 24; $i++) {
            $date = now()->subMonths($i);
            $periods[] = $date->format('m-Y');
        }

        // â”€â”€ DUMMY SUMMARY DATA (angka realistis dalam miliaran Rp) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $summaryData = [
            'marine' => [
                'total'     => 45_600_000_000,
                'derum'     => [
                    'realisasi'  => 28_350_000_000,
                    'anggaran'   => 31_000_000_000,
                    'persentase' => 91.5,
                ],
                'non_derum' => [
                    'realisasi'  => 17_250_000_000,
                    'anggaran'   => 19_500_000_000,
                    'persentase' => 88.5,
                ],
            ],
            'bbm' => [
                'total'      => 24_300_000_000,
                'realisasi'  => 24_300_000_000,
                'anggaran'   => 27_000_000_000,
                'persentase' => 90.0,
            ],
            'air' => [
                'total'        => 8_550_000_000,
                'air_kapal'    => [
                    'realisasi'  => 4_800_000_000,
                    'anggaran'   => 5_500_000_000,
                    'persentase' => 87.3,
                ],
                'air_umum'     => [
                    'realisasi'  => 2_150_000_000,
                    'anggaran'   => 2_400_000_000,
                    'persentase' => 89.6,
                ],
                'air_kontrakor' => [
                    'realisasi'  => 1_600_000_000,
                    'anggaran'   => 1_800_000_000,
                    'persentase' => 88.9,
                ],
            ],
            'listrik' => [
                'total'      => 12_750_000_000,
                'realisasi'  => 12_750_000_000,
                'anggaran'   => 14_000_000_000,
                'persentase' => 91.1,
            ],
            'equipment' => [
                'total'       => 18_420_000_000,
                'realisasi'   => 18_420_000_000,
                'anggaran'    => 21_000_000_000,
                'persentase'  => 87.7,
                'proyek'      => ['realisasi' => 8_500_000_000,  'anggaran' => 9_500_000_000,  'persentase' => 89.5],
                'sparepark'   => ['realisasi' => 3_920_000_000,  'anggaran' => 4_500_000_000,  'persentase' => 87.1],
                'maintenance' => ['realisasi' => 4_800_000_000,  'anggaran' => 5_500_000_000,  'persentase' => 87.3],
                'lainnya'     => ['realisasi' => 1_200_000_000,  'anggaran' => 1_500_000_000,  'persentase' => 80.0],
            ],
        ];

        // â”€â”€ TREND DATA (6 bulan terakhir) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $trendPeriods   = array_reverse(array_slice($periods, 0, 6));
        $trendMarine    = [40_500_000_000, 42_100_000_000, 43_800_000_000, 44_200_000_000, 45_100_000_000, 45_600_000_000];
        $trendBbm       = [21_000_000_000, 22_500_000_000, 23_100_000_000, 23_800_000_000, 24_000_000_000, 24_300_000_000];
        $trendAir       = [7_200_000_000,  7_600_000_000,  7_900_000_000,  8_100_000_000,  8_300_000_000,  8_550_000_000];
        $trendListrik   = [11_000_000_000, 11_400_000_000, 11_800_000_000, 12_100_000_000, 12_400_000_000, 12_750_000_000];
        $trendEquipment = [15_500_000_000, 16_200_000_000, 17_000_000_000, 17_500_000_000, 18_000_000_000, 18_420_000_000];

        // â”€â”€ DATA PER WILAYAH (untuk stacked bar chart) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $wilayahData = [
            'wilayah_1' => ['marine' => 14_200_000_000, 'bbm' => 7_800_000_000,  'air' => 2_650_000_000, 'listrik' => 4_100_000_000, 'equipment' => 5_900_000_000],
            'wilayah_2' => ['marine' => 12_800_000_000, 'bbm' => 6_500_000_000,  'air' => 2_100_000_000, 'listrik' => 3_400_000_000, 'equipment' => 4_700_000_000],
            'wilayah_3' => ['marine' => 10_900_000_000, 'bbm' => 5_800_000_000,  'air' => 1_950_000_000, 'listrik' => 2_950_000_000, 'equipment' => 4_200_000_000],
            'wilayah_4' => ['marine' =>  7_700_000_000, 'bbm' => 4_200_000_000,  'air' => 1_850_000_000, 'listrik' => 2_300_000_000, 'equipment' => 3_620_000_000],
        ];

        // â”€â”€ SEGMENT DETAIL PER WILAYAH â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $segmentWilayahData = [
            'marine' => [
                'wilayah_1' => ['derum' => 8_700_000_000,  'non_derum' => 5_500_000_000,  'total' => 14_200_000_000],
                'wilayah_2' => ['derum' => 7_900_000_000,  'non_derum' => 4_900_000_000,  'total' => 12_800_000_000],
                'wilayah_3' => ['derum' => 6_700_000_000,  'non_derum' => 4_200_000_000,  'total' => 10_900_000_000],
                'wilayah_4' => ['derum' => 5_050_000_000,  'non_derum' => 2_650_000_000,  'total' =>  7_700_000_000],
            ],
            'bbm' => [
                'wilayah_1' => 7_800_000_000,
                'wilayah_2' => 6_500_000_000,
                'wilayah_3' => 5_800_000_000,
                'wilayah_4' => 4_200_000_000,
            ],
            'air' => [
                'wilayah_1' => ['air_kapal' => 1_500_000_000, 'air_umum' => 750_000_000, 'air_kontrakor' => 400_000_000, 'total' => 2_650_000_000],
                'wilayah_2' => ['air_kapal' => 1_200_000_000, 'air_umum' => 580_000_000, 'air_kontrakor' => 320_000_000, 'total' => 2_100_000_000],
                'wilayah_3' => ['air_kapal' => 1_100_000_000, 'air_umum' => 520_000_000, 'air_kontrakor' => 330_000_000, 'total' => 1_950_000_000],
                'wilayah_4' => ['air_kapal' => 1_000_000_000, 'air_umum' => 500_000_000, 'air_kontrakor' => 350_000_000, 'total' => 1_850_000_000],
            ],
            'listrik' => [
                'wilayah_1' => 4_100_000_000,
                'wilayah_2' => 3_400_000_000,
                'wilayah_3' => 2_950_000_000,
                'wilayah_4' => 2_300_000_000,
            ],
            'equipment' => [
                'wilayah_1' => ['proyek' => 2_700_000_000, 'sparepark' => 1_250_000_000, 'maintenance' => 1_550_000_000, 'lainnya' => 400_000_000, 'total' => 5_900_000_000],
                'wilayah_2' => ['proyek' => 2_150_000_000, 'sparepark' => 1_020_000_000, 'maintenance' => 1_230_000_000, 'lainnya' => 300_000_000, 'total' => 4_700_000_000],
                'wilayah_3' => ['proyek' => 1_900_000_000, 'sparepark' =>   900_000_000, 'maintenance' => 1_100_000_000, 'lainnya' => 300_000_000, 'total' => 4_200_000_000],
                'wilayah_4' => ['proyek' => 1_750_000_000, 'sparepark' =>   750_000_000, 'maintenance' =>   920_000_000, 'lainnya' => 200_000_000, 'total' => 3_620_000_000],
            ],
        ];

        // â”€â”€ DB connection flag â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $dbConnected = false;
        $dbError     = null;
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Exception $e) {
            $dbError = $e->getMessage();
        }

        return view('summary', compact(
            'periods',
            'selectedPeriode',
            'summaryData',
            'trendPeriods',
            'trendMarine',
            'trendBbm',
            'trendAir',
            'trendListrik',
            'trendEquipment',
            'wilayahData',
            'segmentWilayahData',
            'dbConnected',
            'dbError'
        ));
    }

    /**
     * Summary LHGK - menampilkan semua branch dikelompokkan per wilayah
     */
    public function summaryLhgk(Request $request)
    {
        $selectedPeriode = $request->get('periode', 'all');
        $regionalGroups = $this->getRegionalGroups();

        // Get available periods
        $periods = [];
        try {
            $periods = DB::connection('dashboard_phinnisi')->table('lhgk')
                ->select('PERIODE')
                ->whereNotNull('PERIODE')
                ->where('PERIODE', '!=', '')
                ->groupBy('PERIODE')
                ->orderByRaw("STR_TO_DATE(CONCAT('01-', PERIODE), '%d-%m-%Y') DESC")
                ->pluck('PERIODE')
                ->toArray();
        } catch (\Exception $e) {
            $periods = [];
        }

        $branchSummary = collect();

        if ($selectedPeriode !== 'all') {
            try {
                // Get all branches with data
                $allBranches = DB::connection('dashboard_phinnisi')->table('lhgk')
                    ->select('NM_BRANCH')
                    ->where('PERIODE', $selectedPeriode)
                    ->whereNotNull('NM_BRANCH')
                    ->where('NM_BRANCH', '!=', '')
                    ->distinct()
                    ->orderBy('NM_BRANCH')
                    ->pluck('NM_BRANCH')
                    ->toArray();

                if (empty($allBranches)) {
                    return view('summary-lhgk', compact('periods', 'selectedPeriode', 'branchSummary', 'regionalGroups'));
                }

                // Step 1: Get basic stats (count, realisasi counts)
                $basicStats = DB::connection('dashboard_phinnisi')->table('lhgk')
                    ->where('PERIODE', $selectedPeriode)
                    ->whereIn('NM_BRANCH', $allBranches)
                    ->selectRaw("
                        NM_BRANCH,
                        COUNT(*) as jumlah_gerakan,
                        SUM(CASE WHEN UPPER(REALISAS_PILOT_VIA) = 'WEB' THEN 1 ELSE 0 END) as realisasi_pandu_web,
                        SUM(CASE WHEN UPPER(REALISAS_PILOT_VIA) = 'MOBILE' THEN 1 ELSE 0 END) as realisasi_pandu_mobile,
                        SUM(CASE WHEN UPPER(REALISAS_PILOT_VIA) = 'PARTIAL' THEN 1 ELSE 0 END) as realisasi_pandu_partial
                    ")
                    ->groupBy('NM_BRANCH')
                    ->get()
                    ->keyBy('NM_BRANCH');

                // Step 2a: Hitung kapal unik dari gabungan NM_KAPAL_1, NM_KAPAL_2, dan NM_KAPAL_3
                $kapalStats = collect();
                try {
                    $q1 = DB::connection('dashboard_phinnisi')->table('lhgk')
                        ->select('NM_BRANCH', 'NM_KAPAL_1 as nama_kapal')
                        ->where('PERIODE', $selectedPeriode)
                        ->whereIn('NM_BRANCH', $allBranches)
                        ->whereNotNull('NM_KAPAL_1')->where('NM_KAPAL_1', '!=', '');
                        
                    $q2 = DB::connection('dashboard_phinnisi')->table('lhgk')
                        ->select('NM_BRANCH', 'NM_KAPAL_2 as nama_kapal')
                        ->where('PERIODE', $selectedPeriode)
                        ->whereIn('NM_BRANCH', $allBranches)
                        ->whereNotNull('NM_KAPAL_2')->where('NM_KAPAL_2', '!=', '');
                        
                    $q3 = DB::connection('dashboard_phinnisi')->table('lhgk')
                        ->select('NM_BRANCH', 'NM_KAPAL_3 as nama_kapal')
                        ->where('PERIODE', $selectedPeriode)
                        ->whereIn('NM_BRANCH', $allBranches)
                        ->whereNotNull('NM_KAPAL_3')->where('NM_KAPAL_3', '!=', '');

                    $unionQuery = $q1->union($q2)->union($q3);
                    
                    $kapalStats = DB::connection('dashboard_phinnisi')
                        ->query()
                        ->fromSub($unionQuery, 'unioned_kapal')
                        ->select('NM_BRANCH', DB::raw('COUNT(DISTINCT nama_kapal) as total_kapal'))
                        ->groupBy('NM_BRANCH')
                        ->get()
                        ->keyBy('NM_BRANCH');
                } catch (\Exception $e) {
                    \Log::warning('Kolom NM_KAPAL_* tidak ditemukan di tabel lhgk.');
                }

                // Step 2: Get all remaining stats in ONE query (simpler WHERE clauses)
                $stats = DB::connection('dashboard_phinnisi')->table('lhgk')
                    ->where('PERIODE', $selectedPeriode)
                    ->whereIn('NM_BRANCH', $allBranches)
                    ->selectRaw("
                        NM_BRANCH,
                        COUNT(DISTINCT NM_PERS_PANDU) as personil_pandu,
                        SUM(CASE WHEN STATUS_NOTA = 'batal' THEN 1 ELSE 0 END) as nota_batal,
                        SUM(CASE WHEN STATUS_NOTA = 'belum verifikasi' THEN 1 ELSE 0 END) as nota_belum_verifikasi,
                        SUM(CASE WHEN (STATUS_NOTA = 'menunggu nota' OR STATUS_NOTA = 'belum verifikasi') THEN 1 ELSE 0 END) as status_nota
                    ")
                    ->groupBy('NM_BRANCH')
                    ->get()
                    ->keyBy('NM_BRANCH');

                // Step 5: Pre-fetch nota data from pandu_prod (optional - can be slow)
                $notaDataByBranch = [];
                // Skip pandu_prod for now due to performance - can be added back with proper indexing
                /*
                try {
                    $notaRecords = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                        ->where('BILLING', 'NOT LIKE', '%HIS%')
                        ->where('INVOICE', 'NOT LIKE', '%INV%')
                        ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                        ->whereIn('NAME_BRANCH', $allBranches)
                        ->select('NAME_BRANCH', 'INVOICE')
                        ->distinct()
                        ->get();

                    foreach ($notaRecords as $record) {
                        if (!isset($notaDataByBranch[$record->NAME_BRANCH])) {
                            $notaDataByBranch[$record->NAME_BRANCH] = 0;
                        }
                        $notaDataByBranch[$record->NAME_BRANCH]++;
                    }
                } catch (\Exception $e) {
                    // Ignore nota errors
                }
                */

                // Build summary from pre-aggregated data
                foreach ($allBranches as $branch) {
                    $basic = $basicStats[$branch] ?? null;
                    if (!$basic) {
                        continue;
                    }

                    $stat = $stats[$branch] ?? (object)[];

                    // Assemble branch summary
                    $branchSummary->push((object)[
                        'NM_BRANCH' => $branch,
                        'jumlah_gerakan' => (int)($basic->jumlah_gerakan ?? 0),
                        'realisasi_pandu' => (object)[
                            'web' => (int)($basic->realisasi_pandu_web ?? 0),
                            'mobile' => (int)($basic->realisasi_pandu_mobile ?? 0),
                            'partial' => (int)($basic->realisasi_pandu_partial ?? 0),
                        ],
                        'realisasi_tunda' => (object)[
                            'web' => 0,
                            'mobile' => 0,
                            'partial' => 0,
                        ],
                        'kapal_tunda' => (int)($kapalStats[$branch]->total_kapal ?? 0),
                        'personil_pandu' => (int)($stat->personil_pandu ?? 0),
                        'nota_data' => (object)[
                            'terbit' => 0,
                            'batal' => (int)($stat->nota_batal ?? 0),
                            'belum_verifikasi' => (int)($stat->nota_belum_verifikasi ?? 0),
                            'kecepatan_terbit' => 0
                        ],
                        'invoice_lebih_2_hari' => 0,
                        'status_nota' => (int)($stat->status_nota ?? 0),
                        'backdate' => 0,
                        'waiting_time_over_30' => 0
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but continue
                \Log::error('Summary LHGK Error: ' . $e->getMessage());
            }
        }

        // Group branches by regional area
        $groupedBranches = [];
        foreach ($regionalGroups as $wilayah => $branches) {
            $groupedBranches[$wilayah] = $branchSummary
                ->filter(function ($item) use ($branches) {
                    return in_array($item->NM_BRANCH, $branches);
                })
                ->sortBy('NM_BRANCH')
                ->values();
        }

        // Remove empty groups
        $groupedBranches = array_filter($groupedBranches, function ($group) {
            return $group->count() > 0;
        });

        return view('summary-lhgk', compact(
            'periods',
            'selectedPeriode',
            'branchSummary',
            'groupedBranches',
            'regionalGroups'
        ));
    }

    private function getRegionalGroups()
    {
        return [
            'WILAYAH 1' => [
                'REGIONAL 1 BELAWAN',
                'REGIONAL 1 BATAM',
                'REGIONAL 1 PANGKALAN SUSU',
                'REGIONAL 1 TEMBILAHAN',
                'REGIONAL 1 TANJUNG BALAI KARIMUN',
                'REGIONAL 1 PEKANBARU',
                'REGIONAL 1 SUNGAI PAKNING',
                'REGIONAL 1 LHOKSEUMAWE',
                'REGIONAL 1 MALAHAYATI',
                'REGIONAL 1 SIBOLGA',
                'REGIONAL 1 TANJUNG PINANG',
                'REGIONAL 1 DUMAI',
                'REGIONAL 1 SELAT MALAKA',
                'REGIONAL 1 KUALA TANJUNG',
                'REGIONAL 1 BENGKALIS',
                'REGIONAL 1 TANJUNG BALAI ASAHAN',
                'REGIONAL 1 SELAT PANJANG',
                'REGIONAL 1 KUALA CINAKU',
                'REGIONAL 1 GUNUNGSITOLI'
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
                'REGIONAL 2 BENGKULU',
                'REGIONAL 2 SUNDA KELAPA'
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
                'REGIONAL 3 TANJUNG EMAS',
                'REGIONAL 3 BIMA',
                'REGIONAL 3 BADAS',
                'REGIONAL 3 PULANG PISAU',
                'REGIONAL 3 PROBOLINGGO',
                'REGIONAL 3 LABUAN BAJO',
                'REGIONAL 3 KALABAHI',
                'REGIONAL 3 TEGAL',
                'REGIONAL 3 ENDE',
                'REGIONAL 3 MAUMERE',
                'REGIONAL 3 WAINGAPU',
                'REGIONAL 3 KALIANGET'
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
                'REGIONAL 4 MAKASSAR',
                'REGIONAL 4 BULA',
                'REGIONAL 4 MANADO'
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
}
