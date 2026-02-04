<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Nota Data</title>
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
        .upload-section {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .badge-status {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-file-earmark-text"></i> Monitoring Nota Data</span>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-graph-up-arrow"></i> Dashboard LHGK
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
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('monitoring.nota') }}" class="row align-items-center">
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
                        <a href="{{ route('monitoring.nota') }}" class="btn btn-outline-secondary mt-4">
                            <i class="bi bi-x-circle"></i> Reset Filter
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            @if($selectedPeriode == 'all' && $selectedBranch == 'all')
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle fs-1"></i>
                        <h5 class="mt-3">Silakan pilih filter Cabang atau Periode untuk melihat data</h5>
                        <p class="mb-0">Pilih salah satu atau kedua filter di atas untuk menampilkan statistik dan grafik pendapatan.</p>
                    </div>
                </div>
            @else
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-primary">{{ number_format($totalNota) }}</h3>
                            <p class="mb-0"><i class="bi bi-file-earmark-text"></i> Total Nota</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-success">Rp {{ number_format($totalPendapatanPandu, 0, ',', '.') }}</h3>
                            <p class="mb-0"><i class="bi bi-cash-coin"></i> Pendapatan Pandu</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-info">Rp {{ number_format($totalPendapatanTunda, 0, ',', '.') }}</h3>
                            <p class="mb-0"><i class="bi bi-cash-stack"></i> Pendapatan Tunda</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-warning">Rp {{ number_format($totalPendapatanPandu + $totalPendapatanTunda, 0, ',', '.') }}</h3>
                            <p class="mb-0"><i class="bi bi-currency-dollar"></i> Total Pendapatan</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if($selectedPeriode != 'all' || $selectedBranch != 'all')
        <!-- Nota Batal Section -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card border-danger">
                    <div class="card-body text-center">
                        <h3 class="text-danger">{{ number_format($totalNotaBatal) }}</h3>
                        <p class="mb-0"><i class="bi bi-x-circle"></i> Jumlah Nota Batal</p>
                        <small class="text-muted">Billing dengan prefix "HIS"</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card border-danger">
                    <div class="card-body text-center">
                        <h3 class="text-danger">Rp {{ number_format($totalPendapatanPanduBatal, 0, ',', '.') }}</h3>
                        <p class="mb-0"><i class="bi bi-cash-coin"></i> Nilai Nota Batal Pandu</p>
                        @if($totalNotaBatal > 0)
                            <small class="text-muted">{{ number_format(($totalPendapatanPanduBatal / ($totalPendapatanPandu + $totalPendapatanPanduBatal)) * 100, 2) }}% dari total</small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card border-danger">
                    <div class="card-body text-center">
                        <h3 class="text-danger">Rp {{ number_format($totalPendapatanTundaBatal, 0, ',', '.') }}</h3>
                        <p class="mb-0"><i class="bi bi-cash-stack"></i> Nilai Nota Batal Tunda</p>
                        @if($totalNotaBatal > 0)
                            <small class="text-muted">{{ number_format(($totalPendapatanTundaBatal / ($totalPendapatanTunda + $totalPendapatanTundaBatal)) * 100, 2) }}% dari total</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($selectedPeriode != 'all' || $selectedBranch != 'all')
        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card stat-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Grafik Pendapatan Per Pandu</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="panduChart" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card stat-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Grafik Pendapatan Per Tunda</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="tundaChart" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Per Pandu Statistics -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card stat-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person-badge"></i> Pendapatan Per Pandu</h5>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        @if($revenuePerPandu->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($revenuePerPandu as $index => $pandu)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-primary rounded-circle me-2">{{ $index + 1 }}</span>
                                            <strong>{{ $pandu->PILOT }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $pandu->total_transaksi }} transaksi</small>
                                        </div>
                                        <div class="text-end">
                                            <strong class="text-success">Rp {{ number_format($pandu->total_revenue, 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">Tidak ada data pandu</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card stat-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-water"></i> Pendapatan Per Tunda</h5>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        @if($revenuePerTunda->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($revenuePerTunda as $index => $tunda)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-info rounded-circle me-2">{{ $index + 1 }}</span>
                                            <strong>{{ $tunda->tunda_name ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $tunda->total_transaksi }} transaksi</small>
                                        </div>
                                        <div class="text-end">
                                            <strong class="text-success">Rp {{ number_format($tunda->total_revenue, 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">Tidak ada data tunda</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top 10 Shipping Agents -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                        <h5 class="mb-0"><i class="bi bi-building"></i> Top 10 Shipping Agent (Total Pendapatan)</h5>
                    </div>
                    <div class="card-body">
                        @if($topShippingAgents->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="80">Rank</th>
                                            <th>Shipping Agent</th>
                                            <th class="text-end">Total Pendapatan (Pandu + Tunda)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topShippingAgents as $index => $agent)
                                            <tr>
                                                <td>
                                                    <span class="badge {{ $index < 3 ? 'bg-warning' : 'bg-secondary' }} rounded-circle" style="width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem;">
                                                        {{ $index + 1 }}
                                                    </span>
                                                </td>
                                                <td><strong>{{ $agent->SHIPPING_AGENT }}</strong></td>
                                                <td class="text-end">
                                                    <strong class="text-success">Rp {{ number_format($agent->total_revenue, 0, ',', '.') }}</strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">Tidak ada data shipping agent</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Upload CSV Section -->
        <div class="upload-section">
            <h5 class="mb-3"><i class="bi bi-upload"></i> Upload Data CSV</h5>
            
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Error!</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('import_errors'))
                <div class="alert alert-warning">
                    <strong>Beberapa baris gagal diimport:</strong>
                    <ul class="mb-0" style="max-height: 200px; overflow-y: auto;">
                        @foreach(session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-3"><i class="bi bi-file-earmark-text"></i> Upload Data Pandu (pandu_prod)</h6>
                    <form method="POST" action="{{ route('upload.nota.csv') }}" enctype="multipart/form-data" class="mb-3" id="uploadPanduForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required id="csvFilePandu">
                                <small class="text-muted">Format: CSV (max 10MB)</small>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100" id="uploadPanduBtn">
                                    <i class="bi bi-upload"></i> Upload Pandu
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-md-6">
                    <h6 class="mb-3"><i class="bi bi-water"></i> Upload Data Tunda (tunda_prod)</h6>
                    <form method="POST" action="{{ route('upload.tunda.csv') }}" enctype="multipart/form-data" class="mb-3" id="uploadTundaForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <input type="file" name="tunda_csv_file" class="form-control" accept=".csv,.txt" required id="csvFileTunda">
                                <small class="text-muted">Format: CSV (max 10MB)</small>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-info w-100" id="uploadTundaBtn">
                                    <i class="bi bi-upload"></i> Upload Tunda
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Data Table - DISABLED -->
        <!--
        <div class="table-container">
            <h5 class="mb-3"><i class="bi bi-table"></i> Data Nota</h5>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Billing</th>
                            <th>Tgl Billing</th>
                            <th>Invoice</th>
                            <th>Nama Kapal</th>
                            <th>Pilot</th>
                            <th>Cabang</th>
                            <th>GRT</th>
                            <th>Pend. Pandu</th>
                            <th>Pend. Tunda</th>
                            <th>Total</th>
                            <th>Pilot Onboard</th>
                            <th>Pilot Off</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="13" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">Tabel data dinonaktifkan.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle pandu form submission with loading state
        document.getElementById('uploadPanduForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('csvFilePandu');
            const uploadBtn = document.getElementById('uploadPanduBtn');
            
            if (fileInput.files.length === 0) {
                e.preventDefault();
                alert('Silakan pilih file CSV terlebih dahulu');
                return false;
            }
            
            // Show loading state
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
        });

        // Handle tunda form submission with loading state
        document.getElementById('uploadTundaForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('csvFileTunda');
            const uploadBtn = document.getElementById('uploadTundaBtn');
            
            if (fileInput.files.length === 0) {
                e.preventDefault();
                alert('Silakan pilih file CSV terlebih dahulu');
                return false;
            }
            
            // Show loading state
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
        });

        // Pandu Chart
        const panduData = @json($revenuePerPandu);
        const panduLabels = panduData.map(item => item.PILOT || 'N/A');
        const panduRevenue = panduData.map(item => item.total_revenue);

        const panduCtx = document.getElementById('panduChart').getContext('2d');
        const panduChart = new Chart(panduCtx, {
            type: 'line',
            data: {
                labels: panduLabels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: panduRevenue,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
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
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });

        // Tunda Chart
        const tundaData = @json($revenuePerTunda);
        const tundaLabels = tundaData.map(item => item.tunda_name || 'N/A');
        const tundaRevenue = tundaData.map(item => item.total_revenue);

        const tundaCtx = document.getElementById('tundaChart').getContext('2d');
        const tundaChart = new Chart(tundaCtx, {
            type: 'line',
            data: {
                labels: tundaLabels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: tundaRevenue,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
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
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
