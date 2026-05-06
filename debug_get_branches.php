<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$branches = DB::connection('dashboard_phinnisi')->table('lhgk')
    ->select('NM_BRANCH')
    ->where('PERIODE', '03-2026')
    ->distinct()
    ->orderBy('NM_BRANCH')
    ->pluck('NM_BRANCH')
    ->toArray();

echo "Total branches: " . count($branches) . "\n";
echo json_encode($branches, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
