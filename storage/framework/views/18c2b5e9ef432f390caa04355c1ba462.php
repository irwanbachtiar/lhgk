<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data SAP (ZFI039) - Dashboard LHGK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card { border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background: white; margin-bottom: 20px; }
        .filter-section { background: white; padding: 15px 20px; border-radius: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .table-container { padding: 20px; overflow-x: auto; }
        .table th { white-space: nowrap; background-color: #f1f5f9; }
        .table td { white-space: nowrap; vertical-align: middle; }
        
        /* Global Loading Animation */
        .global-loading-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(3px);
            z-index: 9999; display: none; align-items: center; justify-content: center;
        }
        .global-loading-content {
            background: white; padding: 40px; border-radius: 20px; text-align: center;
        }
        .global-loading-spinner {
            width: 60px; height: 60px; border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea; border-radius: 50%;
            animation: spin 1s linear infinite; margin: 0 auto 20px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <!-- Global Loading Overlay -->
    <div id="globalLoading" class="global-loading-overlay">
        <div class="global-loading-content">
            <div class="global-loading-spinner"></div>
            <p class="global-loading-text mb-0" id="loadingText">Memuat data...</p>
        </div>
    </div>

    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-database-fill"></i> Data SAP (ZFI039)</span>
            <div>
                <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-graph-up-arrow"></i> Dashboard LHGK
                </a>
                <a href="<?php echo e(route('monitoring.nota')); ?>" class="btn btn-light btn-sm">
                    <i class="bi bi-file-earmark-text"></i> Monitoring Nota
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <?php if(isset($selectedPeriode) && isset($periods)): ?>
        <div class="filter-section">
            <form method="GET" action="<?php echo e(route('sap')); ?>" class="row align-items-center">
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-0"><i class="bi bi-funnel"></i> Filter Periode:</label>
                </div>
                <div class="col-md-4">
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
                <div class="col-md-6">
                    <?php if($selectedPeriode != 'all'): ?>
                        <a href="<?php echo e(route('sap')); ?>" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-x-circle"></i> Reset Filter
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <?php if($error): ?>
                    <div class="alert alert-danger shadow-sm">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Error:</strong> <?php echo e($error); ?>

                    </div>
                <?php else: ?>
                    <h5 class="mb-3"><i class="bi bi-pie-chart-fill text-primary"></i> Pendapatan Berdasarkan Deskripsi</h5>
                    <div class="row mb-4">
                        <!-- Card Total Pendapatan (Dominan) -->
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card h-100 text-white shadow-lg" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border: none;">
                                <div class="card-body">
                                    <h6 class="text-white-50 mb-2"><i class="bi bi-cash-stack text-white"></i> Total Pendapatan</h6>
                                    <h4 class="mb-0 text-white">Rp <?php echo e(number_format((float)$totalPendapatanSummary, 0, ',', '.')); ?></h4>
                                    <?php if($selectedPeriode != 'all'): ?>
                                        <div class="mt-2 pt-2 border-top border-white-50" style="border-color: rgba(255,255,255,0.2) !important;">
                                            <small class="text-white-50"><i class="bi bi-calendar-range"></i> YTD: <strong class="text-white">Rp <?php echo e(number_format((float)$totalPendapatanSummaryYtd, 0, ',', '.')); ?></strong></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    <?php if(isset($summaryData) && $summaryData->count() > 0): ?>
                        <?php $colors = ['#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#0ea5e9']; ?>
                        <?php $__currentLoopData = $summaryData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-3 mb-3">
                                    <div class="card stat-card h-100" style="border-left: 4px solid <?php echo e($colors[$index % count($colors)]); ?>;">
                                        <div class="card-body">
                                            <h6 class="text-muted mb-2 text-truncate" title="<?php echo e($item->description); ?>"><?php echo e($item->description); ?></h6>
                                            <h4 class="mb-0 text-dark">Rp <?php echo e(number_format((float)$item->total_pendapatan, 0, ',', '.')); ?></h4>
                                            <?php if($selectedPeriode != 'all' && isset($summaryDataYtdMap[$item->description])): ?>
                                                <div class="mt-2 pt-2 border-top">
                                                    <small class="text-muted"><i class="bi bi-calendar-range"></i> YTD: <strong>Rp <?php echo e(number_format((float)$summaryDataYtdMap[$item->description], 0, ',', '.')); ?></strong></small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                    </div>

                    <!-- Section Chart Pendapatan Branch -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Grafik Pendapatan Berdasarkan Branch</h5>
                                </div>
                                <div class="card-body">
                                    <div style="height: 400px; width: 100%;">
                                        <canvas id="branchRevenueChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if(isset($ports) && count($ports) > 0): ?>
                        <h5 class="mb-3 mt-4"><i class="bi bi-geo-alt-fill text-success"></i> Daftar SPJM Ports</h5>
                        <div class="row mb-4">
                            <?php $__currentLoopData = $ports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $port): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $portAmount = isset($portDataMap[$port]) ? $portDataMap[$port] : 0;
                                    $portAmountYtd = isset($portDataYtdMap[$port]) ? $portDataYtdMap[$port] : 0;
                                    $portNota = isset($portNotaMap[$port]) ? $portNotaMap[$port] : 0;
                                ?>
                                <div class="col-md-2 col-sm-3 mb-2">
                                    <div class="card stat-card h-100" style="border-top: 3px solid #10b981; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)';">
                                        <div class="card-body py-3">
                                            <p class="mb-2 text-dark fw-semibold small"><?php echo e($port); ?></p>
                                            <p class="mb-0 text-success fw-bold text-truncate" title="Rp <?php echo e(number_format($portAmount, 0, ',', '.')); ?>">
                                                Rp <?php echo e($portAmount > 0 ? number_format($portAmount, 0, ',', '.') : '0'); ?>

                                            </p>
                                            <div class="mt-2 pt-2 border-top border-light">
                                                <small class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-file-earmark-text"></i> Nota: <strong><?php echo e(number_format($portNota, 0, ',', '.')); ?></strong></small>
                                            </div>
                                            <?php if($selectedPeriode != 'all' && isset($isYtdValid) && $isYtdValid): ?>
                                                <div class="mt-1">
                                                    <small class="text-muted" style="font-size: 0.7rem;">YTD: Rp <?php echo e(number_format($portAmountYtd, 0, ',', '.')); ?></small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Section Customer Per Cabang -->
                    <?php if(isset($customersPerBranch) && count($customersPerBranch) > 0): ?>
                        <h5 class="mb-3 mt-4"><i class="bi bi-people-fill text-info"></i> Daftar Customer Per Cabang</h5>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <div class="accordion" id="accordionCustomers">
                                            <?php $__currentLoopData = $ports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $port): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if(isset($customersPerBranch[$port]) && count($customersPerBranch[$port]) > 0): ?>
                                                    <?php $slug = \Illuminate\Support\Str::slug($port); ?>
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="heading-<?php echo e($slug); ?>">
                                                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo e($slug); ?>" aria-expanded="false" aria-controls="collapse-<?php echo e($slug); ?>">
                                                                <?php echo e($port); ?> <span class="badge bg-primary ms-2"><?php echo e(count($customersPerBranch[$port])); ?> Customer</span>
                                                            </button>
                                                        </h2>
                                                        <div id="collapse-<?php echo e($slug); ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo e($slug); ?>" data-bs-parent="#accordionCustomers">
                                                            <div class="accordion-body p-0">
                                                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                                                    <table class="table table-hover table-striped mb-0">
                                                                        <thead class="table-light sticky-top">
                                                                            <tr>
                                                                                <th class="ps-4" style="width: 60px;">No</th>
                                                                                <th>Nama Customer</th>
                                                                                <th class="text-center">Total Nota</th>
                                                                                <th class="text-end pe-4">Total Pendapatan</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php $__currentLoopData = $customersPerBranch[$port]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $cust): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <tr>
                                                                                    <td class="ps-4"><?php echo e($index + 1); ?></td>
                                                                                    <td><?php echo e($cust->customer); ?></td>
                                                                                    <td class="text-center"><span class="badge bg-secondary"><?php echo e(number_format($cust->total_nota ?? 0, 0, ',', '.')); ?></span></td>
                                                                                    <td class="text-end pe-4 text-success fw-semibold">Rp <?php echo e(number_format($cust->total_pendapatan ?? 0, 0, ',', '.')); ?></td>
                                                                                </tr>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Section Daftar Customer Lengkap -->
                    <?php if(isset($allCustomers) && $allCustomers->count() > 0): ?>
                        <h5 class="mb-3 mt-4"><i class="bi bi-shop-window text-warning"></i> Daftar Customer</h5>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 60px;">No</th>
                                                        <th>Nama Customer</th>
                                                        <th class="text-center" style="width: 120px;">Jumlah Transaksi</th>
                                                        <th class="text-end" style="width: 180px;">Total Revenue</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__currentLoopData = $allCustomers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr>
                                                            <td><?php echo e($index + 1); ?></td>
                                                            <td class="fw-semibold"><?php echo e($customer->customer_name); ?></td>
                                                            <td class="text-center">
                                                                <span class="badge bg-info"><?php echo e($customer->transaction_count); ?></span>
                                                            </td>
                                                            <td class="text-end text-success fw-semibold">
                                                                Rp <?php echo e(number_format((float)$customer->total_revenue, 0, ',', '.')); ?>

                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="stat-card">
                        <div class="table-container">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0"><i class="bi bi-table text-primary"></i> Tabel ZFI039</h5>
                                <div class="d-flex gap-2 align-items-center">
                                    <?php if(method_exists($tableData, 'total')): ?>
                                        <span class="badge bg-secondary px-3 py-2">Total: <?php echo e(number_format($tableData->total())); ?> Baris</span>
                                    <?php endif; ?>
                                    <select id="rowsPerPageSelect" class="form-select" style="width: 100px;" onchange="changeRowsPerPage(this.value)">
                                        <option value="25" <?php echo e($rowsPerPage == 25 ? 'selected' : ''); ?>>25 Baris</option>
                                        <option value="50" <?php echo e($rowsPerPage == 50 ? 'selected' : ''); ?>>50 Baris</option>
                                        <option value="100" <?php echo e($rowsPerPage == 100 ? 'selected' : ''); ?>>100 Baris</option>
                                        <option value="200" <?php echo e($rowsPerPage == 200 ? 'selected' : ''); ?>>200 Baris</option>
                                        <option value="500" <?php echo e($rowsPerPage == 500 ? 'selected' : ''); ?>>500 Baris</option>
                                    </select>
                                </div>
                            </div>

                            <?php if(count($columns) > 0 && $tableData->count() > 0): ?>
                                <!-- Filter Row -->
                                <div class="mb-3 p-3 bg-light rounded" id="filterRow" style="display: none;">
                                    <div class="row g-2">
                                        <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="col-md-<?php echo e(12 / min(count($columns), 6)); ?>">
                                                <input type="text" class="form-control form-control-sm" placeholder="Filter <?php echo e(str_replace('_', ' ', $column)); ?>" data-column="<?php echo e($column); ?>" onkeyup="filterTable()">
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <button class="btn btn-sm btn-outline-primary" id="toggleFilterBtn" onclick="toggleFilter()">
                                        <i class="bi bi-funnel"></i> Tampilkan Filter
                                    </button>
                                </div>

                                <table class="table table-hover table-bordered table-striped" id="zfi039Table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 50px;">No</th>
                                            <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <th class="text-capitalize"><?php echo e(str_replace('_', ' ', $column)); ?></th>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody">
                                        <?php $__currentLoopData = $tableData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td class="text-center"><?php echo e(method_exists($tableData, 'firstItem') ? $tableData->firstItem() + $index : $index + 1); ?></td>
                                                <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $val = $row->{$column} ?? null;
                                                    ?>
                                                    <td>
                                                        <?php if(in_array(strtolower($column), ['amount', 'nominal', 'total', 'revenue', 'pendapatan', 'total_pendapatan', 'pendapatan_idr']) && is_numeric($val)): ?>
                                                            Rp <?php echo e(number_format((float)$val, 0, ',', '.')); ?>

                                                        <?php elseif(strtolower($column) === 'status'): ?>
                                                            <span class="badge <?php echo e($val == 'CLEARED' ? 'bg-success' : 'bg-warning text-dark'); ?>"><?php echo e($val); ?></span>
                                                        <?php else: ?>
                                                            <?php echo e($val ?? '-'); ?>

                                                        <?php endif; ?>
                                                    </td>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>

                                <?php if(method_exists($tableData, 'hasPages') && $tableData->hasPages()): ?>
                                    <div class="d-flex justify-content-end mt-4">
                                        <?php echo e($tableData->appends(request()->query())->links('pagination::bootstrap-5')); ?>

                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-info text-center py-4 my-3">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Tabel zfi039 kosong atau tidak memiliki data.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function changeRowsPerPage(value) {
            const params = new URLSearchParams(window.location.search);
            params.set('rows', value);
            window.location.href = window.location.pathname + '?' + params.toString();
        }

        function toggleFilter() {
            const filterRow = document.getElementById('filterRow');
            const toggleBtn = document.getElementById('toggleFilterBtn');
            if (filterRow.style.display === 'none') {
                filterRow.style.display = 'block';
                toggleBtn.textContent = '✕ Sembunyikan Filter';
                toggleBtn.classList.remove('btn-outline-primary');
                toggleBtn.classList.add('btn-primary');
            } else {
                filterRow.style.display = 'none';
                toggleBtn.textContent = '⊕ Tampilkan Filter';
                toggleBtn.classList.remove('btn-primary');
                toggleBtn.classList.add('btn-outline-primary');
            }
        }

        function filterTable() {
            const table = document.getElementById('zfi039Table');
            const tbody = document.getElementById('tableBody');
            const rows = tbody.getElementsByTagName('tr');
            const filterInputs = document.querySelectorAll('#filterRow input[data-column]');

            // Build filter object
            const filters = {};
            filterInputs.forEach(input => {
                filters[input.dataset.column] = input.value.toLowerCase();
            });

            // Filter rows
            let visibleRows = 0;
            for (let row of rows) {
                const cells = row.getElementsByTagName('td');
                let shouldShow = true;

                // Check each column filter
                filterInputs.forEach((input, index) => {
                    const columnFilter = filters[input.dataset.column];
                    if (columnFilter) {
                        // index + 1 because first column is "No"
                        const cellText = cells[index + 1] ? cells[index + 1].textContent.toLowerCase() : '';
                        if (!cellText.includes(columnFilter)) {
                            shouldShow = false;
                        }
                    }
                });

                row.style.display = shouldShow ? '' : 'none';
                if (shouldShow) visibleRows++;
            }

            // Show message if no rows match
            if (visibleRows === 0) {
                tbody.innerHTML = '<tr><td colspan="' + document.querySelectorAll('#zfi039Table th').length + '" class="text-center text-muted py-4">Tidak ada data yang sesuai dengan filter</td></tr>';
            }
        }

        // Reset filter when clearing inputs
        document.addEventListener('DOMContentLoaded', function() {
            const filterInputs = document.querySelectorAll('#filterRow input[data-column]');
            filterInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.value === '') {
                        // Re-filter to show all rows again if all filters are empty
                        let hasFilter = false;
                        filterInputs.forEach(inp => {
                            if (inp.value !== '') hasFilter = true;
                        });
                        if (!hasFilter) location.reload();
                    }
                });
            });
        });
    </script>
