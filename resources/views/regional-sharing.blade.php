<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sharing Per Wilayah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card { border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.08); transition: transform 0.25s, box-shadow 0.25s; overflow: hidden; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 16px rgba(0,0,0,0.12); }
        .filter-section { background: white; padding: 15px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.06); margin-bottom: 20px; }
        .chart-card { min-height: 420px; }
        .wilayah-card { border-left: 6px solid; margin-bottom: 20px; position: relative; }
        .wilayah-1 { border-left-color: #667eea; }
        .wilayah-1::before { content: ''; position: absolute; left: 0; top: 10px; bottom: 10px; width: 6px; background: linear-gradient(180deg, #667eea 0%, rgba(102,126,234,0.4) 100%); border-top-left-radius:12px; border-bottom-left-radius:12px; }
        .wilayah-2 { border-left-color: #f093fb; }
        .wilayah-2::before { content: ''; position: absolute; left: 0; top: 10px; bottom: 10px; width: 6px; background: linear-gradient(180deg, #f093fb 0%, rgba(240,147,251,0.4) 100%); border-top-left-radius:12px; border-bottom-left-radius:12px; }
        .wilayah-3 { border-left-color: #4facfe; }
        .wilayah-3::before { content: ''; position: absolute; left: 0; top: 10px; bottom: 10px; width: 6px; background: linear-gradient(180deg, #4facfe 0%, rgba(79,172,254,0.4) 100%); border-top-left-radius:12px; border-bottom-left-radius:12px; }
        .wilayah-4 { border-left-color: #43e97b; }
        .wilayah-4::before { content: ''; position: absolute; left: 0; top: 10px; bottom: 10px; width: 6px; background: linear-gradient(180deg, #43e97b 0%, rgba(67,233,123,0.4) 100%); border-top-left-radius:12px; border-bottom-left-radius:12px; }
        .jai { border-left-color: #f59e0b; }
        .jai::before { content: ''; position: absolute; left: 0; top: 10px; bottom: 10px; width: 6px; background: linear-gradient(180deg, #f59e0b 0%, rgba(245,158,11,0.4) 100%); border-top-left-radius:12px; border-bottom-left-radius:12px; }
        .metric-box { background: #f8f9fa; border-radius: 10px; padding: 14px 12px; margin-bottom: 10px; border: 1px solid #e9ecef; transition: all 0.2s ease; }
        .metric-box:hover { transform: translateY(-2px); box-shadow: 0 3px 8px rgba(0,0,0,0.08); }
        .metric-label { font-size: 0.75rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .metric-value { font-size: 1.5rem; font-weight: 700; line-height: 1.2; margin: 0; }
        .metric-icon { font-size: 1.2rem; opacity: 0.7; }
        .pct-badge { font-size: 0.875rem; font-weight: 600; padding: 2px 8px; border-radius: 4px; }
        .card-header-custom { background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-bottom: 2px solid #e9ecef; border-radius: 15px 15px 0 0; }
        .progress-label { font-size: 0.7rem; font-weight: 600; color: white; text-shadow: 0 1px 2px rgba(0,0,0,0.3); }
        .chart-canvas-mini { height: 100px !important; margin-top: 10px; }
        /* Main regional chart at normal scale (100%) */
        .regional-chart-scale { transform: scale(1); transform-origin: left top; display: block; }
        .chart-body-wrapper { overflow: visible; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-geo-alt"></i> Sharing Per Wilayah</span>
            <div>
                <a href="{{ route('dashboard.operasional') }}" class="btn btn-light btn-sm me-2"><i class="bi bi-kanban-fill"></i> Dashboard Operasional</a>
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-2"><i class="bi bi-graph-up-arrow"></i> Dashboard LHGK</a>
                <a href="{{ route('monitoring.nota') }}" class="btn btn-light btn-sm me-2"><i class="bi bi-file-earmark-text"></i> Monitoring Nota</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="filter-section">
            <form method="GET" action="{{ route('regional.sharing') }}" class="row align-items-center">
                <div class="col-md-2"><label class="form-label"><i class="bi bi-funnel"></i> Periode:</label></div>
                <div class="col-md-4">
                    <select name="periode" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ ($selectedPeriode ?? 'all') == 'all' ? 'selected' : '' }}>Pilih Periode</option>
                        @foreach($periods as $p)
                            <option value="{{ $p }}" {{ (isset($selectedPeriode) && $selectedPeriode == $p) ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    @if(isset($selectedPeriode) && $selectedPeriode != 'all')
                        <a href="{{ route('regional.sharing') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Reset</a>
                    @endif
                    <a href="{{ route('regional.sharing.export', ['periode' => $selectedPeriode ?? 'all']) }}" class="btn btn-success ms-2">Download Excel</a>
                    <a href="{{ route('regional.sharing.detail', ['periode' => $selectedPeriode ?? 'all']) }}" class="btn btn-primary ms-2">Lihat Detail Cabang</a>
                </div>
            </form>
        </div>

        @if(($selectedPeriode ?? 'all') == 'all')
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle fs-1"></i>
                <h5 class="mt-3">Silakan pilih periode untuk melihat data sharing per wilayah</h5>
                <p class="mb-0">Data akan dikelompokkan berdasarkan wilayah.</p>
            </div>
        @else
            <div class="row mb-4">
                <div class="col-12 mb-2"><h5 class="text-muted"><i class="bi bi-bar-chart"></i> Ringkasan Sharing</h5></div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-success">Rp {{ number_format($totalPandu ?? 0, 0, ',', '.') }}</h3>
                            <p class="mb-0"><i class="bi bi-cash-coin"></i> Total Pandu</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-info">Rp {{ number_format($totalTunda ?? 0, 0, ',', '.') }}</h3>
                            <p class="mb-0"><i class="bi bi-cash-stack"></i> Total Tunda</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-primary">{{ number_format($totalBranches ?? 0) }}</h3>
                            <p class="mb-0"><i class="bi bi-diagram-3"></i> Jumlah Cabang</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-warning">Rp {{ number_format($totalOverall ?? 0, 0, ',', '.') }}</h3>
                            <p class="mb-0"><i class="bi bi-graph-up"></i> Total Keseluruhan</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card stat-card" style="min-height: 550px;">
                        <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Sharing per Wilayah (Pandu vs Tunda)</h5>
                        </div>
                            <div class="card-body" style="height: 300px;">
                            <div class="chart-body-wrapper">
                                <canvas id="regionalChart" class="regional-chart-scale"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card" style="min-height: 550px;">
                        <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Distribusi Total</h5>
                        </div>
                            <div class="card-body" style="height: 300px;">
                            <div class="chart-body-wrapper">
                                <canvas id="distributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                @foreach($regionalData as $wilayah => $d)
                    @php
                        $wilayahParts = explode(' ', $wilayah);
                        if ($wilayah == 'JAI') { $cardClass = 'jai'; }
                        else { $num = $wilayahParts[1] ?? '1'; $cardClass = 'wilayah-' . $num; }
                        $pandu = $d['pandu_revenue'] ?? 0;
                        $tunda = $d['tunda_revenue'] ?? 0;
                        $total = $d['total_revenue'] ?? 0;
                        $branches = $d['total_branches'] ?? 0;
                        $sum = max(1, $pandu + $tunda);
                        $panduPct = round($pandu / $sum * 100, 1);
                        $tundaPct = round($tunda / $sum * 100, 1);
                    @endphp
                    <div class="col-md-6 mb-4">
                        <div class="card stat-card {{ $cardClass }}">
                            <div class="card-header card-header-custom py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1"><i class="bi bi-geo-alt-fill me-1"></i>{{ $wilayah }}</h5>
                                        <small class="text-muted"><i class="bi bi-building"></i> {{ $branches }} Cabang</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="metric-label">Total Revenue</div>
                                        <div class="h4 mb-0 fw-bold text-dark">Rp {{ number_format($total, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="metric-box border-start border-success border-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="metric-label text-success">
                                                        <i class="bi bi-cash-coin metric-icon"></i> Pandu
                                                    </div>
                                                    <div class="metric-value text-success">
                                                        Rp {{ number_format($pandu / 1000000, 1, ',', '.') }}M
                                                    </div>
                                                    <small class="text-muted d-block mt-1">Rp {{ number_format($pandu, 0, ',', '.') }}</small>
                                                </div>
                                                <span class="pct-badge bg-success bg-opacity-10 text-success">{{ $panduPct }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="metric-box border-start border-info border-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="metric-label text-info">
                                                        <i class="bi bi-cash-stack metric-icon"></i> Tunda
                                                    </div>
                                                    <div class="metric-value text-info">
                                                        Rp {{ number_format($tunda / 1000000, 1, ',', '.') }}M
                                                    </div>
                                                    <small class="text-muted d-block mt-1">Rp {{ number_format($tunda, 0, ',', '.') }}</small>
                                                </div>
                                                <span class="pct-badge bg-info bg-opacity-10 text-info">{{ $tundaPct }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted fw-semibold"><i class="bi bi-bar-chart-fill"></i> Perbandingan Komposisi</small>
                                        <small class="text-muted">Pandu/Tunda: {{ $panduPct }}% / {{ $tundaPct }}%</small>
                                    </div>
                                    <div class="progress" style="height: 24px; border-radius: 8px;">
                                        <div class="progress-bar bg-success d-flex align-items-center justify-content-center progress-label" 
                                             role="progressbar" 
                                             style="width: {{ $panduPct }}%" 
                                             aria-valuenow="{{ $panduPct }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            @if($panduPct > 15)Pandu {{ $panduPct }}%@endif
                                        </div>
                                        <div class="progress-bar bg-info d-flex align-items-center justify-content-center progress-label" 
                                             role="progressbar" 
                                             style="width: {{ $tundaPct }}%" 
                                             aria-valuenow="{{ $tundaPct }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            @if($tundaPct > 15)Tunda {{ $tundaPct }}%@endif
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
        @if(($selectedPeriode ?? 'all') != 'all')
        // Debug: Log chart data
        console.log('Chart initialization starting...');
        
        const labels = {!! $chartLabels ?? '[]' !!};
        const pandu = {!! $chartPandu ?? '[]' !!};
        const tunda = {!! $chartTunda ?? '[]' !!};
        
        console.log('Labels:', labels);
        console.log('Pandu data:', pandu);
        console.log('Tunda data:', tunda);

        // compute totals per wilayah for display on bars
        const totalsForBars = labels.map((_, i) => ((pandu[i] || 0) + (tunda[i] || 0)));

        // Bar chart (horizontal) - pandu vs tunda per wilayah dengan total
        const regionalCtx = document.getElementById('regionalChart');
        console.log('Regional chart canvas:', regionalCtx);
        
        if (regionalCtx) {
            console.log('Creating regional bar chart...');
            new Chart(regionalCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        { 
                            label: 'Pandu', 
                            data: pandu, 
                            backgroundColor: 'rgba(75,192,192,0.85)', 
                            borderColor: 'rgb(75,192,192)', 
                            borderWidth: 2,
                            borderRadius: 4
                        },
                        { 
                            label: 'Tunda', 
                            data: tunda, 
                            backgroundColor: 'rgba(54,162,235,0.85)', 
                            borderColor: 'rgb(54,162,235)', 
                            borderWidth: 2,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: { 
                        x: { 
                            beginAtZero: true, 
                            stacked: false,
                            ticks: { 
                                callback: function(value) {
                                    return 'Rp ' + (value/1000000).toFixed(0) + 'M';
                                },
                                font: { size: 11 }
                            },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        y: {
                            stacked: false,
                            ticks: { 
                                font: { size: 12, weight: 'bold' },
                                autoSkip: false
                            },
                            grid: { display: false }
                        }
                    },
                    layout: {
                        padding: {
                            left: 10,
                            right: 160,
                            top: 10,
                            bottom: 10
                        }
                    },
                    plugins: { 
                        legend: {
                            display: true,
                            position: 'top',
                            labels: { 
                                padding: 15,
                                font: { size: 13, weight: 'bold' },
                                usePointStyle: true,
                                pointStyle: 'rectRounded'
                            }
                        },
                        tooltip: { 
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 12,
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 12 },
                            callbacks: { 
                                label: function(ctx) {
                                    return ctx.dataset.label + ': Rp ' + ctx.parsed.x.toLocaleString('id-ID');
                                },
                                footer: function(tooltipItems) {
                                    if (tooltipItems.length > 0) {
                                        const idx = tooltipItems[0].dataIndex;
                                        const total = totalsForBars[idx] || 0;
                                        return 'Total: Rp ' + total.toLocaleString('id-ID');
                                    }
                                    return '';
                                }
                            },
                            footerFont: { size: 12, weight: 'bold' },
                            footerColor: '#fbbf24'
                        }
                    }
                },
                plugins: [
                    {
                        id: 'showTotalsLabel',
                        afterDatasetsDraw: function(chart) {
                            const ctx = chart.ctx;
                            ctx.save();
                            
                            try {
                                const meta1 = chart.getDatasetMeta(0);
                                const meta2 = chart.getDatasetMeta(1);
                                
                                if (!meta1 || !meta2 || !meta1.data || !meta2.data) {
                                    console.warn('Chart metadata not available');
                                    ctx.restore();
                                    return;
                                }
                                
                                meta1.data.forEach(function(bar, index) {
                                    if (!bar || !meta2.data[index]) return;
                                    
                                    const bar1 = meta1.data[index];
                                    const bar2 = meta2.data[index];
                                    
                                    // Get rightmost position
                                    const pandoX = bar1.x || 0;
                                    const tundaX = bar2.x || 0;
                                    const maxX = Math.max(pandoX, tundaX);
                                    
                                    const y = bar1.y || 0;
                                    const total = totalsForBars[index] || 0;
                                    const totalText = 'Total: Rp ' + (total / 1000000).toFixed(1) + 'M';
                                    
                                    // Setup text style
                                    ctx.font = '700 11px Arial';
                                    const textWidth = ctx.measureText(totalText).width;
                                    const padding = 6;
                                    const boxX = maxX + 10;
                                    const boxY = y - 9;
                                    const boxWidth = textWidth + (padding * 2);
                                    const boxHeight = 18;
                                    
                                    // Draw background box
                                    ctx.fillStyle = '#fbbf24';
                                    ctx.fillRect(boxX, boxY, boxWidth, boxHeight);
                                    
                                    // Draw text
                                    ctx.fillStyle = '#000';
                                    ctx.textAlign = 'left';
                                    ctx.textBaseline = 'middle';
                                    ctx.fillText(totalText, boxX + padding, y);
                                });
                            } catch (e) {
                                console.error('Error drawing totals:', e);
                            }
                            
                            ctx.restore();
                        }
                    }
                ]
            });
            console.log('Regional bar chart created successfully!');
        } else {
            console.error('Regional chart canvas not found!');
        }

        // Pie chart distribution
        const distributionCtx = document.getElementById('distributionChart');
        console.log('Distribution chart canvas:', distributionCtx);
        
        if (distributionCtx) {
            console.log('Creating distribution pie chart...');
            const distributionTotals = {!! $chartTotals ?? '[]' !!};
            console.log('Distribution totals:', distributionTotals);
            
            new Chart(distributionCtx.getContext('2d'), {
                type: 'pie',
                data: { 
                    labels: labels, 
                    datasets: [{ 
                        data: distributionTotals, 
                        backgroundColor: [
                            'rgba(102,126,234,0.8)',
                            'rgba(240,147,251,0.8)',
                            'rgba(79,172,254,0.8)',
                            'rgba(67,233,123,0.8)',
                            'rgba(245,158,11,0.8)'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }] 
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    plugins: { 
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: { size: 12 }
                            }
                        },
                        tooltip: { 
                            callbacks: { 
                                label: function(context) { 
                                    const v = context.parsed || 0; 
                                    const total = context.dataset.data.reduce((a,b)=>a+b,0); 
                                    const pct = total>0?((v/total)*100).toFixed(1):0; 
                                    return context.label + ': Rp ' + v.toLocaleString('id-ID') + ' ('+pct+'%)'; 
                                } 
                            } 
                        } 
                    }
                }
            });
            console.log('Distribution pie chart created successfully!');
        } else {
            console.error('Distribution chart canvas not found!');
        }
        @endif
    </script>
</body>
</html>
