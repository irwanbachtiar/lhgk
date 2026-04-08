<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Summary Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    body {
        background-color: #f8f9fa;
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
    :root {
        --card-radius: 12px;
        --accent-marine: #3b82f6;
        --accent-bbm: #f59e0b;
        --accent-air: #06b6d4;
        --accent-listrik: #ef4444;
        --accent-equipment: #8b5cf6;
    }
    .stat-card {
        border-radius: var(--card-radius);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
        background: white;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    .navbar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: .45rem 1rem;
    }
    .navbar-brand {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        font-weight: 600;
        color: #fff !important;
    }
    .navbar-brand .bi {
        background: rgba(255,255,255,0.12);
        padding: 6px;
        border-radius: 8px;
        font-size: 1rem;
    }
    .navbar .btn-light {
        border-radius: 10px;
        padding: .35rem .6rem;
        box-shadow: 0 1px 0 rgba(0,0,0,0.03);
        color: #2d3748 !important;
    }
    .navbar .btn-light .bi { margin-right: .45rem; }
    .period-filter {
        background: white;
        padding: 15px;
        border-radius: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .segment-header {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 3px solid;
    }
    .segment-marine { border-color: var(--accent-marine); color: var(--accent-marine); }
    .segment-bbm { border-color: var(--accent-bbm); color: var(--accent-bbm); }
    .segment-air { border-color: var(--accent-air); color: var(--accent-air); }
    .segment-listrik { border-color: var(--accent-listrik); color: var(--accent-listrik); }
    .segment-equipment { border-color: var(--accent-equipment); color: var(--accent-equipment); }
    
    .metric-value { font-size: 1.8rem; font-weight: 700; line-height: 1; }
    .metric-label { font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem; }
    .sub-segment {
        background: #f9fafb;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        border-left: 4px solid;
    }
    .sub-segment-marine { border-left-color: var(--accent-marine); }
    .sub-segment-bbm { border-left-color: var(--accent-bbm); }
    .sub-segment-air { border-left-color: var(--accent-air); }
    .sub-segment-listrik { border-left-color: var(--accent-listrik); }
    .sub-segment-equipment { border-left-color: var(--accent-equipment); }
</style>
</head>
<body>

<div class="container-fluid px-0">
    <div class="px-4">

<!-- Global Loading Overlay -->
<div id="globalLoading" class="global-loading-overlay">
    <div class="global-loading-content">
        <div class="global-loading-spinner"></div>
        <p class="global-loading-text" id="loadingText">Memproses data...</p>
    </div>
</div>

<!-- HEADER -->
<nav class="navbar navbar-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1"><i class="bi bi-bar-chart-fill"></i> Summary Dashboard</span>
        <div>
            <a href="<?php echo e(route('dashboard.operasional')); ?>" class="btn btn-light btn-sm me-2">
                <i class="bi bi-kanban-fill"></i> Dashboard Operasional
            </a>
            <a href="<?php echo e(route('trafik')); ?>" class="btn btn-light btn-sm me-2">
                <i class="bi bi-graph-up-arrow"></i> Dashboard LHGK
            </a>
            <a href="<?php echo e(route('monitoring.nota')); ?>" class="btn btn-light btn-sm me-2">
                <i class="bi bi-file-earmark-text"></i> Monitoring Nota
            </a>
            <a href="<?php echo e(route('regional.revenue')); ?>" class="btn btn-light btn-sm me-2">
                <i class="bi bi-geo-alt"></i> Pendapatan Wilayah
            </a>
            <a href="<?php echo e(route('anper')); ?>" class="btn btn-light btn-sm me-2">
                <i class="bi bi-building-fill-gear"></i> Pendapatan Anper
            </a>
        </div>
        <div class="d-flex align-items-center ms-3">
            <?php if(isset($dbConnected) && $dbConnected): ?>
                <span class="badge bg-success">DB: Connected</span>
            <?php else: ?>
                <span class="badge bg-danger" title="<?php echo e($dbError ?? 'Unknown'); ?>">DB: Disconnected</span>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <!-- FILTER -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="period-filter">
                <form id="filterForm" method="GET" action="" class="row align-items-center">
                    <div class="col-md-2">
                        <label class="form-label"><i class="bi bi-funnel"></i> Filter:</label>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><i class="bi bi-calendar-check"></i> Periode:</label>
                        <div class="d-flex">
                            <select name="periode" id="periodeFilter" class="form-select" style="width:200px">
                                <option value="all">-- Semua Periode --</option>
                                <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($period); ?>" <?php echo e(request('periode') == $period ? 'selected' : ''); ?>><?php echo e($period); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary ms-2">
                                <i class="bi bi-funnel-fill"></i> Apply
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo e(url()->current()); ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- WILAYAH COMPARISON + SEGMENT CONTRIBUTION -->
    <div class="row mb-4">
        <div class="col-12 col-md-9">
            <div class="stat-card p-3" style="height:100%">
                <h6 class="mb-3"><i class="bi bi-bar-chart"></i> Perbandingan Realisasi per Wilayah</h6>
                <div id="wilayahChart"></div>
                <!-- Tombol shortcut per wilayah -->
                <div class="d-flex justify-content-around mt-2 flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="showWilayahDetail(0)">
                        <i class="bi bi-geo-alt-fill"></i> Wilayah 1
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="showWilayahDetail(1)">
                        <i class="bi bi-geo-alt-fill"></i> Wilayah 2
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="showWilayahDetail(2)">
                        <i class="bi bi-geo-alt-fill"></i> Wilayah 3
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="showWilayahDetail(3)">
                        <i class="bi bi-geo-alt-fill"></i> Wilayah 4
                    </button>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3 mt-3 mt-md-0">
            <div class="stat-card p-3">
                <h6 class="mb-3"><i class="bi bi-pie-chart-fill"></i> Segment Contribution</h6>
                <div id="segmentContributionDonut" role="img" aria-label="Segment Contribution"></div>
            </div>
        </div>
    </div>

    <!-- Distribusi Per Segmen per Unit (disabled) -->

    <!-- DATA GRID BREAKDOWN MARINE PER WILAYAH -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card p-3">
                <h6 class="mb-3"><i class="bi bi-water"></i> <strong>Breakdown Marine per Wilayah</strong></h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 25%">Wilayah</th>
                                <th class="text-end" style="width: 25%">Derum</th>
                                <th class="text-end" style="width: 25%">Non Derum</th>
                                <th class="text-end" style="width: 25%">Total Marine</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = ['wilayah_1' => 'Wilayah 1', 'wilayah_2' => 'Wilayah 2', 'wilayah_3' => 'Wilayah 3', 'wilayah_4' => 'Wilayah 4']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <strong class="text-primary"><?php echo e($label); ?></strong>
                                </td>
                                <td class="text-end">
                                    <span class="fw-semibold">Rp <?php echo e(number_format($segmentWilayahData['marine'][$key]['derum'], 0, ',', '.')); ?></span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-semibold">Rp <?php echo e(number_format($segmentWilayahData['marine'][$key]['non_derum'], 0, ',', '.')); ?></span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success" style="font-size: 1.1em">Rp <?php echo e(number_format($segmentWilayahData['marine'][$key]['total'], 0, ',', '.')); ?></strong>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <!-- Total Row -->
                            <tr class="table-warning">
                                <td><strong>TOTAL SEMUA WILAYAH</strong></td>
                                <td class="text-end">
                                    <strong>Rp <?php echo e(number_format($summaryData['marine']['derum']['realisasi'], 0, ',', '.')); ?></strong>
                                </td>
                                <td class="text-end">
                                    <strong>Rp <?php echo e(number_format($summaryData['marine']['non_derum']['realisasi'], 0, ',', '.')); ?></strong>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success" style="font-size: 1.2em">Rp <?php echo e(number_format($summaryData['marine']['total'], 0, ',', '.')); ?></strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Additional Info Cards -->
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="bg-light p-3 rounded">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Wilayah Tertinggi</h6>
                                <strong class="text-primary">
                                    <?php
                                        $highestWilayah = collect($segmentWilayahData['marine'])->sortByDesc('total')->first();
                                        $highestWilayahName = collect($segmentWilayahData['marine'])->sortByDesc('total')->keys()->first();
                                        $wilayahNames = ['wilayah_1' => 'Wilayah 1', 'wilayah_2' => 'Wilayah 2', 'wilayah_3' => 'Wilayah 3', 'wilayah_4' => 'Wilayah 4'];
                                    ?>
                                    <?php echo e($wilayahNames[$highestWilayahName]); ?>

                                </strong>
                                <div><small>Rp <?php echo e(number_format($highestWilayah['total'], 0, ',', '.')); ?></small></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-light p-3 rounded">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Derum Dominan</h6>
                                <strong class="text-info">
                                    <?php
                                        $derumDominant = collect($segmentWilayahData['marine'])->sortByDesc('derum')->keys()->first();
                                    ?>
                                    <?php echo e($wilayahNames[$derumDominant]); ?>

                                </strong>
                                <div><small>Rp <?php echo e(number_format($segmentWilayahData['marine'][$derumDominant]['derum'], 0, ',', '.')); ?></small></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-light p-3 rounded">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Non Derum Dominan</h6>
                                <strong class="text-warning">
                                    <?php
                                        $nonDerumDominant = collect($segmentWilayahData['marine'])->sortByDesc('non_derum')->keys()->first();
                                    ?>
                                    <?php echo e($wilayahNames[$nonDerumDominant]); ?>

                                </strong>
                                <div><small>Rp <?php echo e(number_format($segmentWilayahData['marine'][$nonDerumDominant]['non_derum'], 0, ',', '.')); ?></small></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-light p-3 rounded">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Rata-rata per Wilayah</h6>
                                <strong class="text-secondary">
                                    Rp <?php echo e(number_format($summaryData['marine']['total'] / 4, 0, ',', '.')); ?>

                                </strong>
                                <div><small>dari 4 wilayah</small></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SEGMENT 1: MARINE -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="segment-header segment-marine"><i class="bi bi-water"></i> Marine</h5>
        </div>
        
        <div class="col-md-4">
            <div class="stat-card p-4">
                <div class="metric-label">Total Marine</div>
                <div class="metric-value text-primary">Rp <?php echo e(number_format($summaryData['marine']['total'], 0, ',', '.')); ?></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="sub-segment sub-segment-marine">
                <strong>Derum</strong>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <div>
                        <div class="metric-value" style="font-size:1.4rem">Rp <?php echo e(number_format($summaryData['marine']['derum']['realisasi'], 0, ',', '.')); ?></div>
                        <small class="text-muted">Realisasi</small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary" style="font-size:1rem"><?php echo e($summaryData['marine']['derum']['persentase']); ?>%</span>
                        <div><small class="text-muted">Anggaran: Rp <?php echo e(number_format($summaryData['marine']['derum']['anggaran'], 0, ',', '.')); ?></small></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="sub-segment sub-segment-marine">
                <strong>Non Derum</strong>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <div>
                        <div class="metric-value" style="font-size:1.4rem">Rp <?php echo e(number_format($summaryData['marine']['non_derum']['realisasi'], 0, ',', '.')); ?></div>
                        <small class="text-muted">Realisasi</small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary" style="font-size:1rem"><?php echo e($summaryData['marine']['non_derum']['persentase']); ?>%</span>
                        <div><small class="text-muted">Anggaran: Rp <?php echo e(number_format($summaryData['marine']['non_derum']['anggaran'], 0, ',', '.')); ?></small></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Marine Doughnut: Distribusi per Wilayah (4 donuts: Derum vs Non Derum per Wilayah) -->
        <div class="col-12 mt-3">
            <div class="stat-card p-3">
                <h6 class="mb-3"><strong>Distribusi Marine per Wilayah</strong></h6>
                <div class="row">
                    <?php $__currentLoopData = ['wilayah_1' => 'Wilayah 1', 'wilayah_2' => 'Wilayah 2', 'wilayah_3' => 'Wilayah 3', 'wilayah_4' => 'Wilayah 4']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-6 col-md-3 text-center mb-3">
                        <div id="marineDonut_<?php echo e($key); ?>" role="img" aria-label="Distribusi Marine <?php echo e($label); ?>"></div>
                        <div class="mt-2"><small><strong><?php echo e($label); ?></strong></small></div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <!-- Marine per Wilayah -->
        <div class="col-12 mt-3">
            <div class="stat-card p-3">
                <h6 class="mb-3"><strong>Breakdown per Wilayah</strong></h6>
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Wilayah</th>
                            <th class="text-end">Derum</th>
                            <th class="text-end">Non Derum</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = ['wilayah_1' => 'Wilayah 1', 'wilayah_2' => 'Wilayah 2', 'wilayah_3' => 'Wilayah 3', 'wilayah_4' => 'Wilayah 4']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><strong><?php echo e($label); ?></strong></td>
                            <td class="text-end">Rp <?php echo e(number_format($segmentWilayahData['marine'][$key]['derum'], 0, ',', '.')); ?></td>
                            <td class="text-end">Rp <?php echo e(number_format($segmentWilayahData['marine'][$key]['non_derum'], 0, ',', '.')); ?></td>
                            <td class="text-end"><strong>Rp <?php echo e(number_format($segmentWilayahData['marine'][$key]['total'], 0, ',', '.')); ?></strong></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SEGMENT 2: BBM -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="segment-header segment-bbm"><i class="bi bi-fuel-pump"></i> BBM</h5>
        </div>
        
        <div class="col-md-6">
            <div class="stat-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="metric-label">Realisasi BBM</div>
                        <div class="metric-value text-warning">Rp <?php echo e(number_format($summaryData['bbm']['realisasi'], 0, ',', '.')); ?></div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-warning text-dark" style="font-size:1.2rem"><?php echo e($summaryData['bbm']['persentase']); ?>%</span>
                        <div class="mt-2"><small class="text-muted">Anggaran: Rp <?php echo e(number_format($summaryData['bbm']['anggaran'], 0, ',', '.')); ?></small></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BBM Distribusi per Wilayah (bar chart) -->
        <div class="col-12 mt-3">
            <div class="stat-card p-3">
                <h6 class="mb-3"><strong>Distribusi BBM per Wilayah</strong></h6>
                <div id="bbmBarChart"></div>
            </div>
        </div>

        <!-- BBM per Wilayah (table) -->
        <div class="col-12 mt-3">
            <div class="stat-card p-3">
                <h6 class="mb-3"><strong>Breakdown per Wilayah</strong></h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 50%">Wilayah</th>
                                <th class="text-end" style="width: 50%">Total BBM</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = ['wilayah_1' => 'Wilayah 1', 'wilayah_2' => 'Wilayah 2', 'wilayah_3' => 'Wilayah 3', 'wilayah_4' => 'Wilayah 4']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><strong class="text-warning"><?php echo e($label); ?></strong></td>
                                <td class="text-end">Rp <?php echo e(number_format($segmentWilayahData['bbm'][$key] ?? 0, 0, ',', '.')); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <tr class="table-warning">
                                <td><strong>TOTAL SEMUA WILAYAH</strong></td>
                                <td class="text-end"><strong class="text-warning">Rp <?php echo e(number_format($summaryData['bbm']['realisasi'] ?? array_sum($segmentWilayahData['bbm'] ?? []), 0, ',', '.')); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Summary Cards (BBM) -->
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Wilayah Tertinggi</h6>
                                <strong class="text-warning">
                                    <?php
                                        $bbmCollect = collect($segmentWilayahData['bbm'] ?? []);
                                        $highestKey = $bbmCollect->count() ? $bbmCollect->sortDesc()->keys()->first() : null;
                                        $wilayahNames = ['wilayah_1' => 'Wilayah 1', 'wilayah_2' => 'Wilayah 2', 'wilayah_3' => 'Wilayah 3', 'wilayah_4' => 'Wilayah 4'];
                                    ?>
                                    <?php echo e($highestKey ? $wilayahNames[$highestKey] : '-'); ?>

                                </strong>
                                <div><small>Rp <?php echo e(number_format($bbmCollect->get($highestKey, 0), 0, ',', '.')); ?></small></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Rata-rata per Wilayah</h6>
                                <strong class="text-secondary">
                                    Rp <?php echo e(number_format((($summaryData['bbm']['realisasi'] ?? array_sum($segmentWilayahData['bbm'] ?? [])) / 4), 0, ',', '.')); ?>

                                </strong>
                                <div><small>dari 4 wilayah</small></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Distribusi Terendah</h6>
                                <strong class="text-info">
                                    <?php
                                        $lowestKey = $bbmCollect->count() ? $bbmCollect->sort()->keys()->first() : null;
                                    ?>
                                    <?php echo e($lowestKey ? $wilayahNames[$lowestKey] : '-'); ?>

                                </strong>
                                <div><small>Rp <?php echo e(number_format($bbmCollect->get($lowestKey, 0), 0, ',', '.')); ?></small></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SEGMENT 3: AIR -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="segment-header segment-air"><i class="bi bi-droplet"></i> Air</h5>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card p-4">
                <div class="metric-label">Total Air</div>
                <div class="metric-value text-info">Rp <?php echo e(number_format($summaryData['air']['total'], 0, ',', '.')); ?></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="sub-segment sub-segment-air">
                <strong>Air Kapal</strong>
                <div class="mt-2">
                    <div class="metric-value" style="font-size:1.2rem">Rp <?php echo e(number_format($summaryData['air']['air_kapal']['realisasi'], 0, ',', '.')); ?></div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <small class="text-muted">Realisasi</small>
                        <span class="badge bg-info" style="font-size:0.85rem"><?php echo e($summaryData['air']['air_kapal']['persentase']); ?>%</span>
                    </div>
                    <small class="text-muted">Anggaran: Rp <?php echo e(number_format($summaryData['air']['air_kapal']['anggaran'], 0, ',', '.')); ?></small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="sub-segment sub-segment-air">
                <strong>Air Umum</strong>
                <div class="mt-2">
                    <div class="metric-value" style="font-size:1.2rem">Rp <?php echo e(number_format($summaryData['air']['air_umum']['realisasi'], 0, ',', '.')); ?></div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <small class="text-muted">Realisasi</small>
                        <span class="badge bg-info" style="font-size:0.85rem"><?php echo e($summaryData['air']['air_umum']['persentase']); ?>%</span>
                    </div>
                    <small class="text-muted">Anggaran: Rp <?php echo e(number_format($summaryData['air']['air_umum']['anggaran'], 0, ',', '.')); ?></small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="sub-segment sub-segment-air">
                <strong>Air Kontrakor</strong>
                <div class="mt-2">
                    <div class="metric-value" style="font-size:1.2rem">Rp <?php echo e(number_format($summaryData['air']['air_kontrakor']['realisasi'], 0, ',', '.')); ?></div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <small class="text-muted">Realisasi</small>
                        <span class="badge bg-info" style="font-size:0.85rem"><?php echo e($summaryData['air']['air_kontrakor']['persentase']); ?>%</span>
                    </div>
                    <small class="text-muted">Anggaran: Rp <?php echo e(number_format($summaryData['air']['air_kontrakor']['anggaran'], 0, ',', '.')); ?></small>
                </div>
            </div>
        </div>

        <!-- Air Distribusi per Wilayah (donut charts per wilayah: Air Kapal / Air Umum / Air Kontrakor) -->
        <div class="col-12 mt-3">
            <div class="stat-card p-3">
                <h6 class="mb-3"><strong>Distribusi Air per Wilayah</strong></h6>
                <div class="row">
                    <?php $__currentLoopData = ['wilayah_1' => 'Wilayah 1', 'wilayah_2' => 'Wilayah 2', 'wilayah_3' => 'Wilayah 3', 'wilayah_4' => 'Wilayah 4']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-6 col-md-3 text-center mb-3">
                        <div id="airDonut_<?php echo e($key); ?>" role="img" aria-label="Distribusi Air <?php echo e($label); ?>"></div>
                        <div class="mt-2"><small><strong><?php echo e($label); ?></strong></small></div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <!-- Air per Wilayah -->
        <div class="col-12 mt-3">
            <div class="stat-card p-3">
                <h6 class="mb-3"><strong>Breakdown per Wilayah</strong></h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 30%">Wilayah</th>
                                <th class="text-end" style="width: 17%">Air Kapal</th>
                                <th class="text-end" style="width: 17%">Air Umum</th>
                                <th class="text-end" style="width: 17%">Air Kontrakor</th>
                                <th class="text-end" style="width: 19%">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $airCollect = collect($segmentWilayahData['air'] ?? []);
                                $sum_air_kapal = $airCollect->sum('air_kapal');
                                $sum_air_umum = $airCollect->sum('air_umum');
                                $sum_air_kontrakor = $airCollect->sum('air_kontrakor');
                                $sum_air_total = $airCollect->sum('total');
                            ?>
                            <?php $__currentLoopData = ['wilayah_1' => 'Wilayah 1', 'wilayah_2' => 'Wilayah 2', 'wilayah_3' => 'Wilayah 3', 'wilayah_4' => 'Wilayah 4']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><strong class="text-info"><?php echo e($label); ?></strong></td>
                                <td class="text-end">Rp <?php echo e(number_format($segmentWilayahData['air'][$key]['air_kapal'] ?? 0, 0, ',', '.')); ?></td>
                                <td class="text-end">Rp <?php echo e(number_format($segmentWilayahData['air'][$key]['air_umum'] ?? 0, 0, ',', '.')); ?></td>
                                <td class="text-end">Rp <?php echo e(number_format($segmentWilayahData['air'][$key]['air_kontrakor'] ?? 0, 0, ',', '.')); ?></td>
                                <td class="text-end"><strong>Rp <?php echo e(number_format($segmentWilayahData['air'][$key]['total'] ?? 0, 0, ',', '.')); ?></strong></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <tr class="table-warning">
                                <td><strong>TOTAL SEMUA WILAYAH</strong></td>
                                <td class="text-end"><strong>Rp <?php echo e(number_format($sum_air_kapal, 0, ',', '.')); ?></strong></td>
                                <td class="text-end"><strong>Rp <?php echo e(number_format($sum_air_umum, 0, ',', '.')); ?></strong></td>
                                <td class="text-end"><strong>Rp <?php echo e(number_format($sum_air_kontrakor, 0, ',', '.')); ?></strong></td>
                                <td class="text-end"><strong class="text-info">Rp <?php echo e(number_format($sum_air_total ?: ($summaryData['air']['total'] ?? 0), 0, ',', '.')); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Wilayah Tertinggi</h6>
                                <strong class="text-info">
                                    <?php
                                        $highestAir = $airCollect->sortByDesc('total')->first();
                                        $highestKeyAir = $airCollect->sortByDesc('total')->keys()->first();
                                        $wilayahNames = ['wilayah_1' => 'Wilayah 1', 'wilayah_2' => 'Wilayah 2', 'wilayah_3' => 'Wilayah 3', 'wilayah_4' => 'Wilayah 4'];
                                    ?>
                                    <?php echo e($highestKeyAir ? $wilayahNames[$highestKeyAir] : '-'); ?>

                                </strong>
                                <div><small>Rp <?php echo e(number_format($highestAir['total'] ?? 0, 0, ',', '.')); ?></small></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Rata-rata per Wilayah</h6>
                                <strong class="text-secondary">Rp <?php echo e(number_format((($sum_air_total ?: ($summaryData['air']['total'] ?? 0)) / 4), 0, ',', '.')); ?></strong>
                                <div><small>dari 4 wilayah</small></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Distribusi Terendah</h6>
                                <strong class="text-info">
                                    <?php
                                        $lowestKeyAir = $airCollect->sort()->keys()->first();
                                    ?>
                                    <?php echo e($lowestKeyAir ? $wilayahNames[$lowestKeyAir] : '-'); ?>

                                </strong>
                                <div><small>Rp <?php echo e(number_format($airCollect->get($lowestKeyAir)['total'] ?? 0, 0, ',', '.')); ?></small></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SEGMENT 4: LISTRIK -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="segment-header segment-listrik"><i class="bi bi-lightning-charge"></i> Listrik</h5>
        </div>
        
        <div class="col-md-6">
            <div class="stat-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="metric-label">Realisasi Listrik</div>
                        <div class="metric-value text-danger">Rp <?php echo e(number_format($summaryData['listrik']['realisasi'], 0, ',', '.')); ?></div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-danger" style="font-size:1.2rem"><?php echo e($summaryData['listrik']['persentase']); ?>%</span>
                        <div class="mt-2"><small class="text-muted">Anggaran: Rp <?php echo e(number_format($summaryData['listrik']['anggaran'], 0, ',', '.')); ?></small></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Listrik Distribusi per Wilayah (bar chart) -->
        <div class="col-12 mt-3">
            <div class="stat-card p-3">
                <h6 class="mb-3"><strong>Distribusi Listrik per Wilayah</strong></h6>
                <div id="listrikBarChart"></div>
            </div>
        </div>

        <!-- Listrik per Wilayah (table) -->
        <div class="col-12 mt-3">
            <div class="stat-card p-3">
                <h6 class="mb-3"><strong>Breakdown per Wilayah</strong></h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 50%">Wilayah</th>
                                <th class="text-end" style="width: 50%">Total Listrik</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $listrikCollect = collect($segmentWilayahData['listrik'] ?? []);
                            ?>
                            <?php $__currentLoopData = ['wilayah_1' => 'Wilayah 1', 'wilayah_2' => 'Wilayah 2', 'wilayah_3' => 'Wilayah 3', 'wilayah_4' => 'Wilayah 4']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><strong class="text-danger"><?php echo e($label); ?></strong></td>
                                <td class="text-end">Rp <?php echo e(number_format($segmentWilayahData['listrik'][$key] ?? 0, 0, ',', '.')); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <tr class="table-warning">
                                <td><strong>TOTAL SEMUA WILAYAH</strong></td>
                                <td class="text-end"><strong class="text-danger">Rp <?php echo e(number_format($summaryData['listrik']['realisasi'] ?? $listrikCollect->sum(), 0, ',', '.')); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Wilayah Tertinggi</h6>
                                <strong class="text-danger">
                                    <?php
                                        $highestKey = $listrikCollect->count() ? $listrikCollect->sortDesc()->keys()->first() : null;
                                        $wilayahNames = ['wilayah_1' => 'Wilayah 1', 'wilayah_2' => 'Wilayah 2', 'wilayah_3' => 'Wilayah 3', 'wilayah_4' => 'Wilayah 4'];
                                    ?>
                                    <?php echo e($highestKey ? $wilayahNames[$highestKey] : '-'); ?>

                                </strong>
                                <div><small>Rp <?php echo e(number_format($listrikCollect->get($highestKey, 0), 0, ',', '.')); ?></small></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Rata-rata per Wilayah</h6>
                                <strong class="text-secondary">Rp <?php echo e(number_format((($summaryData['listrik']['realisasi'] ?? $listrikCollect->sum()) / 4), 0, ',', '.')); ?></strong>
                                <div><small>dari 4 wilayah</small></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Distribusi Terendah</h6>
                                <strong class="text-danger">
                                    <?php
                                        $lowestKey = $listrikCollect->count() ? $listrikCollect->sort()->keys()->first() : null;
                                    ?>
                                    <?php echo e($lowestKey ? $wilayahNames[$lowestKey] : '-'); ?>

                                </strong>
                                <div><small>Rp <?php echo e(number_format($listrikCollect->get($lowestKey, 0), 0, ',', '.')); ?></small></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TREND CHART (dinon-aktifkan) -->
    <div class="row mb-4 d-none" id="trendSection">
        <div class="col-12">
            <div class="stat-card p-4">
                <h6 class="mb-3"><i class="bi bi-graph-up"></i> Trend Realisasi per Segmen (6 Bulan Terakhir)</h6>
                <div id="trendChart"></div>
            </div>
        </div>
    </div>

    <!-- SEGMENT 5: EQUIPMENT -->
    <div class="row mb-4">
            <div class="col-12">
                <h5 class="segment-header segment-equipment"><i class="bi bi-tools"></i> Equipment</h5>
        </div>
        
        <div class="col-md-6">
            <div class="stat-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="metric-label">Realisasi Equipment</div>
                        <div class="metric-value" style="color:var(--accent-equipment)">Rp <?php echo e(number_format($summaryData['equipment']['realisasi'] ?? 0, 0, ',', '.')); ?></div>
                    </div>
                    <div class="text-end">
                        <span class="badge" style="background:var(--accent-equipment);color:#fff;font-size:1.2rem"><?php echo e($summaryData['equipment']['persentase'] ?? 0); ?>%</span>
                        <div class="mt-2"><small class="text-muted">Anggaran: Rp <?php echo e(number_format($summaryData['equipment']['anggaran'] ?? 0, 0, ',', '.')); ?></small></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equipment Sub-segments (Proyek / Sparepark / Maintenance / Lainnya) -->
        <div class="col-12 mt-3">
            <div class="row">
                <div class="col-12">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="sub-segment sub-segment-equipment">
                                <strong>Proyek</strong>
                                <div class="mt-2">
                                    <div class="metric-value" style="font-size:1.2rem">Rp <?php echo e(number_format($summaryData['equipment']['proyek']['realisasi'] ?? 0, 0, ',', '.')); ?></div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="text-muted">Realisasi</small>
                                        <span class="badge" style="background:var(--accent-equipment);color:#fff;font-size:0.85rem"><?php echo e($summaryData['equipment']['proyek']['persentase'] ?? 0); ?>%</span>
                                    </div>
                                    <small class="text-muted">Anggaran: Rp <?php echo e(number_format($summaryData['equipment']['proyek']['anggaran'] ?? 0, 0, ',', '.')); ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="sub-segment sub-segment-equipment">
                                <strong>Sparepark</strong>
                                <div class="mt-2">
                                    <div class="metric-value" style="font-size:1.2rem">Rp <?php echo e(number_format($summaryData['equipment']['sparepark']['realisasi'] ?? 0, 0, ',', '.')); ?></div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="text-muted">Realisasi</small>
                                        <span class="badge" style="background:var(--accent-equipment);color:#fff;font-size:0.85rem"><?php echo e($summaryData['equipment']['sparepark']['persentase'] ?? 0); ?>%</span>
                                    </div>
                                    <small class="text-muted">Anggaran: Rp <?php echo e(number_format($summaryData['equipment']['sparepark']['anggaran'] ?? 0, 0, ',', '.')); ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="sub-segment sub-segment-equipment">
                                <strong>Maintenance</strong>
                                <div class="mt-2">
                                    <div class="metric-value" style="font-size:1.2rem">Rp <?php echo e(number_format($summaryData['equipment']['maintenance']['realisasi'] ?? 0, 0, ',', '.')); ?></div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="text-muted">Realisasi</small>
                                        <span class="badge" style="background:var(--accent-equipment);color:#fff;font-size:0.85rem"><?php echo e($summaryData['equipment']['maintenance']['persentase'] ?? 0); ?>%</span>
                                    </div>
                                    <small class="text-muted">Anggaran: Rp <?php echo e(number_format($summaryData['equipment']['maintenance']['anggaran'] ?? 0, 0, ',', '.')); ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="sub-segment sub-segment-equipment">
                                <strong>Lainnya</strong>
                                <div class="mt-2">
                                    <div class="metric-value" style="font-size:1.2rem">Rp <?php echo e(number_format($summaryData['equipment']['lainnya']['realisasi'] ?? 0, 0, ',', '.')); ?></div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="text-muted">Realisasi</small>
                                        <span class="badge" style="background:var(--accent-equipment);color:#fff;font-size:0.85rem"><?php echo e($summaryData['equipment']['lainnya']['persentase'] ?? 0); ?>%</span>
                                    </div>
                                    <small class="text-muted">Anggaran: Rp <?php echo e(number_format($summaryData['equipment']['lainnya']['anggaran'] ?? 0, 0, ',', '.')); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equipment Distribusi per Wilayah (bar chart) -->
        <div class="col-12 mt-3">
            <div class="stat-card p-3">
                <h6 class="mb-3"><strong>Distribusi Equipment per Wilayah</strong></h6>
                <div id="equipmentBarChart"></div>
            </div>
        </div>

        <!-- Equipment Distribusi per Wilayah (donut charts per wilayah: Proyek / Sparepark / Maintenance / Lainnya) -->
        <div class="col-12 mt-3">
            <div class="stat-card p-3">
                <h6 class="mb-3"><strong>Distribusi Equipment per Wilayah</strong></h6>
                <div class="row">
                    <?php $__currentLoopData = ['wilayah_1' => 'Wilayah 1', 'wilayah_2' => 'Wilayah 2', 'wilayah_3' => 'Wilayah 3', 'wilayah_4' => 'Wilayah 4']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-6 col-md-3 text-center mb-3">
                        <div id="equipmentDonut_<?php echo e($key); ?>" role="img" aria-label="Distribusi Equipment <?php echo e($label); ?>"></div>
                        <div class="mt-2"><small><strong><?php echo e($label); ?></strong></small></div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <!-- Equipment per Wilayah (table) -->
        <div class="col-12 mt-3">
            <div class="stat-card p-3">
                <h6 class="mb-3"><strong>Breakdown per Wilayah</strong></h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 25%">Wilayah</th>
                                <th class="text-end" style="width: 15%">Proyek</th>
                                <th class="text-end" style="width: 15%">Sparepark</th>
                                <th class="text-end" style="width: 15%">Maintenance</th>
                                <th class="text-end" style="width: 15%">Lainnya</th>
                                <th class="text-end" style="width: 15%">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $equipmentCollect = collect($segmentWilayahData['equipment'] ?? []);
                                $sum_proyek = $equipmentCollect->sum('proyek');
                                $sum_sparepark = $equipmentCollect->sum('sparepark');
                                $sum_maintenance = $equipmentCollect->sum('maintenance');
                                $sum_lainnya = $equipmentCollect->sum('lainnya');
                                $sum_equipment_total = $equipmentCollect->sum('total');
                            ?>
                            <?php $__currentLoopData = ['wilayah_1' => 'Wilayah 1', 'wilayah_2' => 'Wilayah 2', 'wilayah_3' => 'Wilayah 3', 'wilayah_4' => 'Wilayah 4']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><strong style="color:var(--accent-equipment)"><?php echo e($label); ?></strong></td>
                                <td class="text-end">Rp <?php echo e(number_format($segmentWilayahData['equipment'][$key]['proyek'] ?? 0, 0, ',', '.')); ?></td>
                                <td class="text-end">Rp <?php echo e(number_format($segmentWilayahData['equipment'][$key]['sparepark'] ?? 0, 0, ',', '.')); ?></td>
                                <td class="text-end">Rp <?php echo e(number_format($segmentWilayahData['equipment'][$key]['maintenance'] ?? 0, 0, ',', '.')); ?></td>
                                <td class="text-end">Rp <?php echo e(number_format($segmentWilayahData['equipment'][$key]['lainnya'] ?? 0, 0, ',', '.')); ?></td>
                                <td class="text-end"><strong>Rp <?php echo e(number_format($segmentWilayahData['equipment'][$key]['total'] ?? 0, 0, ',', '.')); ?></strong></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <tr class="table-warning">
                                <td><strong>TOTAL SEMUA WILAYAH</strong></td>
                                <td class="text-end"><strong>Rp <?php echo e(number_format($sum_proyek, 0, ',', '.')); ?></strong></td>
                                <td class="text-end"><strong>Rp <?php echo e(number_format($sum_sparepark, 0, ',', '.')); ?></strong></td>
                                <td class="text-end"><strong>Rp <?php echo e(number_format($sum_maintenance, 0, ',', '.')); ?></strong></td>
                                <td class="text-end"><strong>Rp <?php echo e(number_format($sum_lainnya, 0, ',', '.')); ?></strong></td>
                                <td class="text-end"><strong style="color:var(--accent-equipment)">Rp <?php echo e(number_format($sum_equipment_total ?: ($summaryData['equipment']['realisasi'] ?? 0), 0, ',', '.')); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

</div>

<!-- ===== MODAL DETAIL CABANG PER WILAYAH ===== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<div class="modal fade" id="modalDetailCabang" tabindex="-1" aria-labelledby="modalDetailCabangLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white" id="modalDetailHeader" style="background: linear-gradient(135deg,#667eea,#764ba2)">
                <h5 class="modal-title" id="modalDetailCabangLabel">
                    <i class="bi bi-geo-alt-fill"></i> <span id="modalWilayahTitle">Detail Cabang</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="modalDetailBody">
                <!-- Content rendered by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    // Trend Chart
    const trendPeriods = <?php echo json_encode($trendPeriods, 15, 512) ?>;
    const trendMarine = <?php echo json_encode($trendMarine, 15, 512) ?>;
    const trendBbm = <?php echo json_encode($trendBbm, 15, 512) ?>;
    const trendAir = <?php echo json_encode($trendAir, 15, 512) ?>;
    const trendListrik = <?php echo json_encode($trendListrik, 15, 512) ?>;
    const trendEquipment = <?php echo json_encode($trendEquipment ?? [], 15, 512) ?>;

    const chartOptions = {
        chart: { 
            type: 'line', 
            height: 350,
            toolbar: { show: true }
        },
        series: [
            { name: 'Marine', data: trendMarine },
            { name: 'BBM', data: trendBbm },
            { name: 'Air', data: trendAir },
            { name: 'Listrik', data: trendListrik },
            { name: 'Equipment', data: trendEquipment }
        ],
        xaxis: { 
            categories: trendPeriods,
            labels: { rotate: -45 }
        },
        stroke: { 
            width: [3, 3, 3, 3], 
            curve: 'smooth' 
        },
        yaxis: {
            labels: {
                formatter: function(val) {
                    return 'Rp ' + (val / 1000000).toFixed(0) + 'M';
                }
            }
        },
        colors: ['#3b82f6', '#f59e0b', '#06b6d4', '#ef4444', '#8b5cf6'],
        legend: {
            position: 'top',
            horizontalAlign: 'left'
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return 'Rp ' + val.toLocaleString('id-ID');
                }
            }
        }
    };

    const trendEl = document.querySelector('#trendChart');
    if (trendEl) {
        new ApexCharts(trendEl, chartOptions).render();
    }

    // Wilayah Bar Chart (stacked bar per wilayah: segments stacked inside each bar)
    const wilayahData = <?php echo json_encode($wilayahData, 15, 512) ?>;

    const wilayahCategories = ['Wilayah 1', 'Wilayah 2', 'Wilayah 3', 'Wilayah 4'];

    const marineSeriesData = [
        (wilayahData.wilayah_1 && wilayahData.wilayah_1.marine) ? wilayahData.wilayah_1.marine : 0,
        (wilayahData.wilayah_2 && wilayahData.wilayah_2.marine) ? wilayahData.wilayah_2.marine : 0,
        (wilayahData.wilayah_3 && wilayahData.wilayah_3.marine) ? wilayahData.wilayah_3.marine : 0,
        (wilayahData.wilayah_4 && wilayahData.wilayah_4.marine) ? wilayahData.wilayah_4.marine : 0
    ];

    const bbmSeriesData = [
        (wilayahData.wilayah_1 && wilayahData.wilayah_1.bbm) ? wilayahData.wilayah_1.bbm : 0,
        (wilayahData.wilayah_2 && wilayahData.wilayah_2.bbm) ? wilayahData.wilayah_2.bbm : 0,
        (wilayahData.wilayah_3 && wilayahData.wilayah_3.bbm) ? wilayahData.wilayah_3.bbm : 0,
        (wilayahData.wilayah_4 && wilayahData.wilayah_4.bbm) ? wilayahData.wilayah_4.bbm : 0
    ];

    const airSeriesData = [
        (wilayahData.wilayah_1 && wilayahData.wilayah_1.air) ? wilayahData.wilayah_1.air : 0,
        (wilayahData.wilayah_2 && wilayahData.wilayah_2.air) ? wilayahData.wilayah_2.air : 0,
        (wilayahData.wilayah_3 && wilayahData.wilayah_3.air) ? wilayahData.wilayah_3.air : 0,
        (wilayahData.wilayah_4 && wilayahData.wilayah_4.air) ? wilayahData.wilayah_4.air : 0
    ];

    const listrikSeriesData = [
        (wilayahData.wilayah_1 && wilayahData.wilayah_1.listrik) ? wilayahData.wilayah_1.listrik : 0,
        (wilayahData.wilayah_2 && wilayahData.wilayah_2.listrik) ? wilayahData.wilayah_2.listrik : 0,
        (wilayahData.wilayah_3 && wilayahData.wilayah_3.listrik) ? wilayahData.wilayah_3.listrik : 0,
        (wilayahData.wilayah_4 && wilayahData.wilayah_4.listrik) ? wilayahData.wilayah_4.listrik : 0
    ];

    const equipmentSeriesData = [
        (wilayahData.wilayah_1 && wilayahData.wilayah_1.equipment) ? wilayahData.wilayah_1.equipment : 0,
        (wilayahData.wilayah_2 && wilayahData.wilayah_2.equipment) ? wilayahData.wilayah_2.equipment : 0,
        (wilayahData.wilayah_3 && wilayahData.wilayah_3.equipment) ? wilayahData.wilayah_3.equipment : 0,
        (wilayahData.wilayah_4 && wilayahData.wilayah_4.equipment) ? wilayahData.wilayah_4.equipment : 0
    ];

    const wilayahChartOptions = {
        chart: {
            type: 'bar',
            height: 260,
            stacked: true,
            toolbar: { show: true },
            events: {
                dataPointSelection: function(event, chartContext, config) {
                    showWilayahDetail(config.dataPointIndex);
                },
                click: function(event, chartContext, config) {
                    if (config.dataPointIndex !== undefined && config.dataPointIndex >= 0) {
                        showWilayahDetail(config.dataPointIndex);
                    }
                }
            }
        },
        series: [
            { name: 'Marine', data: marineSeriesData },
            { name: 'BBM', data: bbmSeriesData },
            { name: 'Air', data: airSeriesData },
            { name: 'Listrik', data: listrikSeriesData },
            { name: 'Equipment', data: equipmentSeriesData }
        ],
        xaxis: {
            categories: wilayahCategories,
            labels: {
                style: {
                    fontSize: '11px',
                    fontWeight: 600,
                    colors: ['#374151']
                }
            }
        },
        yaxis: {
            labels: {
                formatter: function(val) {
                    return 'Rp ' + (val / 1000000).toFixed(0) + 'M';
                },
                style: {
                    fontSize: '11px',
                    colors: ['#374151']
                }
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                borderRadius: 4,
                columnWidth: '60%'
            }
        },
        dataLabels: { enabled: false },
        
        colors: ['#3b82f6', '#f59e0b', '#06b6d4', '#ef4444', '#8b5cf6'],
        legend: {
            position: 'top',
            horizontalAlign: 'left',
            fontSize: '12px',
            markers: {
                width: 10,
                height: 10,
                radius: 3
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return 'Rp ' + val.toLocaleString('id-ID');
                }
            }
        },
        grid: {
            borderColor: '#f1f1f1',
            padding: {
                top: 0,
                right: 10,
                bottom: 0,
                left: 10
            }
        }
    };

    try { new ApexCharts(document.querySelector('#wilayahChart'), wilayahChartOptions).render(); } catch(e) { console.error('wilayahChart error:', e); }

    // Segment Contribution donut (aggregate share per segment)
    if (document.querySelector('#segmentContributionDonut')) {
        var summary = <?php echo json_encode($summaryData ?? [], 15, 512) ?>;
        var segSeries = [
            (summary.marine && summary.marine.total) ? summary.marine.total : 0,
            (summary.bbm && summary.bbm.realisasi) ? summary.bbm.realisasi : 0,
            (summary.air && summary.air.total) ? summary.air.total : 0,
            (summary.listrik && summary.listrik.realisasi) ? summary.listrik.realisasi : 0,
            (summary.equipment && summary.equipment.total) ? summary.equipment.total : 0
        ];
        var segLabels = ['Marine','BBM','Air','Listrik','Equipment'];
        var segColors = ['#3b82f6', '#f59e0b', '#06b6d4', '#ef4444', '#8b5cf6'];

        var segOptions = {
            chart: { type: 'donut', height: 220 },
            series: segSeries,
            labels: segLabels,
            colors: segColors,
            legend: { position: 'bottom' },
            dataLabels: {
                enabled: true,
                formatter: function (val, opts) {
                    var idx = opts.seriesIndex;
                    var valNum = opts.w.config.series[idx] || 0;
                    return 'Rp ' + Number(valNum).toLocaleString('id-ID');
                }
            },
            tooltip: { y: { formatter: function(val){ return 'Rp ' + Number(val).toLocaleString('id-ID'); } } }
        };

        try { new ApexCharts(document.querySelector('#segmentContributionDonut'), segOptions).render(); } catch(e) { console.error('segmentDonut error:', e); }
    }

    // Marine Donut Charts (Derum vs Non Derum per Wilayah)
    const marineWilayah = <?php echo json_encode($segmentWilayahData['marine'] ?? [], 15, 512) ?>;
    const wilayahKeys = ['wilayah_1','wilayah_2','wilayah_3','wilayah_4'];
    const donutColors = ['#3b82f6', '#10b981'];

    wilayahKeys.forEach(function(wk){
        const data = marineWilayah[wk] || {};
        const derum = data.derum || 0;
        const nonDerum = data.non_derum || 0;

        const options = {
            chart: { type: 'donut', height: 200 },
            series: [derum, nonDerum],
            labels: ['Derum','Non Derum'],
            colors: donutColors,
            legend: { position: 'bottom' },
            tooltip: { y: { formatter: function(val){ return 'Rp ' + Number(val).toLocaleString('id-ID'); } } },
            dataLabels: { enabled: true, formatter: function (val, opts) { return Math.round(val) + '%'; } }
        };

        const el = document.querySelector('#marineDonut_' + wk);
        if(el) {
            try { new ApexCharts(el, options).render(); } catch(e) { console.error('marineDonut error:', wk, e); }
        }
    });

    // BBM Bar Chart (Distribusi per Wilayah)
    const bbmDataObj = <?php echo json_encode($segmentWilayahData['bbm'] ?? [], 15, 512) ?>;
    const bbmSeries = [
        bbmDataObj.wilayah_1 ?? 0,
        bbmDataObj.wilayah_2 ?? 0,
        bbmDataObj.wilayah_3 ?? 0,
        bbmDataObj.wilayah_4 ?? 0
    ];

    const bbmOptions = {
        chart: { type: 'bar', height: 260 },
        series: [{ name: 'BBM', data: bbmSeries }],
        xaxis: { categories: ['Wilayah 1','Wilayah 2','Wilayah 3','Wilayah 4'] },
        colors: ['#f59e0b'],
        yaxis: { labels: { formatter: function(val){ return 'Rp ' + (val / 1000000).toFixed(0) + 'M'; } } },
        tooltip: { y: { formatter: function(val){ return 'Rp ' + Number(val).toLocaleString('id-ID'); } } },
        plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
        dataLabels: { enabled: false }
    };

    try { new ApexCharts(document.querySelector('#bbmBarChart'), bbmOptions).render(); } catch(e) { console.error('bbmBarChart error:', e); }

    // Air Donut Charts (Air Kapal / Air Umum / Air Kontrakor per wilayah)
    const airWilayah = <?php echo json_encode($segmentWilayahData['air'] ?? [], 15, 512) ?>;
    const airKeys = ['wilayah_1','wilayah_2','wilayah_3','wilayah_4'];
    const airColors = ['#06b6d4', '#7dd3fc', '#0ea5a3'];

    airKeys.forEach(function(ak){
        const d = airWilayah[ak] || {};
        const kapal = d.air_kapal || 0;
        const umum = d.air_umum || 0;
        const kontrakor = d.air_kontrakor || 0;

        const opts = {
            chart: { type: 'donut', height: 200 },
            series: [kapal, umum, kontrakor],
            labels: ['Air Kapal','Air Umum','Air Kontrakor'],
            colors: airColors,
            legend: { position: 'bottom' },
            tooltip: { y: { formatter: function(val){ return 'Rp ' + Number(val).toLocaleString('id-ID'); } } },
            dataLabels: { enabled: true, formatter: function (val, opts) { return Math.round(val) + '%'; } }
        };

        const el = document.querySelector('#airDonut_' + ak);
        if(el) { try { new ApexCharts(el, opts).render(); } catch(e) { console.error('airDonut error:', ak, e); } }
    });

    // Equipment Donut Charts (Proyek / Sparepark / Maintenance / Lainnya per wilayah)
    const equipmentWilayah = <?php echo json_encode($segmentWilayahData['equipment'] ?? [], 15, 512) ?>;
    const equipmentKeys = ['wilayah_1','wilayah_2','wilayah_3','wilayah_4'];
    const equipmentColors = ['#8b5cf6', '#a78bfa', '#7c3aed', '#6d28d9'];

    equipmentKeys.forEach(function(ek){
        const d = equipmentWilayah[ek] || {};
        const proyek = d.proyek || 0;
        const sparepark = d.sparepark || 0;
        const maintenance = d.maintenance || 0;
        const lainnya = d.lainnya || 0;

        const opts = {
            chart: { type: 'donut', height: 200 },
            series: [proyek, sparepark, maintenance, lainnya],
            labels: ['Proyek','Sparepark','Maintenance','Lainnya'],
            colors: equipmentColors,
            legend: { position: 'bottom' },
            tooltip: { y: { formatter: function(val){ return 'Rp ' + Number(val).toLocaleString('id-ID'); } } },
            dataLabels: { enabled: true, formatter: function (val, opts) { return Math.round(val) + '%'; } }
        };

        const el = document.querySelector('#equipmentDonut_' + ek);
        if(el) { try { new ApexCharts(el, opts).render(); } catch(e) { console.error('equipmentDonut error:', ek, e); } }
    });

    // Listrik Bar Chart (Distribusi per Wilayah)
    const listrikDataObj = <?php echo json_encode($segmentWilayahData['listrik'] ?? [], 15, 512) ?>;
    const listrikSeries = [
        listrikDataObj.wilayah_1 ?? 0,
        listrikDataObj.wilayah_2 ?? 0,
        listrikDataObj.wilayah_3 ?? 0,
        listrikDataObj.wilayah_4 ?? 0
    ];

    const listrikOptions = {
        chart: { type: 'bar', height: 260 },
        series: [{ name: 'Listrik', data: listrikSeries }],
        xaxis: { categories: ['Wilayah 1','Wilayah 2','Wilayah 3','Wilayah 4'] },
        colors: ['#ef4444'],
        yaxis: { labels: { formatter: function(val){ return 'Rp ' + (val / 1000000).toFixed(0) + 'M'; } } },
        tooltip: { y: { formatter: function(val){ return 'Rp ' + Number(val).toLocaleString('id-ID'); } } },
        plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
        dataLabels: { enabled: false }
    };

    try { new ApexCharts(document.querySelector('#listrikBarChart'), listrikOptions).render(); } catch(e) { console.error('listrikBarChart error:', e); }

    // Equipment Bar Chart (Distribusi per Wilayah)
    const equipmentDataObj = <?php echo json_encode($segmentWilayahData['equipment'] ?? [], 15, 512) ?>;
    const equipmentSeries = [
        (equipmentDataObj.wilayah_1 && equipmentDataObj.wilayah_1.total) ? equipmentDataObj.wilayah_1.total : (equipmentDataObj.wilayah_1 || 0),
        (equipmentDataObj.wilayah_2 && equipmentDataObj.wilayah_2.total) ? equipmentDataObj.wilayah_2.total : (equipmentDataObj.wilayah_2 || 0),
        (equipmentDataObj.wilayah_3 && equipmentDataObj.wilayah_3.total) ? equipmentDataObj.wilayah_3.total : (equipmentDataObj.wilayah_3 || 0),
        (equipmentDataObj.wilayah_4 && equipmentDataObj.wilayah_4.total) ? equipmentDataObj.wilayah_4.total : (equipmentDataObj.wilayah_4 || 0)
    ];

    const equipmentOptions = {
        chart: { type: 'bar', height: 260 },
        series: [{ name: 'Equipment', data: equipmentSeries }],
        xaxis: { categories: ['Wilayah 1','Wilayah 2','Wilayah 3','Wilayah 4'] },
        colors: ['#8b5cf6'],
        yaxis: { labels: { formatter: function(val){ return 'Rp ' + (val / 1000000).toFixed(0) + 'M'; } } },
        tooltip: { y: { formatter: function(val){ return 'Rp ' + Number(val).toLocaleString('id-ID'); } } },
        plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
        dataLabels: { enabled: false }
    };

    try { new ApexCharts(document.querySelector('#equipmentBarChart'), equipmentOptions).render(); } catch(e) { console.error('equipmentBarChart error:', e); }
});

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
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            showGlobalLoading('Memproses permintaan...');
        });
    });
});

