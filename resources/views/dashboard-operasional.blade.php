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
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-graph-up-arrow"></i> Dashboard Operasional</span>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-2"><i class="bi bi-graph-up-arrow"></i> Dashboard LHGK</a>
                <a href="{{ route('regional.revenue') }}" class="btn btn-light btn-sm me-2"><i class="bi bi-geo-alt"></i> Pendapatan Wilayah</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="period-filter">
                    <form method="GET" action="{{ route('dashboard.operasional') }}" class="row align-items-center">
                        <div class="col-md-2">
                            <label class="form-label"><i class="bi bi-funnel"></i> Filter:</label>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-building"></i> Cabang:</label>
                            <select name="cabang" class="form-select" onchange="this.form.submit()">
                                <option value="all" {{ $selectedBranch == 'all' ? 'selected' : '' }}>Semua Cabang</option>
                                @foreach($regionalGroups as $wilayah => $branches)
                                    <optgroup label="{{ $wilayah }}">
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch }}" {{ $selectedBranch == $branch ? 'selected' : '' }} title="{{ $branch }}">{{ Str::limit($branch, 50) }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><i class="bi bi-calendar-range"></i> Periode:</label>
                            <select name="periode" class="form-select" onchange="this.form.submit()">
                                <option value="all" {{ $selectedPeriode == 'all' ? 'selected' : '' }}>Semua Periode</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period }}" {{ $selectedPeriode == $period ? 'selected' : '' }}>{{ $period }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 text-end">
                            @if($selectedPeriode != 'all' || $selectedBranch != 'all')
                                <a href="{{ route('dashboard.operasional') }}" class="btn btn-outline-secondary mt-4"><i class="bi bi-x-circle"></i> Reset Filter</a>
                                <a href="{{ route('dashboard.operasional.export', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch]) }}" class="btn btn-primary mt-4 ms-2"><i class="bi bi-download"></i> Export Excel</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            @if($selectedPeriode == 'all' || $selectedBranch == 'all')
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                            <h5 class="mt-3">Pilih Cabang dan Periode untuk Menampilkan Data</h5>
                            <p class="mb-0">Silakan pilih <strong>Cabang</strong> dan <strong>Periode</strong> pada filter di atas untuk melihat statistik operasional.</p>
                        </div>
                    </div>
                </div>
            @else

            <!-- Stat Cards -->
            <div class="row stat-row-spacing">
                <div class="col-md-3">
                    <div class="card stat-card bg-white" style="border-left: 4px solid #667eea;">
                        <div class="card-body d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-person-badge fs-2" style="color: #667eea;"></i>
                            </div>
                            <div class="flex-grow-1 text-end">
                                <h3 class="text-dark mb-1">{{ $stats['total_pandu'] ?? 0 }}</h3>
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
                                <h3 class="text-dark mb-1">{{ $tundaDistinct ?? 0 }}</h3>
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
                                <h4 class="text-dark mb-1">{{ number_format($stats['total_transaksi'] ?? 0) }}</h4>
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
                                <h3 class="text-dark mb-1">{{ number_format(optional($viaCounts)->web ?? 0) }}</h3>
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
                                <h3 class="text-dark mb-1">{{ number_format(optional($viaCounts)->mobile ?? 0) }}</h3>
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
                                <h3 class="text-dark mb-1">{{ number_format(optional($viaCounts)->partial ?? 0) }}</h3>
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
                                @php $totalPilots = !empty($pilotList) ? count($pilotList) : 0; @endphp
                                @if($totalPilots > 1)
                                    @php $perCol = (int) ceil($totalPilots / 2); @endphp
                                    <div class="row">
                                        <div class="col-6">
                                            <ol class="mb-0 ps-3" start="1">
                                                @foreach(array_slice($pilotList, 0, $perCol) as $p)
                                                    <li>{{ $p }}</li>
                                                @endforeach
                                            </ol>
                                        </div>
                                        <div class="col-6">
                                            <ol class="mb-0 ps-3" start="{{ $perCol + 1 }}">
                                                @foreach(array_slice($pilotList, $perCol) as $p)
                                                    <li>{{ $p }}</li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    </div>
                                @else
                                    <ol class="mb-0 ps-3">
                                        @if($totalPilots > 0)
                                            @foreach($pilotList as $p)
                                                <li>{{ $p }}</li>
                                            @endforeach
                                        @else
                                            <li>-</li>
                                        @endif
                                    </ol>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header card-soft-header">Daftar Nama Tunda</div>
                        <div class="card-body">
                                    <div style="max-height:220px; overflow:auto;">
                                        @if(!empty($tundaList) && count($tundaList) > 10)
                                            <div class="row">
                                                <div class="col-6">
                                                    <ol class="mb-0 ps-3">
                                                        @foreach(array_slice($tundaList, 0, 10) as $t)
                                                            <li>{{ $t }}</li>
                                                        @endforeach
                                                    </ol>
                                                </div>
                                                <div class="col-6">
                                                    <ol class="mb-0 ps-3" start="11">
                                                        @foreach(array_slice($tundaList, 10) as $t)
                                                            <li>{{ $t }}</li>
                                                        @endforeach
                                                    </ol>
                                                </div>
                                            </div>
                                        @else
                                            <ol class="mb-0 ps-3">
                                                @if(!empty($tundaList) && count($tundaList))
                                                    @foreach($tundaList as $t)
                                                        <li>{{ $t }}</li>
                                                    @endforeach
                                                @else
                                                    <li>-</li>
                                                @endif
                                            </ol>
                                        @endif
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

                    @php $groups = $transaksiByShip->groupBy('pelayaran_group'); @endphp

                    {{-- Dalam Negeri --}}
                    @php $dalamTotal = isset($groups['Dalam Negeri']) ? $groups['Dalam Negeri']->sum('jumlah') : 0; @endphp
                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <h6 class="mb-0">Dalam Negeri</h6>
                        <div class="text-muted">Total: <strong>{{ number_format($dalamTotal) }}</strong></div>
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
                            @if(isset($groups['Dalam Negeri']) && $groups['Dalam Negeri']->isNotEmpty())
                                @foreach($groups['Dalam Negeri']->sortByDesc('jumlah') as $it)
                                    <tr class="child-dalam">
                                        <td>{{ $it->JN_KAPAL }}</td>
                                        <td class="text-end">{{ number_format((float)($it->avg_lama_tunda ?? 0), 2, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format((float)($it->avg_grt ?? 0), 2, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format((float)($it->avg_trt ?? 0), 2, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format((float)($it->avg_at ?? 0), 2, ',', '.') }}</td>
                                        <td class="text-end">{{ $it->jumlah }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="6">Tidak ada data Dalam Negeri</td></tr>
                            @endif
                        </tbody>
                    </table>
                    </div>

                    

                    {{-- Luar Negeri --}}
                    @php $luarTotal = isset($groups['Luar Negeri']) ? $groups['Luar Negeri']->sum('jumlah') : 0; @endphp
                    <div class="d-flex align-items-center justify-content-between mt-4">
                        <h6 class="mb-0">Luar Negeri</h6>
                        <div class="text-muted">Total: <strong>{{ number_format($luarTotal) }}</strong></div>
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
                            @if(isset($groups['Luar Negeri']) && $groups['Luar Negeri']->isNotEmpty())
                                @foreach($groups['Luar Negeri']->sortByDesc('jumlah') as $it)
                                    <tr class="child-luar">
                                        <td>{{ $it->JN_KAPAL }}</td>
                                        <td class="text-end">{{ number_format((float)($it->avg_lama_tunda ?? 0), 2, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format((float)($it->avg_grt ?? 0), 2, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format((float)($it->avg_trt ?? 0), 2, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format((float)($it->avg_at ?? 0), 2, ',', '.') }}</td>
                                        <td class="text-end">{{ $it->jumlah }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="6">Tidak ada data Luar Negeri</td></tr>
                            @endif
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        @endif
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
    });
</script>
</html>
