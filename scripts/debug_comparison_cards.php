<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Analisis Comparison Card Logic ===\n\n";

$conn = DB::connection('dashboard_phinnisi');
$table = 'trafik_rkap_realisasi';

// 1. Check table structure
echo "1. Table Structure for $table:\n";
try {
    $columns = $conn->select("SHOW COLUMNS FROM $table");
    foreach ($columns as $col) {
        echo "   - {$col->Field} ({$col->Type})\n";
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// 2. Check if columns 'satuan', 'jenis', 'nilai' exist
echo "\n2. Checking for Comparison Columns (satuan, jenis, nilai):\n";
try {
    $test = $conn->table($table)
        ->select('satuan', 'jenis', 'nilai')
        ->limit(1)
        ->first();
    
    if ($test) {
        echo "   ✅ Columns exist!\n";
        echo "   Sample: satuan={$test->satuan}, jenis={$test->jenis}, nilai={$test->nilai}\n";
    } else {
        echo "   ⚠️  Query succeeded but no data returned\n";
    }
} catch (Exception $e) {
    echo "   ❌ Columns DO NOT exist or error:\n";
    echo "   " . $e->getMessage() . "\n";
}

// 3. Test the comparison query that controller uses
echo "\n3. Testing Controller's Comparison Query:\n";
try {
    $compRows = $conn->table($table)
        ->selectRaw('satuan, jenis, SUM(nilai) as total')
        ->groupBy('satuan', 'jenis')
        ->get();
    
    echo "   Results:\n";
    foreach ($compRows as $cr) {
        $satuan = strtoupper(trim($cr->satuan ?? ''));
        $jenis = strtolower(trim($cr->jenis ?? ''));
        $total = (float) ($cr->total ?? 0);
        echo "   - satuan=$satuan, jenis=$jenis, total=" . number_format($total) . "\n";
    }
    
    if (count($compRows) === 0) {
        echo "   ⚠️  No rows returned from query\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 4. Check actual data available
echo "\n4. Sample Data from $table:\n";
try {
    $samples = $conn->table($table)
        ->select('*')
        ->limit(3)
        ->get();
    
    foreach ($samples as $idx => $row) {
        echo "   Row " . ($idx + 1) . ":\n";
        foreach ((array)$row as $key => $val) {
            $display = is_numeric($val) ? number_format($val) : $val;
            echo "      $key: $display\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// 5. Alternative: Check if we should use Call/GT columns directly
echo "5. Alternative Logic: Using Call/GT Columns Directly:\n";
try {
    // Try to get realisasi and anggaran from different approach
    echo "   Checking if 'jenis' column differentiates realisasi vs anggaran...\n";
    $distinctJenis = $conn->table($table)
        ->select('jenis')
        ->distinct()
        ->pluck('jenis')
        ->toArray();
    
    echo "   Distinct 'jenis' values: " . implode(', ', $distinctJenis) . "\n";
    
    if (in_array('realisasi', array_map('strtolower', $distinctJenis)) || 
        in_array('anggaran', array_map('strtolower', $distinctJenis))) {
        echo "   ✅ 'jenis' column can differentiate realisasi/anggaran\n";
    } else {
        echo "   ⚠️  'jenis' column does NOT have 'realisasi'/'anggaran' values\n";
        echo "   Possible values: " . implode(', ', $distinctJenis) . "\n";
    }
    
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n=== Recommendations ===\n";
echo "Based on the table structure, the comparison cards should use:\n";
echo "1. If 'satuan'/'jenis'/'nilai' columns exist → current logic OK\n";
echo "2. If only 'Call'/'GT' columns exist → use direct aggregation (no anggaran data)\n";
echo "3. If no valid comparison data → display warning or use placeholder values\n";