// =============================================
// DETAIL CABANG PER WILAYAH - DUMMY DATA
// =============================================
const branchDummyData = {
    0: {
        name: 'Wilayah 1',
        color: '#667eea',
        branches: [
            {
                name: 'Belawan',
                icon: 'bi-building',
                marine:    4800000000,
                bbm:       1200000000,
                air:        350000000,
                listrik:    280000000,
                equipment:  920000000
            },
            {
                name: 'Tanjung Balai Karimun',
                icon: 'bi-building',
                marine:    3100000000,
                bbm:        850000000,
                air:        210000000,
                listrik:    190000000,
                equipment:  560000000
            }
        ]
    },
    1: {
        name: 'Wilayah 2',
        color: '#f093fb',
        branches: [
            {
                name: 'Tanjung Priok',
                icon: 'bi-building',
                marine:    8500000000,
                bbm:       2100000000,
                air:        650000000,
                listrik:    480000000,
                equipment: 1450000000
            },
            {
                name: 'Banten',
                icon: 'bi-building',
                marine:    3200000000,
                bbm:        780000000,
                air:        230000000,
                listrik:    175000000,
                equipment:  610000000
            }
        ]
    },
    2: {
        name: 'Wilayah 3',
        color: '#4facfe',
        branches: [
            {
                name: 'Tanjung Perak',
                icon: 'bi-building',
                marine:    6200000000,
                bbm:       1650000000,
                air:        480000000,
                listrik:    360000000,
                equipment: 1100000000
            },
            {
                name: 'Tanjung Emas',
                icon: 'bi-building',
                marine:    2800000000,
                bbm:        720000000,
                air:        190000000,
                listrik:    145000000,
                equipment:  480000000
            }
        ]
    },
    3: {
        name: 'Wilayah 4',
        color: '#43e97b',
        branches: [
            {
                name: 'Balikpapan',
                icon: 'bi-building',
                marine:    5100000000,
                bbm:       1380000000,
                air:        390000000,
                listrik:    295000000,
                equipment:  870000000
            },
            {
                name: 'Makassar',
                icon: 'bi-building',
                marine:    4200000000,
                bbm:       1050000000,
                air:        310000000,
                listrik:    235000000,
                equipment:  720000000
            }
        ]
    }
};

