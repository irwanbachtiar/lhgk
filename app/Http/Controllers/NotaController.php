<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nota;
use Illuminate\Support\Facades\DB;

class NotaController extends Controller
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
        // Get selected period and branch
        $selectedPeriode = $request->get('periode', 'all');
        $selectedBranch = $request->get('cabang', 'all');

        // Get regional groups
        $regionalGroups = $this->getRegionalGroups();

        // Get available periods (extract from INVOICE_DATE MM-YYYY format)
        $periods = Nota::selectRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') as periode')
            ->whereNotNull('INVOICE_DATE')
            ->where('INVOICE_DATE', '!=', '')
            ->groupBy('periode')
            ->orderByRaw('STR_TO_DATE(CONCAT(\'01-\', periode), \'%d-%m-%Y\') DESC')
            ->pluck('periode');

        // Initialize variables
        $totalNota = 0;
        $totalPendapatanPandu = 0;
        $totalPendapatanTunda = 0;
        $totalNotaBatal = 0;
        $totalPendapatanPanduBatal = 0;
        $totalPendapatanTundaBatal = 0;
        $revenuePerPandu = collect();
        $revenuePerTunda = collect();
        
        // Only load data if at least one filter is selected (not 'all')
        if ($selectedPeriode != 'all' || $selectedBranch != 'all') {
            // Build query
            $query = Nota::query();

            if ($selectedPeriode != 'all') {
                $query->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
            }

            if ($selectedBranch != 'all') {
                $query->where('NAME_BRANCH', $selectedBranch);
            }

            // Statistics - only get totals, no need to paginate data
            // Count distinct INVOICE with conditions:
            // - BILLING tidak mengandung "HIS" (nota batal)
            // - INVOICE tidak mengandung "INV"
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
            
            // Sum revenue directly from REVENUE column (including duplicates)
            $totalPendapatanPandu = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                    return $q->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
                })
                ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                    return $q->where('NAME_BRANCH', $selectedBranch);
                })
                ->sum('REVENUE');
            
            // Get total tunda revenue - sum directly from REVENUE column (including duplicates)
            $totalPendapatanTunda = DB::connection('dashboard_phinnisi')->table('tunda_prod')
                ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                    return $q->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
                })
                ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                    return $q->where('NAME_BRANCH', $selectedBranch);
                })
                ->sum('REVENUE');
            
            // Get cancelled invoices (Nota Batal) - BILLING starting with "HIS"
            // Count distinct BILLING from pandu_prod only
            $totalNotaBatal = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                ->where('BILLING', 'LIKE', 'HIS%')
                ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                    return $q->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
                })
                ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                    return $q->where('NAME_BRANCH', $selectedBranch);
                })
                ->distinct()
                ->count('BILLING');
            
            // Get total revenue from cancelled invoices (Pandu)
            $totalPendapatanPanduBatal = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                ->where('BILLING', 'LIKE', 'HIS%')
                ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                    return $q->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
                })
                ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                    return $q->where('NAME_BRANCH', $selectedBranch);
                })
                ->sum('REVENUE');
            
            // Get total revenue from cancelled invoices (Tunda)
            $totalPendapatanTundaBatal = DB::connection('dashboard_phinnisi')->table('tunda_prod')
                ->where('BILLING', 'LIKE', 'HIS%')
                ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                    return $q->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
                })
                ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                    return $q->where('NAME_BRANCH', $selectedBranch);
                })
                ->sum('REVENUE');
            
            // Get revenue per pilot (PILOT from pandu_prod) - sum all REVENUE including duplicates
            $revenuePerPandu = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                ->where('PILOT', '!=', '')
                ->whereNotNull('PILOT')
                ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                    return $q->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
                })
                ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                    return $q->where('NAME_BRANCH', $selectedBranch);
                })
                ->select('PILOT', DB::raw('SUM(REVENUE) as total_revenue'), DB::raw('COUNT(DISTINCT BILLING) as total_transaksi'))
                ->groupBy('PILOT')
                ->orderByDesc('total_revenue')
                ->get();
            
            // Get revenue per tunda - detect column name first
            try {
                // Get table structure to find correct column name
                $tundaColumns = DB::connection('dashboard_phinnisi')->select("SHOW COLUMNS FROM tunda_prod");
                $tundaNameColumn = null;
                
                // Look for name column in table structure
                $possibleNames = ['namatunda', 'NAMATUNDA', 'nama_tunda', 'NAMA_TUNDA', 'NM_TUNDA', 'nm_tunda', 
                                 'TUGBOAT_NAME', 'tugboat_name', 'NAMA', 'nama', 'NAME', 'name', 'TUGBOAT', 'tugboat'];
                
                foreach ($tundaColumns as $col) {
                    if (in_array($col->Field, $possibleNames)) {
                        $tundaNameColumn = $col->Field;
                        break;
                    }
                }
                
                if ($tundaNameColumn) {
                    $revenuePerTunda = DB::connection('dashboard_phinnisi')->table(DB::raw("(SELECT tunda_prod.BILLING, MAX(tunda_prod.REVENUE) as REVENUE, MAX(tunda_prod.{$tundaNameColumn}) as tunda_name, MAX(tunda_prod.INVOICE_DATE) as INVOICE_DATE FROM dashboard_phinnisi.tunda_prod WHERE tunda_prod.{$tundaNameColumn} IS NOT NULL AND tunda_prod.{$tundaNameColumn} != '' GROUP BY tunda_prod.BILLING) as tunda"))
                        ->join(DB::raw('(SELECT BILLING, MAX(NAME_BRANCH) as NAME_BRANCH FROM dashboard_phinnisi.pandu_prod GROUP BY BILLING) as pandu'), 'tunda.BILLING', '=', 'pandu.BILLING')
                        ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                            return $q->whereRaw('DATE_FORMAT(STR_TO_DATE(tunda.INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
                        })
                        ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                            return $q->where('pandu.NAME_BRANCH', $selectedBranch);
                        })
                        ->select('tunda.tunda_name', DB::raw('SUM(tunda.REVENUE) as total_revenue'), DB::raw('COUNT(DISTINCT tunda.BILLING) as total_transaksi'))
                        ->groupBy('tunda.tunda_name')
                        ->orderByDesc('total_revenue')
                        ->get();
                }
            } catch (\Exception $e) {
                // If error, return empty collection
                \Log::error('Error getting tunda revenue: ' . $e->getMessage());
                $revenuePerTunda = collect();
            }
            
            // Get top 10 shipping agents (combined pandu + tunda revenue)
            $topShippingAgents = DB::connection('dashboard_phinnisi')
                ->table(DB::raw('(
                    SELECT SHIPPING_AGENT, SUM(REVENUE) as total_revenue
                    FROM dashboard_phinnisi.pandu_prod
                    WHERE SHIPPING_AGENT IS NOT NULL AND SHIPPING_AGENT != ""
                    ' . ($selectedPeriode != 'all' ? 'AND DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = "' . $selectedPeriode . '"' : '') . '
                    ' . ($selectedBranch != 'all' ? 'AND NAME_BRANCH = "' . $selectedBranch . '"' : '') . '
                    GROUP BY SHIPPING_AGENT
                    
                    UNION ALL
                    
                    SELECT SHIPPING_AGENT, SUM(REVENUE) as total_revenue
                    FROM dashboard_phinnisi.tunda_prod
                    WHERE SHIPPING_AGENT IS NOT NULL AND SHIPPING_AGENT != ""
                    ' . ($selectedPeriode != 'all' ? 'AND DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = "' . $selectedPeriode . '"' : '') . '
                    ' . ($selectedBranch != 'all' ? 'AND NAME_BRANCH = "' . $selectedBranch . '"' : '') . '
                    GROUP BY SHIPPING_AGENT
                ) as combined'))
                ->select('SHIPPING_AGENT', DB::raw('SUM(total_revenue) as total_revenue'))
                ->groupBy('SHIPPING_AGENT')
                ->orderByDesc('total_revenue')
                ->limit(10)
                ->get();
        } else {
            $topShippingAgents = collect();
        }

        // No pagination needed since we're not showing the table
        $notaData = collect();

        return view('monitoring-nota', compact(
            'periods',
            'selectedPeriode',
            'regionalGroups',
            'selectedBranch',
            'totalNota',
            'totalPendapatanPandu',
            'totalPendapatanTunda',
            'totalNotaBatal',
            'totalPendapatanPanduBatal',
            'totalPendapatanTundaBatal',
            'revenuePerPandu',
            'revenuePerTunda',
            'topShippingAgents'
        ));
    }

    public function uploadCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        set_time_limit(0);
        ini_set('memory_limit', '1G');

        $file = $request->file('csv_file');
        $content = file_get_contents($file->getRealPath());
        
        // Remove BOM
        $bom = pack('H*','EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);
        
        // Convert to UTF-8
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
        
        // Clean header
        $header = array_map(function($col) {
            $col = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $col);
            $col = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $col);
            return trim($col);
        }, $header);

        $imported = 0;
        $errors = [];
        
        DB::connection()->disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('SET AUTOCOMMIT=0');

        try {
            $batchSize = 500;
            $batch = [];
            
            foreach ($csvData as $index => $row) {
                try {
                    if (count(array_filter($row)) === 0) {
                        continue;
                    }
                    
                    $data = [];
                    foreach ($header as $key => $column) {
                        if (isset($row[$key])) {
                            $value = trim($row[$key]);
                            
                            // Normalize date formats
                            if ($column === 'PILOT_DEPLOY' && !empty($value)) {
                                $value = $this->normalizeDateFormat($value);
                            }
                            
                            // Handle numeric columns
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
                        
                        if (count($batch) >= $batchSize) {
                            try {
                                DB::connection('dashboard_phinnisi')->table('pandu_prod')->insert($batch);
                                $imported += count($batch);
                                $batch = [];
                            } catch (\Exception $e) {
                                foreach ($batch as $batchIndex => $item) {
                                    try {
                                        DB::connection('dashboard_phinnisi')->table('pandu_prod')->insert($item);
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
                    $errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
                }
            }
            
            // Insert remaining batch
            if (!empty($batch)) {
                try {
                    DB::connection('dashboard_phinnisi')->table('pandu_prod')->insert($batch);
                    $imported += count($batch);
                } catch (\Exception $e) {
                    foreach ($batch as $batchIndex => $item) {
                        try {
                            DB::connection('dashboard_phinnisi')->table('pandu_prod')->insert($item);
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
            
            // Commit
            DB::statement('COMMIT');
            DB::statement('SET AUTOCOMMIT=1');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            if ($imported > 0) {
                $message = "Berhasil import {$imported} data";
                if (count($errors) > 0) {
                    $message .= " dengan " . count($errors) . " error";
                }
                return redirect()->back()->with('success', $message)->with('import_errors', array_slice($errors, 0, 20));
            } else {
                return redirect()->back()->withErrors([
                    'csv_file' => 'Import gagal: Tidak ada data yang berhasil diimport. ' . 
                    (count($errors) > 0 ? 'Error pertama: ' . $errors[0] : '')
                ])->with('import_errors', array_slice($errors, 0, 20));
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            DB::connection()->enableQueryLog();
            return redirect()->back()->withErrors(['csv_file' => 'Import gagal: ' . $e->getMessage()]);
        }
    }

    private function normalizeDateFormat($date)
    {
        if (empty($date)) {
            return null;
        }

        $formats = [
            'd-m-Y', 'd/m/Y', 'Y-m-d', 'd-M-Y', 'd/M/Y',
            'm-d-Y', 'm/d/Y', 'd.m.Y', 'Y/m/d'
        ];

        foreach ($formats as $format) {
            $parsed = \DateTime::createFromFormat($format, $date);
            if ($parsed && $parsed->format($format) === $date) {
                return $parsed->format('d-m-Y');
            }
        }

        return $date;
    }

    public function uploadTundaCsv(Request $request)
    {
        $request->validate([
            'tunda_csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        set_time_limit(0);
        ini_set('memory_limit', '1G');

        $file = $request->file('tunda_csv_file');
        $content = file_get_contents($file->getRealPath());
        
        // Remove BOM
        $bom = pack('H*','EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);
        
        // Convert to UTF-8
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
            return redirect()->back()->withErrors(['tunda_csv_file' => 'File CSV kosong atau format tidak valid']);
        }
        
        // Clean header
        $header = array_map(function($col) {
            $col = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $col);
            $col = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $col);
            return trim($col);
        }, $header);

        $imported = 0;
        $errors = [];
        
        DB::connection()->disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('SET AUTOCOMMIT=0');

        try {
            $batchSize = 500;
            $batch = [];
            
            foreach ($csvData as $index => $row) {
                try {
                    if (count(array_filter($row)) === 0) {
                        continue;
                    }
                    
                    $data = [];
                    foreach ($header as $key => $column) {
                        if (isset($row[$key])) {
                            $value = trim($row[$key]);
                            
                            // Handle numeric columns - convert '-' to null
                            if (strpos($column, 'REVENUE') !== false || strpos($column, 'revenue') !== false) {
                                if ($value === '' || $value === '-' || $value === 'null') {
                                    $value = null;
                                }
                            }
                            
                            $data[$column] = $value === '' ? null : $value;
                        }
                    }

                    if (!empty($data)) {
                        $batch[] = $data;
                        
                        if (count($batch) >= $batchSize) {
                            try {
                                DB::connection('dashboard_phinnisi')->table('tunda_prod')->insert($batch);
                                $imported += count($batch);
                                $batch = [];
                            } catch (\Exception $e) {
                                foreach ($batch as $batchIndex => $item) {
                                    try {
                                        DB::connection('dashboard_phinnisi')->table('tunda_prod')->insert($item);
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
                    $errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
                }
            }
            
            // Insert remaining batch
            if (!empty($batch)) {
                try {
                    DB::connection('dashboard_phinnisi')->table('tunda_prod')->insert($batch);
                    $imported += count($batch);
                } catch (\Exception $e) {
                    foreach ($batch as $batchIndex => $item) {
                        try {
                            DB::connection('dashboard_phinnisi')->table('tunda_prod')->insert($item);
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
            
            // Commit
            DB::statement('COMMIT');
            DB::statement('SET AUTOCOMMIT=1');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            if ($imported > 0) {
                $message = "Berhasil import {$imported} data tunda";
                if (count($errors) > 0) {
                    $message .= " dengan " . count($errors) . " error";
                }
                return redirect()->back()->with('success', $message)->with('import_errors', array_slice($errors, 0, 20));
            } else {
                return redirect()->back()->withErrors([
                    'tunda_csv_file' => 'Import gagal: Tidak ada data yang berhasil diimport. ' . 
                    (count($errors) > 0 ? 'Error pertama: ' . $errors[0] : '')
                ])->with('import_errors', array_slice($errors, 0, 20));
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            DB::connection()->enableQueryLog();
            return redirect()->back()->withErrors(['tunda_csv_file' => 'Import gagal: ' . $e->getMessage()]);
        }
    }

    public function getNotaBatalData(Request $request)
    {
        $selectedPeriode = $request->get('periode', 'all');
        $selectedBranch = $request->get('cabang', 'all');

        // Query pandu_prod
        $panduQuery = DB::connection('dashboard_phinnisi')
            ->table('pandu_prod')
            ->select(
                'BILLING',
                'INVOICE',
                'INVOICE_DATE',
                'NO_PKK',
                'VESSEL_NAME',
                'SHIPPING_AGENT',
                'FLAG',
                'DELEGATION',
                DB::raw('SUM(REVENUE) as REVENUE_PANDU'),
                DB::raw('0 as REVENUE_TUNDA')
            )
            ->where('BILLING', 'LIKE', 'HIS%')
            ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                return $q->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
            })
            ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                return $q->where('NAME_BRANCH', $selectedBranch);
            })
            ->groupBy('BILLING', 'INVOICE', 'INVOICE_DATE', 'NO_PKK', 'VESSEL_NAME', 'SHIPPING_AGENT', 'FLAG', 'DELEGATION');

        // Query tunda_prod
        $tundaQuery = DB::connection('dashboard_phinnisi')
            ->table('tunda_prod')
            ->select(
                'BILLING',
                'INVOICE',
                'INVOICE_DATE',
                'NO_PKK',
                'VESSEL_NAME',
                'SHIPPING_AGENT',
                'FLAG',
                'DELEGATION',
                DB::raw('0 as REVENUE_PANDU'),
                DB::raw('SUM(REVENUE) as REVENUE_TUNDA')
            )
            ->where('BILLING', 'LIKE', 'HIS%')
            ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                return $q->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
            })
            ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                return $q->where('NAME_BRANCH', $selectedBranch);
            })
            ->groupBy('BILLING', 'INVOICE', 'INVOICE_DATE', 'NO_PKK', 'VESSEL_NAME', 'SHIPPING_AGENT', 'FLAG', 'DELEGATION');

        // Combine results
        $panduData = $panduQuery->get();
        $tundaData = $tundaQuery->get();

        // Merge by BILLING
        $merged = collect();
        
        // Add all pandu data
        foreach ($panduData as $pandu) {
            $merged->push($pandu);
        }
        
        // Add or merge tunda data
        foreach ($tundaData as $tunda) {
            $existing = $merged->first(function($item) use ($tunda) {
                return $item->BILLING == $tunda->BILLING && 
                       $item->INVOICE == $tunda->INVOICE;
            });
            
            if ($existing) {
                $existing->REVENUE_TUNDA = $tunda->REVENUE_TUNDA;
            } else {
                $merged->push($tunda);
            }
        }

        // Sort by date descending
        $notaBatal = $merged->sortByDesc(function($item) {
            return $item->INVOICE_DATE;
        })->values();

        return response()->json([
            'data' => $notaBatal,
            'total' => $notaBatal->count()
        ]);
    }

    public function exportNotaBatal(Request $request)
    {
        $selectedPeriode = $request->get('periode', 'all');
        $selectedBranch = $request->get('cabang', 'all');

        // Query pandu_prod
        $panduQuery = DB::connection('dashboard_phinnisi')
            ->table('pandu_prod')
            ->select(
                'BILLING',
                'INVOICE',
                'INVOICE_DATE',
                'NO_PKK',
                'VESSEL_NAME',
                'SHIPPING_AGENT',
                'FLAG',
                'DELEGATION',
                DB::raw('SUM(REVENUE) as REVENUE_PANDU'),
                DB::raw('0 as REVENUE_TUNDA')
            )
            ->where('BILLING', 'LIKE', 'HIS%')
            ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                return $q->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
            })
            ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                return $q->where('NAME_BRANCH', $selectedBranch);
            })
            ->groupBy('BILLING', 'INVOICE', 'INVOICE_DATE', 'NO_PKK', 'VESSEL_NAME', 'SHIPPING_AGENT', 'FLAG', 'DELEGATION');

        // Query tunda_prod
        $tundaQuery = DB::connection('dashboard_phinnisi')
            ->table('tunda_prod')
            ->select(
                'BILLING',
                'INVOICE',
                'INVOICE_DATE',
                'NO_PKK',
                'VESSEL_NAME',
                'SHIPPING_AGENT',
                'FLAG',
                'DELEGATION',
                DB::raw('0 as REVENUE_PANDU'),
                DB::raw('SUM(REVENUE) as REVENUE_TUNDA')
            )
            ->where('BILLING', 'LIKE', 'HIS%')
            ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                return $q->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
            })
            ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                return $q->where('NAME_BRANCH', $selectedBranch);
            })
            ->groupBy('BILLING', 'INVOICE', 'INVOICE_DATE', 'NO_PKK', 'VESSEL_NAME', 'SHIPPING_AGENT', 'FLAG', 'DELEGATION');

        // Combine results
        $panduData = $panduQuery->get();
        $tundaData = $tundaQuery->get();

        // Merge by BILLING
        $merged = collect();
        
        // Add all pandu data
        foreach ($panduData as $pandu) {
            $merged->push($pandu);
        }
        
        // Add or merge tunda data
        foreach ($tundaData as $tunda) {
            $existing = $merged->first(function($item) use ($tunda) {
                return $item->BILLING == $tunda->BILLING && 
                       $item->INVOICE == $tunda->INVOICE;
            });
            
            if ($existing) {
                $existing->REVENUE_TUNDA = $tunda->REVENUE_TUNDA;
            } else {
                $merged->push($tunda);
            }
        }

        // Sort by date descending
        $notaBatal = $merged->sortByDesc(function($item) {
            return $item->INVOICE_DATE;
        })->values();

        // Create CSV
        $filename = 'nota_batal_' . ($selectedBranch != 'all' ? $selectedBranch . '_' : '') . 
                    ($selectedPeriode != 'all' ? $selectedPeriode . '_' : '') . 
                    date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($notaBatal) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, [
                'No',
                'Billing',
                'Invoice',
                'Invoice Date',
                'No PKK',
                'Vessel Name',
                'Shipping Agent',
                'Flag',
                'Revenue Pandu',
                'Revenue Tunda',
                'Delegation'
            ]);
            
            // Data
            $no = 1;
            foreach ($notaBatal as $row) {
                fputcsv($file, [
                    $no++,
                    $row->BILLING,
                    $row->INVOICE,
                    $row->INVOICE_DATE,
                    $row->NO_PKK,
                    $row->VESSEL_NAME,
                    $row->SHIPPING_AGENT,
                    $row->FLAG,
                    $row->REVENUE_PANDU,
                    $row->REVENUE_TUNDA,
                    $row->DELEGATION
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
