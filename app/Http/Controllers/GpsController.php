<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GpsController extends Controller
{
    public function index(Request $request)
    {
        $selectedPeriode = $request->get('periode', 'all');
        $selectedBranch = $request->get('cabang', 'all');
        $showGrid = $request->boolean('show_grid');

        $regionalGroups = $this->getRegionalGroups();

        try {
            $periodsQuery = DB::table('tabel_gps')
                ->whereNotNull('PERIODE')
                ->where('PERIODE', '!=', '')
                ->whereNotNull('ONBOARD')
                ->where('ONBOARD', '!=', '');

            if ($selectedBranch != 'all') {
                $periodsQuery->where('NM_BRANCH', $selectedBranch);
            }

            $periods = $periodsQuery
                ->groupBy('PERIODE')
                ->orderByRaw("STR_TO_DATE(CONCAT('01-', PERIODE), '%d-%m-%Y') DESC")
                ->pluck('PERIODE');
        } catch (\Exception $e) {
            $periods = collect();
        }

        $points = collect();
        $pointsCount = 0;

        if ($selectedPeriode != 'all' && $selectedBranch != 'all') {
            try {
                $rows = DB::table('tabel_gps')
                    ->select('PPKB_CODE', 'NM_BRANCH', 'NM_KAPAL', 'PANDU_DARI', 'PANDU_KE', 'MULAI_PELAKSANAAN', 'ONBOARD', 'REALISAS_PILOT_VIA')
                    ->whereNotNull('ONBOARD')
                    ->where('ONBOARD', '!=', '')
                    ->where('PERIODE', $selectedPeriode)
                    ->where('NM_BRANCH', $selectedBranch)
                    ->get();

                $points = $rows->map(function ($row) {
                    $coords = preg_split('/[\s,]+/', trim($row->ONBOARD));
                    if (count($coords) < 2 || !is_numeric($coords[0]) || !is_numeric($coords[1])) {
                        return null;
                    }

                    return [
                        'lat' => (float) $coords[0],
                        'lng' => (float) $coords[1],
                        'ppkb_code' => $row->PPKB_CODE,
                        'nm_kapal' => $row->NM_KAPAL,
                        'pandu_dari' => $row->PANDU_DARI,
                        'pandu_ke' => $row->PANDU_KE,
                        'mulai_pelaksanaan' => $row->MULAI_PELAKSANAAN,
                        'realisasi_via' => $row->REALISAS_PILOT_VIA,
                    ];
                })->filter()->values();

                $pointsCount = $points->count();
            } catch (\Exception $e) {
                \Log::error('Error getting GPS points: ' . $e->getMessage());
                $points = collect();
                $pointsCount = 0;
            }
        }

        $gridCount = 0;
        $gridData = null;

        if ($selectedPeriode != 'all' && $selectedBranch != 'all') {
            try {
                $gridCount = DB::table('tabel_gps')
                    ->where('PERIODE', $selectedPeriode)
                    ->where('NM_BRANCH', $selectedBranch)
                    ->count();

                if ($showGrid) {
                    $gridData = DB::table('tabel_gps')
                        ->select(
                            'PPKB_CODE', 'PERIODE', 'NO_UKK', 'NO_BKT_PANDU', 'TGL_JAM_TIBA',
                            'NM_KAPAL', 'JN_KAPAL', 'NM_PERS_PANDU', 'MULAI_PELAKSANAAN',
                            'SELESAI_PELAKSANAAN', 'GERAKAN', 'REALISAS_PILOT_VIA',
                            'COMMANDON', 'COMMANDOFF', 'DEPARTURE', 'ONBOARD', 'PILOTOFF'
                        )
                        ->where('PERIODE', $selectedPeriode)
                        ->where('NM_BRANCH', $selectedBranch)
                        ->orderBy('PPKB_CODE')
                        ->paginate(50)
                        ->withQueryString();
                }
            } catch (\Exception $e) {
                $gridCount = 0;
                $gridData = null;
            }
        }

        return view('gps', compact('regionalGroups', 'periods', 'selectedPeriode', 'selectedBranch', 'points', 'pointsCount', 'showGrid', 'gridCount', 'gridData'));
    }

    public function exportGrid(Request $request)
    {
        $selectedPeriode = $request->get('periode');
        $selectedBranch = $request->get('cabang');

        if (!$selectedPeriode || !$selectedBranch) {
            return redirect()->back()->with('error', 'Pilih periode dan cabang terlebih dahulu');
        }

        $data = DB::table('tabel_gps')
            ->select(
                'PPKB_CODE', 'PERIODE', 'NO_UKK', 'NO_BKT_PANDU', 'TGL_JAM_TIBA',
                'NM_KAPAL', 'JN_KAPAL', 'NM_PERS_PANDU', 'MULAI_PELAKSANAAN',
                'SELESAI_PELAKSANAAN', 'GERAKAN', 'REALISAS_PILOT_VIA',
                'COMMANDON', 'COMMANDOFF', 'DEPARTURE', 'ONBOARD', 'PILOTOFF'
            )
            ->where('PERIODE', $selectedPeriode)
            ->where('NM_BRANCH', $selectedBranch)
            ->orderBy('PPKB_CODE')
            ->get();

        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data GPS untuk periode dan cabang yang dipilih');
        }

        $filename = 'GPS_' . str_replace(' ', '_', $selectedBranch) . '_' . str_replace('-', '', $selectedPeriode) . '_' . date('YmdHis') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'No', 'PPKB Code', 'Periode', 'No. UKK', 'No. Bukti Pandu', 'Tgl Jam Tiba',
                'Nama Kapal', 'Jenis Kapal', 'Nama Pandu', 'Mulai Pelaksanaan', 'Selesai Pelaksanaan',
                'Gerakan', 'Realisasi Pilot Via', 'Command On', 'Command Off', 'Departure', 'Onboard', 'Pilot Off'
            ]);

            $no = 1;
            foreach ($data as $row) {
                fputcsv($file, [
                    $no++,
                    $row->PPKB_CODE ?? '-',
                    $row->PERIODE ?? '-',
                    $row->NO_UKK ?? '-',
                    $row->NO_BKT_PANDU ?? '-',
                    $row->TGL_JAM_TIBA ?? '-',
                    $row->NM_KAPAL ?? '-',
                    $row->JN_KAPAL ?? '-',
                    $row->NM_PERS_PANDU ?? '-',
                    $row->MULAI_PELAKSANAAN ?? '-',
                    $row->SELESAI_PELAKSANAAN ?? '-',
                    $row->GERAKAN ?? '-',
                    $row->REALISAS_PILOT_VIA ?? '-',
                    $row->COMMANDON ?? '-',
                    $row->COMMANDOFF ?? '-',
                    $row->DEPARTURE ?? '-',
                    $row->ONBOARD ?? '-',
                    $row->PILOTOFF ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
