<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$periode = $argv[1] ?? '12-2025';
echo "Distinct NM_BRANCH values for periode={$periode} containing 'BALIKPAPAN'\n";

$rows = DB::connection('dashboard_phinnisi')->select("SELECT DISTINCT NM_BRANCH FROM lhgk WHERE PERIODE = ? AND UPPER(NM_BRANCH) LIKE ? ORDER BY NM_BRANCH", [$periode, '%BALIKPAPAN%']);
foreach ($rows as $r) {
    echo "- " . ($r->NM_BRANCH ?? '(null)') . "\n";
}

return 0;
