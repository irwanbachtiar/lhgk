<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\RegionalController;
use App\Models\Lhgk;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/analisis-kelelahan', [DashboardController::class, 'analisisKelelahan'])->name('analisis.kelelahan');
Route::post('/upload-csv', [DashboardController::class, 'uploadCsv'])->name('upload.csv');
Route::get('/export-departure-delay', [DashboardController::class, 'exportDepartureDelay'])->name('export.departure.delay');

// Monitoring Nota routes
Route::get('/monitoring-nota', [NotaController::class, 'index'])->name('monitoring.nota');
Route::post('/upload-nota-csv', [NotaController::class, 'uploadCsv'])->name('upload.nota.csv');
Route::post('/upload-tunda-csv', [NotaController::class, 'uploadTundaCsv'])->name('upload.tunda.csv');
Route::get('/get-nota-batal-data', [NotaController::class, 'getNotaBatalData'])->name('get.nota.batal.data');
Route::get('/export-nota-batal', [NotaController::class, 'exportNotaBatal'])->name('export.nota.batal');

// Regional Revenue routes
Route::get('/regional-revenue', [RegionalController::class, 'index'])->name('regional.revenue');
Route::get('/regional-detail', [RegionalController::class, 'detail'])->name('regional.detail');
Route::get('/regional-detail/export-excel', [RegionalController::class, 'exportExcel'])->name('regional.detail.export');

