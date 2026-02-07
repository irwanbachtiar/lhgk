<?php
// Usage: php scripts/debug_pendapatan_sap.php [cabang]
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cabang = $argv[1] ?? 'all';
$out = ['cabang' => $cabang];
try {
    $db = $app->make('db');
    $conn = $db->connection('dashboard_phinnisi');

    // Check tables
    $schema = $conn->getSchemaBuilder();
    $hasPendapatan = $schema->hasTable('pendapatan_sap');
    $out['has_pendapatan_sap'] = $hasPendapatan;

    if ($hasPendapatan) {
        // Show columns
        $cols = $conn->select("SHOW COLUMNS FROM pendapatan_sap");
        $fields = array_map(function($c){ return $c->Field; }, $cols);
        $out['columns'] = $fields;

        // detect tanggal nota col
        $tanggalNotaCol = null;
        foreach ($fields as $f) {
            $normalized = strtolower(str_replace([' ', '_'], '', $f));
            if (in_array($normalized, ['tanggalnota', 'tglnota', 'tanggalket', 'tanggaldok', 'tanggal', 'tanggaldokumen', 'invoicedate', 'invoicedate'])) {
                $tanggalNotaCol = $f; break;
            }
        }
        $out['tanggal_nota_col'] = $tanggalNotaCol;

        // detect profit_center col
        $sapProfitCenterCol = null;
        foreach ($fields as $f) {
            if (strtolower(str_replace([' ', '_'], '', $f)) === 'profitcenter' || strtolower($f) === 'profit_center') {
                $sapProfitCenterCol = $f; break;
            }
        }
        $out['sap_profit_center_col'] = $sapProfitCenterCol;

        // sample profit_center table
        try {
            $pcSample = $conn->table('profit_center')->select('profit_center', 'name_branch')->limit(10)->get();
            $out['profit_center_sample'] = $pcSample;
        } catch (Exception $e) {
            $out['profit_center_sample_error'] = $e->getMessage();
        }

        // periods overall
        if ($tanggalNotaCol) {
            try {
                $periods = $conn->table('pendapatan_sap')
                    ->selectRaw("DATE_FORMAT(STR_TO_DATE({$tanggalNotaCol}, '%d-%m-%Y'), '%m-%Y') as periode")
                    ->whereNotNull($tanggalNotaCol)
                    ->where($tanggalNotaCol, '!=', '')
                    ->groupBy('periode')
                    ->orderByRaw("STR_TO_DATE(CONCAT('01-', periode), '%d-%m-%Y') DESC")
                    ->limit(50)
                    ->pluck('periode')
                    ->toArray();
                $out['periods_overall_sample'] = $periods;
                $out['periods_overall_count'] = count($periods);
            } catch (Exception $e) {
                $out['periods_overall_error'] = $e->getMessage();
            }
        }

        // periods for cabang if requested
        if ($cabang !== 'all' && $tanggalNotaCol) {
            try {
                $q = $conn->table('pendapatan_sap');
                if ($sapProfitCenterCol) {
                    $q->join('profit_center as pc', "pendapatan_sap.{$sapProfitCenterCol}", '=', 'pc.profit_center')
                      ->where('pc.name_branch', $cabang);
                } else {
                    $q->where(function($qq) use ($cabang) {
                        $qq->where('NAME_BRANCH', $cabang)
                           ->orWhere('name_branch', $cabang)
                           ->orWhere('CABANG', $cabang)
                           ->orWhere('PROFIT_CENTER', $cabang);
                    });
                }
                $periodsForCabang = $q->selectRaw("DATE_FORMAT(STR_TO_DATE({$tanggalNotaCol}, '%d-%m-%Y'), '%m-%Y') as periode")
                    ->whereNotNull($tanggalNotaCol)
                    ->where($tanggalNotaCol, '!=', '')
                    ->groupBy('periode')
                    ->orderByRaw("STR_TO_DATE(CONCAT('01-', periode), '%d-%m-%Y') DESC")
                    ->limit(50)
                    ->pluck('periode')
                    ->toArray();
                $out['periods_for_cabang_sample'] = $periodsForCabang;
                $out['periods_for_cabang_count'] = count($periodsForCabang);
            } catch (Exception $e) {
                $out['periods_for_cabang_error'] = $e->getMessage();
            }
        }

    } else {
        // show available tables
        try {
            $tables = $conn->select('SHOW TABLES');
            $out['tables'] = $tables;
        } catch (Exception $e) {
            $out['tables_error'] = $e->getMessage();
        }
    }

    echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
