<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Nota Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet" integrity="sha384-QuGBSgV5Im3DzL2z+8Ko9/hqNy/N0O7zwvXAtfd1MvPKWa/UbeLV65cfm4BV5Wgq" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" integrity="sha384-e6nUZLBkQ86NJ6TVVKAeSaK8jWa3NhkYWZFomE39AvDbQWeie9PlQqM3pmYW5d1g" crossorigin="anonymous"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .stat-card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .filter-section {
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .upload-section {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .badge-status {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
        }
        
        /* Global Loading Animation */
        .global-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(3px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease-in;
        }
        .global-loading-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideIn 0.4s ease-out;
            max-width: 300px;
        }
        .global-loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        .global-loading-text {
            color: #333;
            font-weight: 500;
            font-size: 16px;
            margin: 0;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Global Loading Overlay -->
    <div id="globalLoading" class="global-loading-overlay">
        <div class="global-loading-content">
            <div class="global-loading-spinner"></div>
            <p class="global-loading-text" id="loadingText">Memproses data...</p>
        </div>
    </div>

    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-file-earmark-text"></i> Monitoring Nota Data</span>
            <div>
                <a href="<?php echo e(route('dashboard.operasional')); ?>" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-kanban-fill"></i> Dashboard Operasional
                </a>
                <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-graph-up-arrow"></i> Dashboard LHGK
                </a>
                <a href="<?php echo e(route('regional.revenue')); ?>" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-geo-alt"></i> Pendapatan Wilayah
                </a>
                <a href="<?php echo e(route('analisis.kelelahan')); ?>" class="btn btn-light btn-sm">
                    <i class="bi bi-activity"></i> Analisis Kelelahan
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="<?php echo e(route('monitoring.nota')); ?>" class="row align-items-center">
                <div class="col-md-2">
                    <label class="form-label"><i class="bi bi-funnel"></i> Filter:</label>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><i class="bi bi-building"></i> Cabang:</label>
                    <select name="cabang" class="form-select filter-input">
                        <option value="all" <?php echo e($selectedBranch == 'all' ? 'selected' : ''); ?>>Semua Cabang</option>
                        <?php $__currentLoopData = $regionalGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wilayah => $branches): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <optgroup label="<?php echo e($wilayah); ?>">
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($branch); ?>" <?php echo e($selectedBranch == $branch ? 'selected' : ''); ?> title="<?php echo e($branch); ?>">
                                        <?php echo e(Str::limit($branch, 50)); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </optgroup>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><i class="bi bi-calendar-range"></i> Periode:</label>
                    <div class="d-flex">
                        <select name="periode" class="form-select filter-input">
                            <option value="all" <?php echo e($selectedPeriode == 'all' ? 'selected' : ''); ?>>Semua Periode</option>
                            <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($period); ?>" <?php echo e($selectedPeriode == $period ? 'selected' : ''); ?>>
                                    <?php echo e($period); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <button type="button" class="btn btn-primary ms-2" onclick="document.getElementById('globalLoading').style.display='flex'; this.closest('form').submit();">Apply</button>
                    </div>
                </div>
                <div class="col-md-3">
                    <?php if($selectedPeriode != 'all' || $selectedBranch != 'all'): ?>
                        <a href="<?php echo e(route('monitoring.nota')); ?>" class="btn btn-outline-secondary mt-4">
                            <i class="bi bi-x-circle"></i> Reset Filter
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php if($selectedPeriode == 'all' && $selectedBranch == 'all'): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle fs-1"></i>
                        <h5 class="mt-3">Silakan pilih filter Cabang atau Periode untuk melihat data</h5>
                        <p class="mb-0">Pilih salah satu atau kedua filter di atas untuk menampilkan statistik dan grafik pendapatan.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-primary"><?php echo e(number_format($totalNota)); ?></h3>
                            <p class="mb-0"><i class="bi bi-file-earmark-text"></i> Total Nota</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-success">Rp <?php echo e(number_format($totalPendapatanPandu, 0, ',', '.')); ?></h3>
                            <p class="mb-0"><i class="bi bi-cash-coin"></i> Pendapatan Pandu</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-info">Rp <?php echo e(number_format($totalPendapatanTunda, 0, ',', '.')); ?></h3>
                            <p class="mb-0"><i class="bi bi-cash-stack"></i> Pendapatan Tunda</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-warning">Rp <?php echo e(number_format($totalPendapatanPandu + $totalPendapatanTunda, 0, ',', '.')); ?></h3>
                            <p class="mb-0"><i class="bi bi-currency-dollar"></i> Total Pendapatan</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if($selectedPeriode != 'all' || $selectedBranch != 'all'): ?>
        <!-- Nota Batal Section -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card border-danger">
                    <div class="card-body text-center">
                        <h3 class="text-danger"><?php echo e(number_format($totalNotaBatal)); ?></h3>
                        <p class="mb-0"><i class="bi bi-x-circle"></i> Jumlah Nota Batal</p>
                        <small class="text-muted">Billing dengan prefix "HIS"</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card border-danger">
                    <div class="card-body text-center">
                        <h3 class="text-danger">Rp <?php echo e(number_format($totalPendapatanPanduBatal, 0, ',', '.')); ?></h3>
                        <p class="mb-0"><i class="bi bi-cash-coin"></i> Nilai Nota Batal Pandu</p>
                        <?php if(($totalPendapatanPandu + $totalPendapatanPanduBatal) > 0): ?>
                            <small class="text-muted"><?php echo e(number_format(($totalPendapatanPanduBatal / ($totalPendapatanPandu + $totalPendapatanPanduBatal)) * 100, 2)); ?>% dari total</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card border-danger">
                    <div class="card-body text-center">
                        <h3 class="text-danger">Rp <?php echo e(number_format($totalPendapatanTundaBatal, 0, ',', '.')); ?></h3>
                        <p class="mb-0"><i class="bi bi-cash-stack"></i> Nilai Nota Batal Tunda</p>
                        <?php if(($totalPendapatanTunda + $totalPendapatanTundaBatal) > 0): ?>
                            <small class="text-muted"><?php echo e(number_format(($totalPendapatanTundaBatal / ($totalPendapatanTunda + $totalPendapatanTundaBatal)) * 100, 2)); ?>% dari total</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if($selectedPeriode != 'all' || $selectedBranch != 'all'): ?>
        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card stat-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Grafik Pendapatan Per Pandu</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="panduChart" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card stat-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Grafik Pendapatan Per Tunda</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="tundaChart" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Per Pandu Statistics -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card stat-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person-badge"></i> Pendapatan Per Pandu</h5>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        <?php if($revenuePerPandu->count() > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php $__currentLoopData = $revenuePerPandu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $pandu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-primary rounded-circle me-2"><?php echo e($index + 1); ?></span>
                                            <strong><?php echo e($pandu->PILOT); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo e($pandu->total_transaksi); ?> transaksi</small>
                                        </div>
                                        <div class="text-end">
                                            <strong class="text-success">Rp <?php echo e(number_format($pandu->total_revenue, 0, ',', '.')); ?></strong>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">Tidak ada data pandu</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card stat-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-water"></i> Pendapatan Per Tunda</h5>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        <?php if($revenuePerTunda->count() > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php $__currentLoopData = $revenuePerTunda; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $tunda): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-info rounded-circle me-2"><?php echo e($index + 1); ?></span>
                                            <strong><?php echo e($tunda->tunda_name ?? 'N/A'); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo e($tunda->total_transaksi); ?> transaksi</small>
                                        </div>
                                        <div class="text-end">
                                            <strong class="text-success">Rp <?php echo e(number_format($tunda->total_revenue, 0, ',', '.')); ?></strong>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">Tidak ada data tunda</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if($selectedPeriode != 'all' || $selectedBranch != 'all'): ?>
        <!-- Data Nota Batal Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-x-circle"></i> Data Nota Batal</h5>
                            <div>
                                <button type="button" class="btn btn-light btn-sm me-2" id="toggleNotaBatalBtn" onclick="toggleNotaBatalTable()">
                                    <i class="bi bi-eye"></i> Tampilkan Data
                                </button>
                                <a href="<?php echo e(route('export.nota.batal', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch])); ?>" 
                                   class="btn btn-success btn-sm" id="downloadNotaBatalBtn" style="display: none;">
                                    <i class="bi bi-download"></i> Download Excel
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" id="notaBatalTableContainer" style="display: none;">
                        <div class="text-center py-4" id="notaBatalLoading">
                            <div class="spinner-border text-danger" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat data nota batal...</p>
                        </div>
                        <div class="table-responsive" id="notaBatalTableWrapper" style="display: none;">
                            <table class="table table-hover table-bordered" id="notaBatalTable">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th width="50">No</th>
                                        <th>Billing</th>
                                        <th>Invoice</th>
                                        <th>Invoice Date</th>
                                        <th>No PKK</th>
                                        <th>Vessel Name</th>
                                        <th>Shipping Agent</th>
                                        <th>Flag</th>
                                        <th>Revenue Pandu</th>
                                        <th>Revenue Tunda</th>
                                        <th>Pelimpahan</th>
                                    </tr>
                                </thead>
                                <tbody id="notaBatalTableBody">
                                </tbody>
                            </table>
                        </div>
                        <div id="notaBatalEmpty" style="display: none;">
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">Tidak ada data nota batal</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if($selectedPeriode != 'all' || $selectedBranch != 'all'): ?>
        <!-- Top 10 Shipping Agents -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                        <h5 class="mb-0"><i class="bi bi-building"></i> Top 10 Shipping Agent (Total Pendapatan)</h5>
                    </div>
                    <div class="card-body">
                        <?php if($topShippingAgents->count() > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="80">Rank</th>
                                            <th>Shipping Agent</th>
                                            <th class="text-end">Total Pendapatan (Pandu + Tunda)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $topShippingAgents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td>
                                                    <span class="badge <?php echo e($index < 3 ? 'bg-warning' : 'bg-secondary'); ?> rounded-circle" style="width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem;">
                                                        <?php echo e($index + 1); ?>

                                                    </span>
                                                </td>
                                                <td><strong><?php echo e($agent->SHIPPING_AGENT); ?></strong></td>
                                                <td class="text-end">
                                                    <strong class="text-success">Rp <?php echo e(number_format($agent->total_revenue, 0, ',', '.')); ?></strong>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">Tidak ada data shipping agent</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Upload CSV Section -->
        <div class="upload-section">
            <h5 class="mb-3"><i class="bi bi-upload"></i> Upload Data CSV</h5>
            
            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <strong>Error!</strong>
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if(session('success')): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('import_errors')): ?>
                <div class="alert alert-warning">
                    <strong>Beberapa baris gagal diimport:</strong>
                    <ul class="mb-0" style="max-height: 200px; overflow-y: auto;">
                        <?php $__currentLoopData = session('import_errors'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-3"><i class="bi bi-file-earmark-text"></i> Upload Data Pandu (pandu_prod)</h6>
                    <form method="POST" action="<?php echo e(route('upload.nota.csv')); ?>" enctype="multipart/form-data" class="mb-3" id="uploadPanduForm">
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <div class="col-md-8">
                                <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required id="csvFilePandu">
                                <small class="text-muted">Format: CSV (max 10MB)</small>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100" id="uploadPanduBtn">
                                    <i class="bi bi-upload"></i> Upload Pandu
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-md-6">
                    <h6 class="mb-3"><i class="bi bi-water"></i> Upload Data Tunda (tunda_prod)</h6>
                    <form method="POST" action="<?php echo e(route('upload.tunda.csv')); ?>" enctype="multipart/form-data" class="mb-3" id="uploadTundaForm">
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <div class="col-md-8">
                                <input type="file" name="tunda_csv_file" class="form-control" accept=".csv,.txt" required id="csvFileTunda">
                                <small class="text-muted">Format: CSV (max 10MB)</small>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-info w-100" id="uploadTundaBtn">
                                    <i class="bi bi-upload"></i> Upload Tunda
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Data Table - DISABLED -->
        <!--
        <div class="table-container">
            <h5 class="mb-3"><i class="bi bi-table"></i> Data Nota</h5>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Billing</th>
                            <th>Tgl Billing</th>
                            <th>Invoice</th>
                            <th>Nama Kapal</th>
                            <th>Pilot</th>
                            <th>Cabang</th>
                            <th>GRT</th>
                            <th>Pend. Pandu</th>
                            <th>Pend. Tunda</th>
                            <th>Total</th>
                            <th>Pilot Onboard</th>
                            <th>Pilot Off</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="13" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">Tabel data dinonaktifkan.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script>
        // Handle pandu form submission with loading state
        document.getElementById('uploadPanduForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('csvFilePandu');
            const uploadBtn = document.getElementById('uploadPanduBtn');
            
            if (fileInput.files.length === 0) {
                e.preventDefault();
                alert('Silakan pilih file CSV terlebih dahulu');
                return false;
            }
            
            // Show global loading
            showGlobalLoading('Mengupload file CSV Pandu...');
            
            // Show loading state
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
        });

        // Handle tunda form submission with loading state
        document.getElementById('uploadTundaForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('csvFileTunda');
            const uploadBtn = document.getElementById('uploadTundaBtn');
            
            if (fileInput.files.length === 0) {
                e.preventDefault();
                alert('Silakan pilih file CSV terlebih dahulu');
                return false;
            }
            
            // Show global loading
            showGlobalLoading('Mengupload file CSV Tunda...');
            
            // Show loading state
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
        });

        // Pandu Chart
        const panduData = <?php echo json_encode($revenuePerPandu, 15, 512) ?>;
        const panduLabels = panduData.map(item => item.PILOT || 'N/A');
        const panduRevenue = panduData.map(item => item.total_revenue);

        const panduCtx = document.getElementById('panduChart').getContext('2d');
        const panduChart = new Chart(panduCtx, {
            type: 'line',
            data: {
                labels: panduLabels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: panduRevenue,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });

        // Tunda Chart
        const tundaData = <?php echo json_encode($revenuePerTunda, 15, 512) ?>;
        const tundaLabels = tundaData.map(item => item.tunda_name || 'N/A');
        const tundaRevenue = tundaData.map(item => item.total_revenue);

        const tundaCtx = document.getElementById('tundaChart').getContext('2d');
        const tundaChart = new Chart(tundaCtx, {
            type: 'line',
            data: {
                labels: tundaLabels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: tundaRevenue,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });

        // Toggle Nota Batal Table
        let notaBatalLoaded = false;
        
        function toggleNotaBatalTable() {
            const container = document.getElementById('notaBatalTableContainer');
            const btn = document.getElementById('toggleNotaBatalBtn');
            const downloadBtn = document.getElementById('downloadNotaBatalBtn');
            
            if (container.style.display === 'none') {
                container.style.display = 'block';
                btn.innerHTML = '<i class="bi bi-eye-slash"></i> Sembunyikan Data';
                downloadBtn.style.display = 'inline-block';
                
                if (!notaBatalLoaded) {
                    loadNotaBatalData();
                }
            } else {
                container.style.display = 'none';
                btn.innerHTML = '<i class="bi bi-eye"></i> Tampilkan Data';
                downloadBtn.style.display = 'none';
            }
        }
        
        function loadNotaBatalData() {
            const loading = document.getElementById('notaBatalLoading');
            const tableWrapper = document.getElementById('notaBatalTableWrapper');
            const emptyState = document.getElementById('notaBatalEmpty');
            const tableBody = document.getElementById('notaBatalTableBody');
            
            showGlobalLoading('Memuat data nota batal...');
            loading.style.display = 'block';
            tableWrapper.style.display = 'none';
            emptyState.style.display = 'none';
            
            const periode = '<?php echo e($selectedPeriode); ?>';
            const cabang = '<?php echo e($selectedBranch); ?>';
            
            fetch(`<?php echo e(route('get.nota.batal.data')); ?>?periode=${periode}&cabang=${cabang}`)
                .then(response => response.json())
                .then(data => {
                    hideGlobalLoading();
                    loading.style.display = 'none';
                    
                    if (data.data && data.data.length > 0) {
                        tableWrapper.style.display = 'block';
                        tableBody.innerHTML = '';
                        
                        data.data.forEach((row, index) => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${index + 1}</td>
                                <td>${row.BILLING || '-'}</td>
                                <td>${row.INVOICE || '-'}</td>
                                <td>${row.INVOICE_DATE || '-'}</td>
                                <td>${row.NO_PKK || '-'}</td>
                                <td>${row.VESSEL_NAME || '-'}</td>
                                <td>${row.SHIPPING_AGENT || '-'}</td>
                                <td>${row.FLAG || '-'}</td>
                                <td class="text-end">Rp ${Number(row.REVENUE_PANDU || 0).toLocaleString('id-ID')}</td>
                                <td class="text-end">Rp ${Number(row.REVENUE_TUNDA || 0).toLocaleString('id-ID')}</td>
                                <td>${row.DELEGATION || '-'}</td>
                            `;
                            tableBody.appendChild(tr);
                        });
                        
                        notaBatalLoaded = true;
                    } else {
                        emptyState.style.display = 'block';
                    }
                })
                .catch(error => {
                    hideGlobalLoading();
                    loading.style.display = 'none';
                    emptyState.style.display = 'block';
                    console.error('Error loading nota batal data:', error);
                });
        }
        
        // Global Loading Functions
        function showGlobalLoading(message = 'Memproses data...') {
            const overlay = document.getElementById('globalLoading');
            const text = document.getElementById('loadingText');
            if (overlay) {
                if (text) text.textContent = message;
                overlay.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
        
        function hideGlobalLoading() {
            const overlay = document.getElementById('globalLoading');
            if (overlay) {
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
        
        // Auto-show loading for form submissions
        document.addEventListener('DOMContentLoaded', function() {
            // Handle filter form submissions with loading (not upload forms)
            const filterForms = document.querySelectorAll('form:not(#uploadPanduForm):not(#uploadTundaForm)');
            filterForms.forEach(form => {
                form.addEventListener('submit', function() {
                    showGlobalLoading('Memproses permintaan...');
                });
            });
            
            // Handle select onchange submissions
            const selects = document.querySelectorAll('select[onchange*="submit"]');
            selects.forEach(select => {
                select.addEventListener('change', function() {
                    showGlobalLoading('Memuat data...');
                });
            });
        });
    </script>
</body>
</html>
<?php /**PATH D:\project ai\lhgk\resources\views/monitoring-nota.blade.php ENDPATH**/ ?>