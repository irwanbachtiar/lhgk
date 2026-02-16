<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$periode = $argv[1] ?? '12-2025';
$branch = $argv[2] ?? 'REGIONAL 4 BALIKPAPAN';

echo "Checking different tunda count methods for branch={$branch} periode={$periode}\n";

$like = '%' . strtoupper($branch) . '%';

// per-column sum (count each occurrence)
$colSql = "SELECT
    (
        SUM(CASE WHEN UPPER(COALESCE(realisas_tug1_via,'')) = 'MOBILE' THEN 1 ELSE 0 END) +
        SUM(CASE WHEN UPPER(COALESCE(realisas_tug2_via,'')) = 'MOBILE' THEN 1 ELSE 0 END) +
        SUM(CASE WHEN UPPER(COALESCE(realisas_tug3_via,'')) = 'MOBILE' THEN 1 ELSE 0 END) +
        SUM(CASE WHEN UPPER(COALESCE(realisas_tug4_via,'')) = 'MOBILE' THEN 1 ELSE 0 END)
    ) as mobile_col_sum
FROM lhgk WHERE PERIODE = ? AND UPPER(NM_BRANCH) LIKE ?";
$col = DB::connection('dashboard_phinnisi')->selectOne($colSql, [$periode, $like]);

// per-row unique (count rows where any tug column is MOBILE)
$rowSql = "SELECT COUNT(*) as mobile_row_count FROM lhgk WHERE PERIODE = ? AND UPPER(NM_BRANCH) LIKE ? AND (
    UPPER(COALESCE(realisas_tug1_via,'')) = 'MOBILE' OR
    UPPER(COALESCE(realisas_tug2_via,'')) = 'MOBILE' OR
    UPPER(COALESCE(realisas_tug3_via,'')) = 'MOBILE' OR
    UPPER(COALESCE(realisas_tug4_via,'')) = 'MOBILE'
)
";
$row = DB::connection('dashboard_phinnisi')->selectOne($rowSql, [$periode, $like]);

// fallback REALISAS_PILOT_VIA mobile count
$fallbackSql = "SELECT SUM(CASE WHEN UPPER(COALESCE(REALISAS_PILOT_VIA,'')) = 'MOBILE' THEN 1 ELSE 0 END) as mobile_fallback FROM lhgk WHERE PERIODE = ? AND UPPER(NM_BRANCH) LIKE ?";
$fb = DB::connection('dashboard_phinnisi')->selectOne($fallbackSql, [$periode, $like]);

echo "Results:\n";
echo "  mobile per-column sum: " . (($col->mobile_col_sum ?? 0) + 0) . "\n";
echo "  mobile per-row (any column): " . (($row->mobile_row_count ?? 0) + 0) . "\n";
echo "  mobile fallback REALISAS_PILOT_VIA: " . (($fb->mobile_fallback ?? 0) + 0) . "\n";

return 0;
