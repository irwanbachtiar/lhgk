<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class SapController extends Controller
{
    private function detectColumns()
    {
        $result = [
            'tanggal_col' => 'tanggal_nota',  // Default ke kolom yang sudah dikonfirmasi
            'pendapatan_col' => 'pendapatan_idr',  // Default ke kolom yang sudah dikonfirmasi
            'description_col' => 'name',  // Port name
            'customer_col' => 'customer_name',  // Default ke kolom customer
        ];

        try {
            $cols = DB::connection('dashboard_phinnisi')->select("SHOW COLUMNS FROM zfi039");
            $fields = array_map(function($c){ return $c->Field; }, $cols);
            
            // Detect tanggal nota column (prefer tanggal_nota)
            foreach ($fields as $f) {
                $normalized = strtolower(str_replace([' ', '_'], '', $f));
                if (in_array($normalized, ['tanggalnota', 'tanggal_nota', 'tglnota', 'postingdate', 'posting_date'])) {
                    $result['tanggal_col'] = $f;
                    break;
                }
            }

            // Detect pendapatan column (prefer pendapatan_idr)
            foreach ($fields as $f) {
                $normalized = strtolower(str_replace([' ', '_'], '', $f));
                if (in_array($normalized, ['pendapatanidr', 'pendapatan_idr', 'amount'])) {
                    $result['pendapatan_col'] = $f;
                    break;
                }
            }

            // Detect description/port column
            foreach ($fields as $f) {
                $normalized = strtolower(str_replace([' ', '_'], '', $f));
                if (in_array($normalized, ['name', 'port', 'portname', 'spjm', 'description'])) {
                    $result['description_col'] = $f;
                    break;
                }
            }

            // Detect customer column
            foreach ($fields as $f) {
                $normalized = strtolower(str_replace([' ', '_'], '', $f));
                if (in_array($normalized, ['customer', 'customername', 'namapelanggan', 'pelanggan', 'soldto', 'custname', 'cust'])) {
                    $result['customer_col'] = $f;
                    break;
                }
            }
        } catch (\Exception $e) {
            // Use defaults
        }

        return $result;
    }

    public function index(Request $request)
    {
        $selectedPeriode = $request->get('periode', 'all');
        $rowsPerPage = (int)$request->get('rows', 50);
        $rowsPerPage = in_array($rowsPerPage, [25, 50, 100, 200, 500]) ? $rowsPerPage : 50;
        $error = null;
        
        // Detect columns
        $cols = $this->detectColumns();
        $tanggalCol = $cols['tanggal_col'];
        $pendapatanCol = $cols['pendapatan_col'];
        $descCol = $cols['description_col'];
        $custCol = $cols['customer_col'];

        // Get available periods from database (format YYYY-MM dari tanggal_nota)
        $periods = collect();
        try {
            if ($tanggalCol) {
                $periodsData = DB::connection('dashboard_phinnisi')
                    ->table('zfi039')
                    ->selectRaw("DATE_FORMAT({$tanggalCol}, '%Y-%m') as periode")
                    ->whereRaw("LENGTH(COALESCE({$tanggalCol}, '')) > 0")
                    ->groupByRaw("DATE_FORMAT({$tanggalCol}, '%Y-%m')")
                    ->orderByRaw("MAX({$tanggalCol}) DESC")
                    ->get();
                
                $periods = $periodsData->pluck('periode');
            }
        } catch (\Exception $e) {
            // Jika gagal, gunakan periode dummy
        }

        // Data untuk Card Summary Pendapatan dari database
        $summaryData = collect();
        $summaryDataYtdMap = [];
        $totalPendapatanSummary = 0;
        $totalPendapatanSummaryYtd = 0;
        
        $isYtdValid = true;
        
        if ($selectedPeriode != 'all') {
            $parts = explode('-', $selectedPeriode);
            if (count($parts) == 2) {
                // Deteksi otomatis format YYYY-MM atau MM-YYYY
                if (strlen($parts[0]) == 4) {
                    $tahun = $parts[0];
                    $bulan = (int)$parts[1]; // Jika format YYYY-MM
                } else {
                    $bulan = (int)$parts[0]; // Jika format MM-YYYY
                    $tahun = $parts[1];
                }
            }
        }

        try {
            if ($tanggalCol && $pendapatanCol) {
                // Perhitungan Total Pendapatan Keseluruhan
                $totalQuery = DB::connection('dashboard_phinnisi')
                    ->table('zfi039')
                    ->selectRaw("SUM(CAST({$pendapatanCol} AS DECIMAL(20,2))) as total");

                if ($selectedPeriode != 'all') {
                    $totalQuery->whereRaw("DATE_FORMAT({$tanggalCol}, '%Y-%m') = ?", [$selectedPeriode]);
                }
                $totalPendapatanSummary = (float)$totalQuery->value('total');

                if ($selectedPeriode != 'all') {
                    $parts = explode('-', $selectedPeriode);
                    if (count($parts) == 2) {
                        $ytdTotalQuery = DB::connection('dashboard_phinnisi')
                            ->table('zfi039')
                            ->selectRaw("SUM(CAST({$pendapatanCol} AS DECIMAL(20,2))) as total")
                            ->whereRaw("COALESCE(DATE_FORMAT(STR_TO_DATE({$tanggalCol}, '%d-%m-%Y'), '%Y'), DATE_FORMAT({$tanggalCol}, '%Y')) = ?", [$tahun])
                            ->whereRaw("COALESCE(DATE_FORMAT(STR_TO_DATE({$tanggalCol}, '%d-%m-%Y'), '%m'), DATE_FORMAT({$tanggalCol}, '%m')) <= ?", [$bulan]);
                        
                        $totalPendapatanSummaryYtd = (float)$ytdTotalQuery->value('total');
                    }
                }

                // Cari kolom deskripsi (seperti description, keterangan, jenis_pendapatan) untuk summary
                $descSummaryCol = null;
                $colsList = DB::connection('dashboard_phinnisi')->select("SHOW COLUMNS FROM zfi039");
                $fieldsList = array_map(function($c){ return $c->Field; }, $colsList);
                foreach ($fieldsList as $f) {
                    $normalized = strtolower(str_replace([' ', '_'], '', $f));
                    if (in_array($normalized, ['description', 'keterangan', 'jenispendapatan', 'group'])) {
                        $descSummaryCol = $f;
                        break;
                    }
                }

                if ($descSummaryCol) {
                    $summaryQuery = DB::connection('dashboard_phinnisi')
                        ->table('zfi039')
                        ->selectRaw("{$descSummaryCol} as description, SUM(CAST({$pendapatanCol} AS DECIMAL(20,2))) as total_pendapatan")
                        ->whereNotNull($descSummaryCol)
                        ->whereRaw("LENGTH(COALESCE({$descSummaryCol}, '')) > 0");

                    if ($selectedPeriode != 'all') {
                        $summaryQuery->whereRaw("DATE_FORMAT({$tanggalCol}, '%Y-%m') = ?", [$selectedPeriode]);
                    }

                    $summaryData = $summaryQuery->groupBy($descSummaryCol)->orderBy('total_pendapatan', 'DESC')->get();

                    // Perhitungan Year-to-Date (YTD) untuk summary
                    if ($selectedPeriode != 'all') {
                        $parts = explode('-', $selectedPeriode);
                        if (count($parts) == 2) {
                            if (strlen($parts[0]) == 4) {
                                $tahun = $parts[0];
                                $bulan = $parts[1];
                            } else {
                                $bulan = $parts[0];
                                $tahun = $parts[1];
                            }
                            
                            $ytdSummaryQuery = DB::connection('dashboard_phinnisi')
                                ->table('zfi039')
                                ->selectRaw("{$descSummaryCol} as description, SUM(CAST({$pendapatanCol} AS DECIMAL(20,2))) as total_pendapatan")
                                ->whereNotNull($descSummaryCol)
                                ->whereRaw("LENGTH(COALESCE({$descSummaryCol}, '')) > 0")
                                ->whereRaw("COALESCE(DATE_FORMAT(STR_TO_DATE({$tanggalCol}, '%d-%m-%Y'), '%Y'), DATE_FORMAT({$tanggalCol}, '%Y')) = ?", [$tahun])
                                ->whereRaw("COALESCE(DATE_FORMAT(STR_TO_DATE({$tanggalCol}, '%d-%m-%Y'), '%m'), DATE_FORMAT({$tanggalCol}, '%m')) <= ?", [$bulan])
                                ->groupBy($descSummaryCol)
                                ->get();
                                
                            foreach ($ytdSummaryQuery as $item) {
                                $summaryDataYtdMap[$item->description] = (float)$item->total_pendapatan;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Abaikan jika tidak ada kolom untuk summary
        }

        // Daftar port yang diizinkan sesuai request
        $allowedPorts = [
            'SPJM Parepare', 'SPJM Ambon', 'SPJM Bontang', 'SPJM Pantoloan', 'SPJM S. Pakning',
            'SPJM T.B Karimu', 'SPJM Tarakan', 'SPJM Pkln Susu', 'SPJM Tanjung Red', 'SPJM Balikpapan',
            'SPJM Cirebon', 'SPJM Gorontalo', 'SPJM Grogot', 'SPJM Kendari', 'SPJM Luwuk Tangk',
            'SPJM Manado', 'SPJM Nipah', 'SPJM Panjang', 'SPJM Sangatta', 'SPJM Sangkulirang',
            'SPJM Sorong', 'SPJM Tj. Santan',
        ];

        // Get ports data with pendapatan grouped by period
        $ports = $allowedPorts;
        $portData = collect();
        $portDataYtdMap = [];
        $portNotaMap = [];
        $customersPerBranch = [];
        
        try {
            if ($tanggalCol && $pendapatanCol && $descCol) {
                // Cek kolom no_faktur dan tanggal_pembatalan
                $hasNoFaktur = false;
                $hasTglBatal = false;
                try {
                    $colsList = DB::connection('dashboard_phinnisi')->select("SHOW COLUMNS FROM zfi039");
                    $fieldsList = array_map(function($c){ return $c->Field; }, $colsList);
                    $hasNoFaktur = in_array('no_faktur', $fieldsList);
                    $hasTglBatal = in_array('tanggal_pembatalan', $fieldsList);
                } catch (\Exception $e) {}

                $notaSelect = "";
                if ($hasNoFaktur) {
                    if ($hasTglBatal) {
                        $notaSelect = ", COUNT(DISTINCT CASE WHEN COALESCE(tanggal_pembatalan, '') = '' THEN no_faktur ELSE NULL END) as total_nota";
                    } else {
                        $notaSelect = ", COUNT(DISTINCT no_faktur) as total_nota";
                    }
                } else {
                    $notaSelect = ", 0 as total_nota";
                }

                // Get port pendapatan data
                $query = DB::connection('dashboard_phinnisi')
                    ->table('zfi039')
                    ->selectRaw("{$descCol} as port, SUM(CAST({$pendapatanCol} AS DECIMAL(20,2))) as total_pendapatan{$notaSelect}")
                    ->whereIn($descCol, $allowedPorts);
                
                // Apply period filter - format periode adalah YYYY-MM dari database
                if ($selectedPeriode != 'all') {
                    $query->whereRaw("DATE_FORMAT({$tanggalCol}, '%Y-%m') = ?", [$selectedPeriode]);
                }
                
                $portData = $query
                    ->whereRaw("LENGTH(COALESCE({$descCol}, '')) > 0")
                    ->groupBy($descCol)
                    ->orderBy('total_pendapatan', 'DESC')
                    ->get();

                // Perhitungan Year-to-Date (YTD) untuk branch port
                if ($selectedPeriode != 'all') {
                    $parts = explode('-', $selectedPeriode);
                    if (count($parts) == 2) {
                        // Deteksi YYYY-MM vs MM-YYYY
                        if (strlen($parts[0]) == 4) {
                            $tahun = $parts[0];
                            $bulan = $parts[1];
                        } else {
                            $bulan = $parts[0];
                            $tahun = $parts[1];
                        }
                        
                        $ytdQuery = DB::connection('dashboard_phinnisi')
                            ->table('zfi039')
                            ->selectRaw("{$descCol} as port, SUM(CAST({$pendapatanCol} AS DECIMAL(20,2))) as total_pendapatan")
                            ->whereIn($descCol, $allowedPorts)
                            ->whereRaw("COALESCE(DATE_FORMAT(STR_TO_DATE({$tanggalCol}, '%d-%m-%Y'), '%Y'), DATE_FORMAT({$tanggalCol}, '%Y')) = ?", [$tahun])
                            ->whereRaw("COALESCE(DATE_FORMAT(STR_TO_DATE({$tanggalCol}, '%d-%m-%Y'), '%m'), DATE_FORMAT({$tanggalCol}, '%m')) <= ?", [$bulan])
                            ->whereRaw("LENGTH(COALESCE({$descCol}, '')) > 0")
                            ->groupBy($descCol)
                            ->get();
                            
                        foreach ($ytdQuery as $item) {
                            $matchedPort = collect($allowedPorts)->first(function($p) use ($item) {
                                return strtolower($p) === strtolower($item->port);
                            });
                            if ($matchedPort) {
                                $portDataYtdMap[$matchedPort] = (float)$item->total_pendapatan;
                            }
                        }
                    }
                }

                // Get customers per branch
                if ($custCol) {
                    $custQuery = DB::connection('dashboard_phinnisi')
                        ->table('zfi039')
                        ->selectRaw("{$descCol} as port, {$custCol} as customer, SUM(CAST({$pendapatanCol} AS DECIMAL(20,2))) as total_pendapatan{$notaSelect}")
                        ->whereIn($descCol, $allowedPorts)
                        ->whereNotNull($custCol)
                        ->whereRaw("LENGTH(COALESCE({$custCol}, '')) > 0");

                    if ($selectedPeriode != 'all') {
                        $custQuery->whereRaw("DATE_FORMAT({$tanggalCol}, '%Y-%m') = ?", [$selectedPeriode]);
                    }

                    $custDataRaw = $custQuery->groupBy($descCol, $custCol)
                        ->orderBy('total_pendapatan', 'DESC')
                        ->get();

                    foreach ($custDataRaw as $item) {
                        $customersPerBranch[$item->port][] = $item;
                    }
                }
            }
        } catch (\Exception $e) {
        }

        // Get all unique customers (tidak dikelompokkan per branch)
        $allCustomers = collect();
        try {
            if ($custCol && $pendapatanCol) {
                $allCustQuery = DB::connection('dashboard_phinnisi')
                    ->table('zfi039')
                    ->selectRaw("{$custCol} as customer_name, SUM(CAST({$pendapatanCol} AS DECIMAL(20,2))) as total_revenue, COUNT(DISTINCT {$custCol}) as transaction_count")
                    ->whereNotNull($custCol)
                    ->whereRaw("LENGTH(COALESCE({$custCol}, '')) > 0");

                if ($selectedPeriode != 'all') {
                    $allCustQuery->whereRaw("DATE_FORMAT({$tanggalCol}, '%Y-%m') = ?", [$selectedPeriode]);
                }

                $allCustomers = $allCustQuery->groupBy($custCol)
                    ->orderBy('total_revenue', 'DESC')
                    ->limit(50)
                    ->get();
            }
        } catch (\Exception $e) {
        }

        // Convert portData to associative array for easy lookup
        $portDataMap = [];
        foreach ($portData as $item) {
            $matchedPort = collect($allowedPorts)->first(function($p) use ($item) {
                return strtolower($p) === strtolower($item->port);
            });
            if ($matchedPort) {
                $portDataMap[$matchedPort] = (float)$item->total_pendapatan;
                $portNotaMap[$matchedPort] = (int)($item->total_nota ?? 0);
            }
        }
        
        // Sort ports so those with revenue appear first, then alphabetically
        usort($ports, function($a, $b) use ($portDataMap) {
            $valA = $portDataMap[$a] ?? 0;
            $valB = $portDataMap[$b] ?? 0;
            if ($valA == $valB) {
                return strcmp($a, $b);
            }
            return $valB <=> $valA;
        });

        // Dummy data untuk Tabel ZFI039
        $columns = ['document_no', 'posting_date', 'customer_name', 'description', 'amount', 'status'];
        // Kolom untuk Tabel ZFI039
        $columns = [];
        
        // Get tabel data dari database
        $tableData = collect();
        try {
            if ($tanggalCol && $pendapatanCol) {
                $dataQuery = DB::connection('dashboard_phinnisi')->table('zfi039');
                
                // Only fetch records with valid tanggal_nota
                $dataQuery->whereRaw("LENGTH(COALESCE({$tanggalCol}, '')) > 0");
                
                if ($selectedPeriode != 'all') {
                    $dataQuery->whereRaw("DATE_FORMAT({$tanggalCol}, '%Y-%m') = ?", [$selectedPeriode]);
                }
                
                $tableData = $dataQuery->paginate($rowsPerPage);
                
                // Update array columns menggunakan struktur kolom asli dari tabel database (bila ada)
                if ($tableData->count() > 0) {
                    $columns = array_keys((array)$tableData->first());
                }
            } else {
                throw new \Exception("Kolom tidak terdeteksi");
            }
        } catch (\Exception $e) {
            // Fallback ke dummy data jika database error
            $dummyRows = collect([
                (object)['document_no' => '1000001234', 'posting_date' => '01-05-2026', 'customer_name' => 'PT PELAYARAN NASIONAL', 'description' => 'Pendapatan Jasa Pemanduan', 'amount' => '15000000', 'status' => 'CLEARED'],
                (object)['document_no' => '1000001235', 'posting_date' => '02-05-2026', 'customer_name' => 'PT SAMUDERA INDONESIA', 'description' => 'Pendapatan Jasa Penundaan', 'amount' => '32500000', 'status' => 'CLEARED'],
                (object)['document_no' => '1000001236', 'posting_date' => '03-05-2026', 'customer_name' => 'PT MERATUS LINE', 'description' => 'Pendapatan Air Kapal', 'amount' => '2500000', 'status' => 'OPEN'],
                (object)['document_no' => '1000001237', 'posting_date' => '04-05-2026', 'customer_name' => 'PT SPIL', 'description' => 'Pendapatan Jasa Penundaan', 'amount' => '18000000', 'status' => 'CLEARED'],
                (object)['document_no' => '1000001238', 'posting_date' => '05-05-2026', 'customer_name' => 'PT BINTANG LAUT', 'description' => 'Pendapatan Rupa-rupa', 'amount' => '1500000', 'status' => 'OPEN'],
            ]);

            if ($selectedPeriode != 'all') {
                $dummyRows = $dummyRows->filter(function($row) use ($selectedPeriode) {
                    $rowPeriode = \Carbon\Carbon::createFromFormat('d-m-Y', $row->posting_date)->format('Y-m');
                    return $rowPeriode === $selectedPeriode;
                })->values();
            }

            $tableData = new LengthAwarePaginator(
                $dummyRows,
                $dummyRows->count(),
                50,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            $tableData = collect();
        }

        return view('sap', compact('tableData', 'columns', 'error', 'summaryData', 'summaryDataYtdMap', 'selectedPeriode', 'periods', 'ports', 'portDataMap', 'portDataYtdMap', 'isYtdValid', 'totalPendapatanSummary', 'totalPendapatanSummaryYtd', 'portNotaMap', 'customersPerBranch', 'allCustomers', 'rowsPerPage'));
    }
}