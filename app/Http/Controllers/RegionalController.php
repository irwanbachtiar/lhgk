<?php
//tes git
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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
    
    public function detail(Request $request)
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
        $branchDetails = [];

        // Only load data if period is selected
        if ($selectedPeriode != 'all') {
            foreach ($regionalGroups as $wilayah => $branches) {
                // Get detailed data per branch - OPTIMIZED with groupBy
                $branchPanduData = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                    ->select('NAME_BRANCH')
                    ->selectRaw('SUM(REVENUE) as total_revenue')
                    ->selectRaw('COUNT(DISTINCT BILLING) as total_transaksi')
                    ->whereIn('NAME_BRANCH', $branches)
                    ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                    ->groupBy('NAME_BRANCH')
                    ->get()
                    ->keyBy('NAME_BRANCH');
                
                $branchTundaData = DB::connection('dashboard_phinnisi')->table('tunda_prod')
                    ->select('NAME_BRANCH')
                    ->selectRaw('SUM(REVENUE) as total_revenue')
                    ->whereIn('NAME_BRANCH', $branches)
                    ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                    ->groupBy('NAME_BRANCH')
                    ->get()
                    ->keyBy('NAME_BRANCH');
                
                $branchDetails[$wilayah] = [];
                foreach ($branches as $branch) {
                    $panduData = $branchPanduData->get($branch);
                    $tundaData = $branchTundaData->get($branch);
                    
                    $branchPandu = $panduData->total_revenue ?? 0;
                    $branchTunda = $tundaData->total_revenue ?? 0;
                    $branchTransaksi = $panduData->total_transaksi ?? 0;
                    
                    // Only add branches with data
                    if ($branchPandu > 0 || $branchTunda > 0) {
                        $branchDetails[$wilayah][$branch] = [
                            'pandu' => $branchPandu,
                            'tunda' => $branchTunda,
                            'total' => $branchPandu + $branchTunda,
                            'transaksi' => $branchTransaksi
                        ];
                    }
                }
            }
        }

        return view('regional-detail', compact(
            'periods',
            'selectedPeriode',
            'branchDetails',
            'regionalGroups'
        ));
    }
    
    public function exportExcel(Request $request)
    {
        $selectedPeriode = $request->get('periode', 'all');
        
        if ($selectedPeriode == 'all') {
            return redirect()->route('regional.detail')->with('error', 'Silakan pilih periode terlebih dahulu');
        }
        
        $regionalGroups = $this->getRegionalGroups();
        $branchDetails = [];

        foreach ($regionalGroups as $wilayah => $branches) {
            $branchPanduData = DB::connection('dashboard_phinnisi')->table('pandu_prod')
                ->select('NAME_BRANCH')
                ->selectRaw('SUM(REVENUE) as total_revenue')
                ->selectRaw('COUNT(DISTINCT BILLING) as total_transaksi')
                ->whereIn('NAME_BRANCH', $branches)
                ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                ->groupBy('NAME_BRANCH')
                ->get()
                ->keyBy('NAME_BRANCH');
            
            $branchTundaData = DB::connection('dashboard_phinnisi')->table('tunda_prod')
                ->select('NAME_BRANCH')
                ->selectRaw('SUM(REVENUE) as total_revenue')
                ->whereIn('NAME_BRANCH', $branches)
                ->whereRaw('DATE_FORMAT(STR_TO_DATE(INVOICE_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode])
                ->groupBy('NAME_BRANCH')
                ->get()
                ->keyBy('NAME_BRANCH');
            
            $branchDetails[$wilayah] = [];
            foreach ($branches as $branch) {
                $panduData = $branchPanduData->get($branch);
                $tundaData = $branchTundaData->get($branch);
                
                $branchPandu = $panduData->total_revenue ?? 0;
                $branchTunda = $tundaData->total_revenue ?? 0;
                $branchTransaksi = $panduData->total_transaksi ?? 0;
                
                if ($branchPandu > 0 || $branchTunda > 0) {
                    $branchDetails[$wilayah][$branch] = [
                        'pandu' => $branchPandu,
                        'tunda' => $branchTunda,
                        'total' => $branchPandu + $branchTunda,
                        'transaksi' => $branchTransaksi
                    ];
                }
            }
        }
        
        // Create Excel file
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('LHGK System')
            ->setTitle('Pendapatan Detail Per Wilayah - ' . $selectedPeriode)
            ->setSubject('Regional Revenue Report')
            ->setDescription('Laporan pendapatan detail per cabang dan wilayah');
        
        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ];
        
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1a202c']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]
        ];
        
        $subtitleStyle = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '667eea']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f8f9fa']],
            'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => '667eea']]]
        ];
        
        $totalStyle = [
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f8f9fa']],
            'borders' => ['top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM]]
        ];
        
        // Title
        $sheet->setCellValue('A1', 'LAPORAN PENDAPATAN DETAIL PER WILAYAH');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->applyFromArray($titleStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);
        
        $sheet->setCellValue('A2', 'Periode: ' . $selectedPeriode);
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        
        $currentRow = 4;
        
        foreach ($branchDetails as $wilayah => $branches) {
            if (empty($branches)) continue;
            
            // Wilayah header
            $sheet->setCellValue('A' . $currentRow, $wilayah);
            $sheet->mergeCells('A' . $currentRow . ':F' . $currentRow);
            $sheet->getStyle('A' . $currentRow)->applyFromArray($subtitleStyle);
            $sheet->getRowDimension($currentRow)->setRowHeight(25);
            $currentRow++;
            
            // Column headers
            $sheet->setCellValue('A' . $currentRow, 'No');
            $sheet->setCellValue('B' . $currentRow, 'Nama Cabang');
            $sheet->setCellValue('C' . $currentRow, 'Pendapatan Pandu');
            $sheet->setCellValue('D' . $currentRow, 'Pendapatan Tunda');
            $sheet->setCellValue('E' . $currentRow, 'Total Pendapatan');
            $sheet->setCellValue('F' . $currentRow, 'Transaksi');
            $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->applyFromArray($headerStyle);
            $sheet->getRowDimension($currentRow)->setRowHeight(20);
            $currentRow++;
            
            // Data rows
            $no = 1;
            $totalPandu = 0;
            $totalTunda = 0;
            $totalRevenue = 0;
            $totalTransaksi = 0;
            
            foreach ($branches as $branchName => $data) {
                $sheet->setCellValue('A' . $currentRow, $no++);
                $sheet->setCellValue('B' . $currentRow, $branchName);
                $sheet->setCellValue('C' . $currentRow, $data['pandu']);
                $sheet->setCellValue('D' . $currentRow, $data['tunda']);
                $sheet->setCellValue('E' . $currentRow, $data['total']);
                $sheet->setCellValue('F' . $currentRow, $data['transaksi']);
                
                // Format currency
                $sheet->getStyle('C' . $currentRow)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('D' . $currentRow)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('E' . $currentRow)->getNumberFormat()->setFormatCode('#,##0');
                
                // Borders
                $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
                $totalPandu += $data['pandu'];
                $totalTunda += $data['tunda'];
                $totalRevenue += $data['total'];
                $totalTransaksi += $data['transaksi'];
                
                $currentRow++;
            }
            
            // Total row
            $sheet->setCellValue('A' . $currentRow, '');
            $sheet->setCellValue('B' . $currentRow, 'TOTAL ' . $wilayah);
            $sheet->setCellValue('C' . $currentRow, $totalPandu);
            $sheet->setCellValue('D' . $currentRow, $totalTunda);
            $sheet->setCellValue('E' . $currentRow, $totalRevenue);
            $sheet->setCellValue('F' . $currentRow, $totalTransaksi);
            
            $sheet->getStyle('C' . $currentRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('D' . $currentRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('E' . $currentRow)->getNumberFormat()->setFormatCode('#,##0');
            
            $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->applyFromArray($totalStyle);
            $currentRow += 2;
        }
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(12);
        
        // Set alignment
        $sheet->getStyle('A4:A' . ($currentRow - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C4:F' . ($currentRow - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        
        // Generate filename
        $filename = 'Pendapatan_Detail_PerWilayah_' . str_replace('-', '_', $selectedPeriode) . '_' . date('Ymd_His') . '.xlsx';
        
        // Create writer and download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
