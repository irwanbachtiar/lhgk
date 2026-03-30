<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

echo "Tables in dashboard_phinnisi:\n\n";
try {
    $rows = DB::connection('dashboard_phinnisi')->select('SHOW TABLES');
    foreach ($rows as $r) {
        $arr = (array)$r;
        echo " - " . array_values($arr)[0] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
