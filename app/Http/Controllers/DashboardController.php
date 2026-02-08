<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lhgk;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function getRegionalGroups()
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
                'REGIONAL 4 NUSANTARA REGAS',
                'JAI PATIMBAN',
                'KANCI I',
                'KANCI II'
            ]
        ];
    }

    public function operasional(Request $request)
    {
        $selectedPeriode = $request->get('periode', 'all');
        $selectedBranch = $request->get('cabang', 'all');

        $regionalGroups = $this->getRegionalGroups();

        $allBranches = Lhgk::select('NM_BRANCH')
            ->whereNotNull('NM_BRANCH')
            ->where('NM_BRANCH', '!=', '')
            ->groupBy('NM_BRANCH')
            ->orderBy('NM_BRANCH')
            ->pluck('NM_BRANCH')
            ->toArray();

        $periods = Lhgk::select('PERIODE')
            ->whereNotNull('PERIODE')
            ->where('PERIODE', '!=', '')
            ->groupBy('PERIODE')
            ->orderByRaw("STR_TO_DATE(CONCAT('01-', PERIODE), '%d-%m-%Y') DESC")
            ->pluck('PERIODE');

        // If filters not selected, show empty state
        if ($selectedPeriode == 'all' || $selectedBranch == 'all') {
            $stats = [];
            $tundaDistinct = 0;
            $transaksiByShip = collect();
            return view('dashboard-operasional', compact('stats', 'tundaDistinct', 'transaksiByShip', 'periods', 'selectedPeriode', 'regionalGroups', 'allBranches', 'selectedBranch'));
        }

        // Base query with filters
        $baseQuery = Lhgk::where('PERIODE', $selectedPeriode)
            ->where('NM_BRANCH', $selectedBranch);

        // jumlah pandu (distinct NM_PERS_PANDU)
        $totalPandu = (clone $baseQuery)->distinct('NM_PERS_PANDU')->count('NM_PERS_PANDU');

        // jumlah kapal tunda: count distinct names across NM_KAPAL_1/2/3
        $tundaResult = DB::connection('dashboard_phinnisi')->selectOne(
            "SELECT COUNT(DISTINCT nm) as total FROM (
                SELECT NM_KAPAL_1 as nm FROM lhgk WHERE PERIODE = ? AND NM_BRANCH = ? AND NM_KAPAL_1 IS NOT NULL AND NM_KAPAL_1 != ''
                UNION ALL
                SELECT NM_KAPAL_2 as nm FROM lhgk WHERE PERIODE = ? AND NM_BRANCH = ? AND NM_KAPAL_2 IS NOT NULL AND NM_KAPAL_2 != ''
                UNION ALL
                SELECT NM_KAPAL_3 as nm FROM lhgk WHERE PERIODE = ? AND NM_BRANCH = ? AND NM_KAPAL_3 IS NOT NULL AND NM_KAPAL_3 != ''
            ) t",
            [$selectedPeriode, $selectedBranch, $selectedPeriode, $selectedBranch, $selectedPeriode, $selectedBranch]
        );

        $tundaDistinct = $tundaResult->total ?? 0;

        // jumlah transaksi berdasarkan kelompok pelayaran (Dalam Negeri / Luar Negeri) lalu jenis kapal, serta rata-rata GT
        $pelayaranCase = "CASE WHEN LOWER(PELAYARAN) LIKE '%luar%' THEN 'Luar Negeri' ELSE 'Dalam Negeri' END";

        // compute averages ignoring zero values using NULLIF; convert lama_tunda from HH:MM to decimal hours and capture counts of non-zero values for weighting
        $transaksiByShip = (clone $baseQuery)
            ->selectRaw("{$pelayaranCase} as pelayaran_group, JN_KAPAL, COUNT(*) as jumlah,
                AVG(NULLIF(
                    CASE
                        WHEN lama_tunda LIKE '%:%' THEN (CAST(SUBSTRING_INDEX(REPLACE(lama_tunda,' ',''), ':', 1) AS DECIMAL(12,2)) + (CAST(SUBSTRING_INDEX(REPLACE(lama_tunda,' ',''), ':', -1) AS DECIMAL(12,2)) / 60.0))
                        WHEN lama_tunda REGEXP '^[0-9]+(\\.[0-9]+)?$' THEN CAST(lama_tunda AS DECIMAL(12,2))
                        ELSE NULL
                    END
                , 0)) as avg_lama_tunda,
                SUM(CASE WHEN NULLIF(
                    CASE
                        WHEN lama_tunda LIKE '%:%' THEN (CAST(SUBSTRING_INDEX(REPLACE(lama_tunda,' ',''), ':', 1) AS DECIMAL(12,2)) + (CAST(SUBSTRING_INDEX(REPLACE(lama_tunda,' ',''), ':', -1) AS DECIMAL(12,2)) / 60.0))
                        WHEN lama_tunda REGEXP '^[0-9]+(\\.[0-9]+)?$' THEN CAST(lama_tunda AS DECIMAL(12,2))
                        ELSE NULL
                    END
                , 0) IS NOT NULL THEN 1 ELSE 0 END) as cnt_lama,
                AVG(NULLIF(CAST(KP_GRT AS DECIMAL(12,2)), 0)) as avg_grt,
                SUM(CASE WHEN NULLIF(CAST(KP_GRT AS DECIMAL(12,2)), 0) IS NOT NULL THEN 1 ELSE 0 END) as cnt_grt,
                AVG(NULLIF(CAST(TRT AS DECIMAL(12,2)), 0)) as avg_trt,
                SUM(CASE WHEN NULLIF(CAST(TRT AS DECIMAL(12,2)), 0) IS NOT NULL THEN 1 ELSE 0 END) as cnt_trt,
                AVG(NULLIF(CAST(AT_Jam AS DECIMAL(12,2)), 0)) as avg_at,
                SUM(CASE WHEN NULLIF(CAST(AT_Jam AS DECIMAL(12,2)), 0) IS NOT NULL THEN 1 ELSE 0 END) as cnt_at")
            ->whereNotNull('JN_KAPAL')
            ->where('JN_KAPAL', '!=', '')
            ->groupBy(DB::raw($pelayaranCase), 'JN_KAPAL')
            ->orderByRaw("{$pelayaranCase} ASC")
            ->orderBy('jumlah', 'desc')
            ->get();

        $stats = [
            'total_pandu' => $totalPandu,
            'total_tunda_kapal' => $tundaDistinct,
            'total_transaksi' => (clone $baseQuery)->count()
        ];

        // counts of REALISAS_PILOT_VIA (web, mobile, partial) from lhgk
        $viaCounts = (clone $baseQuery)
            ->selectRaw("SUM(CASE WHEN UPPER(REALISAS_PILOT_VIA) = 'WEB' THEN 1 ELSE 0 END) as web")
            ->selectRaw("SUM(CASE WHEN UPPER(REALISAS_PILOT_VIA) = 'MOBILE' THEN 1 ELSE 0 END) as mobile")
            ->selectRaw("SUM(CASE WHEN UPPER(REALISAS_PILOT_VIA) = 'PARTIAL' THEN 1 ELSE 0 END) as partial")
            ->first();

        // daftar nama pandu (distinct)
        $pilotList = (clone $baseQuery)
            ->whereNotNull('NM_PERS_PANDU')
            ->where('NM_PERS_PANDU', '!=', '')
            ->groupBy('NM_PERS_PANDU')
            ->orderBy('NM_PERS_PANDU')
            ->pluck('NM_PERS_PANDU')
            ->toArray();

        // daftar nama tunda (distinct across NM_KAPAL_1/2/3)
        $tundaRows = DB::connection('dashboard_phinnisi')->select(
            "SELECT DISTINCT nm FROM (
                SELECT NM_KAPAL_1 as nm FROM lhgk WHERE PERIODE = ? AND NM_BRANCH = ? AND NM_KAPAL_1 IS NOT NULL AND NM_KAPAL_1 != ''
                UNION ALL
                SELECT NM_KAPAL_2 as nm FROM lhgk WHERE PERIODE = ? AND NM_BRANCH = ? AND NM_KAPAL_2 IS NOT NULL AND NM_KAPAL_2 != ''
                UNION ALL
                SELECT NM_KAPAL_3 as nm FROM lhgk WHERE PERIODE = ? AND NM_BRANCH = ? AND NM_KAPAL_3 IS NOT NULL AND NM_KAPAL_3 != ''
            ) t ORDER BY nm",
            [$selectedPeriode, $selectedBranch, $selectedPeriode, $selectedBranch, $selectedPeriode, $selectedBranch]
        );

        $tundaList = array_map(function($r) { return $r->nm ?? null; }, $tundaRows);

        return view('dashboard-operasional', compact('stats', 'tundaDistinct', 'transaksiByShip', 'periods', 'selectedPeriode', 'regionalGroups', 'allBranches', 'selectedBranch', 'pilotList', 'tundaList', 'viaCounts'));
    }

    public function exportOperasional(Request $request)
    {
        $selectedPeriode = $request->get('periode');
        $selectedBranch = $request->get('cabang');

        if (!$selectedPeriode || !$selectedBranch) {
            return redirect()->back()->with('error', 'Pilih periode dan cabang terlebih dahulu');
        }

        $baseQuery = Lhgk::where('PERIODE', $selectedPeriode)
            ->where('NM_BRANCH', $selectedBranch);

        $pelayaranCase = "CASE WHEN LOWER(PELAYARAN) LIKE '%luar%' THEN 'Luar Negeri' ELSE 'Dalam Negeri' END";

        $rows = (clone $baseQuery)
            ->selectRaw("{$pelayaranCase} as pelayaran_group, JN_KAPAL, COUNT(*) as jumlah,
                AVG(NULLIF(
                    CASE
                        WHEN lama_tunda LIKE '%:%' THEN (CAST(SUBSTRING_INDEX(REPLACE(lama_tunda,' ',''), ':', 1) AS DECIMAL(12,2)) + (CAST(SUBSTRING_INDEX(REPLACE(lama_tunda,' ',''), ':', -1) AS DECIMAL(12,2)) / 60.0))
                        WHEN lama_tunda REGEXP '^[0-9]+(\\.[0-9]+)?$' THEN CAST(lama_tunda AS DECIMAL(12,2))
                        ELSE NULL
                    END
                , 0)) as avg_lama_tunda,
                SUM(CASE WHEN NULLIF(
                    CASE
                        WHEN lama_tunda LIKE '%:%' THEN (CAST(SUBSTRING_INDEX(REPLACE(lama_tunda,' ',''), ':', 1) AS DECIMAL(12,2)) + (CAST(SUBSTRING_INDEX(REPLACE(lama_tunda,' ',''), ':', -1) AS DECIMAL(12,2)) / 60.0))
                        WHEN lama_tunda REGEXP '^[0-9]+(\\.[0-9]+)?$' THEN CAST(lama_tunda AS DECIMAL(12,2))
                        ELSE NULL
                    END
                , 0) IS NOT NULL THEN 1 ELSE 0 END) as cnt_lama,
                AVG(NULLIF(CAST(KP_GRT AS DECIMAL(12,2)), 0)) as avg_grt,
                SUM(CASE WHEN NULLIF(CAST(KP_GRT AS DECIMAL(12,2)), 0) IS NOT NULL THEN 1 ELSE 0 END) as cnt_grt,
                AVG(NULLIF(CAST(TRT AS DECIMAL(12,2)), 0)) as avg_trt,
                SUM(CASE WHEN NULLIF(CAST(TRT AS DECIMAL(12,2)), 0) IS NOT NULL THEN 1 ELSE 0 END) as cnt_trt,
                AVG(NULLIF(CAST(AT_Jam AS DECIMAL(12,2)), 0)) as avg_at,
                SUM(CASE WHEN NULLIF(CAST(AT_Jam AS DECIMAL(12,2)), 0) IS NOT NULL THEN 1 ELSE 0 END) as cnt_at")
            ->whereNotNull('JN_KAPAL')
            ->where('JN_KAPAL', '!=', '')
            ->groupBy(DB::raw($pelayaranCase), 'JN_KAPAL')
            ->orderByRaw("{$pelayaranCase} ASC")
            ->orderBy('JN_KAPAL')
            ->get();

        // Prepare grouped data for CSV by pelayaran_group then JN_KAPAL
        $grouped = $rows->groupBy('pelayaran_group');

        $filename = 'Operasional_' . str_replace(' ', '_', $selectedBranch) . '_' . str_replace('-', '', $selectedPeriode) . '_' . date('YmdHis') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($grouped) {
            $out = fopen('php://output', 'w');
            // BOM
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($out, ['Jenis Kapal', 'Pelayaran', 'Jumlah', 'Rata-rata Jam Tunda', 'Rata-rata GT', 'Rata-rata TRT', 'Rata-rata AT']);

            foreach ($grouped as $jn => $items) {
                // parent totals: sum jumlah, weighted avg
                $totalJumlah = $items->sum('jumlah');
                // compute weighted parent averages using counts of non-zero values (cnt_*)
                $weightedSumLama = $items->reduce(function($carry, $i) {
                    return $carry + ((float)($i->avg_lama_tunda ?? 0) * (int)($i->cnt_lama ?? 0));
                }, 0);
                $weightedSumGrt = $items->reduce(function($carry, $i) {
                    return $carry + ((float)($i->avg_grt ?? 0) * (int)($i->cnt_grt ?? 0));
                }, 0);
                $weightedSumTrt = $items->reduce(function($carry, $i) {
                    return $carry + ((float)($i->avg_trt ?? 0) * (int)($i->cnt_trt ?? 0));
                }, 0);
                $weightedSumAt = $items->reduce(function($carry, $i) {
                    return $carry + ((float)($i->avg_at ?? 0) * (int)($i->cnt_at ?? 0));
                }, 0);

                $totalCntLama = $items->sum(function($i) { return (int)($i->cnt_lama ?? 0); });
                $totalCntGrt = $items->sum(function($i) { return (int)($i->cnt_grt ?? 0); });
                $totalCntTrt = $items->sum(function($i) { return (int)($i->cnt_trt ?? 0); });
                $totalCntAt = $items->sum(function($i) { return (int)($i->cnt_at ?? 0); });

                $parentAvgLama = $totalCntLama ? ($weightedSumLama / $totalCntLama) : 0;
                $parentAvgGrt = $totalCntGrt ? ($weightedSumGrt / $totalCntGrt) : 0;
                $parentAvgTrt = $totalCntTrt ? ($weightedSumTrt / $totalCntTrt) : 0;
                $parentAvgAt = $totalCntAt ? ($weightedSumAt / $totalCntAt) : 0;

                fputcsv($out, [$jn, '', $totalJumlah, number_format((float)$parentAvgLama, 2, '.', ''), number_format((float)$parentAvgGrt, 2, '.', ''), number_format((float)$parentAvgTrt, 2, '.', ''), number_format((float)$parentAvgAt, 2, '.', '')]);

                foreach ($items as $it) {
                    fputcsv($out, [$it->JN_KAPAL ?? '', $it->pelayaran_group ?? '-', $it->jumlah, number_format((float)($it->avg_lama_tunda ?? 0), 2, '.', ''), number_format((float)($it->avg_grt ?? 0), 2, '.', ''), number_format((float)($it->avg_trt ?? 0), 2, '.', ''), number_format((float)($it->avg_at ?? 0), 2, '.', '')]);
                }
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function index(Request $request)
    {
        // Get selected period and branch
        $selectedPeriode = $request->get('periode', 'all');
        $selectedBranch = $request->get('cabang', 'all');

        // Get available branches with regional grouping
        $regionalGroups = $this->getRegionalGroups();
        
        // Get all available branches from database
        $allBranches = Lhgk::select('NM_BRANCH')
            ->whereNotNull('NM_BRANCH')
            ->where('NM_BRANCH', '!=', '')
            ->groupBy('NM_BRANCH')
            ->orderBy('NM_BRANCH')
            ->pluck('NM_BRANCH')
            ->toArray();

        // Get available periods from PERIODE column (format MM-YYYY)
        $periods = Lhgk::select('PERIODE')
            ->whereNotNull('PERIODE')
            ->where('PERIODE', '!=', '')
            ->groupBy('PERIODE')
            ->orderByRaw("STR_TO_DATE(CONCAT('01-', PERIODE), '%d-%m-%Y') DESC")
            ->pluck('PERIODE');

        // Only load data if both filters are selected (not 'all')
        if ($selectedPeriode == 'all' || $selectedBranch == 'all') {
            $statistics = collect();
            $totalOverall = [
                'total_nota' => 0,
                'total_pandu' => 0,
                'total_pendapatan_pandu' => 0,
                'total_pendapatan_tunda' => 0,
                'total_transaksi' => 0,
                'kecepatan_terbit_nota' => 0
            ];
            $topPilot = null;
            $shipStatsByGT = collect();
            $showDeparture = false;
            $departureDelayCount = 0;
            $showStatusNota = false;
            $statusNotaCount = 0;
            $showWaitingTime = false;
            $waitingTimeCount = 0;
            return view('dashboard', compact('statistics', 'totalOverall', 'periods', 'selectedPeriode', 'regionalGroups', 'allBranches', 'selectedBranch', 'topPilot', 'shipStatsByGT', 'showDeparture', 'departureDelayCount', 'showStatusNota', 'statusNotaCount', 'showWaitingTime', 'waitingTimeCount'));
        }

        // Build query with period filter
        $query = Lhgk::select('NM_PERS_PANDU')
            ->selectRaw('SUM(PENDAPATAN_PANDU) as total_pendapatan_pandu')
            ->selectRaw('SUM(PENDAPATAN_TUNDA) as total_pendapatan_tunda')
            ->selectRaw('COUNT(*) as total_transaksi')
            ->selectRaw('SUM(COALESCE(KP_GRT, 0)) as total_grt')
            ->selectRaw('AVG(CAST(SUBSTRING_INDEX(WT, " : ", 1) AS UNSIGNED) + CAST(SUBSTRING_INDEX(WT, " : ", -1) AS UNSIGNED) / 60.0) as rata_rata_wt')
            ->selectRaw('SUM(CASE WHEN (CAST(SUBSTRING_INDEX(WT, " : ", 1) AS UNSIGNED) + CAST(SUBSTRING_INDEX(WT, " : ", -1) AS UNSIGNED) / 60.0) > 0.5 THEN 1 ELSE 0 END) as transaksi_wt_di_atas_30')
            ->where('PERIODE', $selectedPeriode)
            ->where('NM_BRANCH', $selectedBranch);

        $statistics = $query->groupBy('NM_PERS_PANDU')
            ->orderBy('total_pendapatan_pandu', 'desc')
            ->get();

        // Get ship type breakdown and realisasi via for each pilot
        foreach ($statistics as $stat) {
            // Ship types
            $shipQuery = Lhgk::select('JN_KAPAL')
                ->selectRaw('COUNT(*) as jumlah')
                ->where('NM_PERS_PANDU', $stat->NM_PERS_PANDU)
                ->whereNotNull('JN_KAPAL')
                ->where('JN_KAPAL', '!=', '')
                ->where('PERIODE', $selectedPeriode)
                ->where('NM_BRANCH', $selectedBranch);

            $stat->ship_types = $shipQuery->groupBy('JN_KAPAL')
                ->orderBy('jumlah', 'desc')
                ->get();

            // Realisasi via (Mobile/Web)
            $viaQuery = Lhgk::select('REALISAS_PILOT_VIA')
                ->selectRaw('COUNT(*) as jumlah')
                ->where('NM_PERS_PANDU', $stat->NM_PERS_PANDU)
                ->whereNotNull('REALISAS_PILOT_VIA')
                ->where('REALISAS_PILOT_VIA', '!=', '')
                ->where('PERIODE', $selectedPeriode)
                ->where('NM_BRANCH', $selectedBranch);

            $realisasiVia = $viaQuery->groupBy('REALISAS_PILOT_VIA')->get();
            
            // Format ke array untuk kemudahan akses
            $stat->via_mobile = $realisasiVia->where('REALISAS_PILOT_VIA', 'MOBILE')->first()->jumlah ?? 0;
            $stat->via_web = $realisasiVia->where('REALISAS_PILOT_VIA', 'WEB')->first()->jumlah ?? 0;
            $stat->via_partial = $realisasiVia->where('REALISAS_PILOT_VIA', 'PARTIAL')->first()->jumlah ?? 0;
        }

        // Get total overall statistics dengan filter periode dan cabang
        $baseQuery = Lhgk::where('PERIODE', $selectedPeriode)
            ->where('NM_BRANCH', $selectedBranch);

        // Hitung kecepatan terbit nota (rata-rata selisih hari antara BILL_DATE dan SELESAI_PELAKSANAAN untuk GERAKAN='DEPARTURE')
        $kecepatanTerbitNota = (clone $baseQuery)
            ->whereRaw("GERAKAN = 'DEPARTURE'")
            ->whereNotNull('BILL_DATE')
            ->whereNotNull('SELESAI_PELAKSANAAN')
            ->where('BILL_DATE', '!=', '')
            ->where('SELESAI_PELAKSANAAN', '!=', '')
            ->selectRaw('AVG(DATEDIFF(STR_TO_DATE(BILL_DATE, "%d-%m-%Y"), STR_TO_DATE(SELESAI_PELAKSANAAN, "%d-%m-%Y"))) as avg_days')
            ->value('avg_days') ?? 0;

        // Hitung total nota dari pandu_prod (sama seperti di monitoring nota)
        $totalNota = DB::connection('dashboard_phinnisi')->table('pandu_prod')
            ->select('INVOICE')
            ->where('BILLING', 'NOT LIKE', '%HIS%')
            ->where('INVOICE', 'NOT LIKE', '%INV%')
            ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                return $q->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
            })
            ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                return $q->where('NAME_BRANCH', $selectedBranch);
            })
            ->distinct()
            ->count('INVOICE');

        // Hitung total dengan memastikan filter periode diterapkan
        // WT format: "HH : MM" (string), need to convert to decimal hours
        $totalOverall = [
            'total_nota' => $totalNota,
            'total_pandu' => (clone $baseQuery)->distinct('NM_PERS_PANDU')->count('NM_PERS_PANDU'),
            'total_pendapatan_pandu' => (clone $baseQuery)->sum('PENDAPATAN_PANDU'),
            'total_pendapatan_tunda' => (clone $baseQuery)->sum('PENDAPATAN_TUNDA'),
            'total_transaksi' => (clone $baseQuery)->count(),
            'transaksi_wt_di_atas_30' => (clone $baseQuery)
                ->whereRaw("(CAST(SUBSTRING_INDEX(WT, ' : ', 1) AS UNSIGNED) + CAST(SUBSTRING_INDEX(WT, ' : ', -1) AS UNSIGNED) / 60.0) > 0.5")
                ->count(),
            'rata_rata_wt' => (clone $baseQuery)
                ->selectRaw("AVG(CAST(SUBSTRING_INDEX(WT, ' : ', 1) AS UNSIGNED) + CAST(SUBSTRING_INDEX(WT, ' : ', -1) AS UNSIGNED) / 60.0) as avg_wt")
                ->value('avg_wt'),
            'max_wt' => (clone $baseQuery)
                ->selectRaw("MAX(CAST(SUBSTRING_INDEX(WT, ' : ', 1) AS UNSIGNED) + CAST(SUBSTRING_INDEX(WT, ' : ', -1) AS UNSIGNED) / 60.0) as max_wt")
                ->value('max_wt'),
            'nota_batal' => (clone $baseQuery)->where('STATUS_NOTA', 'batal')->count(),
            'menunggu_nota' => (clone $baseQuery)->where('STATUS_NOTA', 'menunggu nota')->count(),
            'belum_verifikasi' => (clone $baseQuery)->where('STATUS_NOTA', 'belum verifikasi')->count(),
            'kecepatan_terbit_nota' => round($kecepatanTerbitNota, 1)
        ];

        // Get top pilot dengan produksi tertinggi
        $topPilotQuery = Lhgk::select('NM_PERS_PANDU', 'NM_BRANCH')
            ->selectRaw('COUNT(*) as total_produksi')
            ->selectRaw('SUM(PENDAPATAN_PANDU) as total_pendapatan_pandu')
            ->selectRaw('SUM(PENDAPATAN_TUNDA) as total_pendapatan_tunda')
            ->selectRaw('SUM(PENDAPATAN_PANDU + PENDAPATAN_TUNDA) as total_pendapatan')
            ->selectRaw('AVG(CAST(SUBSTRING_INDEX(WT, " : ", 1) AS UNSIGNED) + CAST(SUBSTRING_INDEX(WT, " : ", -1) AS UNSIGNED) / 60.0) as rata_rata_wt')
            ->whereNotNull('NM_PERS_PANDU')
            ->where('NM_PERS_PANDU', '!=', '')
            ->where('PERIODE', $selectedPeriode)
            ->where('NM_BRANCH', $selectedBranch);

        $topPilot = $topPilotQuery->groupBy('NM_PERS_PANDU', 'NM_BRANCH')
            ->orderBy('total_produksi', 'desc')
            ->first();

        // Get statistics by RANGE_GT and JENIS_KAPAL_DARI_BENDERA
        $shipStatsByGT = Lhgk::select('RANGE_GT', 'JENIS_KAPAL_DARI_BENDERA')
            ->selectRaw('COUNT(*) as total_transaksi')
            ->selectRaw('SUM(COALESCE(TOTAL_PENDAPATAN_PANDU_CLEAN, 0)) as total_pendapatan_pandu')
            ->selectRaw('SUM(COALESCE(TOTAL_PENDAPATAN_TUNDA_CLEAN, 0)) as total_pendapatan_tunda')
            ->selectRaw('SUM(COALESCE(TOTAL_PENDAPATAN_PANDU_CLEAN, 0) + COALESCE(TOTAL_PENDAPATAN_TUNDA_CLEAN, 0)) as total_pendapatan')
            ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                return $q->where('PERIODE', $selectedPeriode);
            })
            ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                return $q->where('NM_BRANCH', $selectedBranch);
            })
            ->whereNotNull('RANGE_GT')
            ->whereNotNull('JENIS_KAPAL_DARI_BENDERA')
            ->where('RANGE_GT', '!=', '')
            ->where('JENIS_KAPAL_DARI_BENDERA', '!=', '')
            ->groupBy('RANGE_GT', 'JENIS_KAPAL_DARI_BENDERA')
            ->orderByRaw("FIELD(RANGE_GT, '0-3500 GT', '3501-8000 GT', '8001-14000 GT', '14001-18000 GT', '18001-26000 GT', '26001-40000 GT', '40001-75000 GT', '>75000 GT')")
            ->orderBy('JENIS_KAPAL_DARI_BENDERA')
            ->get();

        // Check if user wants to see departure delay data
        $showDeparture = $request->get('show_departure', 0);
        
        // Get count of departure delay data (lightweight query with limit for display)
        if ($showDeparture) {
            // When showing data, get exact count
            $departureDelayCount = Lhgk::whereRaw("GERAKAN = 'DEPARTURE'")
                ->whereNotNull('INVOICE_DATE')
                ->whereNotNull('SELESAI_PELAKSANAAN')
                ->where('INVOICE_DATE', '!=', '')
                ->where('SELESAI_PELAKSANAAN', '!=', '')
                ->whereRaw('DATEDIFF(STR_TO_DATE(INVOICE_DATE, "%d-%m-%Y"), STR_TO_DATE(SELESAI_PELAKSANAAN, "%d-%m-%Y")) > 2')
                ->where('PERIODE', $selectedPeriode)
                ->where('NM_BRANCH', $selectedBranch)
                ->count();
        } else {
            // When not showing, just check if any exists (faster)
            $departureDelayCount = Lhgk::whereRaw("GERAKAN = 'DEPARTURE'")
                ->whereNotNull('INVOICE_DATE')
                ->whereNotNull('SELESAI_PELAKSANAAN')
                ->where('INVOICE_DATE', '!=', '')
                ->where('SELESAI_PELAKSANAAN', '!=', '')
                ->whereRaw('DATEDIFF(STR_TO_DATE(INVOICE_DATE, "%d-%m-%Y"), STR_TO_DATE(SELESAI_PELAKSANAAN, "%d-%m-%Y")) > 2')
                ->where('PERIODE', $selectedPeriode)
                ->where('NM_BRANCH', $selectedBranch)
                ->limit(1)
                ->count();
        }

        // Check if user wants to see status nota data
        $showStatusNota = $request->get('show_status_nota', 0);
        $filterStatusNota = $request->get('filter_status_nota', 'all'); // all, menunggu nota, belum verifikasi
        
        // Build status nota filter array
        $statusNotaFilter = [];
        if ($filterStatusNota == 'all') {
            $statusNotaFilter = ['menunggu nota', 'belum verifikasi'];
        } else {
            $statusNotaFilter = [$filterStatusNota];
        }
        
        // Get count of status nota data
        if ($showStatusNota) {
            $statusNotaCount = Lhgk::whereIn('STATUS_NOTA', $statusNotaFilter)
                ->where('PERIODE', $selectedPeriode)
                ->where('NM_BRANCH', $selectedBranch)
                ->count();
        } else {
            $statusNotaCount = Lhgk::whereIn('STATUS_NOTA', $statusNotaFilter)
                ->where('PERIODE', $selectedPeriode)
                ->where('NM_BRANCH', $selectedBranch)
                ->limit(1)
                ->count();
        }

        // Load actual departure delay data only if requested
        $departureDelayData = null;
        if ($showDeparture && $departureDelayCount > 0) {
            $departureDelayData = Lhgk::select(
                    'NO_UKK',
                    'NM_KAPAL',
                    'NM_PERS_PANDU',
                    'NM_BRANCH',
                    'GERAKAN',
                    'SELESAI_PELAKSANAAN',
                    'INVOICE_DATE',
                    'PENDAPATAN_PANDU',
                    'PENDAPATAN_TUNDA'
                )
                ->selectRaw('DATEDIFF(STR_TO_DATE(INVOICE_DATE, "%d-%m-%Y"), STR_TO_DATE(SELESAI_PELAKSANAAN, "%d-%m-%Y")) as selisih_hari')
                ->whereRaw("GERAKAN = 'DEPARTURE'")
                ->whereNotNull('INVOICE_DATE')
                ->whereNotNull('SELESAI_PELAKSANAAN')
                ->where('INVOICE_DATE', '!=', '')
                ->where('SELESAI_PELAKSANAAN', '!=', '')
                ->whereRaw('DATEDIFF(STR_TO_DATE(INVOICE_DATE, "%d-%m-%Y"), STR_TO_DATE(SELESAI_PELAKSANAAN, "%d-%m-%Y")) > 2')
                ->where('PERIODE', $selectedPeriode)
                ->where('NM_BRANCH', $selectedBranch)
                ->orderByRaw('DATEDIFF(STR_TO_DATE(INVOICE_DATE, "%d-%m-%Y"), STR_TO_DATE(SELESAI_PELAKSANAAN, "%d-%m-%Y")) DESC')
                ->paginate(10)
                ->appends(['periode' => $selectedPeriode, 'cabang' => $selectedBranch, 'show_departure' => 1]);
        }

        // Load actual status nota data only if requested
        $statusNotaData = null;
        if ($showStatusNota && $statusNotaCount > 0) {
            $statusNotaData = Lhgk::select(
                    'NO_UKK',
                    'NM_KAPAL',
                    'PELAYARAN',
                    'NM_PERS_PANDU',
                    'MULAI_PELAKSANAAN',
                    'SELESAI_PELAKSANAAN',
                    'PENDAPATAN_PANDU',
                    'PENDAPATAN_TUNDA',
                    'STATUS_NOTA'
                )
                ->whereIn('STATUS_NOTA', $statusNotaFilter)
                ->where('PERIODE', $selectedPeriode)
                ->where('NM_BRANCH', $selectedBranch)
                ->orderBy('MULAI_PELAKSANAAN', 'desc')
                ->paginate(10)
                ->appends(['periode' => $selectedPeriode, 'cabang' => $selectedBranch, 'show_status_nota' => 1, 'filter_status_nota' => $filterStatusNota]);
        }

        // Check if user wants to see waiting time data
        $showWaitingTime = $request->get('show_waiting_time', 0);
        
        // Get count of waiting time data (WT > 00:30)
        if ($showWaitingTime) {
            $waitingTimeCount = Lhgk::whereRaw("(CAST(SUBSTRING_INDEX(WT, ' : ', 1) AS UNSIGNED) + CAST(SUBSTRING_INDEX(WT, ' : ', -1) AS UNSIGNED) / 60.0) > 0.5")
                ->where('PERIODE', $selectedPeriode)
                ->where('NM_BRANCH', $selectedBranch)
                ->count();
        } else {
            $waitingTimeCount = Lhgk::whereRaw("(CAST(SUBSTRING_INDEX(WT, ' : ', 1) AS UNSIGNED) + CAST(SUBSTRING_INDEX(WT, ' : ', -1) AS UNSIGNED) / 60.0) > 0.5")
                ->where('PERIODE', $selectedPeriode)
                ->where('NM_BRANCH', $selectedBranch)
                ->limit(1)
                ->count();
        }

        // Load actual waiting time data only if requested
        $waitingTimeData = null;
        if ($showWaitingTime && $waitingTimeCount > 0) {
            $waitingTimeData = Lhgk::select(
                    'PPKB_CODE',
                    'NO_UKK',
                    'NO_BKT_PANDU',
                    'NM_KAPAL',
                    'NM_PERS_PANDU',
                    'TGL_TIBA',
                    'JAM_TIBA',
                    'TGL_PMT',
                    'JAM_PMT',
                    'PNK',
                    'KB',
                    'MULAI_PELAKSANAAN',
                    'SELESAI_PELAKSANAAN',
                    'WT',
                    'PANDU_DARI',
                    'PANDU_KE'
                )
                ->selectRaw('(CAST(SUBSTRING_INDEX(WT, " : ", 1) AS UNSIGNED) + CAST(SUBSTRING_INDEX(WT, " : ", -1) AS UNSIGNED) / 60.0) as wt_decimal')
                ->whereRaw("(CAST(SUBSTRING_INDEX(WT, ' : ', 1) AS UNSIGNED) + CAST(SUBSTRING_INDEX(WT, ' : ', -1) AS UNSIGNED) / 60.0) > 0.5")
                ->where('PERIODE', $selectedPeriode)
                ->where('NM_BRANCH', $selectedBranch)
                ->orderByRaw('(CAST(SUBSTRING_INDEX(WT, " : ", 1) AS UNSIGNED) + CAST(SUBSTRING_INDEX(WT, " : ", -1) AS UNSIGNED) / 60.0) DESC')
                ->paginate(10)
                ->appends(['periode' => $selectedPeriode, 'cabang' => $selectedBranch, 'show_waiting_time' => 1]);
        }

        return view('dashboard', compact('statistics', 'totalOverall', 'periods', 'selectedPeriode', 'regionalGroups', 'allBranches', 'selectedBranch', 'topPilot', 'shipStatsByGT', 'showDeparture', 'departureDelayCount', 'departureDelayData', 'showStatusNota', 'statusNotaCount', 'statusNotaData', 'filterStatusNota', 'showWaitingTime', 'waitingTimeCount', 'waitingTimeData'));
    }

    public function exportDepartureDelay(Request $request)
    {
        $selectedPeriode = $request->get('periode');
        $selectedBranch = $request->get('cabang');

        if (!$selectedPeriode || !$selectedBranch) {
            return redirect()->back()->with('error', 'Pilih periode dan cabang terlebih dahulu');
        }

        // Get all departure delay data for export
        $departureDelayData = Lhgk::select(
                'NO_UKK',
                'NM_KAPAL',
                'NM_PERS_PANDU',
                'NM_BRANCH',
                'GERAKAN',
                'SELESAI_PELAKSANAAN',
                'INVOICE_DATE',
                'PENDAPATAN_PANDU',
                'PENDAPATAN_TUNDA'
            )
            ->selectRaw('DATEDIFF(STR_TO_DATE(INVOICE_DATE, "%d-%m-%Y"), STR_TO_DATE(SELESAI_PELAKSANAAN, "%d-%m-%Y")) as selisih_hari')
            ->whereRaw("GERAKAN = 'DEPARTURE'")
            ->whereNotNull('INVOICE_DATE')
            ->whereNotNull('SELESAI_PELAKSANAAN')
            ->where('INVOICE_DATE', '!=', '')
            ->where('SELESAI_PELAKSANAAN', '!=', '')
            ->whereRaw('DATEDIFF(STR_TO_DATE(INVOICE_DATE, "%d-%m-%Y"), STR_TO_DATE(SELESAI_PELAKSANAAN, "%d-%m-%Y")) > 2')
            ->where('PERIODE', $selectedPeriode)
            ->where('NM_BRANCH', $selectedBranch)
            ->orderByRaw('DATEDIFF(STR_TO_DATE(INVOICE_DATE, "%d-%m-%Y"), STR_TO_DATE(SELESAI_PELAKSANAAN, "%d-%m-%Y")) DESC')
            ->get();

        if ($departureDelayData->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data keterlambatan invoice untuk periode dan cabang yang dipilih');
        }

        // Generate CSV file
        $filename = 'Keterlambatan_Invoice_Departure_' . str_replace(' ', '_', $selectedBranch) . '_' . str_replace('-', '', $selectedPeriode) . '_' . date('YmdHis') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($departureDelayData) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, [
                'No',
                'No. UKK',
                'Nama Kapal',
                'Nama Pandu',
                'Cabang',
                'Gerakan',
                'Selesai Pelaksanaan',
                'Invoice Date',
                'Selisih (Hari)',
                'Pendapatan Pandu',
                'Pendapatan Tunda',
                'Total Pendapatan'
            ]);

            // Data
            $no = 1;
            foreach ($departureDelayData as $data) {
                fputcsv($file, [
                    $no++,
                    $data->NO_UKK,
                    $data->NM_KAPAL,
                    $data->NM_PERS_PANDU,
                    $data->NM_BRANCH,
                    strtoupper($data->GERAKAN),
                    $data->SELESAI_PELAKSANAAN,
                    $data->INVOICE_DATE,
                    $data->selisih_hari . ' hari',
                    number_format($data->PENDAPATAN_PANDU, 0, ',', '.'),
                    number_format($data->PENDAPATAN_TUNDA, 0, ',', '.'),
                    number_format($data->PENDAPATAN_PANDU + $data->PENDAPATAN_TUNDA, 0, ',', '.')
                ]);
            }

            // Summary
            fputcsv($file, []);
            fputcsv($file, [
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'TOTAL:',
                number_format($departureDelayData->sum('PENDAPATAN_PANDU'), 0, ',', '.'),
                number_format($departureDelayData->sum('PENDAPATAN_TUNDA'), 0, ',', '.'),
                number_format($departureDelayData->sum('PENDAPATAN_PANDU') + $departureDelayData->sum('PENDAPATAN_TUNDA'), 0, ',', '.')
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportStatusNota(Request $request)
    {
        $selectedPeriode = $request->get('periode');
        $selectedBranch = $request->get('cabang');
        $filterStatusNota = $request->get('filter_status_nota', 'all');

        if (!$selectedPeriode || !$selectedBranch) {
            return redirect()->back()->with('error', 'Pilih periode dan cabang terlebih dahulu');
        }

        // Build status nota filter array
        $statusNotaFilter = [];
        if ($filterStatusNota == 'all') {
            $statusNotaFilter = ['menunggu nota', 'belum verifikasi'];
        } else {
            $statusNotaFilter = [$filterStatusNota];
        }

        // Get all status nota data for export
        $statusNotaData = Lhgk::select(
                'NO_UKK',
                'NM_KAPAL',
                'PELAYARAN',
                'NM_PERS_PANDU',
                'MULAI_PELAKSANAAN',
                'SELESAI_PELAKSANAAN',
                'PENDAPATAN_PANDU',
                'PENDAPATAN_TUNDA',
                'STATUS_NOTA'
            )
            ->whereIn('STATUS_NOTA', $statusNotaFilter)
            ->where('PERIODE', $selectedPeriode)
            ->where('NM_BRANCH', $selectedBranch)
            ->orderBy('MULAI_PELAKSANAAN', 'desc')
            ->get();

        if ($statusNotaData->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data status nota untuk periode dan cabang yang dipilih');
        }

        // Generate CSV file
        $filename = 'Status_Nota_' . str_replace(' ', '_', $selectedBranch) . '_' . str_replace('-', '', $selectedPeriode) . '_' . date('YmdHis') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($statusNotaData) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, [
                'No',
                'No. UKK',
                'Nama Kapal',
                'Pelayaran',
                'Nama Pandu',
                'Mulai Pelaksanaan',
                'Selesai Pelaksanaan',
                'Pendapatan Pandu',
                'Pendapatan Tunda',
                'Total Pendapatan',
                'Status Nota'
            ]);

            // Data
            $no = 1;
            foreach ($statusNotaData as $data) {
                fputcsv($file, [
                    $no++,
                    $data->NO_UKK,
                    $data->NM_KAPAL,
                    $data->PELAYARAN ?? '-',
                    $data->NM_PERS_PANDU,
                    $data->MULAI_PELAKSANAAN,
                    $data->SELESAI_PELAKSANAAN,
                    number_format($data->PENDAPATAN_PANDU, 0, ',', '.'),
                    number_format($data->PENDAPATAN_TUNDA, 0, ',', '.'),
                    number_format($data->PENDAPATAN_PANDU + $data->PENDAPATAN_TUNDA, 0, ',', '.'),
                    strtoupper($data->STATUS_NOTA)
                ]);
            }

            // Summary
            fputcsv($file, []);
            fputcsv($file, [
                '',
                '',
                '',
                '',
                '',
                '',
                'TOTAL:',
                number_format($statusNotaData->sum('PENDAPATAN_PANDU'), 0, ',', '.'),
                number_format($statusNotaData->sum('PENDAPATAN_TUNDA'), 0, ',', '.'),
                number_format($statusNotaData->sum('PENDAPATAN_PANDU') + $statusNotaData->sum('PENDAPATAN_TUNDA'), 0, ',', '.')
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportWaitingTime(Request $request)
    {
        $selectedPeriode = $request->get('periode');
        $selectedBranch = $request->get('cabang');

        if (!$selectedPeriode || !$selectedBranch) {
            return redirect()->back()->with('error', 'Pilih periode dan cabang terlebih dahulu');
        }

        // Get all waiting time data for export (WT > 00:30)
        $waitingTimeData = Lhgk::select(
                'PPKB_CODE',
                'NO_UKK',
                'NO_BKT_PANDU',
                'NM_KAPAL',
                'NM_PERS_PANDU',
                'TGL_TIBA',
                'JAM_TIBA',
                'TGL_PMT',
                'JAM_PMT',
                'PNK',
                'KB',
                'MULAI_PELAKSANAAN',
                'SELESAI_PELAKSANAAN',
                'WT',
                'PANDU_DARI',
                'PANDU_KE'
            )
            ->selectRaw('(CAST(SUBSTRING_INDEX(WT, " : ", 1) AS UNSIGNED) + CAST(SUBSTRING_INDEX(WT, " : ", -1) AS UNSIGNED) / 60.0) as wt_decimal')
            ->whereRaw("(CAST(SUBSTRING_INDEX(WT, ' : ', 1) AS UNSIGNED) + CAST(SUBSTRING_INDEX(WT, ' : ', -1) AS UNSIGNED) / 60.0) > 0.5")
            ->where('PERIODE', $selectedPeriode)
            ->where('NM_BRANCH', $selectedBranch)
            ->orderByRaw('(CAST(SUBSTRING_INDEX(WT, " : ", 1) AS UNSIGNED) + CAST(SUBSTRING_INDEX(WT, " : ", -1) AS UNSIGNED) / 60.0) DESC')
            ->get();

        if ($waitingTimeData->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data waiting time untuk periode dan cabang yang dipilih');
        }

        // Generate CSV file
        $filename = 'Waiting_Time_' . str_replace(' ', '_', $selectedBranch) . '_' . str_replace('-', '', $selectedPeriode) . '_' . date('YmdHis') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($waitingTimeData) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, [
                'No',
                'PPKB Code',
                'No. UKK',
                'No. Bukti Pandu',
                'Nama Kapal',
                'Nama Pandu',
                'Tanggal Tiba',
                'Jam Tiba',
                'Tanggal PMT',
                'Jam PMT',
                'PNK',
                'KB',
                'Mulai Pelaksanaan',
                'Selesai Pelaksanaan',
                'WT',
                'Pandu Dari',
                'Pandu Ke'
            ]);

            // Data
            $no = 1;
            foreach ($waitingTimeData as $data) {
                fputcsv($file, [
                    $no++,
                    $data->PPKB_CODE ?? '-',
                    $data->NO_UKK ?? '-',
                    $data->NO_BKT_PANDU ?? '-',
                    $data->NM_KAPAL ?? '-',
                    $data->NM_PERS_PANDU ?? '-',
                    $data->TGL_TIBA ?? '-',
                    $data->JAM_TIBA ?? '-',
                    $data->TGL_PMT ?? '-',
                    $data->JAM_PMT ?? '-',
                    $data->PNK ?? '-',
                    $data->KB ?? '-',
                    $data->MULAI_PELAKSANAAN ?? '-',
                    $data->SELESAI_PELAKSANAAN ?? '-',
                    $data->WT ?? '-',
                    $data->PANDU_DARI ?? '-',
                    $data->PANDU_KE ?? '-'
                ]);
            }

            // Summary
            fputcsv($file, []);
            fputcsv($file, [
                '',
                '',
                '',
                'TOTAL TRANSAKSI:',
                count($waitingTimeData),
                '',
                '',
                '',
                '',
                'RATA-RATA WT:',
                number_format($waitingTimeData->avg('wt_decimal'), 2) . ' jam'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function analisisKelelahan(Request $request)
    {
        // Get selected period and branch
        $selectedPeriode = $request->get('periode', 'all');
        $selectedBranch = $request->get('cabang', 'all');

        // Get regional groups
        $regionalGroups = $this->getRegionalGroups();

        // Get available periods
        $periods = Lhgk::select('PERIODE')
            ->whereNotNull('PERIODE')
            ->where('PERIODE', '!=', '')
            ->groupBy('PERIODE')
            ->orderByRaw("STR_TO_DATE(CONCAT('01-', PERIODE), '%d-%m-%Y') DESC")
            ->pluck('PERIODE');

        // Only load data if both filters are selected (not 'all')
        if ($selectedPeriode == 'all' || $selectedBranch == 'all') {
            $panduData = collect(); // Return empty collection
            return view('analisis-kelelahan', compact('panduData', 'periods', 'selectedPeriode', 'regionalGroups', 'selectedBranch'));
        }

        // Build query for pandu statistics
        $query = Lhgk::select('NM_PERS_PANDU')
            ->selectRaw('COUNT(*) as total_pelayanan')
            ->selectRaw('MIN(mulai_pelaksanaan) as jam_mulai_pertama')
            ->selectRaw('MAX(selesai_pelaksanaan) as jam_selesai_terakhir')
            ->whereNotNull('NM_PERS_PANDU')
            ->whereNotNull('mulai_pelaksanaan')
            ->whereNotNull('selesai_pelaksanaan')
            ->where('PERIODE', $selectedPeriode)
            ->where('NM_BRANCH', $selectedBranch);

        $panduData = $query->groupBy('NM_PERS_PANDU')
            ->orderBy('total_pelayanan', 'desc')
            ->get();

        // Calculate working hours and fatigue level
        foreach ($panduData as $pandu) {
            // Get all services for this pilot
            $servicesQuery = Lhgk::select('mulai_pelaksanaan', 'selesai_pelaksanaan', 'NM_KAPAL', 'PILOT_DEPLOY')
                ->where('NM_PERS_PANDU', $pandu->NM_PERS_PANDU)
                ->whereNotNull('mulai_pelaksanaan')
                ->whereNotNull('selesai_pelaksanaan')
                ->where('PERIODE', $selectedPeriode)
                ->where('NM_BRANCH', $selectedBranch);

            $pandu->services = $servicesQuery->orderBy('mulai_pelaksanaan')->get();

            // Calculate total hours
            $totalMinutes = 0;
            foreach ($pandu->services as $service) {
                try {
                    $mulai = $service->mulai_pelaksanaan;
                    $selesai = $service->selesai_pelaksanaan;
                    
                    if (empty($mulai) || empty($selesai)) {
                        continue;
                    }
                    
                    // Extract time part if datetime format (e.g., "30-11-2025 22:43" -> "22:43")
                    if (strpos($mulai, ' ') !== false) {
                        $parts = explode(' ', $mulai);
                        $mulai = end($parts);
                    }
                    if (strpos($selesai, ' ') !== false) {
                        $parts = explode(' ', $selesai);
                        $selesai = end($parts);
                    }
                    
                    // Try different time formats
                    $formats = ['H:i:s', 'H:i', 'G:i:s', 'G:i'];
                    $start = null;
                    $end = null;
                    
                    foreach ($formats as $format) {
                        if (!$start) {
                            $start = \DateTime::createFromFormat($format, $mulai);
                        }
                        if (!$end) {
                            $end = \DateTime::createFromFormat($format, $selesai);
                        }
                    }
                    
                    if ($start && $end) {
                        $diff = $end->getTimestamp() - $start->getTimestamp();
                        if ($diff < 0) {
                            $diff += 86400; // Add 24 hours if crossing midnight
                        }
                        $totalMinutes += $diff / 60;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            $pandu->total_jam_kerja = round($totalMinutes / 60, 2);
            $pandu->rata_rata_jam_per_layanan = $pandu->total_pelayanan > 0 ? round($pandu->total_jam_kerja / $pandu->total_pelayanan, 2) : 0;
            
            // Extract time from datetime for display
            if (!empty($pandu->jam_mulai_pertama) && strpos($pandu->jam_mulai_pertama, ' ') !== false) {
                $parts = explode(' ', $pandu->jam_mulai_pertama);
                $pandu->jam_mulai_pertama = end($parts);
            }
            if (!empty($pandu->jam_selesai_terakhir) && strpos($pandu->jam_selesai_terakhir, ' ') !== false) {
                $parts = explode(' ', $pandu->jam_selesai_terakhir);
                $pandu->jam_selesai_terakhir = end($parts);
            }
            
            // Calculate busiest hour
            $hourDistribution = [];
            foreach ($pandu->services as $service) {
                try {
                    $mulai = $service->mulai_pelaksanaan;
                    if (!empty($mulai)) {
                        // Extract time part if datetime format (e.g., "30-11-2025 22:43" -> "22:43")
                        if (strpos($mulai, ' ') !== false) {
                            $parts = explode(' ', $mulai);
                            $mulai = end($parts); // Get the time part
                        }
                        
                        $formats = ['H:i:s', 'H:i', 'G:i:s', 'G:i'];
                        foreach ($formats as $format) {
                            $time = \DateTime::createFromFormat($format, $mulai);
                            if ($time) {
                                $hour = $time->format('H');
                                if (!isset($hourDistribution[$hour])) {
                                    $hourDistribution[$hour] = 0;
                                }
                                $hourDistribution[$hour]++;
                                break;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            if (!empty($hourDistribution)) {
                arsort($hourDistribution);
                $busiestHour = array_key_first($hourDistribution);
                $busiestCount = $hourDistribution[$busiestHour];
                $pandu->jam_tersibuk = $busiestHour . ':00';
                $pandu->jumlah_pelayanan_jam_tersibuk = $busiestCount;
            } else {
                $pandu->jam_tersibuk = '-';
                $pandu->jumlah_pelayanan_jam_tersibuk = 0;
            }
            
            // Calculate services per day distribution
            $dayDistribution = [];
            foreach ($pandu->services as $service) {
                try {
                    $pilotDeploy = $service->PILOT_DEPLOY;
                    if (!empty($pilotDeploy)) {
                        // Extract date part (handle various formats)
                        $date = $pilotDeploy;
                        
                        // If it contains time, extract just the date
                        if (strpos($date, ' ') !== false) {
                            $parts = explode(' ', $date);
                            $date = $parts[0];
                        }
                        
                        // Normalize date format to DD-MM-YYYY
                        $dateFormats = ['d-m-Y', 'd/m/Y', 'Y-m-d', 'd-M-Y', 'd-M-y', 'd-m-y'];
                        $dateObj = null;
                        foreach ($dateFormats as $format) {
                            $dateObj = \DateTime::createFromFormat($format, $date);
                            if ($dateObj) {
                                break;
                            }
                        }
                        
                        if ($dateObj) {
                            $normalizedDate = $dateObj->format('d-m-Y');
                            if (!isset($dayDistribution[$normalizedDate])) {
                                $dayDistribution[$normalizedDate] = 0;
                            }
                            $dayDistribution[$normalizedDate]++;
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            if (!empty($dayDistribution)) {
                arsort($dayDistribution);
                $pandu->distribusi_harian = $dayDistribution;
                $busiestDay = array_key_first($dayDistribution);
                $busiestDayCount = $dayDistribution[$busiestDay];
                $pandu->hari_tersibuk = $busiestDay;
                $pandu->jumlah_pelayanan_hari_tersibuk = $busiestDayCount;
            } else {
                $pandu->distribusi_harian = [];
                $pandu->hari_tersibuk = '-';
                $pandu->jumlah_pelayanan_hari_tersibuk = 0;
            }
            
            // Calculate hourly distribution per day
            $hourlyPerDay = [];
            foreach ($pandu->services as $service) {
                try {
                    $pilotDeploy = $service->PILOT_DEPLOY;
                    $mulai = $service->mulai_pelaksanaan;
                    
                    if (!empty($pilotDeploy) && !empty($mulai)) {
                        // Extract date
                        $date = $pilotDeploy;
                        if (strpos($date, ' ') !== false) {
                            $parts = explode(' ', $date);
                            $date = $parts[0];
                        }
                        
                        // Normalize date
                        $dateFormats = ['d-m-Y', 'd/m/Y', 'Y-m-d', 'd-M-Y', 'd-M-y', 'd-m-y'];
                        $dateObj = null;
                        foreach ($dateFormats as $format) {
                            $dateObj = \DateTime::createFromFormat($format, $date);
                            if ($dateObj) {
                                break;
                            }
                        }
                        
                        if ($dateObj) {
                            $normalizedDate = $dateObj->format('d-m-Y');
                            
                            // Extract hour from time
                            $time = $mulai;
                            if (strpos($time, ' ') !== false) {
                                $parts = explode(' ', $time);
                                $time = end($parts);
                            }
                            
                            $formats = ['H:i:s', 'H:i', 'G:i:s', 'G:i'];
                            foreach ($formats as $format) {
                                $timeObj = \DateTime::createFromFormat($format, $time);
                                if ($timeObj) {
                                    $hour = $timeObj->format('H');
                                    
                                    if (!isset($hourlyPerDay[$normalizedDate])) {
                                        $hourlyPerDay[$normalizedDate] = [];
                                    }
                                    if (!isset($hourlyPerDay[$normalizedDate][$hour])) {
                                        $hourlyPerDay[$normalizedDate][$hour] = 0;
                                    }
                                    $hourlyPerDay[$normalizedDate][$hour]++;
                                    break;
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            // Sort hourly data per day
            foreach ($hourlyPerDay as $date => &$hours) {
                ksort($hours);
            }
            $pandu->distribusi_jam_per_hari = $hourlyPerDay;
            
            // Fatigue level calculation
            // High: > 8 hours/day average, Medium: 6-8 hours, Low: < 6 hours
            if ($pandu->rata_rata_jam_per_layanan > 4) {
                $pandu->tingkat_kelelahan = 'Tinggi';
                $pandu->badge_class = 'danger';
            } elseif ($pandu->rata_rata_jam_per_layanan > 2) {
                $pandu->tingkat_kelelahan = 'Sedang';
                $pandu->badge_class = 'warning';
            } else {
                $pandu->tingkat_kelelahan = 'Rendah';
                $pandu->badge_class = 'success';
            }
        }

        return view('analisis-kelelahan', compact('panduData', 'periods', 'selectedPeriode', 'regionalGroups', 'selectedBranch'));
    }

    public function uploadCsv(Request $request)
    {
        // Increase execution time for very large files
        set_time_limit(0); // No limit
        ini_set('memory_limit', '1024M'); // 1GB
        ini_set('max_execution_time', '0');
        
        $request->validate([
            'csv_file' => 'required|file|mimetypes:text/plain,text/csv,application/csv,text/comma-separated-values,application/vnd.ms-excel|max:10240'
        ], [
            'csv_file.required' => 'File CSV harus diupload',
            'csv_file.file' => 'File yang diupload harus berupa file',
            'csv_file.mimetypes' => 'File harus berupa CSV',
            'csv_file.max' => 'Ukuran file maksimal 10MB'
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        // Read file with proper encoding handling
        $content = file_get_contents($path);
        
        // Remove BOM (Byte Order Mark) if present
        $bom = pack('H*','EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);
        
        // Convert to UTF-8 if needed
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        }
        
        // Parse CSV
        $lines = explode("\n", $content);
        $csvData = array_map(function($line) {
            return str_getcsv(trim($line));
        }, $lines);
        
        // Remove empty lines
        $csvData = array_filter($csvData, function($row) {
            return !empty(array_filter($row));
        });
        
        $header = array_shift($csvData);

        if (empty($header)) {
            return redirect()->back()->withErrors(['csv_file' => 'File CSV kosong atau format tidak valid']);
        }
        
        // Clean header: remove BOM and trim whitespace
        $header = array_map(function($col) {
            // Remove BOM characters and other invisible characters
            $col = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $col);
            // Remove zero-width characters
            $col = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $col);
            return trim($col);
        }, $header);

        $imported = 0;
        $errors = [];
        
        // Disable query log and foreign key checks for maximum performance
        DB::connection()->disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('SET AUTOCOMMIT=0');

        try {
            // Larger batch size for better performance
            $batchSize = 500;
            $batch = [];
            $totalRows = count($csvData);
            
            foreach ($csvData as $index => $row) {
                try {
                    // Skip empty rows
                    if (count(array_filter($row)) === 0) {
                        continue;
                    }
                    
                    $data = [];
                    foreach ($header as $key => $column) {
                        if (isset($row[$key])) {
                            // Trim whitespace and convert empty strings to null
                            $value = trim($row[$key]);
                            
                            // Normalize PILOT_DEPLOY format to DD-MM-YYYY
                            if ($column === 'PILOT_DEPLOY' && !empty($value)) {
                                $value = $this->normalizeDateFormat($value);
                            }
                            
                            // Handle numeric columns: convert '-' and empty to null
                            $numericColumns = ['PT', 'PENDAPATAN_PANDU', 'PENDAPATAN_TUNDA', 'KP_GRT'];
                            if (in_array($column, $numericColumns)) {
                                if ($value === '' || $value === '-' || $value === 'null') {
                                    $value = null;
                                }
                            }
                            
                            $data[$column] = $value === '' ? null : $value;
                        }
                    }

                    if (!empty($data)) {
                        $batch[] = $data;
                        
                        // Insert batch when reaches batch size
                        if (count($batch) >= $batchSize) {
                            try {
                                // Use DB::table for faster insert (no Eloquent overhead)
                                DB::table('lhgk')->insert($batch);
                                $imported += count($batch);
                                $batch = [];
                            } catch (\Exception $e) {
                                // If batch insert fails, try one by one
                                foreach ($batch as $batchIndex => $item) {
                                    try {
                                        DB::table('lhgk')->insert($item);
                                        $imported++;
                                    } catch (\Exception $itemError) {
                                        $errorMsg = $itemError->getMessage();
                                        if (strpos($errorMsg, 'SQLSTATE') !== false) {
                                            preg_match('/SQLSTATE\[.*?\]: (.*?)(?:\(|$)/', $errorMsg, $matches);
                                            $errorMsg = $matches[1] ?? $errorMsg;
                                        }
                                        $errors[] = "Baris " . ($index - count($batch) + $batchIndex + 2) . ": " . $errorMsg;
                                    }
                                }
                                $batch = [];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $errorMsg = $e->getMessage();
                    if (strpos($errorMsg, 'SQLSTATE') !== false) {
                        preg_match('/SQLSTATE\[.*?\]: (.*?)(?:\(|$)/', $errorMsg, $matches);
                        $errorMsg = $matches[1] ?? $errorMsg;
                    }
                    $errors[] = "Baris " . ($index + 2) . ": " . $errorMsg;
                }
            }
            
            // Insert remaining batch
            if (!empty($batch)) {
                try {
                    DB::table('lhgk')->insert($batch);
                    $imported += count($batch);
                } catch (\Exception $e) {
                    // If batch insert fails, try one by one
                    foreach ($batch as $batchIndex => $item) {
                        try {
                            DB::table('lhgk')->insert($item);
                            $imported++;
                        } catch (\Exception $itemError) {
                            $errorMsg = $itemError->getMessage();
                            if (strpos($errorMsg, 'SQLSTATE') !== false) {
                                preg_match('/SQLSTATE\[.*?\]: (.*?)(?:\(|$)/', $errorMsg, $matches);
                                $errorMsg = $matches[1] ?? $errorMsg;
                            }
                            $errors[] = "Baris " . (count($csvData) - count($batch) + $batchIndex + 2) . ": " . $errorMsg;
                        }
                    }
                }
            }
            
            // Commit and re-enable settings
            DB::statement('COMMIT');
            DB::statement('SET AUTOCOMMIT=1');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            // Re-enable query log
            DB::connection()->enableQueryLog();
            
            // Show results
            if ($imported > 0) {
                $message = "Berhasil import $imported data";
                if (!empty($errors)) {
                    $message .= " dengan " . count($errors) . " baris gagal";
                }
                
                $response = ['success' => $message];
                if (!empty($errors)) {
                    // Limit errors shown to 20 for display
                    $response['import_errors'] = array_slice($errors, 0, 20);
                    if (count($errors) > 20) {
                        $response['import_errors'][] = "... dan " . (count($errors) - 20) . " error lainnya";
                    }
                }
                
                return redirect()->back()->with($response);
            } else {
                // No data imported
                return redirect()->back()->withErrors([
                    'csv_file' => 'Import gagal: Tidak ada data yang berhasil diimport. ' . 
                    (count($errors) > 0 ? 'Error pertama: ' . $errors[0] : '')
                ])->with('import_errors', array_slice($errors, 0, 20));
            }
            
            // Commit and re-enable settings
            DB::statement('COMMIT');
            DB::statement('SET AUTOCOMMIT=1');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
        } catch (\Exception $e) {
            DB::rollBack();
            DB::connection()->enableQueryLog();
            return redirect()->back()->withErrors(['csv_file' => 'Import gagal: ' . $e->getMessage()]);
        }
    }

    /**
     * Normalize date format to DD-MM-YYYY
     */
    private function normalizeDateFormat($date)
    {
        // Remove extra spaces
        $date = trim($date);
        
        // Try to parse various date formats
        $formats = [
            'd-m-Y',    // 01-10-2024
            'j-n-Y',    // 1-10-2024
            'd/m/Y',    // 01/10/2024
            'j/n/Y',    // 1/10/2024
            'Y-m-d',    // 2024-10-01
            'd-M-Y',    // 01-Oct-2024
        ];
        
        foreach ($formats as $format) {
            $dateObj = \DateTime::createFromFormat($format, $date);
            if ($dateObj !== false) {
                // Return in DD-MM-YYYY format with leading zeros
                return $dateObj->format('d-m-Y');
            }
        }
        
        // If no format matches, return original value
        return $date;
    }
}
