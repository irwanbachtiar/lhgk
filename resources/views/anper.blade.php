<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendapatan Per Anak Perusahaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet" integrity="sha384-QuGBSgV5Im3DzL2z+8Ko9/hqNy/N0O7zwvXAtfd1MvPKWa/UbeLV65cfm4BV5Wgq" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" integrity="sha384-e6nUZLBkQ86NJ6TVVKAeSaK8jWa3NhkYWZFomE39AvDbQWeie9PlQqM3pmYW5d1g" crossorigin="anonymous"></script>
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
            background: white;
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
        .anper-card {
            border-left: 6px solid;
            margin-bottom: 24px;
        }
        .anper-pms    { border-left-color: #667eea; }
        .anper-jai    { border-left-color: #f59e0b; }
        .anper-legi   { border-left-color: #06b6d4; }
        .anper-epi    { border-left-color: #10b981; }
        .anper-bima   { border-left-color: #8b5cf6; }

        .anper-header-pms  { background: linear-gradient(135deg, #667eea, #764ba2); }
        .anper-header-jai  { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .anper-header-legi { background: linear-gradient(135deg, #06b6d4, #0284c7); }
        .anper-header-epi  { background: linear-gradient(135deg, #10b981, #059669); }
        .anper-header-bima { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }

        .pendapatan-card {
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.07);
            padding: 1.2rem 1.4rem;
            background: #f9fafb;
            height: 100%;
            transition: box-shadow 0.2s;
        }
        .pendapatan-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .pendapatan-label {
            font-size: 0.82rem;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 0.35rem;
        }
        .pendapatan-icon {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        .pendapatan-value {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.1;
        }
        .pendapatan-anggaran {
            font-size: 0.78rem;
            color: #9ca3af;
            margin-top: 0.3rem;
        }
        .progress-thin {
            height: 6px;
            border-radius: 3px;
        }
        .summary-badge {
            font-size: 1rem;
            padding: 0.45rem 0.9rem;
            border-radius: 10px;
        }
        .grand-total-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .section-divider {
            border: none;
            border-top: 2px dashed #e5e7eb;
            margin: 2rem 0;
        }
        /* Global Loading Animation */
        .global-loading-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(3px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease-in;
        }
        .global-loading-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideIn 0.4s ease-out;
            max-width: 300px;
        }
        .global-loading-spinner {
            width: 60px; height: 60px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        .global-loading-text {
            color: #333; font-weight: 500; font-size: 16px; margin: 0;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideIn { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

<!-- Global Loading Overlay -->
<div id="globalLoading" class="global-loading-overlay">
    <div class="global-loading-content">
        <div class="global-loading-spinner"></div>
        <p class="global-loading-text">Memproses data...</p>
    </div>
</div>

<!-- NAVBAR -->
<nav class="navbar navbar-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">
            <i class="bi bi-building-fill-gear"></i> Pendapatan Per Anak Perusahaan
        </span>
        <div>
            <a href="{{ route('dashboard.operasional') }}" class="btn btn-light btn-sm me-2">
                <i class="bi bi-kanban-fill"></i> Dashboard Operasional
            </a>
            <a href="{{ route('trafik') }}" class="btn btn-light btn-sm me-2">
                <i class="bi bi-graph-up-arrow"></i> Dashboard LHGK
            </a>
            <a href="{{ route('regional.revenue') }}" class="btn btn-light btn-sm me-2">
                <i class="bi bi-geo-alt"></i> Pendapatan Wilayah
            </a>
            <a href="{{ route('summary') }}" class="btn btn-light btn-sm me-2">
                <i class="bi bi-bar-chart-fill"></i> Summary
            </a>
            <a href="{{ route('monitoring.nota') }}" class="btn btn-light btn-sm">
                <i class="bi bi-file-earmark-text"></i> Monitoring Nota
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">

    <!-- FILTER SECTION -->
    <div class="filter-section">
        <form method="GET" action="{{ route('anper') }}" class="row align-items-center">
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="bi bi-funnel"></i> Filter Periode:
                </label>
            </div>
            <div class="col-md-4">
                <div class="d-flex">
                    <select name="periode" class="form-select">
                        <option value="all" {{ $selectedPeriode == 'all' ? 'selected' : '' }}>-- Semua Periode --</option>
                        @foreach($periods as $period)
                            <option value="{{ $period }}" {{ $selectedPeriode == $period ? 'selected' : '' }}>
                                {{ $period }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-primary ms-2"
                        onclick="document.getElementById('globalLoading').style.display='flex'; this.closest('form').submit();">
                        <i class="bi bi-funnel-fill"></i> Apply
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                @if($selectedPeriode != 'all')
                    <a href="{{ route('anper') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Reset Filter
                    </a>
                    <span class="ms-3 text-muted">
                        <i class="bi bi-calendar-check"></i>
                        Menampilkan: <strong>{{ $selectedPeriode }}</strong>
                    </span>
                @endif
            </div>
        </form>
    </div>

    @if($selectedPeriode == 'all')
        <!-- Info sebelum pilih periode -->
        <div class="alert alert-info text-center py-5" style="border-radius: 15px;">
            <i class="bi bi-building-fill-gear" style="font-size: 3rem;"></i>
            <h5 class="mt-3">Silakan pilih periode untuk melihat data pendapatan per anak perusahaan</h5>
            <p class="mb-0 text-muted">Data akan dikelompokkan berdasarkan masing-masing anak perusahaan dan jenis pendapatannya.</p>
        </div>
    @else

    <!-- GRAND TOTAL CARD -->
    <div class="grand-total-card mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1"><i class="bi bi-graph-up-arrow"></i> Total Pendapatan Seluruh Anak Perusahaan</h4>
                <p class="mb-0 opacity-75">Periode: {{ $selectedPeriode }}</p>
            </div>
            <div class="col-md-4 text-end">
                <div style="font-size: 0.85rem; opacity: 0.8;">Grand Total Realisasi</div>
                <div style="font-size: 2.2rem; font-weight: 700;">
                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- CHART OVERVIEW -->
    <div class="row mb-4">
        <div class="col-md-7">
            <div class="card stat-card h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-bar-chart-fill"></i> Perbandingan Realisasi per Anak Perusahaan</h6>
                </div>
                <div class="card-body">
                    <canvas id="anperBarChart" style="max-height: 320px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-5 mt-3 mt-md-0">
            <div class="card stat-card h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-pie-chart-fill"></i> Distribusi Kontribusi</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="anperPieChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <hr class="section-divider">

    <!-- =====================================================
         SECTION 1: PMS - Pelindo Marine Service
    ====================================================== -->
    <div class="card stat-card anper-card anper-pms mb-4">
        <div class="card-header anper-header-pms text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-ship"></i>
                        &nbsp;{{ $data['pms']['nama'] }}
                    </h5>
                    <small class="opacity-75"></small>
                </div>
                <div class="text-end">
                    <div style="font-size: 0.8rem; opacity: 0.8;">Total Realisasi</div>
                    <div style="font-size: 1.6rem; font-weight: 700;">
                        Rp {{ number_format($data['pms']['total_realisasi'], 0, ',', '.') }}
                    </div>
                    <span class="badge bg-white text-dark summary-badge">
                        {{ $data['pms']['persentase'] }}% dari Anggaran
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body py-4">
            <div class="row g-3">
                @foreach($data['pms']['pendapatan'] as $key => $item)
                <div class="col-12">
                    <div class="pendapatan-card">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="{{ $item['icon'] }} pendapatan-icon text-{{ $item['color'] }}"></i>
                            </div>
                            <div class="col">
                                <div class="pendapatan-label">{{ $item['label'] }}</div>
                                <div class="pendapatan-value text-{{ $item['color'] }}">
                                    Rp {{ number_format($item['realisasi'], 0, ',', '.') }}
                                </div>
                                <div class="pendapatan-anggaran">
                                    Anggaran: Rp {{ number_format($item['anggaran'], 0, ',', '.') }}
                                </div>
                                <div class="mt-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted">Realisasi vs Anggaran</small>
                                        <small class="fw-bold text-{{ $item['color'] }}">{{ $item['persentase'] }}%</small>
                                    </div>
                                    <div class="progress progress-thin">
                                        <div class="progress-bar bg-{{ $item['color'] }}"
                                            style="width: {{ min($item['persentase'], 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- =====================================================
         SECTION 2: JAI - Jasa Armada Indonesia
    ====================================================== -->
    <div class="card stat-card anper-card anper-jai mb-4">
        <div class="card-header anper-header-jai text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-life-preserver"></i>
                        &nbsp;{{ $data['jai']['nama'] }}
                    </h5>
                    <small class="opacity-75"></small>
                </div>
                <div class="text-end">
                    <div style="font-size: 0.8rem; opacity: 0.8;">Total Realisasi</div>
                    <div style="font-size: 1.6rem; font-weight: 700;">
                        Rp {{ number_format($data['jai']['total_realisasi'], 0, ',', '.') }}
                    </div>
                    <span class="badge bg-white text-dark summary-badge">
                        {{ $data['jai']['persentase'] }}% dari Anggaran
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body py-4">
            <div class="row g-3">
                @foreach($data['jai']['pendapatan'] as $key => $item)
                <div class="col-12">
                    <div class="pendapatan-card">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="{{ $item['icon'] }} pendapatan-icon text-{{ $item['color'] }}"></i>
                            </div>
                            <div class="col">
                                <div class="pendapatan-label">{{ $item['label'] }}</div>
                                <div class="pendapatan-value text-{{ $item['color'] }}">
                                    Rp {{ number_format($item['realisasi'], 0, ',', '.') }}
                                </div>
                                <div class="pendapatan-anggaran">
                                    Anggaran: Rp {{ number_format($item['anggaran'], 0, ',', '.') }}
                                </div>
                                <div class="mt-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted">Realisasi vs Anggaran</small>
                                        <small class="fw-bold text-{{ $item['color'] }}">{{ $item['persentase'] }}%</small>
                                    </div>
                                    <div class="progress progress-thin">
                                        <div class="progress-bar bg-{{ $item['color'] }}"
                                            style="width: {{ min($item['persentase'], 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- =====================================================
         SECTION 3: LEGI - Lamong Energi Indonesia
    ====================================================== -->
    <div class="card stat-card anper-card anper-legi mb-4">
        <div class="card-header anper-header-legi text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-lightning-charge"></i>
                        &nbsp;{{ $data['legi']['nama'] }}
                    </h5>
                    <small class="opacity-75"></small>
                </div>
                <div class="text-end">
                    <div style="font-size: 0.8rem; opacity: 0.8;">Total Realisasi</div>
                    <div style="font-size: 1.6rem; font-weight: 700;">
                        Rp {{ number_format($data['legi']['total_realisasi'], 0, ',', '.') }}
                    </div>
                    <span class="badge bg-white text-dark summary-badge">
                        {{ $data['legi']['persentase'] }}% dari Anggaran
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body py-4">
            <div class="row g-3">
                @foreach($data['legi']['pendapatan'] as $key => $item)
                <div class="col-md-4">
                    <div class="pendapatan-card text-center">
                        <i class="{{ $item['icon'] }} pendapatan-icon text-{{ $item['color'] }}"></i>
                        <div class="pendapatan-label">{{ $item['label'] }}</div>
                        <div class="pendapatan-value text-{{ $item['color'] }}">
                            Rp {{ number_format($item['realisasi'], 0, ',', '.') }}
                        </div>
                        <div class="pendapatan-anggaran">
                            Anggaran: Rp {{ number_format($item['anggaran'], 0, ',', '.') }}
                        </div>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Pencapaian</small>
                                <small class="fw-bold text-{{ $item['color'] }}">{{ $item['persentase'] }}%</small>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-{{ $item['color'] }}"
                                    style="width: {{ min($item['persentase'], 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- =====================================================
         SECTION 4: EPI - Energi Pelabuhan Indonesia
    ====================================================== -->
    <div class="card stat-card anper-card anper-epi mb-4">
        <div class="card-header anper-header-epi text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-battery-charging"></i>
                        &nbsp;{{ $data['epi']['nama'] }}
                    </h5>
                    <small class="opacity-75"></small>
                </div>
                <div class="text-end">
                    <div style="font-size: 0.8rem; opacity: 0.8;">Total Realisasi</div>
                    <div style="font-size: 1.6rem; font-weight: 700;">
                        Rp {{ number_format($data['epi']['total_realisasi'], 0, ',', '.') }}
                    </div>
                    <span class="badge bg-white text-dark summary-badge">
                        {{ $data['epi']['persentase'] }}% dari Anggaran
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body py-4">
            <div class="row g-3 justify-content-center">
                @foreach($data['epi']['pendapatan'] as $key => $item)
                <div class="col-md-5">
                    <div class="pendapatan-card text-center">
                        <i class="{{ $item['icon'] }} pendapatan-icon text-{{ $item['color'] }}"></i>
                        <div class="pendapatan-label">{{ $item['label'] }}</div>
                        <div class="pendapatan-value text-{{ $item['color'] }}">
                            Rp {{ number_format($item['realisasi'], 0, ',', '.') }}
                        </div>
                        <div class="pendapatan-anggaran">
                            Anggaran: Rp {{ number_format($item['anggaran'], 0, ',', '.') }}
                        </div>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Pencapaian</small>
                                <small class="fw-bold text-{{ $item['color'] }}">{{ $item['persentase'] }}%</small>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-{{ $item['color'] }}"
                                    style="width: {{ min($item['persentase'], 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- =====================================================
         SECTION 5: BIMA - Berkah Industri Mesin Angkat
    ====================================================== -->
    <div class="card stat-card anper-card anper-bima mb-4">
        <div class="card-header anper-header-bima text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-tools"></i>
                        &nbsp;{{ $data['bima']['nama'] }}
                    </h5>
                    <small class="opacity-75"></small>
                </div>
                <div class="text-end">
                    <div style="font-size: 0.8rem; opacity: 0.8;">Total Realisasi</div>
                    <div style="font-size: 1.6rem; font-weight: 700;">
                        Rp {{ number_format($data['bima']['total_realisasi'], 0, ',', '.') }}
                    </div>
                    <span class="badge bg-white text-dark summary-badge">
                        {{ $data['bima']['persentase'] }}% dari Anggaran
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body py-4">
            <div class="row g-3">
                @foreach($data['bima']['pendapatan'] as $key => $item)
                <div class="col-md-4">
                    <div class="pendapatan-card text-center">
                        <i class="{{ $item['icon'] }} pendapatan-icon text-{{ $item['color'] }}"></i>
                        <div class="pendapatan-label">{{ $item['label'] }}</div>
                        <div class="pendapatan-value text-{{ $item['color'] }}">
                            Rp {{ number_format($item['realisasi'], 0, ',', '.') }}
                        </div>
                        <div class="pendapatan-anggaran">
                            Anggaran: Rp {{ number_format($item['anggaran'], 0, ',', '.') }}
                        </div>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Pencapaian</small>
                                <small class="fw-bold text-{{ $item['color'] }}">{{ $item['persentase'] }}%</small>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-{{ $item['color'] }}"
                                    style="width: {{ min($item['persentase'], 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @endif {{-- end of selectedPeriode != 'all' --}}

</div>{{-- /container-fluid --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

@if($selectedPeriode != 'all')
<script>
    const anperData = @json($data);
    const anperNames  = Object.values(anperData).map(a => a.singkatan);
    const anperTotals = Object.values(anperData).map(a => a.total_realisasi);
    const anperColors = Object.values(anperData).map(a => a.warna);

    // Bar Chart
    const barCtx = document.getElementById('anperBarChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: Object.values(anperData).map(a => a.singkatan),
            datasets: [{
                label: 'Realisasi (Rp)',
                data: anperTotals,
                backgroundColor: anperColors.map(c => c + 'cc'),
                borderColor: anperColors,
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Rp ' + ctx.parsed.x.toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => 'Rp ' + (v / 1000000000).toFixed(1) + 'M'
                    }
                }
            }
        }
    });

    // Pie Chart
    const pieCtx = document.getElementById('anperPieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: Object.values(anperData).map(a => a.singkatan),
            datasets: [{
                data: anperTotals,
                backgroundColor: anperColors.map(c => c + 'cc'),
                borderColor: anperColors,
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.label + ': Rp ' + ctx.parsed.toLocaleString('id-ID')
                    }
                }
            }
        }
    });
</script>
@endif

</body>
</html>
