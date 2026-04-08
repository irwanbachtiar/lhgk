<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lhgk;
use Illuminate\Support\Facades\DB;

class SarprasController extends Controller
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
        $selectedBranch = $request->get('cabang', 'all');

        $regionalGroups = $this->getRegionalGroups();

        try {
            $allBranches = Lhgk::select('NM_BRANCH')
                ->whereNotNull('NM_BRANCH')
                ->where('NM_BRANCH', '!=', '')
                ->groupBy('NM_BRANCH')
                ->orderBy('NM_BRANCH')
                ->pluck('NM_BRANCH')
                ->toArray();
        } catch (\Exception $e) {
            $allBranches = [];
        }

        try {
            $periods = Lhgk::select('PERIODE')
                ->whereNotNull('PERIODE')
                ->where('PERIODE', '!=', '')
                ->groupBy('PERIODE')
                ->orderByRaw("STR_TO_DATE(CONCAT('01-', PERIODE), '%d-%m-%Y') DESC")
                ->pluck('PERIODE');
        } catch (\Exception $e) {
            $periods = collect();
        }

        $mstRows = collect();
        $mstColumns = [];
        $mstError = null;
        $onlyPanduEndorsement = false;
        $preferredDateCol = null;

        try {
            $schema = DB::connection('dashboard_phinnisi')->getSchemaBuilder();
            if ($schema->hasTable('mst_pandu')) {
                $mstColumns = $schema->getColumnListing('mst_pandu');

                // Prefer a specific column for MASA BERLAKU ENDORSERMENT PELAUT when present
                $nameCol = null;
                $dateCol = null;
                $preferredDateCol = null;
                foreach ($mstColumns as $c) {
                    $lower = strtolower($c);
                    // Prefer exact MASA BERLAKU ENDORSERMENT PANDU / PELAUT
                    if (str_contains($lower, 'masa') && str_contains($lower, 'endor') && (str_contains($lower, 'pandu') || str_contains($lower, 'pelaut'))) {
                        $preferredDateCol = $c;
                        break;
                    }
                }

                // detect branch column as well (prefer NM_BRANCH / NAME_BRANCH / CABANG / BRANCH)
                $branchCol = null;
                foreach ($mstColumns as $c) {
                    $lower = strtolower($c);
                    if (!$nameCol && (str_contains($lower, 'nama') || str_contains($lower, 'name'))) {
                        $nameCol = $c;
                    }
                    if (!$branchCol && (str_contains($lower, 'nm_branch') || str_contains($lower, 'name_branch') || str_contains($lower, 'branch') || str_contains($lower, 'cabang'))) {
                        $branchCol = $c;
                    }
                    if (!$dateCol) {
                        if ($preferredDateCol) {
                            $dateCol = $preferredDateCol;
                        } elseif (str_contains($lower, 'masa') || str_contains($lower, 'berlaku') || str_contains($lower, 'endor') || str_contains($lower, 'expired') || str_contains($lower, 'tgl')) {
                            $dateCol = $c;
                        }
                    }
                }

                // If user specifically wants MASA BERLAKU ENDORSERMENT PANDU, require preferred column
                if ($preferredDateCol) {
                    $dateCol = $preferredDateCol;
                    $onlyPanduEndorsement = true;
                } else {
                    $onlyPanduEndorsement = false;
                }

                if ($onlyPanduEndorsement && !$dateCol) {
                    $mstError = 'Kolom MASA BERLAKU ENDORSERMENT PANDU tidak ditemukan di tabel mst_pandu';
                } elseif (!$dateCol || !$nameCol) {
                    $mstError = 'Kolom nama atau tanggal tidak ditemukan di tabel mst_pandu';
                } else {
                    // Build safe backtick names
                    $nameBack = "`" . str_replace("`", "``", $nameCol) . "`";
                    $dateBack = "`" . str_replace("`", "``", $dateCol) . "`";

                    // Query: group by name (+ branch when available), get nearest expiry (min) where expiry within 3 months or already expired
                    if ($branchCol) {
                        $branchBack = "`" . str_replace("`", "``", $branchCol) . "`";
                        $selectRaw = "{$nameBack} as name, {$branchBack} as branch, MIN(COALESCE(STR_TO_DATE({$dateBack}, '%d-%m-%Y'), STR_TO_DATE({$dateBack}, '%Y-%m-%d'))) as next_expiry, MIN({$dateBack}) as raw_value, COUNT(*) as total_count";
                        $groupExpr = DB::raw("{$nameBack}, {$branchBack}");
                    } else {
                        $selectRaw = "{$nameBack} as name, MIN(COALESCE(STR_TO_DATE({$dateBack}, '%d-%m-%Y'), STR_TO_DATE({$dateBack}, '%Y-%m-%d'))) as next_expiry, MIN({$dateBack}) as raw_value, COUNT(*) as total_count";
                        $groupExpr = DB::raw($nameBack);
                    }

                    $rows = DB::connection('dashboard_phinnisi')
                        ->table('mst_pandu')
                        ->selectRaw($selectRaw)
                        ->whereRaw("COALESCE(STR_TO_DATE({$dateBack}, '%d-%m-%Y'), STR_TO_DATE({$dateBack}, '%Y-%m-%d')) IS NOT NULL")
                        ->groupBy($groupExpr)
                        ->havingRaw("(next_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 MONTH) OR next_expiry < CURDATE())")
                        ->orderByRaw('next_expiry ASC')
                        ->get();

                    // Map results and compute days remaining
                    $mstRows = collect($rows)->map(function($r) use ($branchCol, $dateCol) {
                        $date = $r->next_expiry;
                        $ymd = null;
                        try {
                            $ymd = $date ? date('Y-m-d', strtotime($date)) : null;
                        } catch (\Exception $e) {
                            $ymd = null;
                        }
                        $diff = null;
                        if ($ymd) {
                            $diff = (int) ceil((strtotime($ymd) - strtotime(date('Y-m-d'))) / 86400);
                        }
                        return (object)[
                            'name' => $r->name,
                            'branch' => $branchCol ? ($r->branch ?? null) : null,
                            'next_expiry' => $ymd,
                            'days_remaining' => $diff,
                            'expiry_column' => $dateCol,
                            'raw_value' => property_exists($r, 'raw_value') ? $r->raw_value : null,
                            'count' => property_exists($r, 'total_count') ? (int)$r->total_count : 0
                        ];
                    });
                }
            } else {
                $mstError = 'Tabel mst_pandu tidak ditemukan pada koneksi dashboard_phinnisi';
            }
        } catch (\Exception $e) {
            $mstError = 'Gagal mengambil data mst_pandu: ' . $e->getMessage();
        }

        return view('sarpras', compact('periods', 'selectedPeriode', 'regionalGroups', 'allBranches', 'selectedBranch', 'mstRows', 'mstColumns', 'mstError', 'onlyPanduEndorsement', 'preferredDateCol'));
    }
}