const segmentMeta = {
    marine:    { label: 'Marine',    color: '#3b82f6', icon: 'bi-water' },
    bbm:       { label: 'BBM',       color: '#f59e0b', icon: 'bi-fuel-pump-fill' },
    air:       { label: 'Air',       color: '#06b6d4', icon: 'bi-droplet-fill' },
    listrik:   { label: 'Listrik',   color: '#ef4444', icon: 'bi-plug-fill' },
    equipment: { label: 'Equipment', color: '#8b5cf6', icon: 'bi-tools' }
};

// Track rendered branch charts in modal so we can destroy before re-rendering
let branchChartInstances = [];

function showWilayahDetail(wilayahIndex) {
    console.log('showWilayahDetail called', wilayahIndex);
    const wilayah = branchDummyData[wilayahIndex];
    if (!wilayah) return;

    // Destroy any previously rendered charts
    branchChartInstances.forEach(c => { try { c.destroy(); } catch(e) {} });
    branchChartInstances = [];

    // Update modal header color
    document.getElementById('modalDetailHeader').style.background =
        'linear-gradient(135deg, ' + wilayah.color + ', ' + wilayah.color + 'aa)';
    document.getElementById('modalWilayahTitle').textContent =
        'Detail Cabang – ' + wilayah.name;

    // Build HTML for modal body
    const fmt = v => 'Rp ' + Number(v).toLocaleString('id-ID');
    const fmtM = v => 'Rp ' + (v / 1000000000).toFixed(2) + ' M';
    const segments = ['marine', 'bbm', 'air', 'listrik', 'equipment'];

    let html = '';

    // Summary total cards for each branch
    html += '<div class="row g-3 mb-4">';
    wilayah.branches.forEach((branch, bi) => {
        const total = segments.reduce((s, seg) => s + (branch[seg] || 0), 0);
        html += `
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid ${wilayah.color} !important; border-radius:12px;">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge rounded-pill me-2" style="background:${wilayah.color};font-size:0.85rem">${bi + 1}</span>
                            <h6 class="mb-0 fw-bold"><i class="bi bi-building me-1"></i>${branch.name}</h6>
                        </div>
                        <div class="text-center mb-2">
                            <div style="font-size:0.78rem;color:#6b7280;">Total Pendapatan</div>
                            <div style="font-size:1.6rem;font-weight:700;color:${wilayah.color}">${fmtM(total)}</div>
                        </div>
                        <div id="branchDonut_${wilayahIndex}_${bi}"></div>
                        <div class="mt-3">
                            <table class="table table-sm mb-0" style="font-size:0.82rem">
                                <tbody>
                                    ${segments.map(seg => `
                                    <tr>
                                        <td><i class="${segmentMeta[seg].icon}" style="color:${segmentMeta[seg].color}"></i> ${segmentMeta[seg].label}</td>
                                        <td class="text-end fw-semibold">${fmt(branch[seg] || 0)}</td>
                                    </tr>`).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>`;
    });
    html += '</div>';

    // Comparison bar chart across branches per segment
    html += `
        <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart-fill me-1"></i>Perbandingan Antar Cabang per Segmen – ${wilayah.name}</h6>
                <div id="branchCompareChart_${wilayahIndex}"></div>
            </div>
        </div>`;

    const modalBodyEl = document.getElementById('modalDetailBody');
    if (!modalBodyEl) {
        console.error('modalDetailBody element not found');
        return;
    }
    modalBodyEl.innerHTML = html;

    // Show modal FIRST so ApexCharts can measure container size
    const modalEl = document.getElementById('modalDetailCabang');
    // Backwards-compatible modal instance (Bootstrap version differences)
    const modal = (bootstrap.Modal && typeof bootstrap.Modal.getOrCreateInstance === 'function')
        ? bootstrap.Modal.getOrCreateInstance(modalEl)
        : (bootstrap.Modal && typeof bootstrap.Modal.getOrCreate === 'function')
            ? bootstrap.Modal.getOrCreate(modalEl)
            : new bootstrap.Modal(modalEl);
    modal.show();

    // Wait for modal DOM paint then render charts
    modalEl.addEventListener('shown.bs.modal', function onShown() {
        modalEl.removeEventListener('shown.bs.modal', onShown);

        // Render donut per branch
        wilayah.branches.forEach((branch, bi) => {
            const elId = '#branchDonut_' + wilayahIndex + '_' + bi;
            const el = document.querySelector(elId);
            if (!el) return;

            const seriesData  = segments.map(seg => branch[seg] || 0);
            const seriesLabels = segments.map(seg => segmentMeta[seg].label);
            const seriesColors = segments.map(seg => segmentMeta[seg].color);

            const chart = new ApexCharts(el, {
                chart: { type: 'donut', height: 200 },
                series: seriesData,
                labels: seriesLabels,
                colors: seriesColors,
                legend: { position: 'bottom', fontSize: '11px' },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) { return Math.round(val) + '%'; }
                },
                tooltip: {
                    y: { formatter: v => 'Rp ' + Number(v).toLocaleString('id-ID') }
                },
                plotOptions: { pie: { donut: { size: '60%' } } }
            });
            chart.render();
            branchChartInstances.push(chart);
        });

        // Render grouped comparison bar chart
        const compareEl = document.querySelector('#branchCompareChart_' + wilayahIndex);
        if (compareEl) {
            const branchNames = wilayah.branches.map(b => b.name);
            const compareSeries = segments.map(seg => ({
                name: segmentMeta[seg].label,
                data: wilayah.branches.map(b => b[seg] || 0)
            }));

            const compareChart = new ApexCharts(compareEl, {
                chart: { type: 'bar', height: 280, toolbar: { show: false } },
                series: compareSeries,
                xaxis: { categories: branchNames },
                colors: segments.map(seg => segmentMeta[seg].color),
                plotOptions: {
                    bar: { horizontal: false, borderRadius: 4, columnWidth: '60%' }
                },
                dataLabels: { enabled: false },
                yaxis: {
                    labels: {
                        formatter: v => 'Rp ' + (v / 1000000000).toFixed(1) + 'M'
                    }
                },
                legend: { position: 'top' },
                tooltip: {
                    y: { formatter: v => 'Rp ' + Number(v).toLocaleString('id-ID') }
                }
            });
            compareChart.render();
            branchChartInstances.push(compareChart);
        }
    });
}
</script>

</body>
</html>
<?php /**PATH D:\project ai\lhgk\resources\views/summary.blade.php ENDPATH**/ ?>