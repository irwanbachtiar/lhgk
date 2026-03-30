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
}
