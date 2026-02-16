<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrafikController extends Controller
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
    public function index(Request $request)
    {
        $selectedPeriode = $request->get('periode', 'all');
        $selectedBranch = $request->get('cabang', 'all');

        $conn = DB::connection('dashboard_phinnisi');
        $table = 'trafik';

        // periods and branches (best-effort, fallbacks if columns missing)
        try {
            // Prefer building periods from bulan+tahun (MM-YYYY) when available
            $hasBulan = true;
            try {
                $test = $conn->table($table)->select('bulan','tahun')->limit(1)->first();
                if (! $test) $hasBulan = false;
            } catch (\Exception $e) {
                $hasBulan = false;
            }

            if ($hasBulan) {
                $periods = $conn->table($table)
                    ->selectRaw("CONCAT(LPAD(bulan,2,'0'), '-', tahun) as periode_label")
                    ->whereNotNull('bulan')
                    ->whereNotNull('tahun')
                    ->groupBy('periode_label')
                    ->orderByRaw("STR_TO_DATE(CONCAT('01-', periode_label), '%d-%m-%Y') DESC")
                    ->pluck('periode_label');
            } else {
                $periods = $conn->table($table)
                    ->select('periode')
                    ->whereNotNull('periode')
                    ->where('periode', '!=', '')
                    ->groupBy('periode')
                    ->orderByRaw("STR_TO_DATE(CONCAT('01-', periode), '%d-%m-%Y') DESC")
                    ->pluck('periode');
            }
        } catch (\Exception $e) {
            $periods = collect();
        }

        try {
            $allBranches = $conn->table($table)
                ->select('cabang')
                ->whereNotNull('cabang')
                ->where('cabang', '!=', '')
                ->groupBy('cabang')
                ->orderBy('cabang')
                ->pluck('cabang')
                ->toArray();
        } catch (\Exception $e) {
            $allBranches = [];
        }

        // Build regional grouping by matching actual branch names from DB to our mapping
        $mapping = $this->getRegionalGroups();
        $regionalGroups = [];
        foreach ($mapping as $groupName => $patterns) {
            $regionalGroups[$groupName] = [];
        }

        $otherBranches = [];

        foreach ($allBranches as $branch) {
            $assigned = false;
            foreach ($mapping as $groupName => $patterns) {
                foreach ($patterns as $pattern) {
                    // strip common prefixes like 'REGIONAL 1 ' or 'JAI ' or 'KANCI '
                    $namePart = preg_replace('/^(REGIONAL\s+\d+\s+|JAI\s+|KANCI\s+)/i', '', $pattern);
                    $namePart = trim($namePart);
                    if ($namePart !== '' && stripos($branch, $namePart) !== false) {
                        $regionalGroups[$groupName][] = $branch;
                        $assigned = true;
                        break 2;
                    }
                }
            }
            if (! $assigned) {
                $otherBranches[] = $branch;
            }
        }

        $rows = collect();
        if ($selectedPeriode != 'all' && $selectedBranch != 'all') {
            try {
                // note: trafik table columns are lowercase (cabang, bulan, tahun)
                $rowsQuery = $conn->table($table)->where('cabang', $selectedBranch);

                // If trafik has bulan+tahun, match selectedPeriode (MM-YYYY)
                $hasBulan = true;
                try {
                    $test = $conn->table($table)->select('bulan','tahun')->limit(1)->first();
                    if (! $test) $hasBulan = false;
                } catch (\Exception $e) {
                    $hasBulan = false;
                }

                if ($hasBulan) {
                    list($m, $y) = explode('-', $selectedPeriode);
                    $m = ltrim($m, '0');
                    $rowsQuery->where('bulan', $m)->where('tahun', $y);
                } else {
                    $rowsQuery->where('periode', $selectedPeriode);
                }

                $rows = $rowsQuery->limit(1000)->get();
            } catch (\Exception $e) {
                $rows = collect();
            }
        }

        return view('trafik', compact('rows', 'periods', 'selectedPeriode', 'allBranches', 'selectedBranch', 'regionalGroups', 'otherBranches'));
    }
}
