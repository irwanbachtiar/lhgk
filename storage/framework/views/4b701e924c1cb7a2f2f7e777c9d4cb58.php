<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Operasional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .stat-card { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s ease; border: 1px solid rgba(0,0,0,0.05); }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
        .period-filter { background: white; padding: 15px; border-radius: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2.5rem; }
        .table-fixed { table-layout: fixed; }
        .table-fixed th:nth-child(1), .table-fixed td:nth-child(1) { width: 40%; }
        .table-fixed th:nth-child(n+2), .table-fixed td:nth-child(n+2) { width: 15%; }
        .small-card { min-height: 46px; }
        .small-card .badge { min-width: 28px; }
        /* soft colored header used for small list cards */
        .card-soft-header {
            background: linear-gradient(90deg, rgba(102,126,234,0.08), rgba(240,147,251,0.06));
            border-bottom: 1px solid rgba(0,0,0,0.04);
            font-weight: 600;
            color: #1f2937;
        }
        /* consistent section gap used across the page */
        .section-gap { margin-bottom: 4rem; }

        @media (max-width: 991px) {
            .section-gap { margin-bottom: 3rem; }
        }

        @media (max-width: 575px) {
            .section-gap { margin-bottom: 2rem; }
        }

        /* Stat row uses the same gap to keep sections consistent */
        .stat-row-spacing { margin-bottom: 4rem; }

        /* spacing between wrapped stat columns (controls vertical gaps when cards wrap to next line) */
        .stat-row-spacing > [class*="col-"] {
            margin-bottom: 1.5rem;
        }

        /* tighter spacing for specific stat columns (e.g., Web) */
        .stat-row-spacing > .stat-col-tight { margin-bottom: 0.5rem !important; }

        @media (max-width: 991px) {
            .stat-row-spacing > [class*="col-"] { margin-bottom: 1rem; }
        }

        @media (max-width: 575px) {
            .stat-row-spacing > [class*="col-"] { margin-bottom: 0.5rem; }
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

    <nav class="navbar navbar-dark mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-graph-up-arrow"></i> Dashboard Operasional</span>
            <div>
                <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-light btn-sm me-2"><i class="bi bi-graph-up-arrow"></i> Dashboard LHGK</a>
                <a href="<?php echo e(route('regional.revenue')); ?>" class="btn btn-light btn-sm me-2"><i class="bi bi-geo-alt"></i> Pendapatan Wilayah</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="period-filter">
                    <form method="GET" action="<?php echo e(route('dashboard.operasional')); ?>" class="row align-items-center">
                        <div class="col-md-2">
                            <label class="form-label"><i class="bi bi-funnel"></i> Filter:</label>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-building"></i> Cabang:</label>
                            <select name="cabang" class="form-select filter-input">
                                <option value="all" <?php echo e($selectedBranch == 'all' ? 'selected' : ''); ?>>Semua Cabang</option>
                                <?php $__currentLoopData = $regionalGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wilayah => $branches): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <optgroup label="<?php echo e($wilayah); ?>">
                                        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($branch); ?>" <?php echo e($selectedBranch == $branch ? 'selected' : ''); ?> title="<?php echo e($branch); ?>"><?php echo e(Str::limit($branch, 50)); ?></option>
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
                                    <option value="<?php echo e($period); ?>" <?php echo e($selectedPeriode == $period ? 'selected' : ''); ?>><?php echo e($period); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <button type="button" class="btn btn-primary ms-2" onclick="document.getElementById('globalLoading').style.display='flex'; this.closest('form').submit();">Apply</button>
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            <?php if($selectedPeriode != 'all' || $selectedBranch != 'all'): ?>
                                <a href="<?php echo e(route('dashboard.operasional')); ?>" class="btn btn-outline-secondary mt-4"><i class="bi bi-x-circle"></i> Reset Filter</a>
                                <a href="<?php echo e(route('dashboard.operasional.export', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch])); ?>" class="btn btn-primary mt-4 ms-2"><i class="bi bi-download"></i> Export Excel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <?php if($selectedPeriode == 'all' || $selectedBranch == 'all'): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                            <h5 class="mt-3">Pilih Cabang dan Periode untuk Menampilkan Data</h5>
                            <p class="mb-0">Silakan pilih <strong>Cabang</strong> dan <strong>Periode</strong> pada filter di atas untuk melihat statistik operasional.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>

            <!-- Stat Cards -->
            <div class="row stat-row-spacing">
                <div class="col-md-3">
                    <div class="card stat-card bg-white" style="border-left: 4px solid #667eea;">
                        <div class="card-body d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-person-badge fs-2" style="color: #667eea;"></i>
                            </div>
                            <div class="flex-grow-1 text-end">
                                <h3 class="text-dark mb-1"><?php echo e($stats['total_pandu'] ?? 0); ?></h3>
                                <p class="mb-0 text-muted small">Total Pandu</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-white" style="border-left: 4px solid #f093fb;">
                        <div class="card-body d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-ship fs-2" style="color: #f093fb;"></i>
                            </div>
                            <div class="flex-grow-1 text-end">
                                <h3 class="text-dark mb-1"><?php echo e($tundaDistinct ?? 0); ?></h3>
                                <p class="mb-0 text-muted small">Total Kapal Tunda</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-white" style="border-left: 4px solid #8bea66;">
                        <div class="card-body d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-receipt fs-2" style="color: #8bea66a;"></i>
                            </div>
                            <div class="flex-grow-1 text-end">
                                <h4 class="text-dark mb-1"><?php echo e(number_format($stats['total_transaksi'] ?? 0)); ?></h4>
                                <p class="mb-0 text-muted small">Total Transaksi</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-white" style="border-left: 4px solid #10b981;">
                        <div class="card-body d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-graph-up-arrow fs-2" style="color: #10b981;"></i>
                            </div>
                            <div class="flex-grow-1 text-end">
                                <h4 class="text-dark mb-1">&nbsp;</h4>
                                <p class="mb-0 text-muted small">&nbsp;</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 stat-col-tight">
                    <div class="card stat-card bg-white" style="border-left: 4px solid #667eea;">
                        <div class="card-body d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-globe2 fs-2" style="color: #667eea;"></i>
                            </div>
                            <div class="flex-grow-1 text-end">
                                <h3 class="text-dark mb-1"><?php echo e(number_format(optional($viaCounts)->web ?? 0)); ?></h3>
                                <p class="mb-0 text-muted small">Web</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card bg-white" style="border-left: 4px solid #f093fb;">
                        <div class="card-body d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-phone fs-2" style="color: #f093fb;"></i>
                            </div>
                            <div class="flex-grow-1 text-end">
                                <h3 class="text-dark mb-1"><?php echo e(number_format(optional($viaCounts)->mobile ?? 0)); ?></h3>
                                <p class="mb-0 text-muted small">Mobile</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card bg-white" style="border-left: 4px solid #10b981;">
                        <div class="card-body d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-three-dots fs-2" style="color: #10b981;"></i>
                            </div>
                            <div class="flex-grow-1 text-end">
                                <h3 class="text-dark mb-1"><?php echo e(number_format(optional($viaCounts)->partial ?? 0)); ?></h3>
                                <p class="mb-0 text-muted small">Parsial</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Nama Pandu dan Nama Tunda (dipindahkan ke atas section Jumlah Transaksi) -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header card-soft-header">Daftar Nama Pandu</div>
                        <div class="card-body">
                            <div style="max-height:220px; overflow:auto;">
                                <?php $totalPilots = !empty($pilotList) ? count($pilotList) : 0; ?>
                                <?php if($totalPilots > 1): ?>
                                    <?php $perCol = (int) ceil($totalPilots / 2); ?>
                                    <div class="row">
                                        <div class="col-6">
                                            <ol class="mb-0 ps-3" start="1">
                                                <?php $__currentLoopData = array_slice($pilotList, 0, $perCol); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li><?php echo e($p); ?></li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ol>
                                        </div>
                                        <div class="col-6">
                                            <ol class="mb-0 ps-3" start="<?php echo e($perCol + 1); ?>">
                                                <?php $__currentLoopData = array_slice($pilotList, $perCol); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li><?php echo e($p); ?></li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ol>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <ol class="mb-0 ps-3">
                                        <?php if($totalPilots > 0): ?>
                                            <?php $__currentLoopData = $pilotList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li><?php echo e($p); ?></li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php else: ?>
                                            <li>-</li>
                                        <?php endif; ?>
                                    </ol>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header card-soft-header">Daftar Nama Tunda</div>
                        <div class="card-body">
                                    <div style="max-height:220px; overflow:auto;">
                                        <?php if(!empty($tundaList) && count($tundaList) > 10): ?>
                                            <div class="row">
                                                <div class="col-6">
                                                    <ol class="mb-0 ps-3">
                                                        <?php $__currentLoopData = array_slice($tundaList, 0, 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <li><?php echo e($t); ?></li>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </ol>
                                                </div>
                                                <div class="col-6">
                                                    <ol class="mb-0 ps-3" start="11">
                                                        <?php $__currentLoopData = array_slice($tundaList, 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <li><?php echo e($t); ?></li>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </ol>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <ol class="mb-0 ps-3">
                                                <?php if(!empty($tundaList) && count($tundaList)): ?>
                                                    <?php $__currentLoopData = $tundaList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <li><?php echo e($t); ?></li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php else: ?>
                                                    <li>-</li>
                                                <?php endif; ?>
                                            </ol>
                                        <?php endif; ?>
                                    </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card section-gap">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Jumlah Transaksi berdasarkan Jenis Kapal</h5>
                        <div>
                            <button id="toggle-all" class="btn btn-sm btn-secondary">Toggle Semua Jenis Kapal</button>
                        </div>
                    </div>

                    <?php $groups = $transaksiByShip->groupBy('pelayaran_group'); ?>

                    
                    <?php $dalamTotal = isset($groups['Dalam Negeri']) ? $groups['Dalam Negeri']->sum('jumlah') : 0; ?>
                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <h6 class="mb-0">Dalam Negeri</h6>
                        <div class="text-muted">Total: <strong><?php echo e(number_format($dalamTotal)); ?></strong></div>
                    </div>
                    <div class="table-responsive">
                    <table class="table table-striped table-fixed mt-2">
                        <colgroup>
                            <col style="width:40%">
                            <col style="width:12%">
                            <col style="width:12%">
                            <col style="width:12%">
                            <col style="width:12%">
                            <col style="width:12%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Jenis Kapal</th>
                                <th class="text-end">Rata Rata Jam Tunda</th>
                                <th class="text-end">Rata Rata GT</th>
                                <th class="text-end">Rata Rata TRT</th>
                                <th class="text-end">Rata Rata AT</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(isset($groups['Dalam Negeri']) && $groups['Dalam Negeri']->isNotEmpty()): ?>
                                <?php $__currentLoopData = $groups['Dalam Negeri']->sortByDesc('jumlah'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="child-dalam">
                                        <td><?php echo e($it->JN_KAPAL); ?></td>
                                        <td class="text-end"><?php echo e(number_format((float)($it->avg_lama_tunda ?? 0), 2, ',', '.')); ?></td>
                                        <td class="text-end"><?php echo e(number_format((float)($it->avg_grt ?? 0), 2, ',', '.')); ?></td>
                                        <td class="text-end"><?php echo e(number_format((float)($it->avg_trt ?? 0), 2, ',', '.')); ?></td>
                                        <td class="text-end"><?php echo e(number_format((float)($it->avg_at ?? 0), 2, ',', '.')); ?></td>
                                        <td class="text-end"><?php echo e($it->jumlah); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <tr><td colspan="6">Tidak ada data Dalam Negeri</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>

                    

                    
                    <?php $luarTotal = isset($groups['Luar Negeri']) ? $groups['Luar Negeri']->sum('jumlah') : 0; ?>
                    <div class="d-flex align-items-center justify-content-between mt-4">
                        <h6 class="mb-0">Luar Negeri</h6>
                        <div class="text-muted">Total: <strong><?php echo e(number_format($luarTotal)); ?></strong></div>
                    </div>
                    <div class="table-responsive">
                    <table class="table table-striped table-fixed mt-2">
                        <colgroup>
                            <col style="width:40%">
                            <col style="width:12%">
                            <col style="width:12%">
                            <col style="width:12%">
                            <col style="width:12%">
                            <col style="width:12%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Jenis Kapal</th>
                                <th class="text-end">Rata Rata Jam Tunda</th>
                                <th class="text-end">Rata Rata GT</th>
                                <th class="text-end">Rata Rata TRT</th>
                                <th class="text-end">Rata Rata AT</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(isset($groups['Luar Negeri']) && $groups['Luar Negeri']->isNotEmpty()): ?>
                                <?php $__currentLoopData = $groups['Luar Negeri']->sortByDesc('jumlah'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="child-luar">
                                        <td><?php echo e($it->JN_KAPAL); ?></td>
                                        <td class="text-end"><?php echo e(number_format((float)($it->avg_lama_tunda ?? 0), 2, ',', '.')); ?></td>
                                        <td class="text-end"><?php echo e(number_format((float)($it->avg_grt ?? 0), 2, ',', '.')); ?></td>
                                        <td class="text-end"><?php echo e(number_format((float)($it->avg_trt ?? 0), 2, ',', '.')); ?></td>
                                        <td class="text-end"><?php echo e(number_format((float)($it->avg_at ?? 0), 2, ',', '.')); ?></td>
                                        <td class="text-end"><?php echo e($it->jumlah); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <tr><td colspan="6">Tidak ada data Luar Negeri</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle individual group rows by class (child-dalam / child-luar)
        function toggleClassRows(className) {
            document.querySelectorAll('tr').forEach(function(r) {
                if (!r.className) return;
                if (r.className.split(' ').indexOf(className) === -1) return;
                r.style.display = (r.style.display === 'none' || r.style.display === '') ? '' : 'none';
            });
        }

        // Global toggle button
        var toggleAll = document.getElementById('toggle-all');
        if (toggleAll) {
            toggleAll.addEventListener('click', function() {
                var anyVisible = false;
                document.querySelectorAll('tr').forEach(function(r) {
                    if (!r.className) return;
                    if (r.className.indexOf('child-') === 0) {
                        var style = window.getComputedStyle(r);
                        if (style.display !== 'none') anyVisible = true;
                    }
                });
                document.querySelectorAll('tr').forEach(function(r) {
                    if (!r.className) return;
                    if (r.className.indexOf('child-') === 0) {
                        r.style.display = anyVisible ? 'none' : '';
                    }
                });
            });
        }

        // Per-group toggle buttons (if present in header later)
        document.querySelectorAll('.toggle-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var target = btn.getAttribute('data-target');
                toggleClassRows(target);
            });
        });
        
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
    });
</script>
</html>
<?php /**PATH D:\project ai\lhgk\resources\views/dashboard-operasional.blade.php ENDPATH**/ ?>