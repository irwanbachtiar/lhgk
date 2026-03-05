
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Preview Dashboard Operasional</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100">

  <div class="max-w-7xl mx-auto p-6">
    <div class="grid grid-cols-12 gap-6">

      <!-- SIDEBAR -->
      <aside class="col-span-2 bg-white rounded-xl shadow p-4">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">Filter Wilayah</h2>
        <div class="space-y-2">
          <button class="w-full text-left px-3 py-2 rounded-lg bg-blue-50 text-blue-600 font-medium">Wilayah 1</button>
          <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-600">Wilayah 2</button>
          <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-600">Wilayah 3</button>
          <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-600">Wilayah 4</button>
        </div>
      </aside>

      <!-- MAIN -->
      <main class="col-span-10 space-y-6">

        <h1 class="text-xl font-semibold text-slate-800">Dashboard Operasional Pelabuhan</h1>

        <!-- KPI -->
        <div class="grid grid-cols-12 gap-4">
          <div class="col-span-3 bg-white rounded-xl shadow p-4">
            <p class="text-sm text-slate-500">Call Kapal</p>
            <p class="text-3xl font-bold mt-2">1.245</p>
            <p class="text-xs text-slate-400">Call</p>
          </div>
          <div class="col-span-3 bg-white rounded-xl shadow p-4">
            <p class="text-sm text-slate-500">Produksi Pemanduan</p>
            <p class="text-3xl font-bold text-green-600 mt-2">3.580.000</p>
            <p class="text-xs text-slate-400">GT / Grk</p>
          </div>
          <div class="col-span-3 bg-white rounded-xl shadow p-4">
            <p class="text-sm text-slate-500">Produksi Penundaan</p>
            <p class="text-3xl font-bold text-orange-500 mt-2">125.400</p>
            <p class="text-xs text-slate-400">GT / Jam</p>
          </div>
          <div class="col-span-3 bg-white rounded-xl shadow p-4">
            <p class="text-sm text-slate-500">Pendapatan</p>
            <p class="text-3xl font-bold text-emerald-700 mt-2">Rp 12,45 M</p>
            <p class="text-xs text-slate-400">Rupiah</p>
          </div>
        </div>

        <!DOCTYPE html>
        <html lang="id">
        <head>
        <meta charset="UTF-8">
        <title>Dashboard Kunjungan Kapal</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

        <style>
        body { background:#f4f6f9; }
        .card {
            border:none;
            border-radius:12px;
            box-shadow:0 4px 12px rgba(0,0,0,0.05);
        }
        .kpi-title { font-size:14px; color:#6c757d; }
        .kpi-value { font-size:22px; font-weight:700; }
        .tree-level-1 { font-weight:bold; }
        .tree-level-2 { padding-left:20px; }
        .tree-level-3 { padding-left:40px; }
        .tree-level-4 { padding-left:60px; }
        .tree-level-5 { padding-left:80px; }
        th { font-size:13px; }
        </style>
        </head>
        <body>

        <div class="container-fluid p-4">

        <!-- HEADER -->
        <div class="d-flex justify-content-between mb-4">
            <div>
                <h4 class="fw-bold">Dashboard Monitoring Kunjungan Kapal</h4>
                <small class="text-muted">Call & Gross Tonnage (GT)</small>
            </div>
            <div class="d-flex align-items-center">
                <form id="filtersForm" method="GET" class="d-flex align-items-center gap-2" action="">
                    <select name="branch" id="branchFilter" class="form-select filter-input" style="width:160px">
                        <option value="">-- Semua Cabang --</option>
                        <option value="1">Cabang 1</option>
                        <option value="2">Cabang 2</option>
                        <option value="3">Cabang 3</option>
                    </select>

                    <input type="date" name="start_date" id="startDate" class="form-control filter-input" style="width:160px">
                    <input type="date" name="end_date" id="endDate" class="form-control filter-input" style="width:160px">

                    <!-- Apply button positioned to the right of period filters -->
                    <button type="submit" id="applyFilters" class="btn btn-primary ms-2">Apply</button>
                </form>

                <div class="ms-3">
                    <button class="btn btn-outline-secondary">Export Excel</button>
                </div>
            </div>
        </div>

        <!-- KPI CARDS -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card p-3">
                    <div class="kpi-title">Total Kunjungan</div>
                    <div class="kpi-value">Call: {{ number_format($dashboardSummary['total_real_call'] ?? 0) }}</div>
                    <div class="kpi-value text-primary">GT: {{ number_format($dashboardSummary['total_real_gt'] ?? 0) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <div class="kpi-title">Luar Negeri</div>
                    @php
                        $ln_call = 0; $ln_gt = 0;
                        foreach($trafikData ?? [] as $w=>$d) { $ln_call += $d['luar_negeri']['total_real_call'] ?? $d['luar_negeri']['total_call'] ?? 0; $ln_gt += $d['luar_negeri']['total_real_gt'] ?? $d['luar_negeri']['total_gt'] ?? 0; }
                    @endphp
                    <div class="kpi-value">Call: {{ number_format($ln_call) }}</div>
                    <div class="kpi-value text-success">GT: {{ number_format($ln_gt) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <div class="kpi-title">Dalam Negeri</div>
                    @php
                        $dn_call = 0; $dn_gt = 0;
                        foreach($trafikData ?? [] as $w=>$d) { $dn_call += $d['dalam_negeri']['total_real_call'] ?? $d['dalam_negeri']['total_call'] ?? 0; $dn_gt += $d['dalam_negeri']['total_real_gt'] ?? $d['dalam_negeri']['total_gt'] ?? 0; }
                    @endphp
                    <div class="kpi-value">Call: {{ number_format($dn_call) }}</div>
                    <div class="kpi-value text-warning">GT: {{ number_format($dn_gt) }}</div>
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

        </body>
        </html>
