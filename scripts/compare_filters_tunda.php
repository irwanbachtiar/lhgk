<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$periode = $argv[1] ?? '12-2025';
$branchLike = '%' . strtoupper(($argv[2] ?? 'REGIONAL 4 BALIKPAPAN')) . '%';
$branchExact = $argv[2] ?? 'REGIONAL 4 BALIKPAPAN';

echo "Compare counts for periode={$periode} branchLike={$branchLike} branchExact={$branchExact}\n";

$base = DB::connection('dashboard_phinnisi');

// fallback mobile count (REALISAS_PILOT_VIA)
$fallback = $base->selectOne("SELECT SUM(CASE WHEN UPPER(COALESCE(REALISAS_PILOT_VIA,''))='MOBILE' THEN 1 ELSE 0 END) as mobile FROM lhgk WHERE PERIODE = ? AND UPPER(NM_BRANCH) LIKE ?", [$periode, $branchLike]);

// with PENDAPATAN_TUNDA not null
$withPend = $base->selectOne("SELECT COUNT(*) as mobile FROM lhgk WHERE PERIODE = ? AND UPPER(NM_BRANCH) LIKE ? AND PENDAPATAN_TUNDA IS NOT NULL AND PENDAPATAN_TUNDA <> '' AND UPPER(REALISAS_PILOT_VIA) = 'MOBILE'", [$periode, $branchLike]);

// exact branch match
$exact = $base->selectOne("SELECT COUNT(*) as mobile FROM lhgk WHERE PERIODE = ? AND NM_BRANCH = ? AND UPPER(REALISAS_PILOT_VIA) = 'MOBILE'", [$periode, $branchExact]);

echo "Results:\n";
echo "  fallback REALISAS_PILOT_VIA (LIKE): " . (($fallback->mobile ?? 0) + 0) . "\n";
echo "  with PENDAPATAN_TUNDA not null (LIKE): " . (($withPend->mobile ?? 0) + 0) . "\n";
echo "  exact NM_BRANCH match (NM_BRANCH = '{$branchExact}'): " . (($exact->mobile ?? 0) + 0) . "\n";

return 0;
