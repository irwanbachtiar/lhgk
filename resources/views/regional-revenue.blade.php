<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendapatan Per Wilayah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
        .wilayah-card {
            border-left: 5px solid;
            margin-bottom: 20px;
        }
        .wilayah-1 { border-left-color: #667eea; }
        .wilayah-2 { border-left-color: #f093fb; }
        .wilayah-3 { border-left-color: #4facfe; }
        .wilayah-4 { border-left-color: #43e97b; }
        .jai { border-left-color: #f59e0b; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-geo-alt"></i> Pendapatan Per Wilayah</span>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-graph-up-arrow"></i> Dashboard LHGK
                </a>
                <a href="{{ route('regional.detail') }}" class="btn btn-outline-light btn-sm me-2">
                    <i class="bi bi-table"></i> Detail Per Cabang
                </a>
                <a href="{{ route('monitoring.nota') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-file-earmark-text"></i> Monitoring Nota
                </a>
                <a href="{{ route('analisis.kelelahan') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-activity"></i> Analisis Kelelahan
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('regional.revenue') }}" class="row align-items-center">
                <div class="col-md-2">
                    <label class="form-label"><i class="bi bi-funnel"></i> Filter Periode:</label>
                </div>
                <div class="col-md-4">
                    <select name="periode" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ $selectedPeriode == 'all' ? 'selected' : '' }}>Pilih Periode</option>
                        @foreach($periods as $period)
                            <option value="{{ $period }}" {{ $selectedPeriode == $period ? 'selected' : '' }}>
                                {{ $period }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    @if($selectedPeriode != 'all')
                        <a href="{{ route('regional.revenue') }}" class="btn btn-outline-secondary">
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
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-12 mb-2">
                    <h5 class="text-muted"><i class="bi bi-bar-chart"></i> Summary Pelindo dan SPJM</h5>
                </div>
                @php
                    // Use controller-provided totals for WILAYAH only (excluding JAI)
                    $totalTunda = $totalTundaRevenue ?? 0;
                    $totalAll = $totalPandu + $totalTunda;
                @endphp
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-primary">{{ number_format($totalTransaksi ?? 0) }}</h3>
                            <p class="mb-0"><i class="bi bi-file-earmark-text"></i> Total Transaksi</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-success">Rp {{ number_format($totalPandu, 0, ',', '.') }}</h3>
                            <p class="mb-0"><i class="bi bi-cash-coin"></i> Total Pandu</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-info">Rp {{ number_format($totalTunda, 0, ',', '.') }}</h3>
                            <p class="mb-0"><i class="bi bi-cash-stack"></i> Total Tunda</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-warning">Rp {{ number_format($totalAll, 0, ',', '.') }}</h3>
                            <p class="mb-0"><i class="bi bi-graph-up"></i> Total Pendapatan</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- JAI Summary Cards -->
            <div class="row mb-4">
                <div class="col-12 mb-2">
                    <h5 class="text-muted"><i class="bi bi-building"></i> Summary JAI</h5>
                </div>
                @php
                    $jaiTotalAll = ($jaiTotalPandu ?? 0) + ($jaiTotalTunda ?? 0);
                @endphp
                <div class="col-md-3">
                    <div class="card stat-card" style="border: 2px solid #f59e0b;">
                        <div class="card-body text-center">
                            <h3 class="text-primary">{{ number_format($jaiTotalTransaksi ?? 0) }}</h3>
                            <p class="mb-0"><i class="bi bi-file-earmark-text"></i> Total Transaksi JAI</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card" style="border: 2px solid #f59e0b;">
                        <div class="card-body text-center">
                            <h3 class="text-success">Rp {{ number_format($jaiTotalPandu ?? 0, 0, ',', '.') }}</h3>
                            <p class="mb-0"><i class="bi bi-cash-coin"></i> Total Pandu JAI</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card" style="border: 2px solid #f59e0b;">
                        <div class="card-body text-center">
                            <h3 class="text-info">Rp {{ number_format($jaiTotalTunda ?? 0, 0, ',', '.') }}</h3>
                            <p class="mb-0"><i class="bi bi-cash-stack"></i> Total Tunda JAI</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card" style="border: 2px solid #f59e0b;">
                        <div class="card-body text-center">
                            <h3 class="text-warning">Rp {{ number_format($jaiTotalAll, 0, ',', '.') }}</h3>
                            <p class="mb-0"><i class="bi bi-graph-up"></i> Total Pendapatan JAI</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delegation Summary Cards -->
            @if(!empty($delegationData))
            <div class="row mb-4">
                <div class="col-12 mb-2">
                    <h5 class="text-muted"><i class="bi bi-diagram-3"></i> Summary Per Pelimpahan (Wilayah 1-4)</h5>
                </div>
                @foreach(['PELINDO', 'SPJM'] as $delegation)
                    @php
                        $delData = $delegationData[$delegation] ?? ['pandu' => 0, 'tunda' => 0, 'transaksi' => 0];
                        $delTotalAll = $delData['pandu'] + $delData['tunda'];
                        $borderColors = [
                            'PELINDO' => '#667eea',
                            'SPJM' => '#43e97b'
                        ];
                    @endphp
                    <div class="col-md-6">
                        <div class="card stat-card" style="border: 2px solid {{ $borderColors[$delegation] }};">
                            <div class="card-header text-white" style="background: linear-gradient(135deg, {{ $borderColors[$delegation] }}, {{ $borderColors[$delegation] }}dd);">
                                <h6 class="mb-0"><i class="bi bi-building"></i> {{ $delegation }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6 mb-2">
                                        <small class="text-muted">Transaksi</small>
                                        <h6 class="text-primary mb-0">{{ number_format($delData['transaksi']) }}</h6>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <small class="text-muted">Total</small>
                                        <h6 class="text-warning mb-0">Rp {{ number_format($delTotalAll, 0, ',', '.') }}</h6>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <small class="text-muted">Pandu</small>
                                        <p class="mb-0 text-success"><strong>Rp {{ number_format($delData['pandu'], 0, ',', '.') }}</strong></p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Tunda</small>
                                        <p class="mb-0 text-info"><strong>Rp {{ number_format($delData['tunda'], 0, ',', '.') }}</strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            <!-- Charts Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card stat-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Pendapatan Per Wilayah</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="regionalChart" style="max-height: 400px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card stat-card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Distribusi Total Pendapatan</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="distributionChart" style="max-height: 400px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Button to Detail Page -->
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <a href="{{ route('regional.detail', ['periode' => $selectedPeriode]) }}" class="btn btn-lg btn-primary">
                        <i class="bi bi-table"></i> Lihat Detail Pendapatan Per Cabang
                    </a>
                    <p class="text-muted mt-2 mb-0">Klik tombol di atas untuk melihat breakdown detail pendapatan setiap cabang per wilayah</p>
                </div>
            </div>

            <!-- Regional Details -->
            <div class="row">
                @foreach($regionalData as $wilayah => $data)
                    @php
                        $wilayahParts = explode(' ', $wilayah);
                        if ($wilayah == 'JAI') {
                            $cardClass = 'jai';
                        } else {
                            $wilayahNumber = $wilayahParts[1] ?? '';
                            $cardClass = 'wilayah-' . $wilayahNumber;
                        }
                    @endphp
                    <div class="col-md-6 mb-4">
                        <div class="card stat-card {{ $cardClass }}">
                            <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="bi bi-geo-alt-fill"></i> {{ $wilayah }}</h5>
                                        <div class="text-end">
                                            <small class="text-muted d-block">Total Pendapatan</small>
                                            <strong>
                                                Rp {{ number_format($data['total_revenue'] ?? ($data['pandu_revenue'] + $data['tunda_revenue']), 0, ',', '.') }}
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4">
                                        <div class="mb-3">
                                            <small class="text-muted">Pendapatan Pandu</small>
                                            <h4 class="text-success">Rp {{ number_format($data['pandu_revenue'], 0, ',', '.') }}</h4>
                                        </div>
                                        <div>
                                            <small class="text-muted">Persentase Pandu</small>
                                            @php
                                                $totalPanduAll = array_sum(array_column($regionalData, 'pandu_revenue'));
                                                $percentage = $totalPanduAll > 0 ? ($data['pandu_revenue'] / $totalPanduAll * 100) : 0;
                                            @endphp
                                            <h5 class="text-warning">{{ number_format($percentage, 1) }}%</h5>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mb-3">
                                            <small class="text-muted">Pendapatan Tunda</small>
                                            <h4 class="text-info">Rp {{ number_format($data['tunda_revenue'], 0, ',', '.') }}</h4>
                                        </div>
                                        <div>
                                            <small class="text-muted">Persentase Tunda</small>
                                            @php
                                                $totalTundaAll = array_sum(array_column($regionalData, 'tunda_revenue'));
                                                $percentageTunda = $totalTundaAll > 0 ? ($data['tunda_revenue'] / $totalTundaAll * 100) : 0;
                                            @endphp
                                            <h5 class="text-warning">{{ number_format($percentageTunda, 1) }}%</h5>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mb-3">
                                            <small class="text-muted">Total Transaksi</small>
                                            <h4 class="text-primary">{{ number_format($data['total_transaksi']) }}</h4>
                                        </div>
                                        <div>
                                            <small class="text-muted">Jumlah Cabang</small>
                                            <h5 class="text-secondary">{{ count($regionalGroups[$wilayah]) }} cabang</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        @if($selectedPeriode != 'all')
        // Regional Chart - Bar Chart
        const regionalData = @json($regionalData);
        const totalTundaRevenue = {{ $totalTundaRevenue ?? 0 }};
        const wilayahLabels = Object.keys(regionalData);
        const panduData = wilayahLabels.map(w => regionalData[w].pandu_revenue);
        // Tunda ditampilkan sebagai nilai total per wilayah
        const tundaData = wilayahLabels.map(w => regionalData[w].tunda_revenue);

        const regionalCtx = document.getElementById('regionalChart').getContext('2d');
        const regionalChart = new Chart(regionalCtx, {
            type: 'bar',
            data: {
                labels: wilayahLabels,
                datasets: [
                    {
                        label: 'Pendapatan Pandu (Rp)',
                        data: panduData,
                        backgroundColor: 'rgba(75, 192, 192, 0.7)',
                        borderColor: 'rgb(75, 192, 192)',
                        borderWidth: 2
                    },
                    {
                        label: 'Pendapatan Tunda (Rp)',
                        data: tundaData,
                        backgroundColor: 'rgba(255, 99, 132, 0.7)',
                        borderColor: 'rgb(255, 99, 132)',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                indexAxis: 'y',
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
                                return context.dataset.label + ': Rp ' + context.parsed.x.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            }
                        }
                    }
                }
            }
        });
        // Distribution Chart - Pie Chart (Total Pendapatan: Pandu + Tunda)
        const totalRevenueData = wilayahLabels.map(w => regionalData[w].total_revenue);
        
        console.log('Wilayah Labels:', wilayahLabels);
        console.log('Total Revenue Data:', totalRevenueData);
        console.log('Regional Data:', regionalData);
        
        const distributionCtx = document.getElementById('distributionChart').getContext('2d');
        const distributionChart = new Chart(distributionCtx, {
            type: 'pie',
            data: {
                labels: wilayahLabels,
                datasets: [{
                    data: totalRevenueData,
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(240, 147, 251, 0.8)',
                        'rgba(79, 172, 254, 0.8)',
                        'rgba(67, 233, 123, 0.8)',
                        'rgba(245, 158, 11, 0.8)'
                    ],
                    borderColor: [
                        'rgb(102, 126, 234)',
                        'rgb(240, 147, 251)',
                        'rgb(79, 172, 254)',
                        'rgb(67, 233, 123)',
                        'rgb(245, 158, 11)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': Rp ' + value.toLocaleString('id-ID') + ' (' + percentage + '%)';
                            }
                        }
                    },
                    datalabels: {
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 14
                        },
                        formatter: function(value, context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return 'Rp ' + (value / 1000000).toFixed(1) + 'M\n(' + percentage + '%)';
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
                                const percentage = total > 0 ? ((data / total) * 100).toFixed(1) : 0;
                                
                                // Draw text with black color
                                ctx.fillStyle = '#000000';
                                ctx.font = 'bold 13px Arial';
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';
                                
                                const position = element.tooltipPosition();
                                const valueText = 'Rp ' + (data / 1000000000).toFixed(1) + 'B';
                                const percentText = percentage + '%';
                                
                                // Draw value text
                                ctx.fillText(valueText, position.x, position.y - 10);
                                
                                // Draw percentage text
                                ctx.fillText(percentText, position.x, position.y + 10);
                            });
                        }
                    });
                }
            }]
        });
        @endif
    </script>
</body>
</html>