// Sync Phinnisi routes (manual trigger)
Route::get('/sync-phinnisi-pandu', function() {
    try {
        Artisan::call('phinnisi:sync-pandu');
        $output = Artisan::output();
        return response()->json([
            'success' => true,
            'message' => 'Pandu sync completed',
            'output' => $output
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/sync-phinnisi-tunda', function() {
    try {
        Artisan::call('phinnisi:sync-tunda');
        $output = Artisan::output();
        return response()->json([
            'success' => true,
            'message' => 'Tunda sync completed',
            'output' => $output
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

// Debug route untuk cek periode regional
Route::get('/debug-regional-periods', function() {
    try {
        // Method 1: Current logic
        $periods1 = DB::table('pandu_prod')
            ->selectRaw('DATE_FORMAT(STR_TO_DATE(BILLING_DATE, \'%d-%m-%Y\'), \'%m-%Y\') as periode')
            ->whereNotNull('BILLING_DATE')
            ->where('BILLING_DATE', '!=', '')
            ->groupBy('periode')
            ->orderByRaw('STR_TO_DATE(CONCAT(\'01-\', periode), \'%d-%m-%Y\') DESC')
            ->pluck('periode');
        
        // Get sample data with BILLING_DATE
        $samples = DB::table('pandu_prod')
            ->select('BILLING_DATE', 'NAME_BRANCH', 'BILLING')
            ->whereNotNull('BILLING_DATE')
            ->where('BILLING_DATE', '!=', '')
            ->limit(20)
            ->get();
        
        // Check for invalid dates
        $invalidDates = DB::table('pandu_prod')
            ->select('BILLING_DATE', DB::raw('COUNT(*) as count'))
            ->whereNotNull('BILLING_DATE')
            ->where('BILLING_DATE', '!=', '')
            ->whereRaw('STR_TO_DATE(BILLING_DATE, \'%d-%m-%Y\') IS NULL')
            ->groupBy('BILLING_DATE')
            ->limit(10)
            ->get();
        
        return response()->json([
            'success' => true,
            'periods_count' => $periods1->count(),
            'periods' => $periods1,
            'sample_data' => $samples,
            'invalid_dates' => $invalidDates,
            'date_format_explanation' => 'Expected format: DD-MM-YYYY (e.g., 23-01-2026)'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Debug route untuk cek data periode
Route::get('/debug-periods', function() {
    $data = Lhgk::select('PERIODE', 'NM_BRANCH', 'NM_PERS_PANDU', 'PILOT_DEPLOY')
        ->whereNotNull('PERIODE')
        ->get();
    
    return response()->json([
        'total_records' => $data->count(),
        'samples' => $data->take(20),
        'unique_periods' => Lhgk::select('PERIODE')->whereNotNull('PERIODE')->groupBy('PERIODE')->pluck('PERIODE')->sort()->values(),
        'unique_branches' => Lhgk::select('NM_BRANCH')->whereNotNull('NM_BRANCH')->groupBy('NM_BRANCH')->pluck('NM_BRANCH')->sort()->values()
    ]);
});

// Route untuk clear semua data (gunakan dengan hati-hati!)
Route::get('/clear-data', function() {
    Lhgk::truncate();
    return redirect('/')->with('success', 'Semua data berhasil dihapus');
})->name('clear.data');

// Route untuk clear data corrupt (NM_BRANCH yang panjangnya lebih dari 100 karakter)
Route::get('/clear-corrupt-data', function() {
    $deleted = DB::table('lhgk')
        ->whereRaw('LENGTH(NM_BRANCH) > 100')
        ->delete();
    return redirect('/')->with('success', "Berhasil menghapus {$deleted} data corrupt");
})->name('clear.corrupt');

// Route untuk check struktur tabel pandu_prod
Route::get('/check-pandu-prod', function() {
    try {
        $columns = DB::select("SHOW COLUMNS FROM pandu_prod");
        
        $columnList = [];
        foreach ($columns as $column) {
            $columnList[] = [
                'Field' => $column->Field,
                'Type' => $column->Type,
                'Null' => $column->Null,
                'Key' => $column->Key
            ];
        }
        
        // Sample data
        $sample = DB::table('pandu_prod')->limit(3)->get();
        
        return response()->json([
            'success' => true,
            'total_columns' => count($columns),
            'columns' => $columnList,
            'sample_data' => $sample,
            'total_rows' => DB::table('pandu_prod')->count()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

// Route untuk cek logic total transaksi
Route::get('/check-total-transaksi', function() {
    try {
        $selectedBranch = request()->get('branch', 'REGIONAL 1 TEMBILAHAN');
        $selectedPeriode = request()->get('periode', 'all');
        
        // Method 1: Current logic (distinct count)
        $method1 = DB::table('pandu_prod')
            ->select('BILLING')
            ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                return $q->whereRaw('DATE_FORMAT(STR_TO_DATE(BILLING_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
            })
            ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                return $q->where('NAME_BRANCH', $selectedBranch);
            })
            ->distinct()
            ->count('BILLING');
        
        // Method 2: Count distinct in query
        $method2 = DB::table('pandu_prod')
            ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                return $q->whereRaw('DATE_FORMAT(STR_TO_DATE(BILLING_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
            })
            ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                return $q->where('NAME_BRANCH', $selectedBranch);
            })
            ->selectRaw('COUNT(DISTINCT BILLING) as total')
            ->first();
        
        // Get total rows (with duplicates)
        $totalRows = DB::table('pandu_prod')
            ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
                return $q->whereRaw('DATE_FORMAT(STR_TO_DATE(BILLING_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
            })
            ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                return $q->where('NAME_BRANCH', $selectedBranch);
            })
            ->count();
        
        // Get sample duplicate BILLINGs
        $duplicates = DB::select("
            SELECT BILLING, COUNT(*) as count, 
                   GROUP_CONCAT(DISTINCT NAME_BRANCH) as branches,
                   GROUP_CONCAT(DISTINCT BILLING_DATE) as dates
            FROM pandu_prod 
            WHERE 1=1
            " . ($selectedBranch != 'all' ? "AND NAME_BRANCH = '{$selectedBranch}'" : "") . "
            " . ($selectedPeriode != 'all' ? "AND DATE_FORMAT(STR_TO_DATE(BILLING_DATE, '%d-%m-%Y'), '%m-%Y') = '{$selectedPeriode}'" : "") . "
            GROUP BY BILLING
            HAVING count > 1
            LIMIT 10
        ");
        
        return response()->json([
            'success' => true,
            'filter' => [
                'branch' => $selectedBranch,
                'periode' => $selectedPeriode
            ],
            'results' => [
                'method_1_distinct_count' => $method1,
                'method_2_count_distinct' => $method2->total,
                'total_rows_with_duplicates' => $totalRows,
                'difference' => $totalRows - $method1
            ],
            'sample_duplicates' => $duplicates,
            'conclusion' => [
                'unique_billing' => $method1,
                'duplicate_entries' => $totalRows - $method1,
                'is_correct' => $method1 == $method2->total ? 'Yes' : 'No'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Route untuk cek struktur tunda_prod
Route::get('/check-tunda-structure', function() {
    try {
        // Check tunda_prod columns
        $columns = DB::select("SHOW COLUMNS FROM tunda_prod");
        
        // Sample tunda data
        $samples = DB::table('tunda_prod')
            ->limit(10)
            ->get();
        
        $html = '<h2>Columns in tunda_prod table:</h2><ul>';
        foreach ($columns as $column) {
            $html .= '<li><strong>' . $column->Field . '</strong> (' . $column->Type . ')</li>';
        }
        $html .= '</ul>';
        
        $html .= '<h2>Sample data (first 10 rows):</h2><pre>';
        $html .= print_r($samples, true);
        $html .= '</pre>';
        
        return $html;
    } catch (\Exception $e) {
        return '<h2>Error:</h2><pre>' . $e->getMessage() . '</pre>';
    }
});

// Route untuk debug pendapatan pandu dan cek struktur tunda
Route::get('/debug-pendapatan', function() {
    try {
        $selectedBranch = request()->get('branch', 'REGIONAL 1 TEMBILAHAN');
        $selectedPeriode = request()->get('periode', 'all');
        
        // Get all branches
        $branches = DB::table('pandu_prod')
            ->select('NAME_BRANCH')
            ->whereNotNull('NAME_BRANCH')
            ->where('NAME_BRANCH', '!=', '')
            ->groupBy('NAME_BRANCH')
            ->orderBy('NAME_BRANCH')
            ->pluck('NAME_BRANCH');
        
        // Query with filters
        $query = DB::table('pandu_prod')
            ->where('NAME_BRANCH', $selectedBranch);
        
        if ($selectedPeriode != 'all') {
            $query->whereRaw('DATE_FORMAT(STR_TO_DATE(BILLING_DATE, \'%d-%m-%Y\'), \'%m-%Y\') = ?', [$selectedPeriode]);
        }
        
        $count = $query->count();
        $totalRevenue = $query->sum('REVENUE');
        
        // Sample data
        $samples = DB::table('pandu_prod')
            ->where('NAME_BRANCH', $selectedBranch)
            ->limit(5)
            ->get(['BILLING', 'BILLING_DATE', 'VESSEL_NAME', 'REVENUE', 'NAME_BRANCH']);
        
        // Check tunda_prod columns
        $tundaColumns = DB::select("SHOW COLUMNS FROM tunda_prod");
        
        // Sample tunda data
        $tundaSamples = DB::table('tunda_prod')
            ->limit(5)
            ->get();
        
        return response()->json([
            'success' => true,
            'filter' => [
                'branch' => $selectedBranch,
                'periode' => $selectedPeriode
            ],
            'available_branches' => $branches,
            'results' => [
                'count' => $count,
                'total_revenue' => $totalRevenue,
                'formatted' => 'Rp ' . number_format($totalRevenue, 0, ',', '.')
            ],
            'sample_data' => $samples,
            'tunda_columns' => $tundaColumns,
            'tunda_sample_data' => $tundaSamples
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Route untuk fix table structure
Route::get('/fix-table', function() {
    try {
        // Check and add missing columns
        if (!Schema::hasColumn('lhgk', 'mulai_pelaksanaan')) {
            DB::statement('ALTER TABLE lhgk ADD COLUMN mulai_pelaksanaan VARCHAR(255) NULL AFTER PILOT_DEPLOY');
        }
        if (!Schema::hasColumn('lhgk', 'selesai_pelaksanaan')) {
            DB::statement('ALTER TABLE lhgk ADD COLUMN selesai_pelaksanaan VARCHAR(255) NULL AFTER mulai_pelaksanaan');
        }
        if (!Schema::hasColumn('lhgk', 'TGL_PMT')) {
            DB::statement('ALTER TABLE lhgk ADD COLUMN TGL_PMT VARCHAR(255) NULL AFTER PERIODE');
        }
        if (!Schema::hasColumn('lhgk', 'JAM_PMT')) {
            DB::statement('ALTER TABLE lhgk ADD COLUMN JAM_PMT VARCHAR(255) NULL AFTER TGL_PMT');
        }
        if (!Schema::hasColumn('lhgk', 'PNK')) {
            DB::statement('ALTER TABLE lhgk ADD COLUMN PNK VARCHAR(255) NULL AFTER JAM_PMT');
        }
        
        // Change NM_BRANCH to TEXT type to handle long values
        DB::statement('ALTER TABLE lhgk MODIFY COLUMN NM_BRANCH TEXT NULL');
        
        // Get column list
        $columns = Schema::getColumnListing('lhgk');
        
        return response()->json([
            'success' => true,
            'message' => 'Table structure fixed',
            'columns' => $columns
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

// Route untuk run migration (create pandu table)
Route::get('/run-migration', function() {
    try {
        // Check existing columns first
        $columns = Schema::getColumnListing('pandu_prod');
        
        // Define required columns
        $requiredColumns = [
            'NM_PERS_PANDU' => 'VARCHAR(255) NULL',
            'NM_BRANCH' => 'TEXT NULL',
            'PENDAPATAN_PANDU' => 'DECIMAL(15, 2) NULL',
            'PENDAPATAN_TUNDA' => 'DECIMAL(15, 2) NULL',
            'NM_KAPAL' => 'VARCHAR(255) NULL',
            'JN_KAPAL' => 'VARCHAR(255) NULL',
            'KP_GRT' => 'DECIMAL(15, 2) NULL',
            'PILOT_DEPLOY' => 'VARCHAR(255) NULL',
            'mulai_pelaksanaan' => 'VARCHAR(255) NULL',
            'selesai_pelaksanaan' => 'VARCHAR(255) NULL',
            'REALISAS_PILOT_VIA' => 'VARCHAR(255) NULL',
            'PERIODE' => 'VARCHAR(255) NULL',
            'TGL_PMT' => 'VARCHAR(255) NULL',
            'JAM_PMT' => 'VARCHAR(255) NULL',
            'PNK' => 'VARCHAR(255) NULL',
            'NO_NOTA' => 'VARCHAR(255) NULL',
            'TGL_NOTA' => 'DATE NULL',
            'STATUS_NOTA' => 'VARCHAR(255) NULL',
            'KETERANGAN' => 'TEXT NULL'
        ];
        
        $added = [];
        $afterColumn = 'id';
        
        foreach ($requiredColumns as $columnName => $columnType) {
            if (!in_array($columnName, $columns)) {
                DB::statement("ALTER TABLE pandu_prod ADD COLUMN $columnName $columnType AFTER $afterColumn");
                $added[] = $columnName;
            }
            $afterColumn = $columnName;
        }
        
        return response()->json([
            'success' => true,
            'message' => count($added) > 0 ? 'Added ' . count($added) . ' columns' : 'All columns exist',
            'added_columns' => $added,
            'existing_columns' => $columns
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});
