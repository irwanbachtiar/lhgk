<?php

namespace App\Http\Controllers;

use App\Models\Lhgk;
use Illuminate\Http\Request;

class ContributionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $selectedKapal = trim((string) $request->get('kapal', ''));
        $selectedPeriode = $request->get('periode', 'all');

        $searchResults = collect();
        if ($search !== '') {
            $searchResults = Lhgk::whereNotNull('NM_KAPAL')
                ->where('NM_KAPAL', '!=', '')
                ->where('NM_KAPAL', 'like', '%' . $search . '%')
                ->select('NM_KAPAL')
                ->distinct()
                ->orderBy('NM_KAPAL')
                ->limit(30)
                ->pluck('NM_KAPAL');
        }

        $shipInfo = null;
        $branchList = collect();
        $periods = collect();
        $visitHistory = null;
        $branchTimeline = collect();
        $branchContribution = collect();
        $totalContribution = null;

        if ($selectedKapal !== '') {
            $periods = Lhgk::where('NM_KAPAL', $selectedKapal)
                ->whereNotNull('PERIODE')
                ->where('PERIODE', '!=', '')
                ->select('PERIODE')
                ->distinct()
                ->orderByRaw("STR_TO_DATE(CONCAT('01-', PERIODE), '%d-%m-%Y') DESC")
                ->pluck('PERIODE');

            $filteredQuery = Lhgk::where('NM_KAPAL', $selectedKapal);
            if ($selectedPeriode != 'all') {
                $filteredQuery->where('PERIODE', $selectedPeriode);
            }

            $shipInfo = (clone $filteredQuery)
                ->select('JN_KAPAL', 'KD_BENDERA', 'KP_GRT', 'KP_LOA')
                ->selectRaw('COUNT(*) as jml')
                ->whereNotNull('JN_KAPAL')
                ->where('JN_KAPAL', '!=', '')
                ->groupBy('JN_KAPAL', 'KD_BENDERA', 'KP_GRT', 'KP_LOA')
                ->orderByDesc('jml')
                ->first();

            $branchList = (clone $filteredQuery)
                ->whereNotNull('NM_BRANCH')
                ->where('NM_BRANCH', '!=', '')
                ->select('NM_BRANCH')
                ->distinct()
                ->orderBy('NM_BRANCH')
                ->pluck('NM_BRANCH');

            $visitHistory = (clone $filteredQuery)
                ->select(
                    'PPKB_CODE',
                    'NO_UKK',
                    'NO_BKT_PANDU',
                    'NM_BRANCH',
                    'PERIODE',
                    'TGL_TIBA',
                    'JAM_TIBA',
                    'PENDAPATAN_PANDU',
                    'PENDAPATAN_TUNDA',
                    'STATUS_NOTA'
                )
                ->orderByRaw("STR_TO_DATE(TGL_TIBA, '%d-%m-%Y') DESC")
                ->orderByDesc('JAM_TIBA')
                ->paginate(20, ['*'], 'visit_page')
                ->withQueryString();

            $timelineRows = (clone $filteredQuery)
                ->select(
                    'NM_BRANCH',
                    'PPKB_CODE',
                    'TGL_TIBA',
                    'JAM_TIBA',
                    'PERIODE',
                    'PENDAPATAN_PANDU',
                    'PENDAPATAN_TUNDA'
                )
                ->whereNotNull('NM_BRANCH')
                ->where('NM_BRANCH', '!=', '')
                ->orderByRaw("STR_TO_DATE(TGL_TIBA, '%d-%m-%Y') DESC")
                ->get();

            $branchTimeline = $timelineRows->groupBy('NM_BRANCH');

            $branchContribution = (clone $filteredQuery)
                ->whereNotNull('NM_BRANCH')
                ->where('NM_BRANCH', '!=', '')
                ->select('NM_BRANCH')
                ->selectRaw('COUNT(DISTINCT NO_UKK) as jumlah_kunjungan')
                ->selectRaw('COUNT(*) as jumlah_gerakan')
                ->selectRaw('SUM(PENDAPATAN_PANDU) as total_pandu')
                ->selectRaw('SUM(PENDAPATAN_TUNDA) as total_tunda')
                ->selectRaw('SUM(PENDAPATAN_PANDU + PENDAPATAN_TUNDA) as total_kontribusi')
                ->groupBy('NM_BRANCH')
                ->orderByDesc('total_kontribusi')
                ->get();

            $totalContribution = [
                'jumlah_kunjungan' => $branchContribution->sum('jumlah_kunjungan'),
                'jumlah_gerakan' => $branchContribution->sum('jumlah_gerakan'),
                'total_pandu' => $branchContribution->sum('total_pandu'),
                'total_tunda' => $branchContribution->sum('total_tunda'),
                'total_kontribusi' => $branchContribution->sum('total_kontribusi'),
            ];
        }

        return view('contribution', compact(
            'search',
            'selectedKapal',
            'selectedPeriode',
            'searchResults',
            'shipInfo',
            'branchList',
            'periods',
            'visitHistory',
            'branchTimeline',
            'branchContribution',
            'totalContribution'
        ));
    }

    public function export(Request $request)
    {
        $selectedKapal = trim((string) $request->get('kapal', ''));
        $selectedPeriode = $request->get('periode', 'all');

        if ($selectedKapal === '') {
            return redirect()->back()->with('error', 'Pilih kapal terlebih dahulu');
        }

        $query = Lhgk::where('NM_KAPAL', $selectedKapal);
        if ($selectedPeriode != 'all') {
            $query->where('PERIODE', $selectedPeriode);
        }

        $data = $query
            ->select(
                'PPKB_CODE',
                'NO_UKK',
                'NO_BKT_PANDU',
                'NM_BRANCH',
                'PERIODE',
                'JN_KAPAL',
                'TGL_TIBA',
                'JAM_TIBA',
                'PENDAPATAN_PANDU',
                'PENDAPATAN_TUNDA',
                'STATUS_NOTA'
            )
            ->orderByRaw("STR_TO_DATE(TGL_TIBA, '%d-%m-%Y') DESC")
            ->get();

        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data untuk kapal dan periode yang dipilih');
        }

        $filename = 'Contribution_' . preg_replace('/[^A-Za-z0-9]+/', '_', $selectedKapal) . '_' . date('YmdHis') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'No', 'PPKB Code', 'No. UKK', 'No. Bukti Pandu', 'Cabang', 'Periode',
                'Jenis Kapal', 'Tgl Tiba', 'Jam Tiba', 'Pendapatan Pandu',
                'Pendapatan Tunda', 'Total', 'Status Nota'
            ]);

            $no = 1;
            foreach ($data as $row) {
                $pandu = (float) ($row->PENDAPATAN_PANDU ?? 0);
                $tunda = (float) ($row->PENDAPATAN_TUNDA ?? 0);
                fputcsv($file, [
                    $no++,
                    $row->PPKB_CODE ?? '-',
                    $row->NO_UKK ?? '-',
                    $row->NO_BKT_PANDU ?? '-',
                    $row->NM_BRANCH ?? '-',
                    $row->PERIODE ?? '-',
                    $row->JN_KAPAL ?? '-',
                    $row->TGL_TIBA ?? '-',
                    $row->JAM_TIBA ?? '-',
                    number_format($pandu, 0, ',', '.'),
                    number_format($tunda, 0, ',', '.'),
                    number_format($pandu + $tunda, 0, ',', '.'),
                    $row->STATUS_NOTA ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
