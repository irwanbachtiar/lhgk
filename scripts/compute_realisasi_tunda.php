<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$periode = $argv[1] ?? '12-2025';
$cabang = $argv[2] ?? 'BALIKPAPAN';

echo "Compute realisasi tunda for branch={$cabang} periode={$periode}\n";

$schema = DB::connection('dashboard_phinnisi')->getSchemaBuilder();
$hasTugCols = $schema->hasColumn('lhgk', 'realisas_tug1_via') || $schema->hasColumn('lhgk', 'realisas_tug2_via') || $schema->hasColumn('lhgk', 'realisas_tug3_via') || $schema->hasColumn('lhgk', 'realisas_tug4_via');

if ($hasTugCols) {
            $sql = "SELECT
        (
            SUM(CASE WHEN UPPER(COALESCE(realisas_tug1_via,'')) = 'WEB' THEN 1 ELSE 0 END) +
            SUM(CASE WHEN UPPER(COALESCE(realisas_tug2_via,'')) = 'WEB' THEN 1 ELSE 0 END) +
            SUM(CASE WHEN UPPER(COALESCE(realisas_tug3_via,'')) = 'WEB' THEN 1 ELSE 0 END) +
            SUM(CASE WHEN UPPER(COALESCE(realisas_tug4_via,'')) = 'WEB' THEN 1 ELSE 0 END)
        ) as web,
        (
            SUM(CASE WHEN UPPER(COALESCE(realisas_tug1_via,'')) = 'MOBILE' THEN 1 ELSE 0 END) +
            SUM(CASE WHEN UPPER(COALESCE(realisas_tug2_via,'')) = 'MOBILE' THEN 1 ELSE 0 END) +
            SUM(CASE WHEN UPPER(COALESCE(realisas_tug3_via,'')) = 'MOBILE' THEN 1 ELSE 0 END) +
            SUM(CASE WHEN UPPER(COALESCE(realisas_tug4_via,'')) = 'MOBILE' THEN 1 ELSE 0 END)
        ) as mobile,
        (
            SUM(CASE WHEN UPPER(COALESCE(realisas_tug1_via,'')) = 'PARTIAL' THEN 1 ELSE 0 END) +
            SUM(CASE WHEN UPPER(COALESCE(realisas_tug2_via,'')) = 'PARTIAL' THEN 1 ELSE 0 END) +
            SUM(CASE WHEN UPPER(COALESCE(realisas_tug3_via,'')) = 'PARTIAL' THEN 1 ELSE 0 END) +
            SUM(CASE WHEN UPPER(COALESCE(realisas_tug4_via,'')) = 'PARTIAL' THEN 1 ELSE 0 END)
        ) as partial
    FROM lhgk
    WHERE PERIODE = ? AND UPPER(NM_BRANCH) LIKE ?";

    $row = DB::connection('dashboard_phinnisi')->selectOne($sql, [$periode, '%' . strtoupper($cabang) . '%']);
} else {
    $sql = "SELECT
        SUM(CASE WHEN UPPER(COALESCE(REALISAS_PILOT_VIA,'')) = 'WEB' THEN 1 ELSE 0 END) as web,
        SUM(CASE WHEN UPPER(COALESCE(REALISAS_PILOT_VIA,'')) = 'MOBILE' THEN 1 ELSE 0 END) as mobile,
        SUM(CASE WHEN UPPER(COALESCE(REALISAS_PILOT_VIA,'')) = 'PARTIAL' THEN 1 ELSE 0 END) as partial
    FROM lhgk
    WHERE PERIODE = ? AND UPPER(NM_BRANCH) LIKE ?";

    $row = DB::connection('dashboard_phinnisi')->selectOne($sql, [$periode, '%' . strtoupper($cabang) . '%']);
}

echo "Result:\n";
echo "  web: " . (($row->web ?? 0) + 0) . "\n";
echo "  mobile: " . (($row->mobile ?? 0) + 0) . "\n";
echo "  partial: " . (($row->partial ?? 0) + 0) . "\n";

return 0;
