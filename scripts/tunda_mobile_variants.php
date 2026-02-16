<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$periode = $argv[1] ?? '12-2025';
$branchLike = '%' . strtoupper(($argv[2] ?? 'REGIONAL 4 BALIKPAPAN')) . '%';

echo "Compute variants for MOBILE in periode={$periode}, branchLike={$branchLike}\n";

$db = DB::connection('dashboard_phinnisi');

$total_mobile = $db->selectOne("SELECT COUNT(*) as cnt FROM lhgk WHERE PERIODE = ? AND UPPER(NM_BRANCH) LIKE ? AND UPPER(COALESCE(REALISAS_PILOT_VIA,'')) = 'MOBILE'", [$periode, $branchLike]);
$distinct_pilot = $db->selectOne("SELECT COUNT(DISTINCT NM_PERS_PANDU) as cnt FROM lhgk WHERE PERIODE = ? AND UPPER(NM_BRANCH) LIKE ? AND UPPER(COALESCE(REALISAS_PILOT_VIA,'')) = 'MOBILE'", [$periode, $branchLike]);
$distinct_ship = $db->selectOne("SELECT COUNT(DISTINCT NM_KAPAL) as cnt FROM lhgk WHERE PERIODE = ? AND UPPER(NM_BRANCH) LIKE ? AND UPPER(COALESCE(REALISAS_PILOT_VIA,'')) = 'MOBILE'", [$periode, $branchLike]);
$mobile_with_tunda = $db->selectOne("SELECT COUNT(*) as cnt FROM lhgk WHERE PERIODE = ? AND UPPER(NM_BRANCH) LIKE ? AND UPPER(COALESCE(REALISAS_PILOT_VIA,'')) = 'MOBILE' AND PENDAPATAN_TUNDA IS NOT NULL AND PENDAPATAN_TUNDA <> ''", [$periode, $branchLike]);
$mobile_without_tunda = $db->selectOne("SELECT COUNT(*) as cnt FROM lhgk WHERE PERIODE = ? AND UPPER(NM_BRANCH) LIKE ? AND UPPER(COALESCE(REALISAS_PILOT_VIA,'')) = 'MOBILE' AND (PENDAPATAN_TUNDA IS NULL OR PENDAPATAN_TUNDA = '')", [$periode, $branchLike]);

echo "Results:\n";
echo "  total rows MOBILE: " . (($total_mobile->cnt ?? 0) + 0) . "\n";
echo "  distinct NM_PERS_PANDU: " . (($distinct_pilot->cnt ?? 0) + 0) . "\n";
echo "  distinct NM_KAPAL: " . (($distinct_ship->cnt ?? 0) + 0) . "\n";
echo "  MOBILE with PENDAPATAN_TUNDA not empty: " . (($mobile_with_tunda->cnt ?? 0) + 0) . "\n";
echo "  MOBILE without PENDAPATAN_TUNDA: " . (($mobile_without_tunda->cnt ?? 0) + 0) . "\n";

return 0;
