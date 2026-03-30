<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking DB connection...\n";

try {
    $pdo = DB::connection()->getPdo();
    if ($pdo) {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        echo "Connected using driver: {$driver}\n";

        if ($driver === 'mysql') {
            $rows = DB::select('SHOW TABLES');
            echo "Tables:\n";
            foreach ($rows as $r) {
                $arr = (array) $r;
                echo array_values($arr)[0] . PHP_EOL;
            }
        } elseif ($driver === 'pgsql') {
            $rows = DB::select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname='public'");
            echo "Tables:\n";
            foreach ($rows as $r) {
                echo ($r->tablename ?? '') . PHP_EOL;
            }
        } elseif ($driver === 'sqlite') {
            $rows = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
            echo "Tables:\n";
            foreach ($rows as $r) {
                echo ($r->name ?? '') . PHP_EOL;
            }
        } else {
            echo "Driver not directly supported for table listing: {$driver}\n";
        }
    } else {
        echo "DB connection returned empty PDO.\n";
    }
} catch (Exception $e) {
    echo "DB connection failed: " . $e->getMessage() . PHP_EOL;
    echo "Make sure your .env is configured and migrations run.\n";
}


