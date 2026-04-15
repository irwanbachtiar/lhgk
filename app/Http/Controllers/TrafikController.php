<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrafikController extends Controller
{
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
        $selectedWilayah = $request->get('wilayah', 'all');

        $conn = DB::connection('dashboard_phinnisi');

        // periods and branches (best-effort, fallbacks if columns missing)
        // Use trafik_rkap_realisasi table directly for filters
        $rkapTable = 'trafik_rkap_realisasi';
        try {
            // Check if trafik_rkap_realisasi has bulan+tahun columns
            $hasBulan = true;
            try {
                $test = $conn->table($rkapTable)->select('bulan','tahun')->limit(1)->first();
                if (! $test) $hasBulan = false;
            } catch (\Exception $e) {
                $hasBulan = false;
            }

            if ($hasBulan) {
                $periods = $conn->table($rkapTable)
                    ->selectRaw("CONCAT(LPAD(bulan,2,'0'), '-', tahun) as periode_label")
                    ->whereNotNull('bulan')
                    ->whereNotNull('tahun')
                    ->groupBy('periode_label')
                    ->orderByRaw("STR_TO_DATE(CONCAT('01-', periode_label), '%d-%m-%Y') DESC")
                    ->pluck('periode_label');
            } else {
                $periods = $conn->table($rkapTable)
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
            $allWilayah = $conn->table($rkapTable)
                ->select('wilayah')
                ->whereNotNull('wilayah')
                ->where('wilayah', '!=', '')
                ->groupBy('wilayah')
                ->orderBy('wilayah')
                ->pluck('wilayah')
                ->toArray();
        } catch (\Exception $e) {
            $allWilayah = [];
        }

        // Build regional grouping (simplified for trafik_rkap_realisasi which uses 'wilayah' directly)
        // For trafik_rkap_realisasi: wilayah values are like "wilayah 1", "wilayah 2", etc.
        $mapping = $this->getRegionalGroups();
        $regionalGroups = [];
        foreach ($mapping as $groupName => $patterns) {
            $regionalGroups[$groupName] = [];
        }

        $otherBranches = [];

        // If table has regional branch names (old trafik table), do mapping
        // For trafik_rkap_realisasi with simple wilayah names, skip complex mapping
        foreach ($allWilayah as $wil) {
            if (preg_match('/^wilayah\s+\d+$/i', trim($wil))) {
                // Simple wilayah format (e.g., "wilayah 1"), no need for complex mapping
                continue;
            }
            
            // Legacy branch name mapping
            $assigned = false;
            foreach ($mapping as $groupName => $patterns) {
                foreach ($patterns as $pattern) {
                    // strip common prefixes like 'REGIONAL 1 ' or 'JAI ' or 'KANCI '
                    $namePart = preg_replace('/^(REGIONAL\s+\d+\s+|JAI\s+|KANCI\s+)/i', '', $pattern);
                    $namePart = trim($namePart);
                    if ($namePart !== '' && stripos($wil, $namePart) !== false) {
                        $regionalGroups[$groupName][] = $wil;
                        $assigned = true;
                        break 2;
                    }
                }
            }
            if (! $assigned) {
                $otherBranches[] = $wil;
            }
        }

        $rows = collect();
        // Query data with optional filtering (if filters are 'all', show all data)
        try {
            // note: trafik_rkap_realisasi table has columns: wilayah, periode, Call, GT
            $rowsQuery = $conn->table($rkapTable);

            // If wilayah is specified, apply wilayah filter
            if ($selectedWilayah != 'all') {
                    $rowsQuery->where('wilayah', $selectedWilayah);
                }

                // If trafik_rkap_realisasi has bulan+tahun, match selectedPeriode (MM-YYYY)
                // (hasBulan already determined above)

                // Apply periode filter if provided
                $m = $y = null;
                if ($selectedPeriode != 'all') {
                    if ($hasBulan) {
                        $parts = explode('-', $selectedPeriode);
                        if (count($parts) === 2) {
                            list($m, $y) = $parts;
                            $m = ltrim($m, '0');
                            $rowsQuery->where('bulan', $m)->where('tahun', $y);
                        }
                    } else {
                        $rowsQuery->where('periode', $selectedPeriode);
                    }
                }

                $rows = $rowsQuery->limit(1000)->get();

                // compute "selisih (hari)" — difference in days between
                // selesai pelaksanaan and the closing day of the report month
                $endOfMonth = null;
                if (isset($m) && isset($y)) {
                    try {
                        $endOfMonth = Carbon::createFromDate($y, $m, 1)->endOfMonth();
                    } catch (\Exception $e) {
                        $endOfMonth = null;
                    }
                }

                $rows = $rows->map(function($row) use ($endOfMonth) {
                    $arr = (array) $row;
                    $new = [];

                    foreach ($arr as $k => $v) {
                        $new[$k] = $v;

                        // insert additional column immediately after the selesai pelaksanaan column
                        $keyLow = strtolower($k);
                        if ($keyLow === 'selesai_pelaksanaan' || $keyLow === 'selesai pelaksanaan') {
                            $diff = '';
                            if ($endOfMonth && !empty($v)) {
                                $parsed = null;
                                $formats = ['d-m-Y', 'Y-m-d', 'd/m/Y', 'Y/m/d'];
                                foreach ($formats as $fmt) {
                                    try {
                                        $parsed = Carbon::createFromFormat($fmt, trim($v));
                                        break;
                                    } catch (\Exception $e) {
                                    }
                                }
                                if (! $parsed) {
                                    try {
                                        $parsed = new Carbon(trim($v));
                                    } catch (\Exception $e) {
                                        $parsed = null;
                                    }
                                }

                                if ($parsed) {
                                    $diff = $parsed->diffInDays($endOfMonth, false);
                                }
                            }

                            $new['selisih (hari)'] = $diff;
                        }
                    }

                    return (object) $new;
                });
        } catch (\Exception $e) {
            $rows = collect();
        }

        // --- Data per Wilayah untuk Bar Chart ---
        $wilayahBarData = [];
        try {
            $rkapTable = 'trafik_rkap_realisasi';
            
            // Get data grouped by wilayah and jenis with filters
            $wilayahQuery = $conn->table($rkapTable);
            
            // Apply filters
            if ($selectedWilayah != 'all') {
                $wilayahQuery->where('wilayah', $selectedWilayah);
            }
            if ($selectedPeriode != 'all') {
                // Apply YTD logic for wilayah bar data
                $parts = explode('-', $selectedPeriode);
                if (count($parts) === 2) {
                    list($m, $y) = $parts;
                    $m = intval(ltrim($m, '0'));
                    $y = intval($y);
                    
                    // YTD Logic for periode column format (MM-YYYY)
                    if ($m > 1) {
                        // Build list of periods from 01 to selected month for YTD
                        $periodeList = [];
                        for ($i = 1; $i <= $m; $i++) {
                            $periodeList[] = str_pad($i, 2, '0', STR_PAD_LEFT) . '-' . $y;
                        }
                        $wilayahQuery->whereIn('periode', $periodeList);
                    } else {
                        $wilayahQuery->where('periode', $selectedPeriode);
                    }
                } else {
                    $wilayahQuery->where('periode', $selectedPeriode);
                }
            }
            
            $wilayahRows = $wilayahQuery
                ->selectRaw('wilayah, jenis, SUM(`Call`) as total_call, SUM(`GT`) as total_gt')
                ->groupBy('wilayah', 'jenis')
                ->orderBy('wilayah')
                ->get();
            
            // Organize data by wilayah
            foreach ($wilayahRows as $row) {
                $wil = strtoupper(trim($row->wilayah ?? ''));
                $jenis = strtoupper(trim($row->jenis ?? ''));
                
                if (!isset($wilayahBarData[$wil])) {
                    $wilayahBarData[$wil] = [
                        'call_realisasi' => 0,
                        'call_anggaran' => 0,
                        'gt_realisasi' => 0,
                        'gt_anggaran' => 0
                    ];
                }
                
                if ($jenis === 'REALISASI') {
                    $wilayahBarData[$wil]['call_realisasi'] = (float)($row->total_call ?? 0);
                    $wilayahBarData[$wil]['gt_realisasi'] = (float)($row->total_gt ?? 0);
                } elseif ($jenis === 'ANGGARAN') {
                    $wilayahBarData[$wil]['call_anggaran'] = (float)($row->total_call ?? 0);
                    $wilayahBarData[$wil]['gt_anggaran'] = (float)($row->total_gt ?? 0);
                }
            }
        } catch (\Exception $e) {
            $wilayahBarData = [];
        }

        // --- Comparison totals: Sum all realisasi vs anggaran for CALL & GT ---
        // Note: trafik_rkap_realisasi has columns: Call, GT, jenis (REALISASI/ANGGARAN)
        // NOT satuan/nilai columns
        // Apply same filters as KPI (wilayah and periode)
        $comparisonRealCall = 0;
        $comparisonBudgetCall = 0;
        $comparisonRealGt = 0;
        $comparisonBudgetGt = 0;

        try {
            $rkapTable = 'trafik_rkap_realisasi';
            
            // Get realisasi totals with filters
            $realisasiQuery = $conn->table($rkapTable)
                ->where('jenis', 'REALISASI');
            
            if ($selectedWilayah != 'all') {
                $realisasiQuery->where('wilayah', $selectedWilayah);
            }
            if ($selectedPeriode != 'all') {
                // Apply YTD logic for comparison totals
                $parts = explode('-', $selectedPeriode);
                if (count($parts) === 2) {
                    list($m, $y) = $parts;
                    $m = intval(ltrim($m, '0'));
                    $y = intval($y);
                    
                    // YTD Logic for realisasi with periode column format (MM-YYYY)
                    if ($m > 1) {
                        // Build list of periods from 01 to selected month for YTD
                        $periodeList = [];
                        for ($i = 1; $i <= $m; $i++) {
                            $periodeList[] = str_pad($i, 2, '0', STR_PAD_LEFT) . '-' . $y;
                        }
                        $realisasiQuery->whereIn('periode', $periodeList);
                    } else {
                        $realisasiQuery->where('periode', $selectedPeriode);
                    }
                } else {
                    $realisasiQuery->where('periode', $selectedPeriode);
                }
            }
            
            $realisasi = $realisasiQuery->selectRaw('SUM(`Call`) as total_call, SUM(`GT`) as total_gt')->first();
            
            $comparisonRealCall = $realisasi->total_call ?? 0;
            $comparisonRealGt = $realisasi->total_gt ?? 0;
            
            // Get anggaran totals with filters
            $anggaranQuery = $conn->table($rkapTable)
                ->where('jenis', 'ANGGARAN');
            
            if ($selectedWilayah != 'all') {
                $anggaranQuery->where('wilayah', $selectedWilayah);
            }
            if ($selectedPeriode != 'all') {
                // Apply YTD logic for anggaran totals
                $parts = explode('-', $selectedPeriode);
                if (count($parts) === 2) {
                    list($m, $y) = $parts;
                    $m = intval(ltrim($m, '0'));
                    $y = intval($y);
                    
                    // YTD Logic for anggaran with periode column format (MM-YYYY)
                    if ($m > 1) {
                        // Build list of periods from 01 to selected month for YTD
                        $periodeList = [];
                        for ($i = 1; $i <= $m; $i++) {
                            $periodeList[] = str_pad($i, 2, '0', STR_PAD_LEFT) . '-' . $y;
                        }
                        $anggaranQuery->whereIn('periode', $periodeList);
                    } else {
                        $anggaranQuery->where('periode', $selectedPeriode);
                    }
                } else {
                    $anggaranQuery->where('periode', $selectedPeriode);
                }
            }
            
            $anggaran = $anggaranQuery->selectRaw('SUM(`Call`) as total_call, SUM(`GT`) as total_gt')->first();
            
            $comparisonBudgetCall = $anggaran->total_call ?? 0;
            $comparisonBudgetGt = $anggaran->total_gt ?? 0;
        } catch (\Exception $e) {
            // use defaults if query fails
        }

        // Produksi values (not in trafik_rkap_realisasi, using placeholders)
        $comparisonRealPemanduan = 0;
        $comparisonBudgetPemanduan = 0;
        $comparisonRealPenundaan = 0;
        $comparisonBudgetPenundaan = 0;

        // === KPI AGGREGATIONS ===
        // Calculate total Call and GT based on current filters (REALISASI only)
        // Note: Call and GT columns need backticks as they may be reserved/uppercase
        $kpiQuery = $conn->table($rkapTable)
            ->where('jenis', 'REALISASI');
        
        if ($selectedWilayah != 'all') {
            $kpiQuery->where('wilayah', $selectedWilayah);
        }
        if ($selectedPeriode != 'all') {
            // Apply YTD logic for KPI aggregations
            if ($hasBulan) {
                $parts = explode('-', $selectedPeriode);
                if (count($parts) === 2) {
                    list($m, $y) = $parts;
                    $m = intval(ltrim($m, '0'));
                    $y = intval($y);
                    if ($m > 1) {
                        $kpiQuery->where('tahun', $y)->where('bulan', '<=', $m);
                    } else {
                        $kpiQuery->where('tahun', $y)->where('bulan', $m);
                    }
                }
            } else {
                // YTD Logic for KPI with periode column format (MM-YYYY)
                $parts = explode('-', $selectedPeriode);
                if (count($parts) === 2) {
                    list($m, $y) = $parts;
                    $m = intval(ltrim($m, '0'));
                    $y = intval($y);
                    
                    if ($m > 1) {
                        // Build list of periods from 01 to selected month for YTD
                        $periodeList = [];
                        for ($i = 1; $i <= $m; $i++) {
                            $periodeList[] = str_pad($i, 2, '0', STR_PAD_LEFT) . '-' . $y;
                        }
                        $kpiQuery->whereIn('periode', $periodeList);
                    } else {
                        $kpiQuery->where('periode', $selectedPeriode);
                    }
                } else {
                    $kpiQuery->where('periode', $selectedPeriode);
                }
            }
        }

        try {
            $kpiTotals = $kpiQuery->selectRaw('SUM(`Call`) as total_call, SUM(`GT`) as total_gt')->first();
            $totalCall = $kpiTotals->total_call ?? 0;
            $totalGt = $kpiTotals->total_gt ?? 0;
        } catch (\Exception $e) {
            $totalCall = 0;
            $totalGt = 0;
        }

        // === BUILD trafikData STRUCTURE for view ===
        // Group data by wilayah > jenis (REALISASI/ANGGARAN)
        $trafikData = [];
        try {
            $grouped = $conn->table($rkapTable)
                ->select('wilayah', 'jenis',
                         DB::raw('SUM(`Call`) as total_call'), 
                         DB::raw('SUM(`GT`) as total_gt'))
                ->when($selectedWilayah != 'all', function($q) use ($selectedWilayah) {
                    return $q->where('wilayah', $selectedWilayah);
                })
                ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode, $hasBulan) {
                    if ($hasBulan) {
                        $parts = explode('-', $selectedPeriode);
                        if (count($parts) === 2) {
                            list($m, $y) = $parts;
                            $m = intval(ltrim($m, '0'));
                            $y = intval($y);
                            // YTD Logic: cumulative from month 1 to selected month
                            if ($m > 1) {
                                return $q->where('tahun', $y)->where('bulan', '<=', $m);
                            } else {
                                return $q->where('tahun', $y)->where('bulan', $m);
                            }
                        }
                    } else {
                        // YTD Logic for periode column format (MM-YYYY)
                        $parts = explode('-', $selectedPeriode);
                        if (count($parts) === 2) {
                            list($m, $y) = $parts;
                            $m = intval(ltrim($m, '0'));
                            $y = intval($y);
                            
                            // Build list of periods from 01 to selected month for YTD
                            if ($m > 1) {
                                $periodeList = [];
                                for ($i = 1; $i <= $m; $i++) {
                                    $periodeList[] = str_pad($i, 2, '0', STR_PAD_LEFT) . '-' . $y;
                                }
                                return $q->whereIn('periode', $periodeList);
                            } else {
                                return $q->where('periode', $selectedPeriode);
                            }
                        } else {
                            return $q->where('periode', $selectedPeriode);
                        }
                    }
                })
                ->groupBy('wilayah', 'jenis')
                ->get();

            foreach ($grouped as $row) {
                $wil = $row->wilayah ?? 'unknown';
                $jenis = strtoupper(trim($row->jenis ?? 'REALISASI'));
                
                if (!isset($trafikData[$wil])) {
                    $trafikData[$wil] = [
                        'realisasi_call' => 0,
                        'realisasi_gt' => 0,
                        'anggaran_call' => 0,
                        'anggaran_gt' => 0,
                        'last_year_call' => 0,
                        'last_year_gt' => 0
                    ];
                }
                
                if ($jenis === 'REALISASI') {
                    $trafikData[$wil]['realisasi_call'] = (float)($row->total_call ?? 0);
                    $trafikData[$wil]['realisasi_gt'] = (float)($row->total_gt ?? 0);
                } else if ($jenis === 'ANGGARAN') {
                    $trafikData[$wil]['anggaran_call'] = (float)($row->total_call ?? 0);
                    $trafikData[$wil]['anggaran_gt'] = (float)($row->total_gt ?? 0);
                }
            }

            // === GET LAST YEAR DATA (YoY comparison) ===
            if ($selectedPeriode != 'all') {
                // Parse periode (format: MM-YYYY)
                $periodeParts = explode('-', $selectedPeriode);
                if (count($periodeParts) == 2) {
                    $month = intval(ltrim($periodeParts[0], '0'));
                    $year = (int)$periodeParts[1];
                    $lastYear = $year - 1;
                    
                    $lastYearQuery = $conn->table($rkapTable)
                        ->select('wilayah', 
                                 DB::raw('SUM(`Call`) as total_call'), 
                                 DB::raw('SUM(`GT`) as total_gt'))
                        ->where('jenis', 'REALISASI')
                        ->when($selectedWilayah != 'all', function($q) use ($selectedWilayah) {
                            return $q->where('wilayah', $selectedWilayah);
                        });
                    
                    // Apply YTD logic for last year data
                    if ($hasBulan) {
                        if ($month > 1) {
                            $lastYearQuery->where('tahun', $lastYear)->where('bulan', '<=', $month);
                        } else {
                            $lastYearQuery->where('tahun', $lastYear)->where('bulan', $month);
                        }
                    } else {
                        // YTD Logic for last year data with periode column format (MM-YYYY)
                        if ($month > 1) {
                            // Build list of periods from 01 to selected month for YTD last year
                            $lastYearPeriodeList = [];
                            for ($i = 1; $i <= $month; $i++) {
                                $lastYearPeriodeList[] = str_pad($i, 2, '0', STR_PAD_LEFT) . '-' . $lastYear;
                            }
                            $lastYearQuery->whereIn('periode', $lastYearPeriodeList);
                        } else {
                            $lastYearPeriode = str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . $lastYear;
                            $lastYearQuery->where('periode', $lastYearPeriode);
                        }
                    }
                    
                    $lastYearData = $lastYearQuery->groupBy('wilayah')->get();

                    foreach ($lastYearData as $row) {
                        $wil = $row->wilayah ?? 'unknown';
                        if (isset($trafikData[$wil])) {
                            $trafikData[$wil]['last_year_call'] = (float)($row->total_call ?? 0);
                            $trafikData[$wil]['last_year_gt'] = (float)($row->total_gt ?? 0);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $trafikData = [];
        }

        // --- Data dari tabel prod_rkap_realisasi untuk tabel kedua ---
        $rkapData = [];
        try {
            $rkapTable = 'prod_rkap_realisasi';
            
            $grouped = $conn->table($rkapTable)
                ->select('wilayah', 'jenis', 'layanan',
                         DB::raw('MIN(`satuan`) as satuan'),
                         DB::raw('SUM(`nilai`) as total_nilai'))
                ->when($selectedWilayah != 'all', function($q) use ($selectedWilayah) {
                    return $q->where('wilayah', $selectedWilayah);
                })
                ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                    // YTD Logic for prod_rkap_realisasi with periode column format (MM-YYYY)
                    $parts = explode('-', $selectedPeriode);
                    if (count($parts) === 2) {
                        list($m, $y) = $parts;
                        $m = intval(ltrim($m, '0'));
                        $y = intval($y);
                        
                        if ($m > 1) {
                            // Build list of periods from 01 to selected month for YTD
                            $periodeList = [];
                            for ($i = 1; $i <= $m; $i++) {
                                $periodeList[] = str_pad($i, 2, '0', STR_PAD_LEFT) . '-' . $y;
                            }
                            return $q->whereIn('periode', $periodeList);
                        } else {
                            return $q->where('periode', $selectedPeriode);
                        }
                    } else {
                        return $q->where('periode', $selectedPeriode);
                    }
                })
                ->groupBy('wilayah', 'jenis', 'layanan')
                ->get();

            foreach ($grouped as $row) {
                $wil = $row->wilayah ?? 'unknown';
                $jenis = strtoupper(trim($row->jenis ?? 'REALISASI'));
                $layanan = trim($row->layanan ?? '');
                $satuan = trim($row->satuan ?? '');
                
                if (!isset($rkapData[$wil])) {
                    $rkapData[$wil] = [];
                }
                
                if (!isset($rkapData[$wil][$layanan])) {
                    $rkapData[$wil][$layanan] = [
                        'realisasi' => 0,
                        'anggaran' => 0,
                        'last_year' => 0,
                        'satuan' => $satuan  // Store the unit text
                    ];
                }
                
                $value = (float)($row->total_nilai ?? 0);
                
                if ($jenis === 'REALISASI') {
                    $rkapData[$wil][$layanan]['realisasi'] = $value;
                } elseif ($jenis === 'ANGGARAN') {
                    $rkapData[$wil][$layanan]['anggaran'] = $value;
                }
                
                // Update satuan if not set
                if (empty($rkapData[$wil][$layanan]['satuan'])) {
                    $rkapData[$wil][$layanan]['satuan'] = $satuan;
                }
            }

            // === GET LAST YEAR DATA (YoY comparison) untuk prod_rkap_realisasi ===
            if ($selectedPeriode != 'all') {
                $periodeParts = explode('-', $selectedPeriode);
                if (count($periodeParts) == 2) {
                    $month = intval(ltrim($periodeParts[0], '0'));
                    $year = (int)$periodeParts[1];
                    $lastYear = $year - 1;
                    
                    $lastYearQuery = $conn->table($rkapTable)
                        ->select('wilayah', 'layanan',
                                 DB::raw('SUM(`nilai`) as total_nilai'))
                        ->where('jenis', 'REALISASI')
                        ->when($selectedWilayah != 'all', function($q) use ($selectedWilayah) {
                            return $q->where('wilayah', $selectedWilayah);
                        });
                    
                    // Apply YTD logic for last year data
                    if ($month > 1) {
                        // Build list of periods from 01 to selected month for YTD last year
                        $lastYearPeriodeList = [];
                        for ($i = 1; $i <= $month; $i++) {
                            $lastYearPeriodeList[] = str_pad($i, 2, '0', STR_PAD_LEFT) . '-' . $lastYear;
                        }
                        $lastYearQuery->whereIn('periode', $lastYearPeriodeList);
                    } else {
                        $lastYearPeriode = str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . $lastYear;
                        $lastYearQuery->where('periode', $lastYearPeriode);
                    }
                    
                    $lastYearData = $lastYearQuery->groupBy('wilayah', 'layanan')->get();

                    foreach ($lastYearData as $row) {
                        $wil = $row->wilayah ?? 'unknown';
                        $layanan = trim($row->layanan ?? '');
                        $value = (float)($row->total_nilai ?? 0);
                        
                        if (isset($rkapData[$wil]) && isset($rkapData[$wil][$layanan])) {
                            $rkapData[$wil][$layanan]['last_year'] = $value;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $rkapData = [];
        }

        // === PRODUKSI CARDS DATA ===
        // Calculate production values from prod_rkap_realisasi
        $produksi_penundaan = 0;
        $produksi_pemanduan = 0;
        $real_penundaan = 0;
        $budget_penundaan = 0;
        $real_pemanduan = 0;
        $budget_pemanduan = 0;
        $pct_penundaan = 0;
        $pct_pemanduan = 0;
        $icon_penundaan = '';
        $icon_pemanduan = '';
        $pendapatan = 0;

        try {
            // Get PENUNDAAN data
            $penundaanQuery = $conn->table('prod_rkap_realisasi')
                ->select('jenis', DB::raw('SUM(`nilai`) as total'))
                ->where('layanan', 'PENUNDAAN')
                ->when($selectedWilayah != 'all', function($q) use ($selectedWilayah) {
                    return $q->where('wilayah', $selectedWilayah);
                })
                ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                    // YTD Logic for penundaan with periode column format (MM-YYYY)
                    $parts = explode('-', $selectedPeriode);
                    if (count($parts) === 2) {
                        list($m, $y) = $parts;
                        $m = intval(ltrim($m, '0'));
                        $y = intval($y);
                        
                        if ($m > 1) {
                            // Build list of periods from 01 to selected month for YTD
                            $periodeList = [];
                            for ($i = 1; $i <= $m; $i++) {
                                $periodeList[] = str_pad($i, 2, '0', STR_PAD_LEFT) . '-' . $y;
                            }
                            return $q->whereIn('periode', $periodeList);
                        } else {
                            return $q->where('periode', $selectedPeriode);
                        }
                    } else {
                        return $q->where('periode', $selectedPeriode);
                    }
                })
                ->groupBy('jenis')
                ->get();


            foreach ($penundaanQuery as $row) {
                if ($row->jenis === 'REALISASI') {
                    $real_penundaan = (float)$row->total;
                    $produksi_penundaan = (float)$row->total;
                } elseif ($row->jenis === 'ANGGARAN') {
                    $budget_penundaan = (float)$row->total;
                }
            }

            // Get PEMANDUAN data
            $pemanduanQuery = $conn->table('prod_rkap_realisasi')
                ->select('jenis', DB::raw('SUM(`nilai`) as total'))
                ->where('layanan', 'PEMANDUAN')
                ->when($selectedWilayah != 'all', function($q) use ($selectedWilayah) {
                    return $q->where('wilayah', $selectedWilayah);
                })
                ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                    // YTD Logic for pemanduan with periode column format (MM-YYYY)
                    $parts = explode('-', $selectedPeriode);
                    if (count($parts) === 2) {
                        list($m, $y) = $parts;
                        $m = intval(ltrim($m, '0'));
                        $y = intval($y);
                        
                        if ($m > 1) {
                            // Build list of periods from 01 to selected month for YTD
                            $periodeList = [];
                            for ($i = 1; $i <= $m; $i++) {
                                $periodeList[] = str_pad($i, 2, '0', STR_PAD_LEFT) . '-' . $y;
                            }
                            return $q->whereIn('periode', $periodeList);
                        } else {
                            return $q->where('periode', $selectedPeriode);
                        }
                    } else {
                        return $q->where('periode', $selectedPeriode);
                    }
                })
                ->groupBy('jenis')
                ->get();


            foreach ($pemanduanQuery as $row) {
                if ($row->jenis === 'REALISASI') {
                    $real_pemanduan = (float)$row->total;
                    $produksi_pemanduan = (float)$row->total;
                } elseif ($row->jenis === 'ANGGARAN') {
                    $budget_pemanduan = (float)$row->total;
                }
            }

            // Calculate percentages
            $pct_penundaan = $budget_penundaan > 0 ? round(($real_penundaan / $budget_penundaan) * 100, 1) : 0;
            $pct_pemanduan = $budget_pemanduan > 0 ? round(($real_pemanduan / $budget_pemanduan) * 100, 1) : 0;

            // Set icons based on performance
            $icon_penundaan = $pct_penundaan >= 100 ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-exclamation-triangle text-warning"></i>';
            $icon_pemanduan = $pct_pemanduan >= 100 ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-exclamation-triangle text-warning"></i>';


        } catch (\Exception $e) {
            // Use defaults if query fails
        }

        return view('trafik_simple', compact(
            'rows', 'periods', 'selectedPeriode', 'allWilayah', 'selectedWilayah', 
            'regionalGroups', 'otherBranches', 
            'wilayahBarData',
            'comparisonRealCall', 'comparisonBudgetCall', 'comparisonRealGt', 'comparisonBudgetGt',
            'comparisonRealPemanduan', 'comparisonBudgetPemanduan', 'comparisonRealPenundaan', 'comparisonBudgetPenundaan',
            'totalCall', 'totalGt', 'trafikData', 'rkapData',
            'produksi_penundaan', 'produksi_pemanduan', 'real_penundaan', 'budget_penundaan', 
            'real_pemanduan', 'budget_pemanduan', 'pct_penundaan', 'pct_pemanduan', 
            'icon_penundaan', 'icon_pemanduan', 'pendapatan'
        ));
    }
}
