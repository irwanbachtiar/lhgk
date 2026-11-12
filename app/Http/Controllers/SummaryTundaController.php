<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SummaryTundaController extends Controller
{
    private const BULAN = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

    public function index(Request $request)
    {
        $periodeExpr = "DATE_FORMAT(STR_TO_DATE(BILLING_DATE, '%d-%m-%Y'), '%m-%Y')";

        $availableYears = DB::connection('dashboard_phinnisi')->table('tunda_prod')
            ->whereNotNull('BILLING_DATE')
            ->where('BILLING_DATE', '!=', '')
            ->selectRaw('DISTINCT RIGHT(BILLING_DATE, 4) as tahun')
            ->orderByDesc('tahun')
            ->pluck('tahun');

        $regionalGroups = $this->getRegionalGroups();
        $regionalKeys = array_keys($regionalGroups);

        $selectedTahun = $request->get('tahun', $availableYears->first() ?? date('Y'));
        $selectedRegional = $request->get('regional', 'all');
        // Opt-in: default semua wilayah tersembunyi, hanya yang ada di
        // parameter "shown" yang ditampilkan. Ini (bukan daftar hidden)
        // supaya "sembunyikan yang terakhir" tidak balik ke default aneh
        // (array kosong di query string otomatis hilang dari URL).
        $shownWilayah = (array) $request->get('shown', []);

        $branchToRegional = [];
        foreach ($regionalGroups as $wilayah => $branches) {
            foreach ($branches as $branch) {
                $branchToRegional[$branch] = $wilayah;
            }
        }

        $periodeColumns = array_map(fn ($bulan) => "{$bulan}-{$selectedTahun}", self::BULAN);

        // Data diambil dari tunda_prod: nama tunda dari NAMA_TUNDA, pendapatan
        // dari REVENUE, dan bulan/tahun (periode) diturunkan dari BILLING_DATE.
        // Grouping regional tetap diturunkan dari NAME_BRANCH (tunda_prod juga
        // tidak punya kolom REGIONAL), memakai pemetaan wilayah yang sama.
        $query = DB::connection('dashboard_phinnisi')->table('tunda_prod')
            ->whereRaw('RIGHT(BILLING_DATE, 4) = ?', [$selectedTahun])
            ->whereNotNull('NAMA_TUNDA')
            ->where('NAMA_TUNDA', '!=', '')
            ->whereNotNull('NAME_BRANCH')
            ->where('NAME_BRANCH', '!=', '');

        if ($selectedRegional != 'all') {
            $query->whereIn('NAME_BRANCH', $regionalGroups[$selectedRegional] ?? []);
        }

        $rows = $query
            ->select('NAME_BRANCH', 'NAMA_TUNDA')
            ->selectRaw("$periodeExpr as periode")
            ->selectRaw('SUM(REVENUE) as total')
            ->groupBy('NAME_BRANCH', 'NAMA_TUNDA')
            ->groupByRaw($periodeExpr)
            ->get();

        // Susun struktur: wilayah -> branch -> nama tunda -> periode -> total
        $structure = [];
        foreach ($rows as $row) {
            $wilayah = $branchToRegional[$row->NAME_BRANCH] ?? 'LAINNYA';
            $structure[$wilayah][$row->NAME_BRANCH][trim($row->NAMA_TUNDA)][$row->periode] = (float) $row->total;
        }

        $regionalData = [];
        foreach ($structure as $wilayah => $branches) {
            $branchTables = [];
            foreach ($branches as $branchName => $tundas) {
                $aggregated = $this->aggregateTundaRows($tundas, $periodeColumns);
                $branchTables[] = array_merge(['branch' => $branchName], $aggregated);
            }

            // Urutkan branch berdasarkan nama
            usort($branchTables, fn ($a, $b) => strcmp($a['branch'], $b['branch']));

            $regionalData[$wilayah] = $branchTables;
        }

        // Urutkan sesuai urutan wilayah baku (WILAYAH 1-4, JAI), sisanya di akhir
        $orderedRegionalData = [];
        foreach (array_merge($regionalKeys, ['LAINNYA']) as $wilayah) {
            if (isset($regionalData[$wilayah])) {
                $orderedRegionalData[$wilayah] = $regionalData[$wilayah];
            }
        }

        return view('summary-tunda', [
            'availableYears' => $availableYears,
            'regionalKeys' => $regionalKeys,
            'selectedTahun' => $selectedTahun,
            'selectedRegional' => $selectedRegional,
            'periodeColumns' => $periodeColumns,
            'regionalData' => $orderedRegionalData,
            'shownWilayah' => $shownWilayah,
        ]);
    }

    public function export(Request $request)
    {
        $selectedTahun = $request->get('tahun');
        $wilayah = $request->get('wilayah');

        $regionalGroups = $this->getRegionalGroups();

        if (!$selectedTahun || !$wilayah || !isset($regionalGroups[$wilayah])) {
            return redirect()->back()->with('error', 'Pilih tahun dan wilayah yang valid terlebih dahulu');
        }

        $periodeExpr = "DATE_FORMAT(STR_TO_DATE(BILLING_DATE, '%d-%m-%Y'), '%m-%Y')";
        $periodeColumns = array_map(fn ($bulan) => "{$bulan}-{$selectedTahun}", self::BULAN);

        $rows = DB::connection('dashboard_phinnisi')->table('tunda_prod')
            ->whereRaw('RIGHT(BILLING_DATE, 4) = ?', [$selectedTahun])
            ->whereIn('NAME_BRANCH', $regionalGroups[$wilayah])
            ->whereNotNull('NAMA_TUNDA')
            ->where('NAMA_TUNDA', '!=', '')
            ->select('NAME_BRANCH', 'NAMA_TUNDA')
            ->selectRaw("$periodeExpr as periode")
            ->selectRaw('SUM(REVENUE) as total')
            ->groupBy('NAME_BRANCH', 'NAMA_TUNDA')
            ->groupByRaw($periodeExpr)
            ->get();

        if ($rows->isEmpty()) {
            return redirect()->back()->with('error', "Tidak ada data pendapatan tunda untuk {$wilayah} tahun {$selectedTahun}");
        }

        $structure = [];
        foreach ($rows as $row) {
            $structure[$row->NAME_BRANCH][trim($row->NAMA_TUNDA)][$row->periode] = (float) $row->total;
        }

        $branchTables = [];
        foreach ($structure as $branchName => $tundas) {
            $aggregated = $this->aggregateTundaRows($tundas, $periodeColumns);
            $branchTables[] = array_merge(['branch' => $branchName], $aggregated);
        }
        usort($branchTables, fn ($a, $b) => strcmp($a['branch'], $b['branch']));

        $filename = 'Summary_Tunda_' . str_replace(' ', '_', $wilayah) . '_' . $selectedTahun . '_' . date('YmdHis') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($branchTables, $periodeColumns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            foreach ($branchTables as $branchTable) {
                // Header per branch, diikuti baris kosong pemisah antar branch
                // agar data tiap branch terpisah jelas saat dibuka di Excel.
                fputcsv($file, ['Branch: ' . $branchTable['branch']]);
                fputcsv($file, array_merge(['No', 'Nama Tunda'], $periodeColumns, ['Total']));

                $no = 1;
                foreach ($branchTable['rows'] as $row) {
                    $line = [$no++, $row['nama_tunda']];
                    foreach ($periodeColumns as $periode) {
                        $line[] = number_format($row['cells'][$periode], 0, ',', '.');
                    }
                    $line[] = number_format($row['total'], 0, ',', '.');
                    fputcsv($file, $line);
                }

                $totalLine = ['', 'Total Pendapatan / Bulan'];
                foreach ($periodeColumns as $periode) {
                    $totalLine[] = number_format($branchTable['monthly_total'][$periode], 0, ',', '.');
                }
                $totalLine[] = number_format($branchTable['grand_total'], 0, ',', '.');
                fputcsv($file, $totalLine);

                fputcsv($file, []);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Susun daftar baris nama tunda (diurutkan desc berdasar total) beserta
     * total per bulan dan grand total, dari struktur [nama_tunda => [periode => total]].
     */
    private function aggregateTundaRows(array $tundas, array $periodeColumns)
    {
        $rows = [];
        $monthlyTotal = array_fill_keys($periodeColumns, 0.0);
        $grandTotal = 0.0;

        uasort($tundas, function ($a, $b) {
            return array_sum($b) <=> array_sum($a);
        });

        foreach ($tundas as $tundaName => $periodeValues) {
            $rowTotal = 0.0;
            $cells = [];
            foreach ($periodeColumns as $periode) {
                $value = $periodeValues[$periode] ?? 0.0;
                $cells[$periode] = $value;
                $rowTotal += $value;
                $monthlyTotal[$periode] += $value;
            }
            $rows[] = [
                'nama_tunda' => $tundaName,
                'cells' => $cells,
                'total' => $rowTotal,
            ];
            $grandTotal += $rowTotal;
        }

        return [
            'rows' => $rows,
            'monthly_total' => $monthlyTotal,
            'grand_total' => $grandTotal,
        ];
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
                'REGIONAL 4 MANADO',
                'REGIONAL 4 SAUMLAKI'
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
}
