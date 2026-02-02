<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$periode = $argv[1] ?? '12-2024';
$connection = 'dashboard_phinnisi';

echo "Periode: {$periode}\n\n";

try {
    $dbName = DB::connection($connection)->getDatabaseName();
    echo "Using connection: {$connection} (database: {$dbName})\n\n";
} catch (\Exception $e) {
    echo "Error getting DB connection: " . $e->getMessage() . "\n";
    exit(1);
}

$tables = ['pandu_prod', 'tunda_prod'];

foreach ($tables as $table) {
    echo "--- Table: {$table} ---\n";
    $has = Schema::connection($connection)->hasTable($table) ? 'YES' : 'NO';
    echo "Exists: {$has}\n";
    if ($has === 'NO') {
        echo "\n";
        continue;
    }

    $columns = DB::connection($connection)->getSchemaBuilder()->getColumnListing($table);
    echo "Columns: " . implode(', ', $columns) . "\n";

    $totalRows = DB::connection($connection)->table($table)->count();
    echo "Total rows: {$totalRows}\n";

    $rowsWithInvoice = DB::connection($connection)->table($table)
        ->whereNotNull('INVOICE_DATE')
        ->where('INVOICE_DATE', '<>', '')
        ->count();
    echo "Rows with INVOICE_DATE non-empty: {$rowsWithInvoice}\n";

    $countWithDash = DB::connection($connection)->table($table)
        ->whereRaw("INVOICE_DATE LIKE '%-%'")
        ->count();
    $countWithSlash = DB::connection($connection)->table($table)
        ->whereRaw("INVOICE_DATE LIKE '%/%'")
        ->count();
    $countWithSpace = DB::connection($connection)->table($table)
        ->whereRaw("INVOICE_DATE LIKE '% %'")
        ->count();

    echo "INVOICE_DATE contains '-' : {$countWithDash}\n";
    echo "INVOICE_DATE contains '/' : {$countWithSlash}\n";
    echo "INVOICE_DATE contains ' ' : {$countWithSpace}\n";

    echo "\nSample distinct INVOICE_DATE (up to 20):\n";
    $samples = DB::connection($connection)->table($table)
        ->select('INVOICE_DATE')
        ->whereNotNull('INVOICE_DATE')
        ->where('INVOICE_DATE', '<>', '')
        ->groupBy('INVOICE_DATE')
        ->orderBy('INVOICE_DATE')
        ->limit(20)
        ->get();
    foreach ($samples as $s) {
        echo " - " . $s->INVOICE_DATE . "\n";
    }

    echo "\nFirst 10 rows (INVOICE_DATE, REVENUE, NAME_BRANCH):\n";
    $rows = DB::connection($connection)->table($table)
        ->select('INVOICE_DATE', 'REVENUE', 'NAME_BRANCH')
        ->limit(10)
        ->get();
    foreach ($rows as $r) {
        $inv = $r->INVOICE_DATE ?? '(NULL)';
        $rev = isset($r->REVENUE) ? number_format($r->REVENUE, 0, ',', '.') : '(NULL)';
        $nb = $r->NAME_BRANCH ?? '(NULL)';
        echo " - {$inv} | {$rev} | {$nb}\n";
    }

    echo "\n";
}

echo "Diagnostics complete.\n";
return 0;