<script>
        document.addEventListener("DOMContentLoaded", function() {
            // Ambil data yang dipassing dari Controller
            const ports = <?php echo json_encode($ports); ?>;
            const portDataMap = <?php echo json_encode($portDataMap); ?>;
            const portDataYtdMap = <?php echo json_encode($portDataYtdMap); ?>;
            const isPeriodeSelected = "<?php echo e($selectedPeriode); ?>" !== "all";

            // Filter port agar hanya menampilkan branch yang memiliki pendapatan > 0
            const activePorts = ports.filter(port => (portDataMap[port] && portDataMap[port] > 0) || (portDataYtdMap[port] && portDataYtdMap[port] > 0));

            // Map data ke array sesuai urutan port yang aktif
            const revenueData = activePorts.map(port => portDataMap[port] || 0);
            const ytdData = activePorts.map(port => portDataYtdMap[port] || 0);

            const datasets = [
                {
                    label: isPeriodeSelected ? 'Pendapatan Bulan Ini' : 'Total Pendapatan',
                    data: revenueData,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }
            ];

            // Jika filter periode aktif, tambahkan dataset untuk nilai YTD sebagai perbandingan
            if (isPeriodeSelected) {
                datasets.push({
                    label: 'Pendapatan YTD',
                    data: ytdData,
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                });
            }

            const chartElement = document.getElementById('branchRevenueChart');
            if (chartElement) {
                const ctx = chartElement.getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: activePorts,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        // Format angka menjadi format Rupiah singkatan (Misal: 1 M, 500 Jt)
                                        if (value >= 1e9) {
                                            return 'Rp ' + (value / 1e9).toFixed(1) + ' M';
                                        } else if (value >= 1e6) {
                                            return 'Rp ' + (value / 1e6).toFixed(1) + ' Jt';
                                        }
                                        return 'Rp ' + value;
                                    }
                                }
                            },
                            x: {
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            // Format tooltip dengan format uang yang lengkap
                                            label += new Intl.NumberFormat('id-ID', {
                                                style: 'currency',
                                                currency: 'IDR',
                                                maximumFractionDigits: 0
                                            }).format(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            },
                            legend: {
                                position: 'top',
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html><?php /**PATH D:\project ai\lhgk\resources\views/sap.blade.php ENDPATH**/ ?>