<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnperController extends Controller
{
    public function index(Request $request)
    {
        $selectedPeriode = $request->get('periode', 'all');

        // Dummy periods list (matching the app's typical period format)
        $periods = [
            'Januari 2024', 'Februari 2024', 'Maret 2024', 'April 2024',
            'Mei 2024', 'Juni 2024', 'Juli 2024', 'Agustus 2024',
            'September 2024', 'Oktober 2024', 'November 2024', 'Desember 2024',
            'Januari 2025', 'Februari 2025', 'Maret 2025', 'April 2025',
            'Mei 2025', 'Juni 2025', 'Juli 2025', 'Agustus 2025',
            'September 2025', 'Oktober 2025', 'November 2025', 'Desember 2025',
        ];

        // Dummy data per anak perusahaan
        // Values will be slightly varied per period to simulate real data
        $multiplier = 1.0;
        if ($selectedPeriode != 'all') {
            $index = array_search($selectedPeriode, $periods);
            $multiplier = $index !== false ? (0.85 + ($index * 0.015)) : 1.0;
        }

        $data = [
            'pms' => [
                'nama' => 'Pelindo Marine Service (PMS)',
                'singkatan' => 'PMS',
                'warna' => '#667eea',
                'icon' => 'bi-ship',
                'pendapatan' => [
                    'sarana_bantu' => [
                        'label' => 'Sarana Bantu dan Prasarana Pemanduan dan Penundaan',
                        'realisasi' => (int) round(12500000000 * $multiplier),
                        'anggaran' => 15000000000,
                        'icon' => 'bi-anchor',
                        'color' => 'primary',
                    ],
                ],
            ],
            'jai' => [
                'nama' => 'Jasa Armada Indonesia (JAI)',
                'singkatan' => 'JAI',
                'warna' => '#f59e0b',
                'icon' => 'bi-life-preserver',
                'pendapatan' => [
                    'sarana_bantu' => [
                        'label' => 'Sarana Bantu dan Prasarana Pemanduan dan Penundaan',
                        'realisasi' => (int) round(8750000000 * $multiplier),
                        'anggaran' => 10000000000,
                        'icon' => 'bi-anchor',
                        'color' => 'warning',
                    ],
                ],
            ],
            'legi' => [
                'nama' => 'Lamong Energi Indonesia (LEGI)',
                'singkatan' => 'LEGI',
                'warna' => '#06b6d4',
                'icon' => 'bi-lightning-charge',
                'pendapatan' => [
                    'air' => [
                        'label' => 'Air',
                        'realisasi' => (int) round(3200000000 * $multiplier),
                        'anggaran' => 4000000000,
                        'icon' => 'bi-droplet-fill',
                        'color' => 'info',
                    ],
                    'bbm' => [
                        'label' => 'BBM',
                        'realisasi' => (int) round(15600000000 * $multiplier),
                        'anggaran' => 18000000000,
                        'icon' => 'bi-fuel-pump-fill',
                        'color' => 'warning',
                    ],
                    'listrik' => [
                        'label' => 'Listrik',
                        'realisasi' => (int) round(4800000000 * $multiplier),
                        'anggaran' => 6000000000,
                        'icon' => 'bi-plug-fill',
                        'color' => 'danger',
                    ],
                ],
            ],
            'epi' => [
                'nama' => 'Energi Pelabuhan Indonesia (EPI)',
                'singkatan' => 'EPI',
                'warna' => '#10b981',
                'icon' => 'bi-battery-charging',
                'pendapatan' => [
                    'air' => [
                        'label' => 'Air',
                        'realisasi' => (int) round(2100000000 * $multiplier),
                        'anggaran' => 2500000000,
                        'icon' => 'bi-droplet-fill',
                        'color' => 'info',
                    ],
                    'listrik' => [
                        'label' => 'Listrik',
                        'realisasi' => (int) round(6300000000 * $multiplier),
                        'anggaran' => 7500000000,
                        'icon' => 'bi-plug-fill',
                        'color' => 'danger',
                    ],
                ],
            ],
            'bima' => [
                'nama' => 'Berkah Industri Mesin Angkat (BIMA)',
                'singkatan' => 'BIMA',
                'warna' => '#8b5cf6',
                'icon' => 'bi-tools',
                'pendapatan' => [
                    'proyek' => [
                        'label' => 'Proyek',
                        'realisasi' => (int) round(9450000000 * $multiplier),
                        'anggaran' => 12000000000,
                        'icon' => 'bi-diagram-3-fill',
                        'color' => 'primary',
                    ],
                    'sparepart' => [
                        'label' => 'Spare Part',
                        'realisasi' => (int) round(3120000000 * $multiplier),
                        'anggaran' => 4000000000,
                        'icon' => 'bi-gear-fill',
                        'color' => 'secondary',
                    ],
                    'maintenance' => [
                        'label' => 'Maintenance',
                        'realisasi' => (int) round(5670000000 * $multiplier),
                        'anggaran' => 7000000000,
                        'icon' => 'bi-wrench-adjustable',
                        'color' => 'success',
                    ],
                ],
            ],
        ];

        // Calculate totals per anper
        foreach ($data as &$anper) {
            $anper['total_realisasi'] = array_sum(array_column($anper['pendapatan'], 'realisasi'));
            $anper['total_anggaran'] = array_sum(array_column($anper['pendapatan'], 'anggaran'));
            $anper['persentase'] = $anper['total_anggaran'] > 0
                ? round(($anper['total_realisasi'] / $anper['total_anggaran']) * 100, 1)
                : 0;

            foreach ($anper['pendapatan'] as &$item) {
                $item['persentase'] = $item['anggaran'] > 0
                    ? round(($item['realisasi'] / $item['anggaran']) * 100, 1)
                    : 0;
            }
        }

        $grandTotal = array_sum(array_column($data, 'total_realisasi'));

        return view('anper', compact('data', 'periods', 'selectedPeriode', 'grandTotal'));
    }
}
