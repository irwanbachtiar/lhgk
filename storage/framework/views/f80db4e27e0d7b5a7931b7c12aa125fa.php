<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard LHGK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet" integrity="sha384-QuGBSgV5Im3DzL2z+8Ko9/hqNy/N0O7zwvXAtfd1MvPKWa/UbeLV65cfm4BV5Wgq" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" integrity="sha384-e6nUZLBkQ86NJ6TVVKAeSaK8jWa3NhkYWZFomE39AvDbQWeie9PlQqM3pmYW5d1g" crossorigin="anonymous"></script>
    <script>
        // Dashboard action bar handlers (attach after DOM ready)
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
                refreshBtn.addEventListener('click', function(){
                    window.location.reload();
                });
            }

            if(applyBtn && form){
                applyBtn.addEventListener('click', function(){
                    const overlay = document.getElementById('globalLoading');
                    if(overlay) overlay.style.display = 'flex';
                    try {
                        form.submit();
                    } catch(e){
                        if(overlay) overlay.style.display = 'none';
                        console.error(e);
                    }
                });
            }
        });
    </script>
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
        .metric-value { font-size: 1.8rem; font-weight: 700; line-height: 1; }
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
            <span class="navbar-brand mb-0 h1"><i class="bi bi-graph-up-arrow"></i> Dashboard LHGK</span>
            <div>
                <a href="<?php echo e(route('trafik')); ?>" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-graph-up-arrow"></i> Trafik
                </a>
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
        <!-- Period Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="period-filter">
                    <form id="dashboardFilterForm" method="GET" action="<?php echo e(route('dashboard')); ?>" class="row align-items-center">
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

                                <button type="button" id="btnApplyFilters" class="btn btn-sm btn-primary ms-2" title="Apply filters">
                                    <i class="bi bi-funnel-fill"></i> Apply
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex justify-content-end align-items-center">
                            <div>
                                <?php if($selectedPeriode != 'all' || $selectedBranch != 'all'): ?>
                                    <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-outline-secondary me-2">
                                        <i class="bi bi-x-circle"></i> Reset
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="btn-toolbar" role="toolbar" aria-label="Dashboard actions">
                                <div class="btn-group" role="group" aria-label="Export and refresh">
                                    <button type="button" id="btnDownloadPdf" class="btn btn-sm btn-outline-primary" title="Download current page as PDF">
                                        <i class="bi bi-download"></i> Download
                                    </button>
                                    <button type="button" id="btnRefresh" class="btn btn-sm btn-outline-secondary" title="Refresh dashboard">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                                
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Filter Info / Statistics Cards -->
        <?php if($selectedPeriode == 'all' || $selectedBranch == 'all'): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <h5 class="mt-3">Pilih Cabang dan Periode untuk Menampilkan Data</h5>
                    <p class="mb-0">Silakan pilih <strong>Cabang</strong> dan <strong>Periode</strong> pada filter di atas untuk melihat statistik pandu.</p>
                    <p class="mb-0 mt-2 text-muted"><small>Fitur ini dioptimalkan untuk performa yang lebih baik dengan memuat data sesuai kebutuhan.</small></p>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Overall Statistics -->
        <div class="row mb-4">
            <!-- Total Pandu moved into Realisasi row (kept out of top summary) -->
            <div class="col-md-2">
                <div class="card stat-card bg-white card-accent--pink">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-receipt fs-1" style="color: var(--accent-pink);"></i>
                        </div>
                        <h3 class="text-dark mb-1 metric-value"><?php echo e(number_format($totalOverall['total_transaksi'])); ?></h3>
                        <p class="mb-0 text-muted small">Gerakan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-white card-accent--green">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-cash-coin fs-1" style="color: var(--accent-green);"></i>
                        </div>
                        <h4 class="text-dark mb-1 metric-value">Rp <?php echo e(number_format($totalOverall['total_pendapatan_pandu'], 0, ',', '.')); ?></h4>
                        <p class="mb-0 text-muted small">Total Pendapatan Pandu</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-white card-accent--cyan">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-water fs-1" style="color: var(--accent-cyan);"></i>
                        </div>
                        <h4 class="text-dark mb-1 metric-value">Rp <?php echo e(number_format($totalOverall['total_pendapatan_tunda'], 0, ',', '.')); ?></h4>
                        <p class="mb-0 text-muted small">Total Pendapatan Tunda</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-white card-accent--teal">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-graph-up-arrow fs-1" style="color: var(--accent-teal);"></i>
                        </div>
                        <h4 class="text-dark mb-1 metric-value">Rp <?php echo e(number_format($totalOverall['total_pendapatan_pandu'] + $totalOverall['total_pendapatan_tunda'], 0, ',', '.')); ?></h4>
                        <p class="mb-0 text-muted small">Total Pendapatan</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Realisasi Cards: Pemanduan & Penundaan -->
        <div class="row mb-3">
            

            <div class="col-md-3">
                <div class="card stat-card bg-white card-accent--blue">
                    <div class="card-body">
                        <h6 class="mb-2"><i class="bi bi-file-earmark-text"></i> Realisasi Pemanduan</h6>
                        <div class="small text-muted mb-2"></div>
                        <?php
                            $pandu_mobile = (int) data_get($realisasiPandu, 'mobile', 0);
                            $pandu_web = (int) data_get($realisasiPandu, 'web', 0);
                            $pandu_partial = (int) data_get($realisasiPandu, 'partial', 0);
                            $pandu_total = $pandu_mobile + $pandu_web + $pandu_partial;
                            $pandu_mobile_pct = $pandu_total > 0 ? round(($pandu_mobile / $pandu_total) * 100, 1) : 0;
                            $pandu_web_pct = $pandu_total > 0 ? round(($pandu_web / $pandu_total) * 100, 1) : 0;
                            $pandu_partial_pct = $pandu_total > 0 ? round(($pandu_partial / $pandu_total) * 100, 1) : 0;
                        ?>
                        <div class="d-flex gap-2">
                            <span class="via-badge via-mobile"><i class="bi bi-phone"></i> Mobile: <strong><?php echo e(number_format($pandu_mobile)); ?></strong> (<?php echo e($pandu_mobile_pct); ?>%)</span>
                            <span class="via-badge via-web"><i class="bi bi-laptop"></i> Web: <strong><?php echo e(number_format($pandu_web)); ?></strong> (<?php echo e($pandu_web_pct); ?>%)</span>
                            <?php if($pandu_partial > 0): ?>
                                <span class="via-badge"><i class="bi bi-puzzle"></i> Partial: <strong><?php echo e(number_format($pandu_partial)); ?></strong> (<?php echo e($pandu_partial_pct); ?>%)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card bg-white card-accent--teal">
                    <div class="card-body">
                        <h6 class="mb-2"><i class="bi bi-clock-history"></i> Realisasi Penundaan</h6>
                        <div class="small text-muted mb-2"></div>
                        <?php
                            $tunda_mobile = $realisasiTunda->mobile ?? 0;
                            $tunda_web = $realisasiTunda->web ?? 0;
                            $tunda_partial = $realisasiTunda->partial ?? 0;
                            $tunda_total = $tunda_mobile + $tunda_web + $tunda_partial;
                            $tunda_mobile_pct = $tunda_total > 0 ? round(($tunda_mobile / $tunda_total) * 100, 1) : 0;
                            $tunda_web_pct = $tunda_total > 0 ? round(($tunda_web / $tunda_total) * 100, 1) : 0;
                            $tunda_partial_pct = $tunda_total > 0 ? round(($tunda_partial / $tunda_total) * 100, 1) : 0;
                        ?>
                        <div class="d-flex gap-2">
                            <span class="via-badge via-mobile"><i class="bi bi-phone"></i> Mobile: <strong><?php echo e(number_format($tunda_mobile)); ?></strong> (<?php echo e($tunda_mobile_pct); ?>%)</span>
                            <span class="via-badge via-web"><i class="bi bi-laptop"></i> Web: <strong><?php echo e(number_format($tunda_web)); ?></strong> (<?php echo e($tunda_web_pct); ?>%)</span>
                            <?php if($tunda_partial > 0): ?>
                                <span class="via-badge"><i class="bi bi-puzzle"></i> Partial: <strong><?php echo e(number_format($tunda_partial)); ?></strong> (<?php echo e($tunda_partial_pct); ?>%)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card bg-white card-accent--orange">
                    <div class="card-body text-center">
                        <h6 class="mb-2"><img src="/images/tugboat.svg" alt="Tug" style="width:18px;height:18px;" class="me-1"> Total Tunda</h6>
                        <div class="small text-muted mb-2"></div>
                        <div class="h3 mb-0 metric-value"><?php echo e(number_format($totalTundaDistinct ?? 0)); ?></div>
                        <p class="mb-0 text-muted small">Unit</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card bg-white card-accent--indigo">
                    <div class="card-body text-center">
                        <h6 class="mb-2"><i class="bi bi-person-badge me-1"></i> Total Pandu</h6>
                        <div class="small text-muted mb-2"></div>
                        <div class="h3 mb-0 metric-value"><?php echo e(number_format($totalOverall['total_pandu'] ?? 0)); ?></div>
                        <p class="mb-0 text-muted small">Orang</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Combined layout: Left column stacks Waiting Time + Nota Summary; Right column stacks Pilot + Speedometer -->
        <div class="row mb-2 align-items-stretch">
            <div class="col-md-6 d-flex flex-column gap-3">
                <div class="card stat-card bg-white combined-card flex-fill d-flex card-accent--amber">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <h5 class="mb-3"><i class="bi bi-clock-history"></i> Waiting Time</h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-center">
                                <small class="text-muted">WT &gt; 00:30</small>
                                <div class="h4 mb-0"><?php echo e(number_format($totalOverall['transaksi_wt_di_atas_30'] ?? 0)); ?></div>
                            </div>
                            <div class="text-center">
                                <small class="text-muted">Rata-Rata WT</small>
                                <div class="h4 mb-0"><?php echo e(number_format((float)($totalOverall['rata_rata_wt'] ?? 0), 2)); ?></div>
                            </div>
                            <div class="text-center">
                                <small class="text-muted">Maksimal WT</small>
                                <div class="h4 mb-0"><?php echo e(number_format((float)($totalOverall['max_wt'] ?? 0), 2)); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card stat-card bg-white nota-summary flex-fill d-flex match-height card-accent--blue">
                    <div class="card-body ps-0">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div class="nota-left" style="flex:0 0 220px; padding-left:0;">
                                <h5 class="mb-0 title-shift"><i class="bi bi-file-earmark-text"></i> Nota Summary</h5>
                                <div class="text-muted small">Distribusi status nota</div>

                                
                            </div>

                            <div class="nota-canvas-wrap mx-3" style="flex:0 0 160px;">
                                <canvas id="notaSummaryChart" style="width:100%; height:200px; display:block;"></canvas>
                            </div>

                            <div class="nota-legend text-end small text-muted" style="flex:0 0 220px;">
                                <div id="notaSummaryLegend">
                                    <div class="mb-1"><i class="bi bi-circle-fill" style="color:#3b82f6"></i> Terbit: <strong><?php echo e(number_format($totalOverall['total_nota'] ?? 0)); ?></strong></div>
                                    <div class="mb-1"><i class="bi bi-circle-fill" style="color:#ef4444"></i> Batal: <strong><?php echo e(number_format($totalOverall['nota_batal'] ?? 0)); ?></strong></div>
                                    <div class="mb-1"><i class="bi bi-circle-fill" style="color:#f59e0b"></i> Menunggu: <strong><?php echo e(number_format($totalOverall['menunggu_nota'] ?? 0)); ?></strong></div>
                                    <div><i class="bi bi-circle-fill" style="color:#6b7280"></i> Belum Verif: <strong><?php echo e(number_format($totalOverall['belum_verifikasi'] ?? 0)); ?></strong></div>
                                </div>

                                <div class="mt-2 small text-muted">
                                    Total: <strong><?php echo e(number_format((($totalOverall['total_nota'] ?? 0) + ($totalOverall['nota_batal'] ?? 0) + ($totalOverall['menunggu_nota'] ?? 0) + ($totalOverall['belum_verifikasi'] ?? 0)))); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 d-flex flex-column gap-3">
                <?php if($topPilot): ?>
                <div class="card stat-card bg-white combined-card flex-fill d-flex card-accent--amber">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <div class="row w-100 align-items-center">
                            <div class="col-md-4">
                                <h5 class="mb-0 text-dark"><i class="bi bi-trophy-fill text-warning"></i> Pilot Produksi Tertinggi</h5>
                                <small class="text-muted d-block"><?php echo e($topPilot->NM_BRANCH); ?></small>
                            </div>
                            <div class="col-md-2 text-center">
                                <h4 class="mb-0 text-dark"><?php echo e($topPilot->NM_PERS_PANDU); ?></h4>
                            </div>
                            <div class="col-md-2 text-center">
                                <h4 class="mb-0 text-dark"><?php echo e(number_format($topPilot->total_produksi)); ?></h4>
                                <small class="text-muted">Total Produksi</small>
                            </div>
                            <div class="col-md-2 text-center">
                                <h5 class="mb-0 text-dark"><?php echo e(number_format($topPilot->rata_rata_wt, 2)); ?></h5>
                                <small class="text-muted">Rata-Rata WT</small>
                            </div>
                            <div class="col-md-2 text-center">
                                <h5 class="mb-0 text-dark">Rp <?php echo e(number_format($topPilot->total_pendapatan, 0, ',', '.')); ?></h5>
                                <small class="text-muted">Total Pendapatan</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card stat-card bg-white combined-card flex-fill d-flex speedometer-card match-height card-accent--teal">
                    <div class="card-body p-3">
                        <div class="row g-0 align-items-center">
                            <div class="col">
                                <div class="pe-3">
                                    <h5 class="mb-2"><i class="bi bi-speedometer2"></i> Kecepatan Terbit (Bentuk 3)</h5>
                                    <div class="text-muted small">Rata-rata waktu terbit (Hari)</div>
                                </div>
                            </div>
                            <div class="col-auto ps-3">
                                <div style="width:100%; max-width: 340px; position: relative;">
                                    <canvas id="speedometerChart" style="width: 100%; height: 170px; display: block;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <?php if($selectedPeriode != 'all' && $selectedBranch != 'all'): ?>
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <h5 class="card-title">Pendapatan Pemanduan</h5>
                        <canvas id="panduChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <h5 class="card-title">Pendapatan Penundaan</h5>
                        <canvas id="tundaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Departure Invoice Delay Section -->
        <?php if($selectedPeriode != 'all' && $selectedBranch != 'all' && $departureDelayCount > 0): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <?php if(!$showDeparture): ?>
                    <div class="card-body text-center py-4">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Data Keterlambatan Invoice Departure</h5>
                        <p class="text-muted">
                            Ditemukan <strong class="text-danger"><?php echo e(number_format($departureDelayCount)); ?> transaksi</strong> 
                            dengan keterlambatan invoice > 2 hari
                        </p>
                        <a href="<?php echo e(route('dashboard', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch, 'show_departure' => 1])); ?>#departure-section" 
                           class="btn btn-warning">
                            <i class="bi bi-eye"></i> Tampilkan Data Departure
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="card-header" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;" id="departure-section">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-exclamation-triangle-fill"></i> 
                                Data Departure - Keterlambatan Invoice (> 2 Hari)
                            </h5>
                            <div>
                                <a href="<?php echo e(route('export.departure.delay', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch])); ?>" 
                                   class="btn btn-light btn-sm me-2">
                                    <i class="bi bi-file-earmark-excel"></i> Download Excel
                                </a>
                                <a href="<?php echo e(route('dashboard', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch])); ?>" 
                                   class="btn btn-light btn-sm">
                                    <i class="bi bi-x-circle"></i> Sembunyikan
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle-fill"></i>
                            <strong><?php echo e(number_format($departureDelayCount)); ?> transaksi</strong> memiliki selisih antara tanggal selesai pelaksanaan dan invoice lebih dari 2 hari.
                            <div class="mt-2">
                                <small class="text-muted">Data diurutkan berdasarkan selisih hari terbesar</small>
                            </div>
                        </div>
                        
                        <?php if($departureDelayData && $departureDelayData->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>No. UKK</th>
                                        <th>Nama Kapal</th>
                                        <th>Nama Pandu</th>
                                        <th>Cabang</th>
                                        <th>Gerakan</th>
                                        <th>Selesai Pelaksanaan</th>
                                        <th>Tanggal Invoice</th>
                                        <th class="text-center">Selisih (hari)</th>
                                        <th class="text-end">Pendapatan Pandu</th>
                                        <th class="text-end">Pendapatan Tunda</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $departureDelayData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($departureDelayData->firstItem() + $index); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo e($data->NO_UKK); ?></span></td>
                                        <td><strong><?php echo e($data->NM_KAPAL); ?></strong></td>
                                        <td><?php echo e($data->NM_PERS_PANDU); ?></td>
                                        <td><?php echo e($data->NM_BRANCH); ?></td>
                                        <td>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-arrow-up-circle"></i> <?php echo e(strtoupper($data->GERAKAN)); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($data->SELESAI_PELAKSANAAN); ?></td>
                                        <td><?php echo e($data->INVOICE_DATE); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-danger fs-6">
                                                <?php echo e($data->selisih_hari); ?> hari
                                            </span>
                                        </td>
                                        <td class="text-end">Rp <?php echo e(number_format($data->PENDAPATAN_PANDU, 0, ',', '.')); ?></td>
                                        <td class="text-end">Rp <?php echo e(number_format($data->PENDAPATAN_TUNDA, 0, ',', '.')); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($departureDelayData->hasPages()): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan <?php echo e($departureDelayData->firstItem()); ?> - <?php echo e($departureDelayData->lastItem()); ?> dari <?php echo e($departureDelayData->total()); ?> data
                            </div>
                            <div>
                                <?php echo e($departureDelayData->links('pagination::bootstrap-5')); ?>

                            </div>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle"></i> Tidak ada data untuk ditampilkan
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- PKK Manual Section -->
        <?php if($selectedPeriode != 'all' && $selectedBranch != 'all'): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <?php if($pkkManualCount == 0): ?>
                    <div class="card-body text-center py-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">PKK Manual</h5>
                        <p class="text-muted">
                            <strong class="text-success">Tidak ada</strong> transaksi dengan PKK Inaportnet yang diinput manual.
                            Semua nomor PKK sudah sesuai format.
                        </p>
                    </div>
                    <?php elseif(!$showPkkManual): ?>
                    <div class="card-body text-center py-4">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">PKK Manual</h5>
                        <p class="text-muted">
                            Ditemukan <strong class="text-warning"><?php echo e(number_format($pkkManualCount)); ?> transaksi</strong>
                            dengan nomor PKK Inaportnet yang diinput secara manual (bukan format PKK)
                        </p>
                        <a href="<?php echo e(route('dashboard', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch, 'show_pkk_manual' => 1])); ?>#pkk-manual-section"
                           class="btn btn-warning">
                            <i class="bi bi-eye"></i> Tampilkan Data PKK Manual
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;" id="pkk-manual-section">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-card-checklist"></i>
                                PKK Manual - No. PKK Inaportnet Bukan Format PKK
                            </h5>
                            <div>
                                <a href="<?php echo e(route('dashboard', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch])); ?>"
                                   class="btn btn-light btn-sm">
                                    <i class="bi bi-x-circle"></i> Sembunyikan
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-info-circle-fill"></i>
                            <strong><?php echo e(number_format($pkkManualCount)); ?> transaksi</strong> memiliki nilai kolom <code>NO_PKK_INAPORTNET</code> yang tidak dimulai dengan format <strong>PKK</strong> (kemungkinan diinput manual).
                        </div>

                        <?php if($pkkManualData && $pkkManualData->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>No. UKK</th>
                                        <th>Nama Kapal</th>
                                        <th>Nama Pandu</th>
                                        <th>Cabang</th>
                                        <th>Gerakan</th>
                                        <th>Mulai Pelaksanaan</th>
                                        <th>Selesai Pelaksanaan</th>
                                        <th>No. PKK Inaportnet</th>
                                        <th class="text-end">Pendapatan Pandu</th>
                                        <th class="text-end">Pendapatan Tunda</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $pkkManualData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($pkkManualData->firstItem() + $index); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo e($data->NO_UKK); ?></span></td>
                                        <td><strong><?php echo e($data->NM_KAPAL); ?></strong></td>
                                        <td><?php echo e($data->NM_PERS_PANDU); ?></td>
                                        <td><?php echo e($data->NM_BRANCH); ?></td>
                                        <td>
                                            <span class="badge <?php echo e(strtoupper($data->GERAKAN) == 'DEPARTURE' ? 'bg-danger' : 'bg-primary'); ?>">
                                                <?php echo e(strtoupper($data->GERAKAN)); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($data->MULAI_PELAKSANAAN); ?></td>
                                        <td><?php echo e($data->SELESAI_PELAKSANAAN); ?></td>
                                        <td>
                                            <span class="badge bg-warning text-dark"><?php echo e($data->NO_PKK_INAPORTNET); ?></span>
                                        </td>
                                        <td class="text-end">Rp <?php echo e(number_format($data->PENDAPATAN_PANDU, 0, ',', '.')); ?></td>
                                        <td class="text-end">Rp <?php echo e(number_format($data->PENDAPATAN_TUNDA, 0, ',', '.')); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if($pkkManualData->hasPages()): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan <?php echo e($pkkManualData->firstItem()); ?> - <?php echo e($pkkManualData->lastItem()); ?> dari <?php echo e($pkkManualData->total()); ?> data
                            </div>
                            <div>
                                <?php echo e($pkkManualData->links('pagination::bootstrap-5')); ?>

                            </div>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle"></i> Tidak ada data untuk ditampilkan
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Status Nota Section -->
        <?php if($selectedPeriode != 'all' && $selectedBranch != 'all' && $statusNotaCount > 0): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <?php if(!$showStatusNota): ?>
                    <div class="card-body text-center py-4">
                        <i class="bi bi-clipboard-check text-info" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Data Status Nota</h5>
                        <p class="text-muted">
                            Ditemukan <strong class="text-info"><?php echo e(number_format($statusNotaCount)); ?> transaksi</strong> 
                            dengan status "menunggu nota" atau "belum verifikasi"
                        </p>
                        <a href="<?php echo e(route('dashboard', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch, 'show_status_nota' => 1])); ?>#status-nota-section" 
                           class="btn btn-info">
                            <i class="bi bi-eye"></i> Tampilkan Data Status Nota
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="card-header" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white;" id="status-nota-section">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-clipboard-check"></i> 
                                Data Status Nota (Menunggu Nota / Belum Verifikasi)
                            </h5>
                            <div>
                                <a href="<?php echo e(route('export.status.nota', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch, 'filter_status_nota' => $filterStatusNota])); ?>" 
                                   class="btn btn-light btn-sm me-2">
                                    <i class="bi bi-file-earmark-excel"></i> Download Excel
                                </a>
                                <a href="<?php echo e(route('dashboard', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch])); ?>" 
                                   class="btn btn-light btn-sm">
                                    <i class="bi bi-x-circle"></i> Sembunyikan
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filter Status Nota -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <form method="GET" action="<?php echo e(route('dashboard')); ?>" id="filterStatusNotaForm">
                                    <input type="hidden" name="periode" value="<?php echo e($selectedPeriode); ?>">
                                    <input type="hidden" name="cabang" value="<?php echo e($selectedBranch); ?>">
                                    <input type="hidden" name="show_status_nota" value="1">
                                    <div class="input-group">
                                        <label class="input-group-text bg-info text-white"><i class="bi bi-funnel-fill"></i></label>
                                        <select name="filter_status_nota" class="form-select filter-input">
                                            <option value="all" <?php echo e($filterStatusNota == 'all' ? 'selected' : ''); ?>>Semua Status</option>
                                            <option value="menunggu nota" <?php echo e($filterStatusNota == 'menunggu nota' ? 'selected' : ''); ?>>Menunggu Nota</option>
                                            <option value="belum verifikasi" <?php echo e($filterStatusNota == 'belum verifikasi' ? 'selected' : ''); ?>>Belum Verifikasi</option>
                                        </select>
                                        <button type="button" class="btn btn-primary" onclick="document.getElementById('globalLoading').style.display='flex'; this.closest('form').submit();">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle-fill"></i>
                            <strong><?php echo e(number_format($statusNotaCount)); ?> transaksi</strong> 
                            <?php if($filterStatusNota == 'all'): ?>
                                memiliki status nota "menunggu nota" atau "belum verifikasi".
                            <?php else: ?>
                                dengan status "<?php echo e($filterStatusNota); ?>".
                            <?php endif; ?>
                            <div class="mt-2">
                                <small class="text-muted">Data diurutkan berdasarkan mulai pelaksanaan terbaru</small>
                            </div>
                        </div>
                        
                        <?php if($statusNotaData && $statusNotaData->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width:48px">No</th>
                                        <th style="width:140px">No. UKK</th>
                                        <th style="width:260px">Nama Kapal</th>
                                        <th style="width:120px">Pelayaran</th>
                                        <th style="width:180px">Nama Pandu</th>
                                        <th style="width:160px">Mulai Pelaksanaan</th>
                                        <th style="width:160px">Selesai Pelaksanaan</th>
                                        <th style="width:110px" class="text-center">Selisih (hari)</th>
                                        <th class="text-end" style="width:140px">Pendapatan Pandu</th>
                                        <th class="text-end" style="width:140px">Pendapatan Tunda</th>
                                        <th class="text-center" style="width:120px">Status Nota</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $statusNotaData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($statusNotaData->firstItem() + $index); ?></td>
                                        <td><span class="ukk-badge"><?php echo e($data->NO_UKK); ?></span></td>
                                        <td class="nm-kapal"><strong title="<?php echo e($data->NM_KAPAL); ?>"><?php echo e(Str::limit($data->NM_KAPAL, 40)); ?></strong></td>
                                        <td><?php echo e($data->PELAYARAN ?? '-'); ?></td>
                                        <td><?php echo e($data->NM_PERS_PANDU); ?></td>
                                        <td class="text-nowrap small"><?php echo e($data->MULAI_PELAKSANAAN); ?></td>
                                        <td class="text-nowrap small"><?php echo e($data->SELESAI_PELAKSANAAN); ?></td>
                                        <?php $s = $data->SELISIH_HARI; ?>
                                        <td class="text-center">
                                            <?php if($s === null || $s === ''): ?>
                                                -
                                            <?php else: ?>
                                                <span class="badge selisih-badge <?php echo e($s < 0 ? 'bg-danger' : ($s == 0 ? 'bg-secondary' : 'bg-success')); ?>"><?php echo e($s); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">Rp <?php echo e(number_format($data->PENDAPATAN_PANDU, 0, ',', '.')); ?></td>
                                        <td class="text-end">Rp <?php echo e(number_format($data->PENDAPATAN_TUNDA, 0, ',', '.')); ?></td>
                                        <td class="text-center">
                                            <span class="badge <?php echo e($data->STATUS_NOTA == 'menunggu nota' ? 'bg-warning' : 'bg-info'); ?> text-dark">
                                                <?php echo e(strtoupper($data->STATUS_NOTA)); ?>

                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($statusNotaData->hasPages()): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan <?php echo e($statusNotaData->firstItem()); ?> - <?php echo e($statusNotaData->lastItem()); ?> dari <?php echo e($statusNotaData->total()); ?> data
                            </div>
                            <div>
                                <?php echo e($statusNotaData->links('pagination::bootstrap-5')); ?>

                            </div>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle"></i> Tidak ada data untuk ditampilkan
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- PPKB/Realisasi Backdate Section -->
        <?php if($selectedPeriode != 'all' && $selectedBranch != 'all' && $backdateCount > 0): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <?php if(!$showBackdate): ?>
                    <div class="card-body text-center py-4">
                        <i class="bi bi-calendar-x text-danger" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">PPKB / Realisasi Backdate</h5>
                        <p class="text-muted">
                            Ditemukan <strong class="text-danger"><?php echo e(number_format($backdateCount)); ?> transaksi</strong>
                            dengan tanggal mulai pelaksanaan lebih awal dari tanggal PPKB Submit (backdate)
                        </p>
                        <a href="<?php echo e(route('dashboard', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch, 'show_backdate' => 1])); ?>#backdate-section"
                           class="btn btn-danger">
                            <i class="bi bi-eye"></i> Tampilkan Data Backdate
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="card-header" style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: white;" id="backdate-section">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-calendar-x"></i>
                                PPKB / Realisasi Backdate
                            </h5>
                            <div>
                                <a href="<?php echo e(route('export.backdate', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch])); ?>"
                                   class="btn btn-light btn-sm me-2">
                                    <i class="bi bi-file-earmark-excel"></i> Download Excel
                                </a>
                                <a href="<?php echo e(route('dashboard', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch])); ?>"
                                   class="btn btn-light btn-sm">
                                    <i class="bi bi-x-circle"></i> Sembunyikan
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger mb-3">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <strong><?php echo e(number_format($backdateCount)); ?> transaksi</strong> terdeteksi sebagai backdate —
                            tanggal <strong>Mulai Pelaksanaan</strong> lebih awal dari tanggal <strong>PPKB Submit</strong>.
                            <div class="mt-2">
                                <small class="text-muted">Data diurutkan berdasarkan mulai pelaksanaan terbaru</small>
                            </div>
                        </div>

                        <?php if($backdateData && $backdateData->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width:48px">No</th>
                                        <th style="width:130px">PPKB Code</th>
                                        <th style="width:150px">PPKB Submit</th>
                                        <th style="width:120px">No. UKK</th>
                                        <th style="width:140px">No. Bkt Pandu</th>
                                        <th style="width:160px">Tgl Jam Tiba</th>
                                        <th style="width:220px">Nama Kapal</th>
                                        <th style="width:140px">Jenis Kapal</th>
                                        <th style="width:100px">Tgl Tiba</th>
                                        <th style="width:80px">Jam Tiba</th>
                                        <th style="width:100px">Tgl PMT</th>
                                        <th style="width:80px">Jam PMT</th>
                                        <th style="width:155px">Mulai Pelaksanaan</th>
                                        <th style="width:155px">Selesai Pelaksanaan</th>
                                        <th style="width:150px">Created By</th>
                                        <th style="width:150px">Pilot Deploy By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $backdateData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($backdateData->firstItem() + $index); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->PPKB_CODE ?? '-'); ?></td>
                                        <td class="text-nowrap small text-danger fw-semibold"><?php echo e($row->PPKB_SUBMIT ?? '-'); ?></td>
                                        <td><span class="ukk-badge"><?php echo e($row->NO_UKK ?? '-'); ?></span></td>
                                        <td class="text-nowrap small"><?php echo e($row->NO_BKT_PANDU ?? '-'); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->TGL_JAM_TIBA ?? '-'); ?></td>
                                        <td class="nm-kapal"><strong title="<?php echo e($row->NM_KAPAL); ?>"><?php echo e(Str::limit($row->NM_KAPAL, 35)); ?></strong></td>
                                        <td class="text-nowrap small"><?php echo e($row->JN_KAPAL ?? '-'); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->TGL_TIBA ?? '-'); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->JAM_TIBA ?? '-'); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->TGL_PMT ?? '-'); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->JAM_PMT ?? '-'); ?></td>
                                        <td class="text-nowrap small text-success fw-semibold"><?php echo e($row->MULAI_PELAKSANAAN ?? '-'); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->SELESAI_PELAKSANAAN ?? '-'); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->CREATED_BY ?? '-'); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->PILOT_DEPLOY_BY ?? '-'); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if($backdateData->hasPages()): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan <?php echo e($backdateData->firstItem()); ?> - <?php echo e($backdateData->lastItem()); ?> dari <?php echo e($backdateData->total()); ?> data
                            </div>
                            <div>
                                <?php echo e($backdateData->links('pagination::bootstrap-5')); ?>

                            </div>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle"></i> Tidak ada data untuk ditampilkan
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Realisasi Web Section -->
        <?php if($selectedPeriode != 'all' && $selectedBranch != 'all' && $realisasiWebCount > 0): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <?php if(!$showRealisasiWeb): ?>
                    <div class="card-body text-center py-4">
                        <i class="bi bi-globe2 text-primary" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Realisasi Web</h5>
                        <p class="text-muted">
                            Ditemukan <strong class="text-primary"><?php echo e(number_format($realisasiWebCount)); ?> transaksi</strong>
                            dengan realisasi pilot via <strong>WEB</strong>
                        </p>
                        <a href="<?php echo e(route('dashboard', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch, 'show_realisasi_web' => 1])); ?>#realisasi-web-section"
                           class="btn btn-primary">
                            <i class="bi bi-eye"></i> Tampilkan Data Realisasi Web
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="card-header" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white;" id="realisasi-web-section">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-globe2"></i>
                                Realisasi Web (REALISAS_PILOT_VIA = WEB)
                            </h5>
                            <div>
                                <a href="<?php echo e(route('export.realisasi.web', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch])); ?>"
                                   class="btn btn-light btn-sm me-2">
                                    <i class="bi bi-file-earmark-excel"></i> Download Excel
                                </a>
                                <a href="<?php echo e(route('dashboard', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch])); ?>"
                                   class="btn btn-light btn-sm">
                                    <i class="bi bi-x-circle"></i> Sembunyikan
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-primary mb-3">
                            <i class="bi bi-info-circle-fill"></i>
                            <strong><?php echo e(number_format($realisasiWebCount)); ?> transaksi</strong> menggunakan realisasi pilot via <strong>WEB</strong>.
                            <div class="mt-2">
                                <small class="text-muted">Data diurutkan berdasarkan PPKB Code</small>
                            </div>
                        </div>

                        <?php if($realisasiWebData && $realisasiWebData->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width:48px">No</th>
                                        <th style="width:130px">PPKB Code</th>
                                        <th style="width:120px">No. UKK</th>
                                        <th style="width:150px">No. Bukti Pandu</th>
                                        <th style="width:220px">Nama Kapal</th>
                                        <th style="width:180px">Nama Pandu</th>
                                        <th style="width:180px">Pandu Dari</th>
                                        <th style="width:180px">Pandu Ke</th>
                                        <th class="text-center" style="width:130px">Realisasi Via</th>
                                        <th style="width:150px">Created By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $realisasiWebData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($realisasiWebData->firstItem() + $index); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->PPKB_CODE ?? '-'); ?></td>
                                        <td><span class="ukk-badge"><?php echo e($row->NO_UKK ?? '-'); ?></span></td>
                                        <td class="text-nowrap small"><?php echo e($row->NO_BKT_PANDU ?? '-'); ?></td>
                                        <td class="nm-kapal"><strong title="<?php echo e($row->NM_KAPAL); ?>"><?php echo e(Str::limit($row->NM_KAPAL, 35)); ?></strong></td>
                                        <td class="text-nowrap small"><?php echo e($row->NM_PERS_PANDU ?? '-'); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->PANDU_DARI ?? '-'); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->PANDU_KE ?? '-'); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-primary"><?php echo e(strtoupper($row->REALISAS_PILOT_VIA ?? '-')); ?></span>
                                        </td>
                                        <td class="text-nowrap small"><?php echo e($row->CREATED_BY ?? '-'); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if($realisasiWebData->hasPages()): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan <?php echo e($realisasiWebData->firstItem()); ?> - <?php echo e($realisasiWebData->lastItem()); ?> dari <?php echo e($realisasiWebData->total()); ?> data
                            </div>
                            <div>
                                <?php echo e($realisasiWebData->links('pagination::bootstrap-5')); ?>

                            </div>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle"></i> Tidak ada data untuk ditampilkan
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Waiting Time Section -->
        <?php if($selectedPeriode != 'all' && $selectedBranch != 'all' && $waitingTimeCount > 0): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <?php if(!$showWaitingTime): ?>
                    <div class="card-body text-center py-4">
                        <i class="bi bi-clock-history" style="font-size: 3rem; color: #764ba2;"></i>
                        <h5 class="mt-3">Data Waiting Time</h5>
                        <p class="text-muted">
                            Ditemukan <strong style="color: #764ba2;"><?php echo e(number_format($waitingTimeCount)); ?> transaksi</strong> 
                            dengan Waiting Time (WT) di atas 00:30
                        </p>
                        <a href="<?php echo e(route('dashboard', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch, 'show_waiting_time' => 1])); ?>#waiting-time-section" 
                           class="btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <i class="bi bi-eye"></i> Tampilkan Data Waiting Time
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;" id="waiting-time-section">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-clock-history"></i> 
                                Data Waiting Time (WT > 00:30)
                            </h5>
                            <div>
                                <a href="<?php echo e(route('export.waiting.time', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch])); ?>" 
                                   class="btn btn-light btn-sm me-2">
                                    <i class="bi bi-file-earmark-excel"></i> Download Excel
                                </a>
                                <a href="<?php echo e(route('dashboard', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch])); ?>" 
                                   class="btn btn-light btn-sm">
                                    <i class="bi bi-x-circle"></i> Sembunyikan
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-info-circle-fill"></i>
                            <strong><?php echo e(number_format($waitingTimeCount)); ?> transaksi</strong> memiliki Waiting Time (WT) lebih dari 00:30 (30 menit).
                            <div class="mt-2">
                                <small class="text-muted">Data diurutkan berdasarkan WT terbesar</small>
                            </div>
                        </div>
                        
                        <?php if($waitingTimeData && $waitingTimeData->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>PPKB Code</th>
                                        <th>No. UKK</th>
                                        <th>No. Bukti Pandu</th>
                                        <th>Nama Kapal</th>
                                        <th>Nama Pandu</th>
                                        <th>Tgl Tiba</th>
                                        <th>Jam Tiba</th>
                                        <th>Tgl PMT</th>
                                        <th>Jam PMT</th>
                                        <th>PNK</th>
                                        <th>KB</th>
                                        <th>Mulai Pelaksanaan</th>
                                        <th>Selesai Pelaksanaan</th>
                                        <th class="text-center">WT</th>
                                        <th>Pandu Dari</th>
                                        <th>Pandu Ke</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $waitingTimeData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($waitingTimeData->firstItem() + $index); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo e($data->PPKB_CODE ?? '-'); ?></span></td>
                                        <td><span class="badge bg-info"><?php echo e($data->NO_UKK ?? '-'); ?></span></td>
                                        <td><?php echo e($data->NO_BKT_PANDU ?? '-'); ?></td>
                                        <td><strong><?php echo e($data->NM_KAPAL ?? '-'); ?></strong></td>
                                        <td><?php echo e($data->NM_PERS_PANDU ?? '-'); ?></td>
                                        <td><?php echo e($data->TGL_TIBA ?? '-'); ?></td>
                                        <td><?php echo e($data->JAM_TIBA ?? '-'); ?></td>
                                        <td><?php echo e($data->TGL_PMT ?? '-'); ?></td>
                                        <td><?php echo e($data->JAM_PMT ?? '-'); ?></td>
                                        <td><?php echo e($data->PNK ?? '-'); ?></td>
                                        <td><?php echo e($data->KB ?? '-'); ?></td>
                                        <td><?php echo e($data->MULAI_PELAKSANAAN ?? '-'); ?></td>
                                        <td><?php echo e($data->SELESAI_PELAKSANAAN ?? '-'); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-warning text-dark"><?php echo e($data->WT ?? '-'); ?></span>
                                        </td>
                                        <td><?php echo e($data->PANDU_DARI ?? '-'); ?></td>
                                        <td><?php echo e($data->PANDU_KE ?? '-'); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($waitingTimeData->hasPages()): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan <?php echo e($waitingTimeData->firstItem()); ?> - <?php echo e($waitingTimeData->lastItem()); ?> dari <?php echo e($waitingTimeData->total()); ?> data
                            </div>
                            <div>
                                <?php echo e($waitingTimeData->links('pagination::bootstrap-5')); ?>

                            </div>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle"></i> Tidak ada data untuk ditampilkan
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Ship Statistics by GT Range and Flag -->
        <?php if(($selectedPeriode != 'all' || $selectedBranch != 'all') && $shipStatsByGT->count() > 0): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white;">
                        <h5 class="mb-0"><i class="bi bi-ship"></i> Statistik Kapal Berdasarkan Range GT dan Bendera</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Range GT</th>
                                        <th>Jenis Kapal</th>
                                        <th class="text-end">Total Transaksi</th>
                                        <th class="text-end">Pendapatan Pandu</th>
                                        <th class="text-end">Pendapatan Tunda</th>
                                        <th class="text-end">Total Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $currentGT = '';
                                        $totalPanduGlobal = 0;
                                        $totalTundaGlobal = 0;
                                        $totalTransaksiGlobal = 0;
                                        $totalPendapatanGlobal = 0;
                                        
                                        // Calculate totals by flag
                                        $totalPanduNasional = 0;
                                        $totalTundaNasional = 0;
                                        $totalTransaksiNasional = 0;
                                        $totalPendapatanNasional = 0;
                                        
                                        $totalPanduAsing = 0;
                                        $totalTundaAsing = 0;
                                        $totalTransaksiAsing = 0;
                                        $totalPendapatanAsing = 0;
                                    ?>
                                    <?php $__currentLoopData = $shipStatsByGT; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $totalPanduGlobal += $stat->total_pendapatan_pandu;
                                            $totalTundaGlobal += $stat->total_pendapatan_tunda;
                                            $totalTransaksiGlobal += $stat->total_transaksi;
                                            $totalPendapatanGlobal += $stat->total_pendapatan;
                                            
                                            if ($stat->JENIS_KAPAL_DARI_BENDERA == 'KAPAL NASIONAL') {
                                                $totalPanduNasional += $stat->total_pendapatan_pandu;
                                                $totalTundaNasional += $stat->total_pendapatan_tunda;
                                                $totalTransaksiNasional += $stat->total_transaksi;
                                                $totalPendapatanNasional += $stat->total_pendapatan;
                                            } else {
                                                $totalPanduAsing += $stat->total_pendapatan_pandu;
                                                $totalTundaAsing += $stat->total_pendapatan_tunda;
                                                $totalTransaksiAsing += $stat->total_transaksi;
                                                $totalPendapatanAsing += $stat->total_pendapatan;
                                            }
                                            
                                            $rowspan = $shipStatsByGT->where('RANGE_GT', $stat->RANGE_GT)->count();
                                        ?>
                                        <tr>
                                            <?php if($currentGT != $stat->RANGE_GT): ?>
                                                <?php $currentGT = $stat->RANGE_GT; ?>
                                                <td rowspan="<?php echo e($rowspan); ?>" class="align-middle">
                                                    <span class="badge bg-primary"><?php echo e(str_replace(' GT', '', $stat->RANGE_GT)); ?></span>
                                                </td>
                                            <?php endif; ?>
                                            <td>
                                                <span class="badge <?php echo e($stat->JENIS_KAPAL_DARI_BENDERA == 'KAPAL NASIONAL' ? 'bg-success' : 'bg-info'); ?>">
                                                    <?php echo e($stat->JENIS_KAPAL_DARI_BENDERA); ?>

                                                </span>
                                            </td>
                                            <td class="text-end"><?php echo e(number_format($stat->total_transaksi)); ?></td>
                                            <td class="text-end">Rp <?php echo e(number_format($stat->total_pendapatan_pandu, 0, ',', '.')); ?></td>
                                            <td class="text-end">Rp <?php echo e(number_format($stat->total_pendapatan_tunda, 0, ',', '.')); ?></td>
                                            <td class="text-end"><strong>Rp <?php echo e(number_format($stat->total_pendapatan, 0, ',', '.')); ?></strong></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-success">
                                        <th colspan="2" class="text-end">Total Kapal Nasional:</th>
                                        <th class="text-end"><?php echo e(number_format($totalTransaksiNasional)); ?></th>
                                        <th class="text-end">Rp <?php echo e(number_format($totalPanduNasional, 0, ',', '.')); ?></th>
                                        <th class="text-end">Rp <?php echo e(number_format($totalTundaNasional, 0, ',', '.')); ?></th>
                                        <th class="text-end"><strong>Rp <?php echo e(number_format($totalPendapatanNasional, 0, ',', '.')); ?></strong></th>
                                    </tr>
                                    <tr class="table-info">
                                        <th colspan="2" class="text-end">Total Kapal Asing:</th>
                                        <th class="text-end"><?php echo e(number_format($totalTransaksiAsing)); ?></th>
                                        <th class="text-end">Rp <?php echo e(number_format($totalPanduAsing, 0, ',', '.')); ?></th>
                                        <th class="text-end">Rp <?php echo e(number_format($totalTundaAsing, 0, ',', '.')); ?></th>
                                        <th class="text-end"><strong>Rp <?php echo e(number_format($totalPendapatanAsing, 0, ',', '.')); ?></strong></th>
                                    </tr>
                                    <tr class="table-secondary">
                                        <th colspan="2" class="text-end">Total Keseluruhan:</th>
                                        <th class="text-end"><?php echo e(number_format($totalTransaksiGlobal)); ?></th>
                                        <th class="text-end">Rp <?php echo e(number_format($totalPanduGlobal, 0, ',', '.')); ?></th>
                                        <th class="text-end">Rp <?php echo e(number_format($totalTundaGlobal, 0, ',', '.')); ?></th>
                                        <th class="text-end"><strong>Rp <?php echo e(number_format($totalPendapatanGlobal, 0, ',', '.')); ?></strong></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Ship Statistics Charts by GT Range -->
        <?php if(false && ($selectedPeriode != 'all' || $selectedBranch != 'all') && $shipStatsByGT->count() > 0): ?>
        <div class="row mb-4">
            <div class="col-12 mb-3">
                <div class="card stat-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white;">
                        <h5 class="mb-0"><i class="bi bi-pie-chart-fill"></i> Visualisasi Statistik Kapal Per Range GT</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            <i class="bi bi-info-circle"></i> Setiap chart menampilkan distribusi transaksi kapal nasional dan asing untuk masing-masing range GT
                        </p>
                        <div class="row">
                            <?php
                                $gtRanges = $shipStatsByGT->pluck('RANGE_GT')->unique();
                            ?>
                            <?php $__currentLoopData = $gtRanges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gtRange): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $gtData = $shipStatsByGT->where('RANGE_GT', $gtRange);
                                    $nasional = $gtData->where('JENIS_KAPAL_DARI_BENDERA', 'KAPAL NASIONAL')->first();
                                    $asing = $gtData->where('JENIS_KAPAL_DARI_BENDERA', 'KAPAL ASING')->first();
                                    $chartId = 'gtChart' . str_replace([' ', '-', '>', '<'], '', $gtRange);
                                ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card" style="border: 2px solid #8b5cf6;">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 text-center">
                                                <i class="bi bi-ship"></i> <?php echo e(str_replace(' GT', '', $gtRange)); ?>

                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="<?php echo e($chartId); ?>" style="max-height: 250px;"></canvas>
                                            <div class="mt-3">
                                                <!-- Kapal Nasional -->
                                                <div class="mb-3 p-3" style="background: #dcfce7; border-radius: 8px; border-left: 4px solid #22c55e;">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="badge bg-success"><i class="bi bi-flag-fill"></i> Kapal Nasional</span>
                                                        <strong><?php echo e($nasional ? number_format($nasional->total_transaksi) : 0); ?> transaksi</strong>
                                                    </div>
                                                    <div class="row text-center mt-2">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Pendapatan Pandu</small>
                                                            <strong class="text-success">Rp <?php echo e($nasional ? number_format($nasional->total_pendapatan_pandu, 0, ',', '.') : 0); ?></strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Pendapatan Tunda</small>
                                                            <strong class="text-success">Rp <?php echo e($nasional ? number_format($nasional->total_pendapatan_tunda, 0, ',', '.') : 0); ?></strong>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-2 pt-2" style="border-top: 1px solid #86efac;">
                                                        <small class="text-muted">Total Pendapatan:</small>
                                                        <div><strong class="text-success">Rp <?php echo e($nasional ? number_format($nasional->total_pendapatan, 0, ',', '.') : 0); ?></strong></div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Kapal Asing -->
                                                <div class="p-3" style="background: #dbeafe; border-radius: 8px; border-left: 4px solid #3b82f6;">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="badge bg-info"><i class="bi bi-globe"></i> Kapal Asing</span>
                                                        <strong><?php echo e($asing ? number_format($asing->total_transaksi) : 0); ?> transaksi</strong>
                                                    </div>
                                                    <div class="row text-center mt-2">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Pendapatan Pandu</small>
                                                            <strong class="text-info">Rp <?php echo e($asing ? number_format($asing->total_pendapatan_pandu, 0, ',', '.') : 0); ?></strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Pendapatan Tunda</small>
                                                            <strong class="text-info">Rp <?php echo e($asing ? number_format($asing->total_pendapatan_tunda, 0, ',', '.') : 0); ?></strong>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-2 pt-2" style="border-top: 1px solid #93c5fd;">
                                                        <small class="text-muted">Total Pendapatan:</small>
                                                        <div><strong class="text-info">Rp <?php echo e($asing ? number_format($asing->total_pendapatan, 0, ',', '.') : 0); ?></strong></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <!-- Pilot Cards -->
        <?php if($selectedPeriode != 'all' && $selectedBranch != 'all'): ?>
        <div class="row">
            <?php $__currentLoopData = $statistics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title text-primary mb-0">
                                    <i class="bi bi-person-badge"></i> <?php echo e($stat->NM_PERS_PANDU); ?>

                                </h5>
                                <span class="grt-gerak-badge">
                                    <i class="bi bi-speedometer2"></i> GRT | GERAK: <?php echo e(number_format($stat->total_grt, 0, ',', '.')); ?> | <?php echo e($stat->total_transaksi); ?>

                                </span>
                            </div>
                            <div class="mb-2">
                                <span class="badge bg-info">
                                    <i class="bi bi-clock-history"></i> Rata-Rata WT: <?php echo e(number_format($stat->rata_rata_wt, 2)); ?>

                                </span>
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock"></i> WT > 00:30: <?php echo e($stat->transaksi_wt_di_atas_30); ?> kali
                                </span>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <p class="text-muted mb-1">Pendapatan Pandu</p>
                                    <h6 class="text-success">Rp <?php echo e(number_format($stat->total_pendapatan_pandu, 0, ',', '.')); ?></h6>
                                </div>
                                <div class="col-6">
                                    <p class="text-muted mb-1">Pendapatan Tunda</p>
                                    <h6 class="text-info">Rp <?php echo e(number_format($stat->total_pendapatan_tunda, 0, ',', '.')); ?></h6>
                                </div>
                            </div>
                            <hr>
                            <div class="mb-2">
                                <i class="bi bi-bar-chart"></i> Total Transaksi: <strong><?php echo e($stat->total_transaksi); ?></strong>
                            </div>
                            
                            <!-- Realisasi Via Mobile/Web -->
                            <div class="mb-3">
                                <p class="text-muted mb-2"><i class="bi bi-device-ssd"></i> Realisasi Via:</p>
                                <div>
                                    <span class="via-badge via-mobile">
                                        <i class="bi bi-phone"></i> Mobile: <strong><?php echo e($stat->via_mobile); ?></strong>
                                    </span>
                                    <span class="via-badge via-web">
                                        <i class="bi bi-laptop"></i> Web: <strong><?php echo e($stat->via_web); ?></strong>
                                    </span>
                                    <?php if($stat->via_partial > 0): ?>
                                        <span class="via-badge">
                                            <i class="bi bi-puzzle"></i> Partial: <strong><?php echo e($stat->via_partial); ?></strong>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if($stat->ship_types && count($stat->ship_types) > 0): ?>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#ship-types-<?php echo e($loop->index); ?>">
                                        <i class="bi bi-ship"></i> Jenis Kapal Yang Dilayani (<?php echo e(count($stat->ship_types)); ?>)
                                    </button>
                                    
                                    <div class="collapse mt-3" id="ship-types-<?php echo e($loop->index); ?>">
                                        <div class="ship-types-container">
                                            <p class="text-muted mb-2"><i class="bi bi-ship"></i> Jenis Kapal:</p>
                                            <div>
                                                <?php $__currentLoopData = $stat->ship_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <span class="ship-type-badge">
                                                        <?php echo e($shipType->JN_KAPAL); ?> <strong>(<?php echo e($shipType->jumlah); ?>)</strong>
                                                    </span>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>

        <!-- CSV Upload Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-upload"></i> Upload File CSV</h5>
                        <form action="<?php echo e(route('upload.csv')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                                    <small class="text-muted">Format: CSV (maksimal 10MB) | <a href="/debug-periods" target="_blank">Debug Periode</a></small>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-upload"></i> Upload</button>
                                </div>
                                <div class="col-md-2">
                                    <a href="<?php echo e(route('clear.data')); ?>" class="btn btn-danger w-100" onclick="return confirm('Yakin ingin menghapus semua data?')">
                                        <i class="bi bi-trash"></i> Clear Data
                                    </a>
                                </div>
                            </div>
                        </form>
                        
                        <?php if(session('success')): ?>
                            <div class="alert alert-success mt-3">
                                <i class="bi bi-check-circle-fill"></i> <?php echo e(session('success')); ?>

                            </div>
                        <?php endif; ?>
                        
                        <?php if($errors->any()): ?>
                            <div class="alert alert-danger mt-3">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <strong>Error:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(session('import_errors') && count(session('import_errors')) > 0): ?>
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-info-circle-fill"></i>
                                <strong>Warning pada beberapa baris:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php $__currentLoopData = session('import_errors'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script>
        // Prepare data for charts
        const chartData = <?php echo json_encode($chartData ?? [], 15, 512) ?>;
        
        // Only create charts if data exists
        if (chartData && chartData.length > 0) {
            // Pandu Chart - tampilkan semua data
            const panduCtx = document.getElementById('panduChart').getContext('2d');
        new Chart(panduCtx, {
            type: 'bar',
            data: {
                labels: chartData.map(s => s.NM_PERS_PANDU),
                datasets: [{
                    label: 'Pendapatan Pemanduan',
                    data: chartData.map(s => s.total_pendapatan_pandu),
                    backgroundColor: 'rgba(102, 126, 234, 0.7)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Tunda Chart - tampilkan semua data
        const tundaCtx = document.getElementById('tundaChart').getContext('2d');
        new Chart(tundaCtx, {
            type: 'bar',
            data: {
                labels: chartData.map(s => s.NM_PERS_PANDU),
                datasets: [{
                    label: 'Pendapatan Penundaan',
                    data: chartData.map(s => s.total_pendapatan_tunda),
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
        }

        // Ship Statistics by GT Range Charts
        const shipStatsByGT = <?php echo json_encode($shipStatsByGT ?? [], 15, 512) ?>;
        
        if (shipStatsByGT && shipStatsByGT.length > 0) {
            // Group data by GT Range
            const gtRanges = [...new Set(shipStatsByGT.map(s => s.RANGE_GT))];
            
            gtRanges.forEach(gtRange => {
                const gtData = shipStatsByGT.filter(s => s.RANGE_GT === gtRange);
                const chartId = 'gtChart' + gtRange.replace(/[\s\-><]/g, '');
                const chartElement = document.getElementById(chartId);
                
                if (chartElement) {
                    const nasionalData = gtData.find(d => d.JENIS_KAPAL_DARI_BENDERA === 'KAPAL NASIONAL');
                    const asingData = gtData.find(d => d.JENIS_KAPAL_DARI_BENDERA === 'KAPAL ASING');
                    
                    const transaksiNasional = nasionalData ? nasionalData.total_transaksi : 0;
                    const transaksiAsing = asingData ? asingData.total_transaksi : 0;
                    const panduNasional = nasionalData ? nasionalData.total_pendapatan_pandu : 0;
                    const panduAsing = asingData ? asingData.total_pendapatan_pandu : 0;
                    const tundaNasional = nasionalData ? nasionalData.total_pendapatan_tunda : 0;
                    const tundaAsing = asingData ? asingData.total_pendapatan_tunda : 0;
                    
                    const total = transaksiNasional + transaksiAsing;
                    
                    new Chart(chartElement.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Kapal Nasional', 'Kapal Asing'],
                            datasets: [
                                {
                                    label: 'Total Transaksi',
                                    data: [transaksiNasional, transaksiAsing],
                                    backgroundColor: [
                                        'rgba(34, 197, 94, 0.8)',
                                        'rgba(59, 130, 246, 0.8)'
                                    ],
                                    borderColor: [
                                        'rgb(34, 197, 94)',
                                        'rgb(59, 130, 246)'
                                    ],
                                    borderWidth: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    labels: {
                                        padding: 15,
                                        font: {
                                            size: 11,
                                            weight: 'bold'
                                        },
                                        generateLabels: function(chart) {
                                            const data = chart.data;
                                            if (data.labels.length && data.datasets.length) {
                                                return data.labels.map((label, i) => {
                                                    const value = data.datasets[0].data[i];
                                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                                    return {
                                                        text: label + ': ' + value + ' (' + percentage + '%)',
                                                        fillStyle: data.datasets[0].backgroundColor[i],
                                                        hidden: false,
                                                        index: i
                                                    };
                                                });
                                            }
                                            return [];
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.parsed || 0;
                                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                            
                                            const panduValue = context.dataIndex === 0 ? panduNasional : panduAsing;
                                            const tundaValue = context.dataIndex === 0 ? tundaNasional : tundaAsing;
                                            const totalPendapatan = panduValue + tundaValue;
                                            
                                            return [
                                                label + ': ' + value + ' transaksi (' + percentage + '%)',
                                                'Pandu: Rp ' + panduValue.toLocaleString('id-ID'),
                                                'Tunda: Rp ' + tundaValue.toLocaleString('id-ID'),
                                                'Total: Rp ' + totalPendapatan.toLocaleString('id-ID')
                                            ];
                                        }
                                    }
                                }
                            },
                            cutout: '60%'
                        }
                    });
                }
            });
        }
        // Nota Summary donut (minimal)
        (function(){
            const terbit = <?php echo e($totalOverall['total_nota'] ?? 0); ?>;
            const batal = <?php echo e($totalOverall['nota_batal'] ?? 0); ?>;
            const menunggu = <?php echo e($totalOverall['menunggu_nota'] ?? 0); ?>;
            const belum = <?php echo e($totalOverall['belum_verifikasi'] ?? 0); ?>;
            const total = terbit + batal + menunggu + belum;

            const notaCtxEl = document.getElementById('notaSummaryChart');
            if (!notaCtxEl) return;

                const notaChart = new Chart(notaCtxEl.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Terbit','Batal','Menunggu','Belum Verif'],
                    datasets: [{
                        data: [terbit, batal, menunggu, belum],
                        backgroundColor: ['#3b82f6','#ef4444','#f59e0b','#6b7280'],
                        borderColor: ['#ffffff','#ffffff','#ffffff','#ffffff'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    radius: '90%',
                    cutout: '60%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const v = ctx.parsed || 0;
                                    const pct = total > 0 ? ((v/total)*100).toFixed(1) : '0.0';
                                    return ctx.label + ': ' + v + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                },
                plugins: [{
                    id: 'centerText',
                    afterDraw: chart => {
                        const w = chart.width, h = chart.height;
                        const ctx = chart.ctx;
                        ctx.restore();
                        const fontSize = Math.min(w, h) / 6;
                        ctx.font = fontSize + 'px sans-serif';
                        ctx.fillStyle = '#111827';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        const centerText = total > 0 ? Math.round((terbit/total)*100) + '%' : '0%';
                        ctx.fillText(centerText, w/2, h/2.1);
                        ctx.save();
                    }
                }]
            });
        })();

        // Custom semicircular gauge for Kecepatan Terbit (Bentuk 3)
        // Move data to canvas attributes and use external JS module for rendering/animation
        const speedVal = <?php echo e($totalOverall['kecepatan_terbit_nota'] ?? 0); ?>;
        const speedCanvas = document.getElementById('speedometerChart');
        if (speedCanvas) {
            speedCanvas.setAttribute('data-speed-value', speedVal);
            speedCanvas.setAttribute('aria-label', 'Kecepatan Terbit rata-rata dalam hari');
        }
    </script>
    <script src="/js/speedometer-gauge.js"></script>
    <script>
        // If this page was opened with preview_print=1, trigger print dialog
        (function(){
            try{
                const params = new URLSearchParams(window.location.search);
                if(params.get('preview_print') === '1'){
                    window.addEventListener('load', function(){
                        // Give browser a brief moment to render charts
                        setTimeout(function(){
                            window.print();
                            // Do not force-close; user may want to save
                        }, 700);
                    });
                }
            }catch(e){
                console.error('print preview init error', e);
            }
        })();
        
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
<?php /**PATH D:\project ai\lhgk\resources\views/dashboard.blade.php ENDPATH**/ ?>