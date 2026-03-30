<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Kunjungan Kapal</title>
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
            --accent-pink: #f093fb;
            --accent-green: #8bea66;
            --accent-cyan: #66dfea;
            --accent-teal: #10b981;
            --accent-blue: #3b82f6;
            --accent-orange: #ff7f50;
            --accent-indigo: #667eea;
            --accent-amber: #f59e0b;
        }
        .stat-card {
            border-radius: var(--card-radius);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        .pilot-card {
            background: linear-gradient(135deg, #e8eaf6 0%, #c5cae9 100%);
            color: #3f51b5;
        }
        .tunda-card {
            background: linear-gradient(135deg, #fce4ec 0%, #f8bbd0 100%);
            color: #c2185b;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .ship-type-badge {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            margin: 2px;
            display: inline-block;
        }
        .ship-types-container {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        .grt-gerak-badge {
            background-color: #e3f2fd;
            color: #1976d2;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-block;
        }
        .via-badge {
            background-color: #f3e5f5;
            color: #7b1fa2;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            margin: 2px;
        }
        /* Emphasize numeric value inside via-badges for Realisasi cards */
        .via-badge strong {
            font-size: 1.2rem;
            font-weight: 600;
            color: #111827;
            display: inline-block;
            margin-left: 6px;
            line-height: 1;
        }
        .via-mobile {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .via-web {
            background-color: #fff3e0;
            color: #e65100;
        }
        .period-filter {
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        /* Tidy combined cards and speedometer */
        .combined-card .h4, .combined-card h4, .combined-card h5 { margin: 0; }
        .combined-card .text-muted { font-size: 0.85rem; }
        .nota-summary h4 { font-weight: 700; }
        .nota-summary h5 { font-weight: 600; }
        .nota-summary .col-6 { padding-bottom: 8px; }
        /* Minimal donut layout */
        .nota-summary { display:flex; align-items:center; justify-content:center; flex-direction:column; }
        .nota-summary .card-body { padding-left: 0; padding-right: 1rem; }
        .nota-summary .col-12.col-md-4 { padding-left: 0; }
        .nota-summary .title-shift { margin-left: -1.6rem; }
        @media (min-width: 768px) {
            .nota-summary.match-height, .speedometer-card.match-height { min-height: 260px; }
        }
        .nota-summary .nota-canvas-wrap { width:160px; max-width:40%; }
        .speedometer-wrap { display:flex; align-items:center; justify-content:center; flex-direction:column; }
        #speedometerChart { display:block; }
        /* Accent classes to replace inline border-left styles */
        .card-accent--pink { border-left: 4px solid var(--accent-pink); }
        .card-accent--green { border-left: 4px solid var(--accent-green); }
        .card-accent--cyan { border-left: 4px solid var(--accent-cyan); }
        .card-accent--teal { border-left: 4px solid var(--accent-teal); }
        .card-accent--blue { border-left: 4px solid var(--accent-blue); }
        .card-accent--orange { border-left: 4px solid var(--accent-orange); }
        .card-accent--indigo { border-left: 4px solid var(--accent-indigo); }
        .card-accent--amber { border-left: 4px solid var(--accent-amber); }

        /* Metric value utility */
        /* Reduced ~2pt from 1.8rem (≈28.8px) to ~1.63rem (≈26.1px) */
        .metric-value { font-size: 1.63rem; font-weight: 700; line-height: 1; }
        @media (max-width: 767px) {
            .nota-summary .col-6 { flex: 0 0 50%; max-width: 50%; }
            #speedometerChart { display:block; }
            .combined-card .h4 { font-size: 1.1rem; }
        }
        /* Status Nota table tweaks */
        .status-nota-table th { vertical-align: middle; text-transform: none; font-weight: 700; }
        .status-nota-table td { vertical-align: middle; }
        .status-nota-table .ukk-badge { background: #6c757d; color: #fff; padding: .35rem .6rem; border-radius: .35rem; font-size: .8rem; }
        .status-nota-table .nm-kapal { max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .status-nota-table .selisih-badge.bg-success { background-color: #10b981 !important; }
        .status-nota-table .selisih-badge.bg-danger { background-color: #ef4444 !important; }
        .status-nota-table .selisih-badge.bg-secondary { background-color: #6c757d !important; }
    </style>
    <style>
        /* Navbar fine-tuning to match Dashboard appearance */
        .navbar {
            padding: .45rem 1rem;
        }
        /* Explicit dashboard-style blue gradient for trafik header */
        .navbar-dashboard {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }
        .navbar-dashboard .navbar-brand { color: #fff !important; }
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
    </style>
</head>
<body>

<div class="container-fluid px-0">
    <div class="px-4">

<!-- Global Loading Overlay (copied from dashboard) -->
<div id="globalLoading" class="global-loading-overlay">
    <div class="global-loading-content">
        <div class="global-loading-spinner"></div>
        <p class="global-loading-text" id="loadingText">Memproses data...</p>
    </div>
    </div>
</div>

<!-- HEADER -->
<nav class="navbar navbar-dark navbar-dashboard mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1"><i class="bi bi-graph-up-arrow"></i> Trafik</span>
        <div>
            <a href="<?php echo e(route('dashboard.operasional')); ?>" class="btn btn-light btn-sm me-2">
                <i class="bi bi-kanban-fill"></i> Dashboard Operasional
            </a>
            <a href="<?php echo e(route('monitoring.nota')); ?>" class="btn btn-light btn-sm me-2">
                <i class="bi bi-file-earmark-text"></i> Monitoring Nota
            </a>
            <a href="<?php echo e(route('regional.revenue')); ?>" class="btn btn-light btn-sm me-2">
                <i class="bi bi-geo-alt"></i> Pendapatan Wilayah
            </a>
            <a href="<?php echo e(url('regional-sharing')); ?>" class="btn btn-light btn-sm me-2">
                <i class="bi bi-people-fill"></i> Revenue Sharing
            </a>
            <a href="<?php echo e(route('analisis.kelelahan')); ?>" class="btn btn-light btn-sm">
                <i class="bi bi-activity"></i> Analisis Kelelahan
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="period-filter">
                <form id="dashboardFilterForm" method="GET" action="" class="row align-items-center">
                    <div class="col-md-2">
                        <label class="form-label"><i class="bi bi-funnel"></i> Filter:</label>
                    </div>
                    <!-- Wilayah filter hidden - displaying all regions -->
                    <input type="hidden" name="wilayah" value="all">
                    <div class="col-md-2">
                        <label class="form-label"><i class="bi bi-calendar-check"></i> Periode:</label>
                        <div class="d-flex">
                            <select name="periode" id="periodeFilter" class="form-select filter-input" style="width:160px">
                                <option value="all">-- Semua Periode --</option>
                                <?php if(!empty($periods) && count($periods) > 0): ?>
                                    <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($period); ?>" <?php echo e(request('periode') == $period ? 'selected' : ''); ?>><?php echo e($period); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <option value="01-2026">01-2026</option>
                                    <option value="12-2025">12-2025</option>
                                    <option value="11-2025">11-2025</option>
                                <?php endif; ?>
                            </select>

                            <button type="submit" id="btnApplyFilters" class="btn btn-sm btn-primary ms-2" title="Apply filters">
                                <i class="bi bi-funnel-fill"></i> Apply
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex justify-content-end align-items-center">
                        <div>
                            <a href="<?php echo e(url()->current()); ?>" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                        </div>
                        <div class="btn-toolbar" role="toolbar" aria-label="Trafik actions">
                            <div class="btn-group" role="group" aria-label="Export and refresh">
                                <button type="button" id="btnDownloadPdf" class="btn btn-sm btn-outline-primary" title="Download current page as PDF">
                                    <i class="bi bi-download"></i> Download
                                </button>
                                <button type="button" id="btnRefresh" class="btn btn-sm btn-outline-secondary" title="Refresh">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <div class="ms-2">
                                <button id="btnExportExcel" type="button" class="btn btn-sm btn-outline-secondary">Export Excel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- KPI CARDS -->
<div class="row mb-4">
    <?php
        // Use controller-provided KPI values from filtered query
        $trafik_call = $totalCall ?? 0;
        $trafik_gt = $totalGt ?? 0;
        // Use new production data from prod_rkap_realisasi table
        // $produksi_penundaan and $produksi_pemanduan already passed from controller

        // Pendapatan fallback (not in trafik_rkap_realisasi, using 0 for now)
        $pendapatan = 0;

        // YTD logic variables for global use
        $periodeParts = explode('-', $selectedPeriode ?? 'all');
        $isYTD = $selectedPeriode != 'all' && count($periodeParts) === 2 && intval(ltrim($periodeParts[0], '0')) > 1;
    ?>

    <div class="col-md-3">
        <div class="stat-card p-3 card-accent--indigo">
            <div class="kpi-title">
                Trafik
                <?php if($isYTD): ?>
                    <small class="text-info d-block" style="font-size: 0.65rem; font-weight: 400;">
                        <i class="bi bi-calendar-range"></i> YTD (Jan-<?php echo e($periodeParts[0]); ?>/<?php echo e($periodeParts[1]); ?>)
                    </small>
                <?php endif; ?>
            </div>
            <h4 class="metric-value">Call: <?php echo e(number_format($trafik_call)); ?></h4>
            <h4 class="metric-value text-primary">GT: <?php echo e(number_format($trafik_gt)); ?></h4>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card p-3 card-accent--orange">
            <div class="kpi-title">
                Produksi Penundaan
                <?php if($isYTD): ?>
                    <small class="text-info d-block" style="font-size: 0.65rem; font-weight: 400;">
                        <i class="bi bi-calendar-range"></i> YTD (Jan-<?php echo e($periodeParts[0]); ?>/<?php echo e($periodeParts[1]); ?>)
                    </small>
                <?php endif; ?>
            </div>
            <h4 class="metric-value"><?php echo e(number_format($produksi_penundaan)); ?></h4>
            <p class="mb-0 text-muted small">GT / Jam</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card p-3 card-accent--green">
            <div class="kpi-title">
                Produksi Pemanduan
                <?php if($isYTD): ?>
                    <small class="text-info d-block" style="font-size: 0.65rem; font-weight: 400;">
                        <i class="bi bi-calendar-range"></i> YTD (Jan-<?php echo e($periodeParts[0]); ?>/<?php echo e($periodeParts[1]); ?>)
                    </small>
                <?php endif; ?>
            </div>
            <h4 class="metric-value"><?php echo e(number_format($produksi_pemanduan)); ?></h4>
            <p class="mb-0 text-muted small">GT / Grk</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card p-3 card-accent--teal">
            <div class="kpi-title">Pendapatan</div>
            <h4 class="metric-value">Rp <?php echo e(number_format($pendapatan, 0, ',', '.')); ?></h4>
            <p class="mb-0 text-muted small">Total Pendapatan</p>
        </div>
    </div>
</div>

<!-- COMPARISON: REALISASI vs ANGGARAN -->
<div class="row mb-4">
    <div class="col-12">
        <h6>Perbandingan Realisasi vs Anggaran</h6>
    </div>

    <?php
        // Use values from controller (trafik_rkap_realisasi table)
        $real_call = $comparisonRealCall ?? 0;
        $real_gt = $comparisonRealGt ?? 0;
        $budget_call = $comparisonBudgetCall ?? 0;
        $budget_gt = $comparisonBudgetGt ?? 0;

        // Use new production data from prod_rkap_realisasi table (already passed from controller)
        // $real_pemanduan, $real_penundaan, $budget_pemanduan, $budget_penundaan, $pct_pemanduan, $pct_penundaan already available

        // Percent helpers
        $pct = function($r, $b) {
            if($b > 0) return round(($r / $b) * 100, 1);
            return $r > 0 ? 100 : 0;
        };

        // Indicator helpers (arrow icon and color)
        $indicator = function($r, $b) {
            if($r >= $b) {
                return '<i class="bi bi-arrow-up-circle-fill" style="color: #10b981; font-size: 1.2rem;"></i>';
            } else {
                return '<i class="bi bi-arrow-down-circle-fill" style="color: #ef4444; font-size: 1.2rem;"></i>';
            }
        };

        // Only calculate for call and GT (penundaan and pemanduan handled in controller)
        $pct_call = $pct($real_call, $budget_call);
        $pct_gt = $pct($real_gt, $budget_gt);
        
        $icon_call = $indicator($real_call, $budget_call);
        $icon_gt = $indicator($real_gt, $budget_gt);
    ?>

    <div class="col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div><strong>Call</strong> <?php echo $icon_call; ?></div>
                <small><?php echo e($pct_call); ?>%</small>
            </div>
            <div class="mb-2"><small class="text-muted">Realisasi: <?php echo e(number_format($real_call)); ?> — Anggaran: <?php echo e(number_format($budget_call)); ?></small></div>
            <div class="progress" style="height:10px">
                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo e(min(100,$pct_call)); ?>%;" aria-valuenow="<?php echo e($pct_call); ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div><strong>GT</strong> <?php echo $icon_gt; ?></div>
                <small><?php echo e($pct_gt); ?>%</small>
            </div>
            <div class="mb-2"><small class="text-muted">Realisasi: <?php echo e(number_format($real_gt)); ?> — Anggaran: <?php echo e(number_format($budget_gt)); ?></small></div>
            <div class="progress" style="height:10px">
                <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo e(min(100,$pct_gt)); ?>%;" aria-valuenow="<?php echo e($pct_gt); ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div><strong>Produksi Pemanduan</strong> <?php echo $icon_pemanduan; ?></div>
                <small><?php echo e($pct_pemanduan); ?>%</small>
            </div>
            <div class="mb-2"><small class="text-muted">Realisasi: <?php echo e(number_format($real_pemanduan)); ?> — Anggaran: <?php echo e(number_format($budget_pemanduan)); ?></small></div>
            <div class="progress" style="height:10px">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo e(min(100,$pct_pemanduan)); ?>%;" aria-valuenow="<?php echo e($pct_pemanduan); ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div><strong>Produksi Penundaan</strong> <?php echo $icon_penundaan; ?></div>
                <small><?php echo e($pct_penundaan); ?>%</small>
            </div>
            <div class="mb-2"><small class="text-muted">Realisasi: <?php echo e(number_format($real_penundaan)); ?> — Anggaran: <?php echo e(number_format($budget_penundaan)); ?></small></div>
            <div class="progress" style="height:10px">
                <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo e(min(100,$pct_penundaan)); ?>%;" aria-valuenow="<?php echo e($pct_penundaan); ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>

</div>

<!-- BREAKDOWN PER WILAYAH -->
<div class="row mb-4">
    <style>
        .breakdown-table {
            border: 1px solid #e5e7eb;
        }
        .breakdown-table thead th {
            font-weight: 600;
            font-size: 0.875rem;
            padding: 12px 10px;
            vertical-align: middle;
            border-bottom: 2px solid #d1d5db;
        }
        .breakdown-table .header-wilayah { background-color: #374151; color: white; }
        .breakdown-table .header-last-year { background-color: #7c3aed; color: white; }
        .breakdown-table .header-budget { background-color: #2563eb; color: white; }
        .breakdown-table .header-realisasi { background-color: #059669; color: white; }
        .breakdown-table .header-trend { background-color: #dc2626; color: white; }
        .breakdown-table .header-yoy { background-color: #0891b2; color: white; }
        .breakdown-table tbody tr { transition: background-color 0.15s ease; }
        .breakdown-table tbody tr:hover { background-color: #f9fafb; }
        .breakdown-table tbody td { padding: 10px; vertical-align: middle; }
        .breakdown-table .col-last-year { background-color: #faf5ff; }
        .breakdown-table .col-budget { background-color: #eff6ff; }
        .breakdown-table .col-realisasi { background-color: #f0fdf4; }
        .breakdown-table .col-yoy { background-color: #ecfeff; }
        .breakdown-table .wilayah-cell { background-color: #f9fafb; font-weight: 600; color: #1f2937; border-left: 3px solid #374151; }
    </style>

    <div class="col-lg-6">
        <div class="stat-card p-3">
            <h6 class="mb-3">
                <i class="bi bi-geo-alt-fill"></i> Breakdown Per Wilayah
                <?php if($isYTD): ?>
                    <small class="text-info d-block mt-1" style="font-size: 0.75rem;">
                        <i class="bi bi-calendar-range"></i> Data Year-to-Date (Kumulatif Jan - <?php echo e($periodeParts[0]); ?>/<?php echo e($periodeParts[1]); ?>)
                    </small>
                <?php endif; ?>
            </h6>
            <?php if(!empty($trafikData) && count($trafikData) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0 breakdown-table">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle text-center header-wilayah">Wilayah</th>
                                <th rowspan="2" class="align-middle text-center" style="background-color: #f3f4f6; font-weight: 600; width: 80px;">Satuan</th>
                                <th class="text-center header-last-year">
                                    Realisasi Tahun Lalu
                                    <?php if($isYTD): ?>
                                        <br><small class="text-muted">(YTD)</small>
                                    <?php endif; ?>
                                </th>
                                <th class="text-center header-budget">
                                    Anggaran
                                    <?php if($isYTD): ?>
                                        <br><small class="text-muted">(YTD)</small>
                                    <?php endif; ?>
                                </th>
                                <th class="text-center header-realisasi">
                                    Realisasi
                                    <?php if($isYTD): ?>
                                        <br><small class="text-muted">(YTD)</small>
                                    <?php endif; ?>
                                </th>
                                <th class="text-center header-trend">Trend (Realisasi vs Anggaran)</th>
                                <th class="text-center header-yoy">YoY Growth</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $trafikData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wilayah => $wilData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $realCall = $wilData['realisasi_call'] ?? 0;
                                    $realGt = $wilData['realisasi_gt'] ?? 0;
                                    $budgetCall = $wilData['anggaran_call'] ?? 0;
                                    $budgetGt = $wilData['anggaran_gt'] ?? 0;
                                    $lastYearCall = $wilData['last_year_call'] ?? 0;
                                    $lastYearGt = $wilData['last_year_gt'] ?? 0;

                                    $trendCall = $realCall - $budgetCall;
                                    $trendGt = $realGt - $budgetGt;
                                    $trendCallPct = $budgetCall > 0 ? round(($trendCall / $budgetCall) * 100, 1) : 0;
                                    $trendGtPct = $budgetGt > 0 ? round(($trendGt / $budgetGt) * 100, 1) : 0;

                                    $callClass = $trendCall >= 0 ? 'text-success' : 'text-danger';
                                    $gtClass = $trendGt >= 0 ? 'text-success' : 'text-danger';
                                    $callIcon = $trendCall > 0 ? '▲' : ($trendCall < 0 ? '▼' : '−');
                                    $gtIcon = $trendGt > 0 ? '▲' : ($trendGt < 0 ? '▼' : '−');

                                    $yoyCall = $realCall - $lastYearCall;
                                    $yoyGt = $realGt - $lastYearGt;
                                    $yoyCallPct = $lastYearCall > 0 ? round(($yoyCall / $lastYearCall) * 100, 1) : 0;
                                    $yoyGtPct = $lastYearGt > 0 ? round(($yoyGt / $lastYearGt) * 100, 1) : 0;

                                    $yoyCallClass = $yoyCall >= 0 ? 'text-success' : 'text-danger';
                                    $yoyGtClass = $yoyGt >= 0 ? 'text-success' : 'text-danger';
                                    $yoyCallIcon = $yoyCall > 0 ? '▲' : ($yoyCall < 0 ? '▼' : '−');
                                    $yoyGtIcon = $yoyGt > 0 ? '▲' : ($yoyGt < 0 ? '▼' : '−');
                                ?>
                                <tr>
                                    <td rowspan="2" class="align-middle wilayah-cell"><?php echo e(strtoupper($wilayah)); ?></td>
                                    <td class="text-center" style="background-color: #f9fafb; font-weight: 500;">Call</td>
                                    <td class="text-end col-last-year"><?php if($lastYearCall > 0): ?> <?php echo e(number_format($lastYearCall)); ?> <?php else: ?> <span class="text-muted">-</span> <?php endif; ?></td>
                                    <td class="text-end col-budget"><?php echo e(number_format($budgetCall)); ?></td>
                                    <td class="text-end col-realisasi" style="font-weight: 600;"><?php echo e(number_format($realCall)); ?></td>
                                    <td class="text-end <?php echo e($callClass); ?>" style="font-weight: 600;"><span style="font-size: 0.85rem;"><?php echo e($callIcon); ?></span> <?php echo e($trendCall >= 0 ? '+' : ''); ?><?php echo e(number_format($trendCall)); ?><div class="small" style="font-weight: 400;">(<?php echo e($trendCallPct >= 0 ? '+' : ''); ?><?php echo e($trendCallPct); ?>%)</div></td>
                                    <td class="text-end <?php echo e($yoyCallClass); ?> col-yoy" style="font-weight: 600;"><?php if($lastYearCall > 0): ?><span style="font-size: 0.85rem;"><?php echo e($yoyCallIcon); ?></span> <?php echo e($yoyCall >= 0 ? '+' : ''); ?><?php echo e(number_format($yoyCall)); ?><div class="small" style="font-weight: 400;">(<?php echo e($yoyCallPct >= 0 ? '+' : ''); ?><?php echo e($yoyCallPct); ?>%)</div><?php else: ?><span class="text-muted">N/A</span><?php endif; ?></td>
                                </tr>
                                <tr>
                                    <td class="text-center" style="background-color: #f9fafb; font-weight: 500;">GT</td>
                                    <td class="text-end col-last-year"><?php if($lastYearGt > 0): ?> <?php echo e(number_format($lastYearGt)); ?> <?php else: ?> <span class="text-muted">-</span> <?php endif; ?></td>
                                    <td class="text-end col-budget"><?php echo e(number_format($budgetGt)); ?></td>
                                    <td class="text-end col-realisasi" style="font-weight: 600;"><?php echo e(number_format($realGt)); ?></td>
                                    <td class="text-end <?php echo e($gtClass); ?>" style="font-weight: 600;"><span style="font-size: 0.85rem;"><?php echo e($gtIcon); ?></span> <?php echo e($trendGt >= 0 ? '+' : ''); ?><?php echo e(number_format($trendGt)); ?><div class="small" style="font-weight: 400;">(<?php echo e($trendGtPct >= 0 ? '+' : ''); ?><?php echo e($trendGtPct); ?>%)</div></td>
                                    <td class="text-end <?php echo e($yoyGtClass); ?> col-yoy" style="font-weight: 600;"><?php if($lastYearGt > 0): ?><span style="font-size: 0.85rem;"><?php echo e($yoyGtIcon); ?></span> <?php echo e($yoyGt >= 0 ? '+' : ''); ?><?php echo e(number_format($yoyGt)); ?><div class="small" style="font-weight: 400;">(<?php echo e($yoyGtPct >= 0 ? '+' : ''); ?><?php echo e($yoyGtPct); ?>%)</div><?php else: ?><span class="text-muted">N/A</span><?php endif; ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0"><i class="bi bi-info-circle"></i> Tidak ada data trafik untuk ditampilkan.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="stat-card p-3">
            <h6 class="mb-3">
                <i class="bi bi-table"></i> Data Produksi Pemanduan dan Penundaan
                <?php if($isYTD): ?>
                    <small class="text-info d-block mt-1" style="font-size: 0.75rem;">
                        <i class="bi bi-calendar-range"></i> Data Year-to-Date (Kumulatif Jan - <?php echo e($periodeParts[0]); ?>/<?php echo e($periodeParts[1]); ?>)
                    </small>
                <?php endif; ?>
            </h6>
            <?php if(!empty($rkapData) && count($rkapData) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0 breakdown-table">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle text-center header-wilayah">Wilayah</th>
                                <th rowspan="2" class="align-middle text-center" style="background-color: #f3f4f6; font-weight: 600; width: 120px;">Layanan</th>
                                <th rowspan="2" class="align-middle text-center" style="background-color: #f3f4f6; font-weight: 600; width: 100px;">Satuan</th>
                                <th class="text-center header-last-year">
                                    Realisasi Tahun Lalu
                                    <?php if($isYTD): ?>
                                        <br><small class="text-muted">(YTD)</small>
                                    <?php endif; ?>
                                </th>
                                <th class="text-center header-budget">
                                    Anggaran
                                    <?php if($isYTD): ?>
                                        <br><small class="text-muted">(YTD)</small>
                                    <?php endif; ?>
                                </th>
                                <th class="text-center header-realisasi">
                                    Realisasi
                                    <?php if($isYTD): ?>
                                        <br><small class="text-muted">(YTD)</small>
                                    <?php endif; ?>
                                </th>
                                <th class="text-center header-trend">Trend (Realisasi vs Anggaran)</th>
                                <th class="text-center header-yoy">YoY Growth</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $rkapData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wilayah => $layananData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $layananList = array_keys($layananData);
                                    $layananCount = count($layananList);
                                ?>
                                <?php $__currentLoopData = $layananList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $layanan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $data = $layananData[$layanan];
                                        $realisasi = $data['realisasi'] ?? 0;
                                        $anggaran = $data['anggaran'] ?? 0;
                                        $lastYear = $data['last_year'] ?? 0;
                                        $satuan = $data['satuan'] ?? 'GT'; // Use satuan from database

                                        $trend = $realisasi - $anggaran;
                                        $trendPct = $anggaran > 0 ? round(($trend / $anggaran) * 100, 1) : 0;

                                        $trendClass = $trend >= 0 ? 'text-success' : 'text-danger';
                                        $trendIcon = $trend > 0 ? '▲' : ($trend < 0 ? '▼' : '−');

                                        $yoy = $realisasi - $lastYear;
                                        $yoyPct = $lastYear > 0 ? round(($yoy / $lastYear) * 100, 1) : 0;

                                        $yoyClass = $yoy >= 0 ? 'text-success' : 'text-danger';
                                        $yoyIcon = $yoy > 0 ? '▲' : ($yoy < 0 ? '▼' : '−');
                                    ?>
                                    <tr>
                                        <?php if($index == 0): ?>
                                            <td rowspan="<?php echo e($layananCount); ?>" class="align-middle wilayah-cell"><?php echo e(strtoupper($wilayah)); ?></td>
                                        <?php endif; ?>
                                        <td class="text-center" style="background-color: #f9fafb; font-weight: 500;"><?php echo e(strtoupper($layanan)); ?></td>
                                        <td class="text-center" style="background-color: #f9fafb; font-weight: 500; color: #666;"><?php echo e($satuan); ?></td>
                                        <td class="text-end col-last-year"><?php if($lastYear > 0): ?> <?php echo e(number_format($lastYear)); ?> <?php else: ?> <span class="text-muted">-</span> <?php endif; ?></td>
                                        <td class="text-end col-budget"><?php echo e(number_format($anggaran)); ?></td>
                                        <td class="text-end col-realisasi" style="font-weight: 600;"><?php echo e(number_format($realisasi)); ?></td>
                                        <td class="text-end <?php echo e($trendClass); ?>" style="font-weight: 600;"><span style="font-size: 0.85rem;"><?php echo e($trendIcon); ?></span> <?php echo e($trend >= 0 ? '+' : ''); ?><?php echo e(number_format($trend)); ?><div class="small" style="font-weight: 400;">(<?php echo e($trendPct >= 0 ? '+' : ''); ?><?php echo e($trendPct); ?>%)</div></td>
                                        <td class="text-end <?php echo e($yoyClass); ?> col-yoy" style="font-weight: 600;"><?php if($lastYear > 0): ?><span style="font-size: 0.85rem;"><?php echo e($yoyIcon); ?></span> <?php echo e($yoy >= 0 ? '+' : ''); ?><?php echo e(number_format($yoy)); ?><div class="small" style="font-weight: 400;">(<?php echo e($yoyPct >= 0 ? '+' : ''); ?><?php echo e($yoyPct); ?>%)</div><?php else: ?><span class="text-muted">N/A</span><?php endif; ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0"><i class="bi bi-info-circle"></i> Tidak ada data satuan/layanan untuk ditampilkan.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- BAR CHART PER WILAYAH -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="stat-card p-3">
            <h6>
                <i class="bi bi-bar-chart-fill"></i> Perbandingan Call (Realisasi vs Anggaran)
                <?php if($isYTD): ?>
                    <small class="text-info d-block mt-1" style="font-size: 0.75rem;">
                        <i class="bi bi-calendar-range"></i> Data Year-to-Date (Jan - <?php echo e($periodeParts[0]); ?>/<?php echo e($periodeParts[1]); ?>)
                    </small>
                <?php endif; ?>
            </h6>
            <div id="wilayahBarChart"></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="stat-card p-3">
            <h6>
                <i class="bi bi-bar-chart-fill"></i> Perbandingan Produksi per Wilayah (Realisasi vs Anggaran)
                <?php if($isYTD): ?>
                    <small class="text-info d-block mt-1" style="font-size: 0.75rem;">
                        <i class="bi bi-calendar-range"></i> Data Year-to-Date (Jan - <?php echo e($periodeParts[0]); ?>/<?php echo e($periodeParts[1]); ?>)
                    </small>
                <?php endif; ?>
            </h6>
            <div id="produksiBarChart"></div>
        </div>
    </div>
</div>

</div>

<script>
<?php
    // Prepare data for wilayah bar chart
    $wilayah_labels = [];
    $wilayah_call_real = [];
    $wilayah_call_budget = [];
    $wilayah_gt_real = [];
    $wilayah_gt_budget = [];
    
    if (!empty($wilayahBarData)) {
        // Sort wilayah keys
        $sortedWilayah = array_keys($wilayahBarData);
        sort($sortedWilayah);
        
        foreach ($sortedWilayah as $wil) {
            $data = $wilayahBarData[$wil];
            $wilayah_labels[] = $wil;
            $wilayah_call_real[] = $data['call_realisasi'] ?? 0;
            $wilayah_call_budget[] = $data['call_anggaran'] ?? 0;
            $wilayah_gt_real[] = $data['gt_realisasi'] ?? 0;
            $wilayah_gt_budget[] = $data['gt_anggaran'] ?? 0;
        }
    } else {
        // Sample data if no real data
        $wilayah_labels = ['WILAYAH 1', 'WILAYAH 2', 'WILAYAH 3', 'WILAYAH 4'];
        $wilayah_call_real = [2500, 2700, 2500, 2700];
        $wilayah_call_budget = [2700, 2800, 2600, 2900];
        $wilayah_gt_real = [7000, 8120, 7500, 8120];
        $wilayah_gt_budget = [7000, 8620, 7000, 8620];
    }

    // Prepare data for produksi bar chart
    $produksi_labels = [];
    $produksi_penundaan_real = [];
    $produksi_penundaan_budget = [];
    $produksi_pemanduan_real = [];
    $produksi_pemanduan_budget = [];
    
    if (!empty($rkapData)) {
        // Sort wilayah keys
        $sortedProduksiWilayah = array_keys($rkapData);
        sort($sortedProduksiWilayah);
        
        foreach ($sortedProduksiWilayah as $wil) {
            $data = $rkapData[$wil];
            $produksi_labels[] = $wil;
            
            // PENUNDAAN data
            $produksi_penundaan_real[] = $data['PENUNDAAN']['realisasi'] ?? 0;
            $produksi_penundaan_budget[] = $data['PENUNDAAN']['anggaran'] ?? 0;
            
            // PEMANDUAN data
            $produksi_pemanduan_real[] = $data['PEMANDUAN']['realisasi'] ?? 0;
            $produksi_pemanduan_budget[] = $data['PEMANDUAN']['anggaran'] ?? 0;
        }
    } else {
        // Sample data if no real data
        $produksi_labels = ['WILAYAH 1', 'WILAYAH 2', 'WILAYAH 3', 'WILAYAH 4'];
        $produksi_penundaan_real = [150000000, 180000000, 145000000, 175000000];
        $produksi_penundaan_budget = [140000000, 170000000, 140000000, 165000000];
        $produksi_pemanduan_real = [120000000, 140000000, 135000000, 155000000];
        $produksi_pemanduan_budget = [125000000, 145000000, 130000000, 150000000];
    }
?>

document.addEventListener('DOMContentLoaded', function(){
    // Call & GT Chart
    const wilayahLabels = <?php echo json_encode($wilayah_labels, 15, 512) ?>;
    const callReal = <?php echo json_encode($wilayah_call_real, 15, 512) ?>;
    const callBudget = <?php echo json_encode($wilayah_call_budget, 15, 512) ?>;
    const gtReal = <?php echo json_encode($wilayah_gt_real, 15, 512) ?>;
    const gtBudget = <?php echo json_encode($wilayah_gt_budget, 15, 512) ?>;

    // Compute Call variance array (Realisasi - Anggaran)
    const callVariance = callReal.map((v, i) => (v || 0) - (callBudget[i] || 0));

    const varianceOptions = {
        chart: {
            type: 'bar',
            height: 420,
            toolbar: { show: true }
        },
        series: [
            { name: 'Call Variance', data: callVariance }
        ],
        xaxis: {
            categories: wilayahLabels,
            title: { text: 'Wilayah' }
        },
        yaxis: {
            title: { text: 'Variance (Realisasi - Anggaran)' },
            labels: {
                formatter: function(val){ return Math.round(val).toLocaleString(); }
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '60%'
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                if (val === null || val === undefined) return '';
                const n = Math.round(val).toLocaleString();
                return (val > 0 ? '+' : '') + n;
            },
            offsetY: -6,
            style: { fontSize: '11px', colors: ['#ffffff'] }
        },
        stroke: { show: true, width: 1, colors: ['transparent'] },
        legend: { position: 'top', horizontalAlign: 'center' },
        tooltip: {
            shared: true,
            intersect: false,
            y: { formatter: function(val){ return (val >= 0 ? '+' : '') + val.toLocaleString(); } }
        },
        // color function to color positive/negative bars (Call only)
        colors: [
            function({ value }) { return value >= 0 ? '#10b981' : '#ef4444'; }
        ]
    };

    new ApexCharts(document.querySelector('#wilayahBarChart'), varianceOptions).render();

    // Production Chart
    const produksiLabels = <?php echo json_encode($produksi_labels, 15, 512) ?>;
    const penundaanReal = <?php echo json_encode($produksi_penundaan_real, 15, 512) ?>;
    const penundaanBudget = <?php echo json_encode($produksi_penundaan_budget, 15, 512) ?>;
    const pemanduanReal = <?php echo json_encode($produksi_pemanduan_real, 15, 512) ?>;
    const pemanduanBudget = <?php echo json_encode($produksi_pemanduan_budget, 15, 512) ?>;

    const produksiBarOptions = {
        chart: { 
            type: 'bar', 
            height: 400,
            toolbar: { show: true }
        },
        series: [
            { name: 'Penundaan Realisasi', data: penundaanReal },
            { name: 'Penundaan Anggaran', data: penundaanBudget },
            { name: 'Pemanduan Realisasi', data: pemanduanReal },
            { name: 'Pemanduan Anggaran', data: pemanduanBudget }
        ],
        xaxis: { 
            categories: produksiLabels,
            title: { text: 'Wilayah' }
        },
        yaxis: {
            title: { text: 'Nilai Produksi' },
            labels: { 
                formatter: function(val){ 
                    return (val / 1000000).toFixed(0) + 'M'; 
                } 
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '70%',
                dataLabels: {
                    position: 'top'
                }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                return val > 0 ? (val / 1000000).toFixed(0) + 'M' : '';
            },
            offsetY: -20,
            style: {
                fontSize: '10px',
                colors: ['#304758']
            }
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        legend: {
            position: 'top',
            horizontalAlign: 'center'
        },
        tooltip: {
            shared: true,
            intersect: false,
            y: { 
                formatter: function(val){ 
                    return 'Rp ' + val.toLocaleString(); 
                } 
            }
        },
        colors: ['#f59e0b', '#fbbf24', '#8b5cf6', '#a78bfa']
    };

    new ApexCharts(document.querySelector('#produksiBarChart'), produksiBarOptions).render();
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const downloadBtn = document.getElementById('btnDownloadPdf');
    const refreshBtn = document.getElementById('btnRefresh');
    const applyBtn = document.getElementById('btnApplyFilters');
    const form = document.getElementById('dashboardFilterForm');

    if(downloadBtn){
        downloadBtn.addEventListener('click', function(){
            try{
                const params = new URLSearchParams(window.location.search);
                params.set('preview_print', '1');
                const url = window.location.pathname + '?' + params.toString();
                const w = window.open(url, '_blank');
                if(w) w.focus();
            }catch(e){
                console.error(e);
                alert('Gagal menyiapkan download PDF');
            }
        });
    }

    if(refreshBtn){
        refreshBtn.addEventListener('click', function(){ window.location.reload(); });
    }

    const filters = document.querySelectorAll('.filter-input');
    const stops = [];
    filters.forEach(el => {
        const handler = function(e){ e.stopImmediatePropagation(); };
        el.addEventListener('change', handler, true);
        stops.push({el, handler});
    });

    if(form){
        form.addEventListener('submit', function(e){
            stops.forEach(s => s.el.removeEventListener('change', s.handler, true));
            // allow default GET submission
        });
    }
});
</script>

    <script>
        // Global Loading Functions (from dashboard)
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
            // Handle form submissions with loading
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
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
<?php /**PATH D:\project ai\lhgk\resources\views/trafik_simple.blade.php ENDPATH**/ ?>