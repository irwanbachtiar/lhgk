<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pendapatan Per Wilayah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .main-content {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .filter-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .wilayah-1 { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .wilayah-2 { 
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .wilayah-3 { 
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        .wilayah-4 { 
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .period-title {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-light mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1" style="color: #667eea;">
                <i class="bi bi-graph-up"></i> Dashboard Pendapatan Per Wilayah
            </span>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-sm me-2">
                    <i class="bi bi-graph-up-arrow"></i> Dashboard LHGK
                </a>
                <a href="{{ route('monitoring.nota') }}" class="btn btn-outline-primary btn-sm me-2">
                    <i class="bi bi-file-earmark-text"></i> Monitoring Nota
                </a>
                <a href="{{ route('regional.revenue') }}" class="btn btn-outline-primary btn-sm me-2">
                    <i class="bi bi-geo-alt"></i> Regional Revenue
                </a>
                <a href="{{ route('analisis.kelelahan') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-activity"></i> Analisis Kelelahan
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="main-content">
            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" action="{{ route('dashboard.wilayah') }}" class="row align-items-center">
                    <div class="col-md-2">
                        <label class="form-label"><i class="bi bi-funnel"></i> Filter Periode:</label>
                    </div>
                    <div class="col-md-4">
                        <select name="periode" class="form-select" onchange="this.form.submit()">
                            <option value="all" {{ $selectedPeriode == 'all' ? 'selected' : '' }}>Semua Periode</option>
                            @foreach($periods as $period)
                                <option value="{{ $period }}" {{ $selectedPeriode == $period ? 'selected' : '' }}>
                                    {{ $period }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        @if($selectedPeriode != 'all')
                            <a href="{{ route('dashboard.wilayah') }}" class="btn btn-light">
                                <i class="bi bi-x-circle"></i> Reset Filter
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            @if($selectedPeriode == 'all')
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle fs-1"></i>
                    <h5 class="mt-3">Silakan pilih periode untuk melihat data pendapatan per wilayah</h5>
                    <p class="mb-0">Data akan dikelompokkan berdasarkan 4 wilayah regional.</p>
                </div>
            @else
            <!-- Period Title -->
            <div class="period-title">
                <i class="bi bi-calendar-event"></i> 
                Periode {{ $selectedPeriode }}
            </div>

            @php
                // Use controller-provided global totals (sum by INVOICE_DATE from entire table)
                // $totalPandu and $totalTundaRevenue are already computed in controller
                $totalAll = $totalPandu + $totalTundaRevenue;
                $totalTransaksi = array_sum(array_column($regionalData, 'total_transaksi'));

                // Helper function to format currency in millions
                function formatToM($amount) {
                    return number_format($amount / 1000000, 2, ',', '.') . ' juta';
                }
            @endphp

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Total Transaksi</h6>
                                <h3 class="mb-0">{{ number_format($totalTransaksi) }}</h3>
                            </div>
                            <div>
                                <i class="bi bi-receipt fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Pendapatan Pandu</h6>
                                <h4 class="mb-0">Rp {{ formatToM($totalPandu) }}</h4>
                            </div>
                            <div>
                                <i class="bi bi-person-badge fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Pendapatan Tunda</h6>
                                <h4 class="mb-0">Rp {{ formatToM($totalTundaRevenue) }}</h4>
                            </div>
                            <div>
                                <i class="bi bi-water fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Total Pendapatan</h6>
                                <h4 class="mb-0">Rp {{ formatToM($totalAll) }}</h4>
                            </div>
                            <div>
                                <i class="bi bi-cash-coin fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row">
                <!-- Bar Chart - Comparison -->
                <div class="col-md-7">
                    <div class="chart-container" style="height: 350px;">
                        <h5 class="mb-3"><i class="bi bi-bar-chart"></i> Perbandingan Pendapatan Per Wilayah</h5>
                        <div style="height: 280px;">
                            <canvas id="comparisonChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Pie Chart - Distribution -->
                <div class="col-md-5">
                    <div class="chart-container" style="height: 350px;">
                        <h5 class="mb-3"><i class="bi bi-pie-chart"></i> Distribusi Total Pendapatan</h5>
                        <div style="height: 280px;">
                            <canvas id="distributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wilayah Cards -->
            <div class="row mt-4">
                @php
                    $wilayahClasses = ['wilayah-1', 'wilayah-2', 'wilayah-3', 'wilayah-4'];
                    $index = 0;
                @endphp
                @foreach($regionalData as $wilayah => $data)
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card {{ $wilayahClasses[$index] }}">
                            <h5 class="mb-3"><i class="bi bi-geo-alt-fill"></i> {{ $wilayah }}</h5>
                            <div class="mb-2">
                                <small>Transaksi</small>
                                <h6>{{ number_format($data['total_transaksi']) }} transaksi</h6>
                            </div>
                            <div class="mb-2">
                                <small>Pendapatan Pandu</small>
                                <h6>Rp {{ formatToM($data['pandu_revenue']) }}</h6>
                            </div>
                            <div class="mb-2">
                                <small>Pendapatan Tunda</small>
                                <h6>Rp {{ formatToM($data['tunda_revenue']) }}</h6>
                            </div>
                            <hr style="border-color: rgba(255,255,255,0.3);">
                            <div>
                                <strong>Total Pendapatan</strong>
                                <h4>Rp {{ formatToM($data['total_revenue']) }}</h4>
                            </div>
                        </div>
                    </div>
                    @php $index++; @endphp
                @endforeach
            </div>

            <!-- Detail Table -->
            <div class="chart-container mt-4">
                <h5 class="mb-3"><i class="bi bi-table"></i> Detail Pendapatan Per Wilayah</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>Wilayah</th>
                                <th class="text-end">Transaksi</th>
                                <th class="text-end">Pendapatan Pandu</th>
                                <th class="text-end">Pendapatan Tunda</th>
                                <th class="text-end">Total Pendapatan</th>
                                <th class="text-end">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($regionalData as $wilayah => $data)
                                <tr>
                                    <td><strong>{{ $wilayah }}</strong></td>
                                    <td class="text-end">{{ number_format($data['total_transaksi']) }}</td>
                                    <td class="text-end">Rp {{ formatToM($data['pandu_revenue']) }}</td>
                                    <td class="text-end">Rp {{ formatToM($data['tunda_revenue']) }}</td>
                                    <td class="text-end"><strong>Rp {{ formatToM($data['total_revenue']) }}</strong></td>
                                    <td class="text-end">
                                        @if($totalAll > 0)
                                            <span class="badge bg-primary">{{ number_format(($data['total_revenue'] / $totalAll) * 100, 1) }}%</span>
                                        @else
                                            <span class="badge bg-secondary">0%</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th>TOTAL</th>
                                <th class="text-end">{{ number_format($totalTransaksi) }}</th>
                                <th class="text-end">Rp {{ formatToM($totalPandu) }}</th>
                                <th class="text-end">Rp {{ formatToM($totalTundaRevenue) }}</th>
                                <th class="text-end">Rp {{ formatToM($totalAll) }}</th>
                                <th class="text-end"><span class="badge bg-success">100%</span></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        @if($selectedPeriode != 'all')
        // Prepare data for charts
        const regionalData = @json($regionalData);
        const wilayahLabels = Object.keys(regionalData);
        const panduData = wilayahLabels.map(w => regionalData[w].pandu_revenue);
        const tundaData = wilayahLabels.map(w => regionalData[w].tunda_revenue);
        const totalData = wilayahLabels.map(w => regionalData[w].total_revenue);

        // Comparison Chart - Bar Chart
        const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
        const comparisonChart = new Chart(comparisonCtx, {
            type: 'bar',
            data: {
                labels: wilayahLabels,
                datasets: [
                    {
                        label: 'Pendapatan Pandu',
                        data: panduData,
                        backgroundColor: 'rgba(102, 126, 234, 0.85)',
                        borderColor: 'rgb(102, 126, 234)',
                        borderWidth: 2,
                        borderRadius: 8,
                        barThickness: 'flex',
                        maxBarThickness: 60
                    },
                    {
                        label: 'Pendapatan Tunda',
                        data: tundaData,
                        backgroundColor: 'rgba(79, 172, 254, 0.85)',
                        borderColor: 'rgb(79, 172, 254)',
                        borderWidth: 2,
                        borderRadius: 8,
                        barThickness: 'flex',
                        maxBarThickness: 60
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2.5,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 13,
                                weight: 'bold'
                            },
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'Rp ' + (context.parsed.y / 1000000).toFixed(2) + ' juta';
                                return label;
                            },
                            footer: function(tooltipItems) {
                                let total = 0;
                                tooltipItems.forEach(function(tooltipItem) {
                                    total += tooltipItem.parsed.y;
                                });
                                return 'Total: Rp ' + (total / 1000000).toFixed(2) + ' juta';
                            }
                        }
                    },
                    datalabels: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: 'bold'
                            },
                            color: '#666'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#666',
                            callback: function(value) {
                                if (value >= 1000000000) {
                                    return 'Rp ' + (value / 1000000000).toFixed(1) + 'B';
                                } else if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(0) + 'M';
                                } else if (value >= 1000) {
                                    return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                                }
                                return 'Rp ' + value;
                            }
                        }
                    }
                }
            }
        });

        // Distribution Chart - Pie Chart
        const distributionCtx = document.getElementById('distributionChart').getContext('2d');
        const distributionChart = new Chart(distributionCtx, {
            type: 'pie',
            data: {
                labels: wilayahLabels,
                datasets: [{
                    data: totalData,
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(240, 147, 251, 0.8)',
                        'rgba(79, 172, 254, 0.8)',
                        'rgba(67, 233, 123, 0.8)'
                    ],
                    borderColor: [
                        'rgb(102, 126, 234)',
                        'rgb(240, 147, 251)',
                        'rgb(79, 172, 254)',
                        'rgb(67, 233, 123)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1.2,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right',
                        labels: {
                            padding: 10,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': Rp ' + (value / 1000000).toFixed(2) + ' juta (' + percentage + '%)';
                            }
                        }
                    }
                },
                plugins: [{
                    id: 'customLabels',
                    afterDatasetsDraw: function(chart) {
                        const ctx = chart.ctx;
                        chart.data.datasets.forEach(function(dataset, datasetIndex) {
                            const meta = chart.getDatasetMeta(datasetIndex);
                            if (!meta.hidden) {
                                meta.data.forEach(function(element, index) {
                                    const data = dataset.data[index];
                                    const total = dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((data / total) * 100).toFixed(1);
                                    
                                    // Draw text
                                    ctx.fillStyle = '#000';
                                    ctx.font = 'bold 11px Arial';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';
                                    
                                    const position = element.tooltipPosition();
                                    const text1 = 'Rp ' + (data / 1000000).toFixed(1) + ' juta';
                                    const text2 = '(' + percentage + '%)';
                                    
                                    ctx.fillText(text1, position.x, position.y - 8);
                                    ctx.fillText(text2, position.x, position.y + 8);
                                });
                            }
                        });
                    }
                }]
            }
        });
        @endif
    </script>
</body>
</html>
