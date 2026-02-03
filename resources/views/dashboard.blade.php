<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard LHGK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .stat-card {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        .pilot-card {
            background: linear-gradient(135deg, #e8eaf6 0%, #c5cae9 100%);
            color: #3f51b5;
        }
        .tunda-card {
            background: linear-gradient(135deg, #fce4ec 0%, #f8bbd0 100%);
            color: #c2185b;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .ship-type-badge {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            margin: 2px;
            display: inline-block;
        }
        .ship-types-container {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        .grt-gerak-badge {
            background-color: #e3f2fd;
            color: #1976d2;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-block;
        }
        .via-badge {
            background-color: #f3e5f5;
            color: #7b1fa2;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            margin: 2px;
        }
        .via-mobile {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .via-web {
            background-color: #fff3e0;
            color: #e65100;
        }
        .period-filter {
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-graph-up-arrow"></i> Dashboard LHGK</span>
            <div>
                <a href="{{ route('monitoring.nota') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-file-earmark-text"></i> Monitoring Nota
                </a>
                <a href="{{ route('regional.revenue') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-geo-alt"></i> Pendapatan Wilayah
                </a>
                <a href="{{ route('analisis.kelelahan') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-activity"></i> Analisis Kelelahan
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Period Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="period-filter">
                    <form method="GET" action="{{ route('dashboard') }}" class="row align-items-center">
                        <div class="col-md-2">
                            <label class="form-label"><i class="bi bi-funnel"></i> Filter:</label>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><i class="bi bi-building"></i> Cabang:</label>
                            <select name="cabang" class="form-select" onchange="this.form.submit()">
                                <option value="all" {{ $selectedBranch == 'all' ? 'selected' : '' }}>Semua Cabang</option>
                                @foreach($regionalGroups as $wilayah => $branches)
                                    <optgroup label="{{ $wilayah }}">
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch }}" {{ $selectedBranch == $branch ? 'selected' : '' }} title="{{ $branch }}">
                                                {{ Str::limit($branch, 50) }}
                                            </option>
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
                                    <option value="{{ $period }}" {{ $selectedPeriode == $period ? 'selected' : '' }}>
                                        {{ $period }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            @if($selectedPeriode != 'all' || $selectedBranch != 'all')
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary mt-4">
                                    <i class="bi bi-x-circle"></i> Reset Filter
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Filter Info / Statistics Cards -->
        @if($selectedPeriode == 'all' || $selectedBranch == 'all')
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <h5 class="mt-3">Pilih Cabang dan Periode untuk Menampilkan Data</h5>
                    <p class="mb-0">Silakan pilih <strong>Cabang</strong> dan <strong>Periode</strong> pada filter di atas untuk melihat statistik pandu.</p>
                    <p class="mb-0 mt-2 text-muted"><small>Fitur ini dioptimalkan untuk performa yang lebih baik dengan memuat data sesuai kebutuhan.</small></p>
                </div>
            </div>
        </div>
        @else
        <!-- Overall Statistics -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card stat-card bg-white" style="border-left: 4px solid #667eea;">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-person-badge fs-1" style="color: #667eea;"></i>
                        </div>
                        <h3 class="text-dark mb-1">{{ $totalOverall['total_pandu'] }}</h3>
                        <p class="mb-0 text-muted small">Total Pandu</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card bg-white" style="border-left: 4px solid #f093fb;">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-receipt fs-1" style="color: #f093fb;"></i>
                        </div>
                        <h3 class="text-dark mb-1">{{ number_format($totalOverall['total_transaksi']) }}</h3>
                        <p class="mb-0 text-muted small">Total Transaksi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-white" style="border-left: 4px solid #667eea;">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-cash-coin fs-1" style="color: #667eea;"></i>
                        </div>
                        <h4 class="text-dark mb-1">Rp {{ number_format($totalOverall['total_pendapatan_pandu'], 0, ',', '.') }}</h4>
                        <p class="mb-0 text-muted small">Total Pendapatan Pandu</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-white" style="border-left: 4px solid #f093fb;">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-water fs-1" style="color: #f093fb;"></i>
                        </div>
                        <h4 class="text-dark mb-1">Rp {{ number_format($totalOverall['total_pendapatan_tunda'], 0, ',', '.') }}</h4>
                        <p class="mb-0 text-muted small">Total Pendapatan Tunda</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card bg-white" style="border-left: 4px solid #10b981;">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-graph-up-arrow fs-1" style="color: #10b981;"></i>
                        </div>
                        <h4 class="text-dark mb-1">Rp {{ number_format($totalOverall['total_pendapatan_pandu'] + $totalOverall['total_pendapatan_tunda'], 0, ',', '.') }}</h4>
                        <p class="mb-0 text-muted small">Total Pendapatan</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- WT Statistics Row -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card bg-white" style="border-left: 4px solid #f59e0b;">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-clock fs-1" style="color: #f59e0b;"></i>
                        </div>
                        <h3 class="text-dark mb-1">{{ $totalOverall['transaksi_wt_di_atas_30'] }}</h3>
                        <p class="mb-0 text-muted">WT > 00:30</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-white" style="border-left: 4px solid #3b82f6;">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-clock-history fs-1" style="color: #3b82f6;"></i>
                        </div>
                        <h3 class="text-dark mb-1">{{ number_format((float)($totalOverall['rata_rata_wt'] ?? 0), 2) }}</h3>
                        <p class="mb-0 text-muted">Rata-Rata WT</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-white" style="border-left: 4px solid #ef4444;">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="bi bi-exclamation-triangle fs-1" style="color: #ef4444;"></i>
                        </div>
                        <h3 class="text-dark mb-1">{{ number_format((float)($totalOverall['max_wt'] ?? 0), 2) }}</h3>
                        <p class="mb-0 text-muted">Maksimal WT</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Pilot Card -->
        @if($topPilot)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card bg-white" style="border-left: 4px solid #f59e0b;">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <h5 class="mb-0 text-dark"><i class="bi bi-trophy-fill text-warning"></i> Pilot Produksi Tertinggi</h5>
                            </div>
                            <div class="col-md-2">
                                <h4 class="mb-0 text-dark">{{ $topPilot->NM_PERS_PANDU }}</h4>
                                <small class="text-muted">{{ $topPilot->NM_BRANCH }}</small>
                            </div>
                            <div class="col-md-2 text-center">
                                <h4 class="mb-0 text-dark">{{ number_format($topPilot->total_produksi) }}</h4>
                                <small class="text-muted">Total Produksi</small>
                            </div>
                            <div class="col-md-2 text-center">
                                <h5 class="mb-0 text-dark">{{ number_format($topPilot->rata_rata_wt, 2) }}</h5>
                                <small class="text-muted">Rata-Rata WT</small>
                            </div>
                            <div class="col-md-2 text-center">
                                <h5 class="mb-0 text-dark">Rp {{ number_format($topPilot->total_pendapatan_pandu, 0, ',', '.') }}</h5>
                                <small class="text-muted">Pendapatan Pandu</small>
                            </div>
                            <div class="col-md-2 text-center">
                                <h5 class="mb-0 text-dark">Rp {{ number_format($topPilot->total_pendapatan, 0, ',', '.') }}</h5>
                                <small class="text-muted">Total Pendapatan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endif

        <!-- CSV Upload Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-upload"></i> Upload File CSV</h5>
                        <form action="{{ route('upload.csv') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                                    <small class="text-muted">Format: CSV (maksimal 10MB) | <a href="/debug-periods" target="_blank">Debug Periode</a></small>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-upload"></i> Upload</button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('clear.data') }}" class="btn btn-danger w-100" onclick="return confirm('Yakin ingin menghapus semua data?')">
                                        <i class="bi bi-trash"></i> Clear Data
                                    </a>
                                </div>
                            </div>
                        </form>
                        
                        @if(session('success'))
                            <div class="alert alert-success mt-3">
                                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                            </div>
                        @endif
                        
                        @if($errors->any())
                            <div class="alert alert-danger mt-3">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <strong>Error:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        @if(session('import_errors') && count(session('import_errors')) > 0)
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-info-circle-fill"></i>
                                <strong>Warning pada beberapa baris:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach(session('import_errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Ship Statistics by GT Range and Flag -->
        @if(($selectedPeriode != 'all' || $selectedBranch != 'all') && $shipStatsByGT->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white;">
                        <h5 class="mb-0"><i class="bi bi-ship"></i> Statistik Kapal Berdasarkan Range GT dan Bendera</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Range GT</th>
                                        <th>Jenis Kapal</th>
                                        <th class="text-end">Total Transaksi</th>
                                        <th class="text-end">Pendapatan Pandu</th>
                                        <th class="text-end">Pendapatan Tunda</th>
                                        <th class="text-end">Total Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $currentGT = '';
                                        $totalPanduGlobal = 0;
                                        $totalTundaGlobal = 0;
                                        $totalTransaksiGlobal = 0;
                                        $totalPendapatanGlobal = 0;
                                        
                                        // Calculate totals by flag
                                        $totalPanduNasional = 0;
                                        $totalTundaNasional = 0;
                                        $totalTransaksiNasional = 0;
                                        $totalPendapatanNasional = 0;
                                        
                                        $totalPanduAsing = 0;
                                        $totalTundaAsing = 0;
                                        $totalTransaksiAsing = 0;
                                        $totalPendapatanAsing = 0;
                                    @endphp
                                    @foreach($shipStatsByGT as $stat)
                                        @php
                                            $totalPanduGlobal += $stat->total_pendapatan_pandu;
                                            $totalTundaGlobal += $stat->total_pendapatan_tunda;
                                            $totalTransaksiGlobal += $stat->total_transaksi;
                                            $totalPendapatanGlobal += $stat->total_pendapatan;
                                            
                                            if ($stat->JENIS_KAPAL_DARI_BENDERA == 'KAPAL NASIONAL') {
                                                $totalPanduNasional += $stat->total_pendapatan_pandu;
                                                $totalTundaNasional += $stat->total_pendapatan_tunda;
                                                $totalTransaksiNasional += $stat->total_transaksi;
                                                $totalPendapatanNasional += $stat->total_pendapatan;
                                            } else {
                                                $totalPanduAsing += $stat->total_pendapatan_pandu;
                                                $totalTundaAsing += $stat->total_pendapatan_tunda;
                                                $totalTransaksiAsing += $stat->total_transaksi;
                                                $totalPendapatanAsing += $stat->total_pendapatan;
                                            }
                                            
                                            $rowspan = $shipStatsByGT->where('RANGE_GT', $stat->RANGE_GT)->count();
                                        @endphp
                                        <tr>
                                            @if($currentGT != $stat->RANGE_GT)
                                                @php $currentGT = $stat->RANGE_GT; @endphp
                                                <td rowspan="{{ $rowspan }}" class="align-middle">
                                                    <span class="badge bg-primary">{{ str_replace(' GT', '', $stat->RANGE_GT) }}</span>
                                                </td>
                                            @endif
                                            <td>
                                                <span class="badge {{ $stat->JENIS_KAPAL_DARI_BENDERA == 'KAPAL NASIONAL' ? 'bg-success' : 'bg-info' }}">
                                                    {{ $stat->JENIS_KAPAL_DARI_BENDERA }}
                                                </span>
                                            </td>
                                            <td class="text-end">{{ number_format($stat->total_transaksi) }}</td>
                                            <td class="text-end">Rp {{ number_format($stat->total_pendapatan_pandu, 0, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($stat->total_pendapatan_tunda, 0, ',', '.') }}</td>
                                            <td class="text-end"><strong>Rp {{ number_format($stat->total_pendapatan, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-success">
                                        <th colspan="2" class="text-end">Total Kapal Nasional:</th>
                                        <th class="text-end">{{ number_format($totalTransaksiNasional) }}</th>
                                        <th class="text-end">Rp {{ number_format($totalPanduNasional, 0, ',', '.') }}</th>
                                        <th class="text-end">Rp {{ number_format($totalTundaNasional, 0, ',', '.') }}</th>
                                        <th class="text-end"><strong>Rp {{ number_format($totalPendapatanNasional, 0, ',', '.') }}</strong></th>
                                    </tr>
                                    <tr class="table-info">
                                        <th colspan="2" class="text-end">Total Kapal Asing:</th>
                                        <th class="text-end">{{ number_format($totalTransaksiAsing) }}</th>
                                        <th class="text-end">Rp {{ number_format($totalPanduAsing, 0, ',', '.') }}</th>
                                        <th class="text-end">Rp {{ number_format($totalTundaAsing, 0, ',', '.') }}</th>
                                        <th class="text-end"><strong>Rp {{ number_format($totalPendapatanAsing, 0, ',', '.') }}</strong></th>
                                    </tr>
                                    <tr class="table-secondary">
                                        <th colspan="2" class="text-end">Total Keseluruhan:</th>
                                        <th class="text-end">{{ number_format($totalTransaksiGlobal) }}</th>
                                        <th class="text-end">Rp {{ number_format($totalPanduGlobal, 0, ',', '.') }}</th>
                                        <th class="text-end">Rp {{ number_format($totalTundaGlobal, 0, ',', '.') }}</th>
                                        <th class="text-end"><strong>Rp {{ number_format($totalPendapatanGlobal, 0, ',', '.') }}</strong></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Chart Section -->
        @if($selectedPeriode != 'all' && $selectedBranch != 'all')
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <h5 class="card-title">Pendapatan Pemanduan</h5>
                        <canvas id="panduChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <h5 class="card-title">Pendapatan Penundaan</h5>
                        <canvas id="tundaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pilot Cards -->
        <div class="row">
            @foreach($statistics as $stat)
                <div class="col-md-4 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title text-primary mb-0">
                                    <i class="bi bi-person-badge"></i> {{ $stat->NM_PERS_PANDU }}
                                </h5>
                                <span class="grt-gerak-badge">
                                    <i class="bi bi-speedometer2"></i> GRT | GERAK: {{ number_format($stat->total_grt, 0, ',', '.') }} | {{ $stat->total_transaksi }}
                                </span>
                            </div>
                            <div class="mb-2">
                                <span class="badge bg-info">
                                    <i class="bi bi-clock-history"></i> Rata-Rata WT: {{ number_format($stat->rata_rata_wt, 2) }}
                                </span>
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock"></i> WT > 00:30: {{ $stat->transaksi_wt_di_atas_30 }} kali
                                </span>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <p class="text-muted mb-1">Pendapatan Pandu</p>
                                    <h6 class="text-success">Rp {{ number_format($stat->total_pendapatan_pandu, 0, ',', '.') }}</h6>
                                </div>
                                <div class="col-6">
                                    <p class="text-muted mb-1">Pendapatan Tunda</p>
                                    <h6 class="text-info">Rp {{ number_format($stat->total_pendapatan_tunda, 0, ',', '.') }}</h6>
                                </div>
                            </div>
                            <hr>
                            <div class="mb-2">
                                <i class="bi bi-bar-chart"></i> Total Transaksi: <strong>{{ $stat->total_transaksi }}</strong>
                            </div>
                            
                            <!-- Realisasi Via Mobile/Web -->
                            <div class="mb-3">
                                <p class="text-muted mb-2"><i class="bi bi-device-ssd"></i> Realisasi Via:</p>
                                <div>
                                    <span class="via-badge via-mobile">
                                        <i class="bi bi-phone"></i> Mobile: <strong>{{ $stat->via_mobile }}</strong>
                                    </span>
                                    <span class="via-badge via-web">
                                        <i class="bi bi-laptop"></i> Web: <strong>{{ $stat->via_web }}</strong>
                                    </span>
                                    @if($stat->via_partial > 0)
                                        <span class="via-badge">
                                            <i class="bi bi-puzzle"></i> Partial: <strong>{{ $stat->via_partial }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if($stat->ship_types && count($stat->ship_types) > 0)
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#ship-types-{{ $loop->index }}">
                                        <i class="bi bi-ship"></i> Jenis Kapal Yang Dilayani ({{ count($stat->ship_types) }})
                                    </button>
                                    
                                    <div class="collapse mt-3" id="ship-types-{{ $loop->index }}">
                                        <div class="ship-types-container">
                                            <p class="text-muted mb-2"><i class="bi bi-ship"></i> Jenis Kapal:</p>
                                            <div>
                                                @foreach($stat->ship_types as $shipType)
                                                    <span class="ship-type-badge">
                                                        {{ $shipType->JN_KAPAL }} <strong>({{ $shipType->jumlah }})</strong>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prepare data for charts
        const statistics = @json($statistics);
        
        // Only create charts if data exists
        if (statistics && statistics.length > 0) {
            // Pandu Chart - tampilkan semua data
            const panduCtx = document.getElementById('panduChart').getContext('2d');
        new Chart(panduCtx, {
            type: 'line',
            data: {
                labels: statistics.map(s => s.NM_PERS_PANDU),
                datasets: [{
                    label: 'Pendapatan Pemanduan',
                    data: statistics.map(s => s.total_pendapatan_pandu),
                    backgroundColor: 'rgba(102, 126, 234, 0.2)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Tunda Chart - tampilkan semua data
        const tundaCtx = document.getElementById('tundaChart').getContext('2d');
        new Chart(tundaCtx, {
            type: 'line',
            data: {
                labels: statistics.map(s => s.NM_PERS_PANDU),
                datasets: [{
                    label: 'Pendapatan Penundaan',
                    data: statistics.map(s => s.total_pendapatan_tunda),
                    backgroundColor: 'rgba(16, 185, 129, 0.15)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
        }
    </script>
</body>
</html>
