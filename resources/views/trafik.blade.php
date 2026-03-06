
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
    <title>Preview Dashboard Operasional</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
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
                        <span class="navbar-brand mb-0 h1"><i class="bi bi-graph-up-arrow"></i> Dashboard LHGK</span>
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

                <h1 class="text-xl font-semibold text-slate-800">Dashboard Operasional Pelabuhan</h1>

                <!-- KPI (moved below filter) -->

        <div class="container-fluid p-4">

        <!-- HEADER -->
        <div class="d-flex justify-content-between mb-4">
            <div>
                <h4 class="fw-bold">Dashboard Monitoring Kunjungan Kapal</h4>
                <small class="text-muted">Call & Gross Tonnage (GT)</small>
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
                            @if(!empty($periods) && is_array($periods))
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
                    <div class="kpi-value">Call: {{ number_format($trafik_call) }}</div>
                    <div class="kpi-value text-primary">GT: {{ number_format($trafik_gt) }}</div>
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

        <!-- CHART -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card p-3">
                    <h6>Trend Call vs GT</h6>
                    <div id="trendChart"></div>
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
        var options = {
            chart: { type: 'line', height: 350 },
            series: [
                { name: 'Call', data: [1200,1500,1700,1600,1800] },
                { name: 'GT', data: [800000,900000,950000,920000,1000000] }
            ],
            xaxis: { categories: ['Jan','Feb','Mar','Apr','Mei'] },
            yaxis: [
                { title: { text: "Call" } },
                { opposite: true, title: { text: "GT" } }
            ]
        };
        new ApexCharts(document.querySelector("#trendChart"), options).render();
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

        
