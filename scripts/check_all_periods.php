<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Check ALL Database Periods ===\n\n";

$conn = DB::connection('dashboard_phinnisi');
$table = 'trafik_rkap_realisasi';

echo "All periods in $table:\n";
try {
    $periods = $conn->table($table)
        ->select('periode', DB::raw('COUNT(*) as count'), DB::raw('SUM(`Call`) as total_call'))
        ->groupBy('periode')
        ->orderBy('periode', 'desc')
        ->get();
    
    foreach ($periods as $p) {
        echo "  - {$p->periode}: {$p->count} rows, Call=" . number_format($p->total_call) . "\n";
    }
    
    echo "\nTotal across ALL periods:\n";
    $total = $conn->table($table)->selectRaw('COUNT(*) as count, SUM(`Call`) as total_call')->first();
    echo "  - Total: {$total->count} rows, Call=" . number_format($total->total_call) . "\n";
    
    if (count($periods) == 1) {
        echo "\n⚠️  WARNING: Only ONE period exists in database!\n";
        echo "    This means periode='all' will return the SAME data as periode='{$periods[0]->periode}'\n";
        echo "    To properly test filtering, you need multiple periods in the database.\n";
    } else {
        echo "\n✅ Multiple periods exist - filter should work correctly.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
