<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendapatan Detail Per Wilayah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #2c3e50;
            line-height: 1.6;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-section {
            background: white;
            padding: 20px 25px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            border: 1px solid #e8eaed;
        }
        
        .wilayah-section {
            background: white;
            border-radius: 12px;
            padding: 0;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e8eaed;
            overflow: hidden;
        }
        
        .wilayah-content {
            display: grid;
            grid-template-columns: 0.6fr 1fr;
            gap: 0;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .chart-container {
            padding: 25px;
            background: #fafbfc;
            border-left: 1px solid #e8eaed;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .chart-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .wilayah-header {
            padding: 20px 25px;
            background: linear-gradient(to right, #f8f9fa, #ffffff);
            border-bottom: 1px solid #e8eaed;
        }
        
        .wilayah-1 .wilayah-header { border-left: 4px solid #667eea; }
        .wilayah-2 .wilayah-header { border-left: 4px solid #f093fb; }
        .wilayah-3 .wilayah-header { border-left: 4px solid #4facfe; }
        .wilayah-4 .wilayah-header { border-left: 4px solid #43e97b; }
        .jai .wilayah-header { border-left: 4px solid #f59e0b; }
        
        .wilayah-title {
            color: #1a202c;
            font-weight: 600;
            font-size: 1.25rem;
            margin-bottom: 12px;
        }
        
        .data-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        
        .data-grid thead th {
            background: #f8f9fa;
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 0.8125rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e8eaed;
        }
        
        .data-grid tbody tr {
            border-bottom: 1px solid #f1f3f5;
            transition: background 0.15s ease;
        }
        
        .data-grid tbody tr:hover {
            background: #f8f9fb;
        }
        
        .data-grid tbody tr:last-child {
            border-bottom: none;
        }
        
        .data-grid td {
            padding: 14px 16px;
            color: #495057;
        }
        
        .data-grid td:first-child {
            color: #374151;
            font-weight: 500;
        }
        
        .data-grid .text-end {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }
        
        .data-grid tfoot {
            background: #f8f9fa;
            border-top: 2px solid #dee2e6;
        }
        
        .data-grid tfoot td {
            padding: 14px 16px;
            font-weight: 600;
            color: #212529;
        }
        
        .grid-number-success { color: #10b981; }
        .grid-number-info { color: #3b82f6; }
        .grid-number-warning { color: #f59e0b; font-weight: 600; }
        .grid-number-primary { color: #6366f1; }
        
        .grid-row-no {
            color: #adb5bd;
            font-weight: 400;
            text-align: center;
            width: 50px;
        }
        
        .summary-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 0.8125rem;
            font-weight: 500;
            margin-right: 8px;
            margin-bottom: 6px;
        }
        
        .badge-pandu {
            background: #d1fae5;
            color: #047857;
        }
        
        .badge-tunda {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .badge-total {
            background: #fef3c7;
            color: #b45309;
        }
        
        .badge-count {
            background: #e0e7ff;
            color: #4f46e5;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
            background: white;
            border-radius: 12px;
            border: 1px solid #e8eaed;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #cbd5e0;
        }
        
        .scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .scroll-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        
        .bi {
            vertical-align: middle;
        }
        
        @media print {
            .navbar, .filter-section, .scroll-top, .chart-container {
                display: none !important;
            }
            .wilayah-section {
                page-break-inside: avoid;
                box-shadow: none;
                border: 1px solid #dee2e6;
            }
            .wilayah-content {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 1200px) {
            .wilayah-content {
                grid-template-columns: 1fr;
            }
            .chart-container {
                border-left: none;
                border-top: 1px solid #e8eaed;
            }
        }
        
        @media (max-width: 768px) {
            .wilayah-header {
                padding: 15px;
            }
            .wilayah-title {
                font-size: 1.1rem;
            }
            .data-grid thead th,
            .data-grid td {
                padding: 10px 12px;
                font-size: 0.8125rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-table"></i> Pendapatan Detail Per Wilayah</span>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-graph-up-arrow"></i> Dashboard LHGK
                </a>
                <a href="{{ route('regional.revenue') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-geo-alt"></i> Pendapatan Per Wilayah
                </a>
                <a href="{{ route('monitoring.nota') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-file-earmark-text"></i> Monitoring Nota
                </a>
                <button onclick="window.print()" class="btn btn-light btn-sm">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('regional.detail') }}" class="row align-items-center">
                <div class="col-md-2">
                    <label class="form-label fw-bold"><i class="bi bi-funnel"></i> Filter Periode:</label>
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
                <div class="col-md-6">
                    @if($selectedPeriode != 'all')
                        <a href="{{ route('regional.detail') }}" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-x-circle"></i> Reset Filter
                        </a>
                        <a href="{{ route('regional.detail.export', ['periode' => $selectedPeriode]) }}" class="btn btn-success">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Download Excel
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if($selectedPeriode == 'all')
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle fs-1"></i>
                <h5 class="mt-3">Silakan pilih periode untuk melihat detail pendapatan per cabang</h5>
                <p class="mb-0">Data akan dikelompokkan berdasarkan wilayah regional.</p>
            </div>
        @else
            @foreach($branchDetails as $wilayah => $branches)
                @if(!empty($branches))
                    @php
                        $wilayahParts = explode(' ', $wilayah);
                        if ($wilayah == 'JAI') {
                            $sectionClass = 'jai';
                        } else {
                            $wilayahNumber = $wilayahParts[1] ?? '';
                            $sectionClass = 'wilayah-' . $wilayahNumber;
                        }
                        
                        $totalPandu = array_sum(array_column($branches, 'pandu'));
                        $totalTunda = array_sum(array_column($branches, 'tunda'));
                        $totalRevenue = array_sum(array_column($branches, 'total'));
                        $totalTransaksi = array_sum(array_column($branches, 'transaksi'));
                    @endphp
                    
                    <div class="wilayah-section {{ $sectionClass }}" id="wilayah-{{ $wilayah }}">
                        <div class="wilayah-header">
                            <h4 class="wilayah-title">
                                <i class="bi bi-geo-alt-fill me-2"></i>{{ $wilayah }}
                            </h4>
                            <div class="mt-2">
                                <span class="summary-badge badge-pandu">
                                    <i class="bi bi-cash-coin"></i>
                                    <span>Rp {{ number_format($totalPandu, 0, ',', '.') }}</span>
                                </span>
                                <span class="summary-badge badge-tunda">
                                    <i class="bi bi-cash-stack"></i>
                                    <span>Rp {{ number_format($totalTunda, 0, ',', '.') }}</span>
                                </span>
                                <span class="summary-badge badge-total">
                                    <i class="bi bi-graph-up"></i>
                                    <span>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                                </span>
                                <span class="summary-badge badge-count">
                                    <i class="bi bi-building"></i>
                                    <span>{{ count($branches) }} Cabang</span>
                                </span>
                            </div>
                        </div>
                        
                        <div class="wilayah-content">
                            <div class="table-container">
                                <table class="data-grid">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th>Nama Cabang</th>
                                            <th class="text-end" style="width: 160px;">Pandu</th>
                                            <th class="text-end" style="width: 160px;">Tunda</th>
                                            <th class="text-end" style="width: 180px;">Total</th>
                                            <th class="text-end" style="width: 100px;">Transaksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($branches as $branchName => $data)
                                            <tr>
                                                <td class="grid-row-no">{{ $loop->iteration }}</td>
                                                <td>
                                                    <i class="bi bi-pin-map text-primary me-2" style="font-size: 0.875rem;"></i>
                                                    <span>{{ $branchName }}</span>
                                                </td>
                                                <td class="text-end grid-number-success">{{ number_format($data['pandu'], 0, ',', '.') }}</td>
                                                <td class="text-end grid-number-info">{{ number_format($data['tunda'], 0, ',', '.') }}</td>
                                                <td class="text-end grid-number-warning">{{ number_format($data['total'], 0, ',', '.') }}</td>
                                                <td class="text-end grid-number-primary">{{ number_format($data['transaksi']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" class="text-end">
                                                <i class="bi bi-calculator me-1"></i>TOTAL
                                            </td>
                                            <td class="text-end grid-number-success">{{ number_format($totalPandu, 0, ',', '.') }}</td>
                                            <td class="text-end grid-number-info">{{ number_format($totalTunda, 0, ',', '.') }}</td>
                                            <td class="text-end grid-number-warning">{{ number_format($totalRevenue, 0, ',', '.') }}</td>
                                            <td class="text-end grid-number-primary">{{ number_format($totalTransaksi) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <div class="chart-container">
                                <div class="chart-title">
                                    <i class="bi bi-bar-chart-fill me-1"></i>
                                    Perbandingan Pandu vs Tunda Per Cabang
                                </div>
                                @php
                                    $branchCount = count($branches);
                                    $chartHeight = max(400, $branchCount * 35);
                                @endphp
                                <div style="height: {{ $chartHeight }}px; position: relative; overflow-y: auto;">
                                    <canvas id="chart-{{ str_replace(' ', '-', $wilayah) }}"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
            
            @php
                $hasData = false;
                foreach($branchDetails as $branches) {
                    if(!empty($branches)) {
                        $hasData = true;
                        break;
                    }
                }
            @endphp
            
            @if(!$hasData)
                <div class="empty-state">
                    <i class="bi bi-inbox fs-1"></i>
                    <h5 class="mt-3">Tidak ada data untuk periode ini</h5>
                    <p>Silakan pilih periode lain yang memiliki data transaksi.</p>
                </div>
            @endif
        @endif
    </div>
    
    <!-- Scroll to Top Button -->
    <div class="scroll-top" id="scrollTop" onclick="scrollToTop()">
        <i class="bi bi-arrow-up-short fs-4"></i>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize charts for each wilayah
        @if($selectedPeriode != 'all')
            @foreach($branchDetails as $wilayah => $branches)
                @if(!empty($branches))
                    @php
                        $branchNames = array_keys($branches);
                        $panduValues = array_column($branches, 'pandu');
                        $tundaValues = array_column($branches, 'tunda');
                    @endphp
                    
                    // Chart for {{ $wilayah }}
                    (function() {
                        const ctx = document.getElementById('chart-{{ str_replace(' ', '-', $wilayah) }}');
                        if (ctx) {
                            new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: @json($branchNames),
                                    datasets: [
                                        {
                                            label: 'Pandu',
                                            data: @json($panduValues),
                                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                                            borderColor: 'rgba(16, 185, 129, 1)',
                                            borderWidth: 1
                                        },
                                        {
                                            label: 'Tunda',
                                            data: @json($tundaValues),
                                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                                            borderColor: 'rgba(59, 130, 246, 1)',
                                            borderWidth: 1
                                        }
                                    ]
                                },
                                options: {
                                    indexAxis: 'y',
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    barThickness: 10,
                                    categoryPercentage: 0.98,
                                    barPercentage: 0.95,
                                    plugins: {
                                        legend: {
                                            display: true,
                                            position: 'top',
                                            labels: {
                                                padding: 8,
                                                font: {
                                                    size: 10,
                                                    family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto'
                                                },
                                                usePointStyle: true,
                                                pointStyle: 'rect',
                                                boxWidth: 12,
                                                boxHeight: 12
                                            }
                                        },
                                        tooltip: {
                                            callbacks: {
                                                title: function(context) {
                                                    const label = context[0].label;
                                                    // Shorten branch name for tooltip
                                                    return label.length > 30 ? label.substring(0, 30) + '...' : label;
                                                },
                                                label: function(context) {
                                                    const label = context.dataset.label || '';
                                                    const value = context.parsed.x || 0;
                                                    return label + ': Rp ' + value.toLocaleString('id-ID');
                                                }
                                            },
                                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                            padding: 10,
                                            titleFont: {
                                                size: 11,
                                                weight: 'bold'
                                            },
                                            bodyFont: {
                                                size: 10
                                            },
                                            cornerRadius: 4
                                        }
                                    },
                                    scales: {
                                        y: {
                                            ticks: {
                                                callback: function(value, index) {
                                                    const label = this.getLabelForValue(value);
                                                    // Extract last part after "REGIONAL X"
                                                    const parts = label.split(' ');
                                                    if (parts.length > 2) {
                                                        const shortName = parts.slice(2).join(' ');
                                                        return shortName.length > 15 ? shortName.substring(0, 15) + '...' : shortName;
                                                    }
                                                    return label.length > 15 ? label.substring(0, 15) + '...' : label;
                                                },
                                                font: {
                                                    size: 9
                                                },
                                                autoSkip: false,
                                                padding: 10
                                            },
                                            grid: {
                                                display: false
                                            }
                                        },
                                        x: {
                                            beginAtZero: true,
                                            ticks: {
                                                callback: function(value) {
                                                    if (value >= 1000000000) {
                                                        return 'Rp ' + (value / 1000000000).toFixed(1) + 'M';
                                                    } else if (value >= 1000000) {
                                                        return 'Rp ' + (value / 1000000).toFixed(0) + 'Jt';
                                                    }
                                                    return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                                                },
                                                font: {
                                                    size: 9
                                                }
                                            },
                                            grid: {
                                                color: 'rgba(0, 0, 0, 0.05)'
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    })();
                @endif
            @endforeach
        @endif
        
        // Scroll to top functionality
        window.onscroll = function() {
            const scrollTop = document.getElementById('scrollTop');
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                scrollTop.style.display = 'flex';
            } else {
                scrollTop.style.display = 'none';
            }
        };
        
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
</body>
</html>
