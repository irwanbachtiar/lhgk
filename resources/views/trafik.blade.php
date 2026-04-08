
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
    <title>Trafik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet" integrity="sha384-QuGBSgV5Im3DzL2z+8Ko9/hqNy/N0O7zwvXAtfd1MvPKWa/UbeLV65cfm4BV5Wgq" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@5.10.5/dist/apexcharts.min.js" integrity="sha384-+KZlkTrOyPdGdjLz5cJEzWi2nXf0PbWqRQAm6QTJu+BoRCMGN826uQ0upRk2BcqX" crossorigin="anonymous"></script>
</head>
<body class="bg-slate-100">

    <div class="container-fluid px-0">
        <div class="container px-4">
            <div class="row gx-3">

            <!-- SIDEBAR removed per request -->

    <!-- MAIN (full width now sidebar removed) -->
    <main class="col-12">

                <nav class="navbar navbar-dark mb-4">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1"><i class="bi bi-graph-up-arrow"></i> Trafik</span>
                        <div>
                            <a href="{{ route('dashboard.operasional') }}" class="btn btn-light btn-sm me-2">
                                <i class="bi bi-kanban-fill"></i> Dashboard Operasional
                            </a>
                            <a href="{{ route('monitoring.nota') }}" class="btn btn-light btn-sm me-2">
                                <i class="bi bi-file-earmark-text"></i> Monitoring Nota
                            </a>
                            <a href="{{ route('regional.revenue') }}" class="btn btn-light btn-sm me-2">
                                <i class="bi bi-geo-alt"></i> Pendapatan Wilayah
                            </a>
                            <a href="{{ url('regional-sharing') }}" class="btn btn-light btn-sm me-2">
                                <i class="bi bi-people-fill"></i> Revenue Sharing
                            </a>
                            <a href="{{ route('analisis.kelelahan') }}" class="btn btn-light btn-sm">
                                <i class="bi bi-activity"></i> Analisis Kelelahan
                            </a>
                        </div>
                    </div>
                </nav>

                <h1 class="text-xl font-semibold text-slate-800">Trafik</h1>

                <!-- KPI (moved below filter) -->

        <div class="container-fluid p-4">

        <!-- HEADER -->
        <div class="d-flex justify-content-between mb-4">
            <div>
                <h4 class="fw-bold">Monitoring Trafik Kapal</h4>
                <small class="text-muted">Call per Wilayah</small>
            </div>
            <div class="d-flex align-items-center">
                <form id="filtersForm" method="GET" class="d-flex align-items-center gap-2" action="">
                    <div style="display:flex;align-items:center;gap:.5rem">
                        <select name="wilayah" id="wilayahFilter" class="form-select filter-input" style="width:160px">
                            <option value="all" {{ request('wilayah') == 'all' ? 'selected' : '' }}>All Wilayah</option>
                            <option value="wilayah 1" {{ request('wilayah') == 'wilayah 1' ? 'selected' : '' }}>Wilayah 1</option>
                            <option value="wilayah 2" {{ request('wilayah') == 'wilayah 2' ? 'selected' : '' }}>Wilayah 2</option>
                            <option value="wilayah 3" {{ request('wilayah') == 'wilayah 3' ? 'selected' : '' }}>Wilayah 3</option>
                            <option value="wilayah 4" {{ request('wilayah') == 'wilayah 4' ? 'selected' : '' }}>Wilayah 4</option>
                        </select>

                        <select name="periode" id="periodeFilter" class="form-select filter-input" style="width:160px">
                            <option value="all">-- Semua Periode --</option>
                            @if(!empty($periods) && count($periods) > 0)
                                @foreach($periods as $period)
                                    <option value="{{ $period }}" {{ request('periode') == $period ? 'selected' : '' }}>{{ $period }}</option>
                                @endforeach
                            @else
                                <option value="01-2026">01-2026</option>
                                <option value="12-2025">12-2025</option>
                                <option value="11-2025">11-2025</option>
                            @endif
                        </select>

                        <!-- Apply button positioned to the right of periode select -->
                        <button type="submit" id="applyFilters" class="btn btn-primary ms-2">Apply</button>
                    </div>
                </form>

                <div class="ms-3">
                    <button class="btn btn-outline-secondary">Export Excel</button>
                </div>
            </div>
        </div>

        <!-- KPI CARDS -->
        <div class="row mb-4">
            @php
                $trafik_call = $dashboardSummary['total_real_call'] ?? 0;
                $trafik_gt = $dashboardSummary['total_real_gt'] ?? 0;

                $produksi_penundaan = 0;
                $produksi_pemanduan = 0;
                foreach($trafikData ?? [] as $w => $d) {
                    $produksi_penundaan += $d['produksi_penundaan'] ?? $d['produksi_tunda'] ?? 0;
                    $produksi_pemanduan += $d['produksi_pemanduan'] ?? $d['produksi_pandu'] ?? 0;
                }

                $pendapatan = 0;
                if(!empty($totalOverall) && is_array($totalOverall)) {
                    $pendapatan = ($totalOverall['total_pendapatan_pandu'] ?? 0) + ($totalOverall['total_pendapatan_tunda'] ?? 0);
                } else {
                    $pendapatan = $dashboardSummary['total_pendapatan'] ?? 0;
                }
            @endphp

            <div class="col-md-3">
                <div class="card p-3">
                    <div class="kpi-title">Trafik</div>
                    <div class="d-flex align-items-center mt-3">
                        <div class="flex-fill text-center">
                            <div class="text-muted small">Call</div>
                            <div class="fs-1 fw-bolder mb-0">{{ number_format($trafik_call) }}</div>
                        </div>
                        <div class="flex-fill text-center border-start ps-3">
                            <div class="text-muted small">GT</div>
                            <div class="fs-1 fw-bolder text-primary mb-0">{{ number_format($trafik_gt) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card p-3">
                    <div class="kpi-title">Produksi Penundaan</div>
                    <h4 class="metric-value">{{ number_format($produksi_penundaan) }}</h4>
                    <p class="mb-0 text-muted small">GT / Jam</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card p-3">
                    <div class="kpi-title">Produksi Pemanduan</div>
                    <h4 class="metric-value">{{ number_format($produksi_pemanduan) }}</h4>
                    <p class="mb-0 text-muted small">GT / Grk</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card p-3">
                    <div class="kpi-title">Pendapatan</div>
                    <h4 class="metric-value">Rp {{ number_format($pendapatan, 0, ',', '.') }}</h4>
                    <p class="mb-0 text-muted small">Total Pendapatan</p>
                </div>
            </div>
        </div>

        <!-- CHARTS - SPLIT INTO TWO SEPARATE CHARTS -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card p-3">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Perbandingan Call per wilayah</h6>
                    </div>
                    <div class="card-body">
                        <div id="callOnlyChart" style="min-height: 350px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0"><i class="bi bi-graph-up"></i> Perbandingan Produksi per Wilayah (Realisasi vs Anggaran)</h6>
                    </div>
                    <div class="card-body">
                        <div id="produksiChart" style="min-height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- EXTRA: Call vs Anggaran (Grouped bar with variance) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card p-3">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0"><i class="bi bi-bar-chart-line-fill"></i> Perbandingan Call (Realisasi vs Anggaran) — Variansi %</h6>
                    </div>
                    <div class="card-body">
                        <div id="callComparisonChart" style="min-height: 320px;"></div>
                        <div class="mt-3" id="callDifferenceTableContainer">
                            <h6 class="mb-2">Ringkasan Selisih per Wilayah</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="callDifferenceTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Wilayah</th>
                                            <th class="text-end">Anggaran</th>
                                            <th class="text-end">Realisasi</th>
                                            <th class="text-end">Selisih</th>
                                            <th class="text-end">% Varian</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TREE TABLE -->
        <div class="card p-3">
            <h6>Summary Hierarki</h6>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Uraian</th>
                        <th class="text-end">Call</th>
                        <th class="text-end">GT</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Simple flattened summary: show top 4 locations aggregated across wilayah
                        $rows = [];
                        foreach($trafikData ?? [] as $wil=>$wdata) {
                            foreach(['dalam_negeri','luar_negeri'] as $k) {
                                foreach($wdata[$k]['locations'] ?? [] as $lok=>$vals) {
                                    $rows[] = [
                                        'uraian' => $lok,
                                        'call' => $vals['realisasi_call'] ?? $vals['call'] ?? 0,
                                        'gt' => $vals['realisasi_gt'] ?? $vals['gt'] ?? 0,
                                    ];
                                }
                            }
                        }
                        if (empty($rows)) {
                            $rows = [ ['uraian'=>'Dermaga Umum','call'=>4500,'gt'=>3200000], ['uraian'=>'Pelayaran Luar Negeri','call'=>2700,'gt'=>2100000], ['uraian'=>'Kapal','call'=>1900,'gt'=>1600000], ['uraian'=>'General Cargo','call'=>700,'gt'=>650000] ];
                        }
                    @endphp

                    @foreach(array_slice($rows,0,10) as $r)
                    <tr>
                        <td class="tree-level-1">{{ $r['uraian'] }}</td>
                        <td class="text-end">{{ number_format($r['call']) }}</td>
                        <td class="text-end">{{ number_format($r['gt']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Chart Call & GT per Wilayah (Left Side)
            // Left chart will render only Call (Realisasi vs Anggaran)
                try {
                const wilayahLabels = ['WILAYAH 1','WILAYAH 2','WILAYAH 3','WILAYAH 4'];
                const callReal = [7547, 4238, 8473, 10329];
                const callBudget = [8320, 4715, 9267, 9165];

                const callOnlyOptions = {
                    chart: { type: 'bar', height: 180, stacked: false },
                    series: [
                        { name: 'Call Realisasi', data: callReal },
                        { name: 'Call Anggaran', data: callBudget }
                    ],
                    plotOptions: { bar: { horizontal: false, columnWidth: '60%' } },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            // For realisasi series show value and percent vs anggaran
                            if (opts.seriesIndex === 0) {
                                const budget = callBudget[opts.dataPointIndex] || 0;
                                const percent = budget ? ((val / budget - 1) * 100).toFixed(1) : '0.0';
                                return val.toLocaleString() + ' (' + (percent > 0 ? '+'+percent : percent) + '%)';
                            }
                            return val.toLocaleString();
                        },
                        style: { fontSize: '10px' }
                    },
                    xaxis: { categories: wilayahLabels },
                    yaxis: { title: { text: 'Call' } },
                    colors: ['#20B2AA','#68D8F0'],
                    legend: { position: 'top', horizontalAlign: 'left' }
                };
                const callOnlyChart = new ApexCharts(document.querySelector("#callOnlyChart"), callOnlyOptions);
                callOnlyChart.render();

                // Additional grouped bar with clearer comparison (separate card)
                const selisih = callReal.map((r,i) => r - (callBudget[i] || 0));

                // prepare annotations (selisih values) placed above the larger of the two bars
                const annotationsPoints = wilayahLabels.map((lab,i) => {
                    const top = Math.max(callReal[i]||0, callBudget[i]||0);
                    const y = top + Math.round(top * 0.04) + 1; // small offset above bar
                    const diff = selisih[i] || 0;
                    const labelText = (diff > 0 ? '+' + diff.toLocaleString() : diff.toLocaleString());
                    return {
                        x: lab,
                        y: y,
                        label: {
                            borderColor: '#555',
                            offsetY: -6,
                            style: { color: '#111', background: '#fff', fontSize: '12px', padding: { left:6, right:6, top:4, bottom:4 } },
                            text: labelText
                        }
                    };
                });

                const comparisonOptions = {
                    // use mixed chart: default line, bars as 'column'
                    chart: { type: 'line', height: 320 },
                    series: [
                        { name: 'Anggaran', type: 'column', data: callBudget },
                        { name: 'Realisasi', type: 'column', data: callReal },
                        { name: 'Selisih (R-A)', type: 'line', data: selisih, dataLabels: { enabled: true } }
                    ],
                    plotOptions: { bar: { horizontal: false, columnWidth: '55%' } },
                    // enable dataLabels only on the Selisih (line) series to show differences
                    dataLabels: {
                        enabled: true,
                        enabledOnSeries: [2],
                        offsetY: -6,
                        formatter: function(v, opts) {
                            if (opts.seriesIndex === 2) {
                                return (v > 0 ? '+'+v.toLocaleString() : v.toLocaleString());
                            }
                            return '';
                        },
                        style: { fontSize: '11px', colors: ['#111'] }
                    },
                    stroke: { width: [0,0,3], curve: 'smooth' },
                    markers: { size: 5 },
                    xaxis: { categories: wilayahLabels },
                    yaxis: { title: { text: 'Call' } },
                    colors: ['#68D8F0','#20B2AA','#FF6347'],
                    legend: { position: 'top', horizontalAlign: 'left' },
                    annotations: { points: annotationsPoints }
                };
                const comparisonChart = new ApexCharts(document.querySelector('#callComparisonChart'), comparisonOptions);
                comparisonChart.render().then(function(){
                            // populate small difference table
                            try {
                                const tbody = document.querySelector('#callDifferenceTable tbody');
                                if (tbody) {
                                    tbody.innerHTML = '';
                                    wilayahLabels.forEach((lab,i) => {
                                        const ang = callBudget[i] || 0;
                                        const rea = callReal[i] || 0;
                                        const diff = rea - ang;
                                        const pct = ang ? ((diff / ang) * 100).toFixed(1) : '0.0';
                                        const tr = document.createElement('tr');
                                        tr.innerHTML = `<td>${lab}</td><td class="text-end">${ang.toLocaleString()}</td><td class="text-end">${rea.toLocaleString()}</td><td class="text-end">${diff>0? '+'+diff.toLocaleString(): diff.toLocaleString()}</td><td class="text-end">${pct}%</td>`;
                                        tbody.appendChild(tr);
                                    });
                                }
                            } catch(e){ console.error('Error populating difference table', e); }
                }).catch(function(err){
                    console.error('Error rendering comparisonChart:', err);
                });
            } catch (error) {
                console.error('Error rendering Call charts:', error);
            }

            // Chart Produksi per Wilayah (Right Side)
            var produksiOptions = {
                chart: { 
                    type: 'bar', 
                    height: 350,
                    toolbar: { show: true }
                },
                series: [
                    { 
                        name: 'Pendapatan Realisasi', 
                        data: [4694, 11485, 13478, 13076],
                        color: '#FF8C00'
                    },
                    { 
                        name: 'Pendapatan Anggaran', 
                        data: [4394, 11194, 12998, 12384],
                        color: '#FFD700'
                    },
                    { 
                        name: 'Pemanduan Realisasi', 
                        data: [376, 536, 8794, 8794],
                        color: '#9370DB'
                    },
                    { 
                        name: 'Pemanduan Anggaran', 
                        data: [268, 518, 8794, 12584],
                        color: '#DDA0DD'
                    }
                ],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '60%',
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val.toLocaleString();
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '10px',
                        colors: ['#304758']
                    }
                },
                xaxis: { 
                    categories: ['WILAYAH 1', 'WILAYAH 2', 'WILAYAH 3', 'WILAYAH 4'],
                    title: { text: 'Wilayah' }
                },
                yaxis: {
                    title: { text: "Nilai Produksi" },
                    labels: {
                        formatter: function (val) {
                            return val.toLocaleString();
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left'
                },
                grid: {
                    borderColor: '#e3e6f0'
                }
            };
            
                try {
                const produksiChart = new ApexCharts(document.querySelector("#produksiChart"), produksiOptions);
                produksiChart.render();
            } catch (error) {
                console.error('Error rendering Produksi Chart:', error);
            }
        });
        </script>

        <script>
        document.addEventListener('DOMContentLoaded', function(){
            // Prevent existing global change handlers from auto-loading data on filter change
            const filters = document.querySelectorAll('.filter-input');
            const stops = [];
            filters.forEach(el => {
                const handler = function(e){ e.stopImmediatePropagation(); };
                el.addEventListener('change', handler, true);
                stops.push({el, handler});
            });

            const form = document.getElementById('filtersForm');
            if (!form) return;

            // On apply (form submit) remove interceptors so any global listeners can run if needed,
            // then allow normal submission to proceed (GET request will reload page with filters)
            form.addEventListener('submit', function(e){
                stops.forEach(s => s.el.removeEventListener('change', s.handler, true));
                // allow default submission to continue
            });
        });
        </script>

        
