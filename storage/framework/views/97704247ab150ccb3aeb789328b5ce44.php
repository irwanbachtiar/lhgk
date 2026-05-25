<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pantau Pengunjung - LHGK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --primary: #0d6efd;
            --secondary: #6c757d;
            --success: #198754;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #0dcaf0;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .container-main {
            max-width: 1400px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        .card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 15px 0;
        }

        .stat-card .label {
            font-size: 0.95rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card.blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-card.green {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
        }

        .stat-card.orange {
            background: linear-gradient(135deg, #f77f00 0%, #fcbf49 100%);
        }

        .stat-card.red {
            background: linear-gradient(135deg, #d62828 0%, #f77f00 100%);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-period {
            padding: 8px 20px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: #333;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-period:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        .btn-period.active {
            background: white;
            color: #667eea;
            font-weight: 600;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin: 30px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }

        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .table tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 30px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn {
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-outline-light {
            border: 2px solid white;
            color: white;
            background: transparent;
        }

        .btn-outline-light:hover {
            background: white;
            color: #667eea;
        }

        .pagination {
            margin-top: 20px;
        }

        .page-link {
            color: #667eea;
            border-color: #e0e0e0;
        }

        .page-link:hover {
            background-color: #667eea;
            border-color: #667eea;
            color: white;
        }

        .page-link.active {
            background-color: #667eea;
            border-color: #667eea;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .badge-device {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin: 2px;
        }

        .badge-device.desktop {
            background: #e3f2fd;
            color: #1976d2;
        }

        .badge-device.mobile {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .badge-device.tablet {
            background: #e8f5e9;
            color: #388e3c;
        }

        .info-box {
            background: rgba(102, 126, 234, 0.1);
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .filter-buttons {
                flex-direction: column;
            }

            .btn-period {
                width: 100%;
            }

            .stat-card .number {
                font-size: 2rem;
            }

            .section-title {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <i class="bi bi-graph-up"></i> LHGK Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">
                            <i class="bi bi-house"></i> Dashboard Utama
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/visitors">
                            <i class="bi bi-people"></i> Pantau Pengunjung
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container container-main mt-5">
        <!-- Info Box -->
        <div class="info-box">
            <i class="bi bi-info-circle"></i>
            <strong>Informasi:</strong> Halaman ini menampilkan statistik pengunjung aplikasi LHGK secara real-time. Data diperbarui otomatis setiap kali ada pengunjung baru.
        </div>

        <!-- Filter Periode -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="card-title mb-3">
                    <i class="bi bi-filter"></i> Pilih Periode
                </h6>
                <div class="filter-buttons">
                    <a href="?period=today" class="btn-period <?php echo e($period === 'today' ? 'active' : ''); ?>">
                        <i class="bi bi-calendar-day"></i> Hari Ini
                    </a>
                    <a href="?period=week" class="btn-period <?php echo e($period === 'week' ? 'active' : ''); ?>">
                        <i class="bi bi-calendar-week"></i> Minggu Ini
                    </a>
                    <a href="?period=month" class="btn-period <?php echo e($period === 'month' ? 'active' : ''); ?>">
                        <i class="bi bi-calendar-month"></i> Bulan Ini
                    </a>
                    <a href="?period=all" class="btn-period <?php echo e($period === 'all' ? 'active' : ''); ?>">
                        <i class="bi bi-calendar-range"></i> Semua Data
                    </a>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="/visitors/export?period=<?php echo e($period); ?>" class="btn btn-outline-light" target="_blank">
                <i class="bi bi-download"></i> Export CSV
            </a>
            <button class="btn btn-outline-light" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stat-card blue">
                    <i class="bi bi-people" style="font-size: 2rem;"></i>
                    <div class="number"><?php echo e($stats->total_visitors); ?></div>
                    <div class="label">Total Pengunjung</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card green">
                    <i class="bi bi-globe" style="font-size: 2rem;"></i>
                    <div class="number"><?php echo e($stats->unique_ips); ?></div>
                    <div class="label">IP Unik</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card orange">
                    <i class="bi bi-file-text" style="font-size: 2rem;"></i>
                    <div class="number"><?php echo e($stats->total_pages); ?></div>
                    <div class="label">Halaman Dikunjungi</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card red">
                    <i class="bi bi-devices" style="font-size: 2rem;"></i>
                    <div class="number"><?php echo e($stats->top_device->device ?? '-'); ?></div>
                    <div class="label">Device Populer</div>
                </div>
            </div>
        </div>

        <!-- Traffic Chart -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-graph-up"></i> Grafik Traffic
                </h5>
                <?php if(count($chartData->data) > 0): ?>
                    <div class="chart-container">
                        <canvas id="trafficChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-graph-up"></i>
                        <p>Tidak ada data untuk periode ini</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Row dengan 3 kolom -->
        <div class="row g-4 mb-4">
            <!-- Top Pages -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-file-earmark"></i> Halaman Teratas
                        </h5>
                        <?php if($topPages->count() > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php $__currentLoopData = $topPages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted"><?php echo e(substr($page->page_url, 0, 30)); ?><?php if(strlen($page->page_url) > 30): ?>...<?php endif; ?></small>
                                        </div>
                                        <span class="badge bg-primary"><?php echo e($page->total); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p>Tidak ada data</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Browser Distribution -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-globe"></i> Browser
                        </h5>
                        <?php if($browserDistribution->count() > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php $__currentLoopData = $browserDistribution; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $browser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><?php echo e($browser->browser); ?></small>
                                        <span class="badge bg-info"><?php echo e($browser->total); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p>Tidak ada data</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- OS Distribution -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-cpu"></i> Operating System
                        </h5>
                        <?php if($osDistribution->count() > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php $__currentLoopData = $osDistribution; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $os): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><?php echo e($os->os); ?></small>
                                        <span class="badge bg-warning"><?php echo e($os->total); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p>Tidak ada data</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Device Distribution -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-phone"></i> Distribusi Device
                </h5>
                <?php if($deviceDistribution->count() > 0): ?>
                    <div class="row">
                        <?php $__currentLoopData = $deviceDistribution; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="text-center">
                                    <div class="badge-device <?php echo e(strtolower($device->device)); ?>">
                                        <?php echo e($device->device); ?>

                                    </div>
                                    <div style="font-size: 1.5rem; font-weight: bold; margin-top: 10px;">
                                        <?php echo e($device->total); ?>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Tidak ada data</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Latest Visitors -->
        <div class="section-title">
            <i class="bi bi-clock-history"></i> Pengunjung Terbaru
        </div>
        <div class="card mb-4">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>IP Address</th>
                            <th>Browser</th>
                            <th>OS</th>
                            <th>Device</th>
                            <th>Device Name</th>
                            <th>Halaman</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $latestVisitors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visitor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <small class="text-muted"><?php echo e($visitor->visited_at->format('d M Y H:i:s')); ?></small>
                                </td>
                                <td><?php echo e($visitor->ip_address); ?></td>
                                <td>
                                    <small><?php echo e($visitor->browser ?? '-'); ?></small>
                                </td>
                                <td>
                                    <small><?php echo e($visitor->os ?? '-'); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo e($visitor->device); ?></span>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo e($visitor->device_name ?? '-'); ?></small>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo e(substr($visitor->page_url, 0, 40)); ?><?php if(strlen($visitor->page_url) > 40): ?>...<?php endif; ?></small>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
                                    <p class="mt-2">Tidak ada data pengunjung untuk periode ini</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if($latestVisitors->hasPages()): ?>
                <div class="card-footer">
                    <?php echo e($latestVisitors->render('pagination::bootstrap-5')); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 mt-5" style="color: white;">
        <p class="mb-0">© 2026 LHGK Dashboard - Pantau Pengunjung Aplikasi</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Traffic Chart
        const trafficChart = document.getElementById('trafficChart');
        if (trafficChart) {
            const ctx = trafficChart.getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($chartData->labels); ?>,
                    datasets: [{
                        label: 'Pengunjung',
                        data: <?php echo json_encode($chartData->data); ?>,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#667eea',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 7,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Auto refresh setiap 5 menit
        setInterval(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
<?php /**PATH D:\project ai\lhgk\resources\views/visitors/monitor.blade.php ENDPATH**/ ?>