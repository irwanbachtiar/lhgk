<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendapatan SAP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card { border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .filter-section { background: white; padding: 15px; border-radius: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .table-container { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-cash-stack"></i> Pendapatan SAP</span>
            <div>
                <a href="{{ route('monitoring.nota') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-file-earmark-text"></i> Monitoring Nota
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-graph-up-arrow"></i> Dashboard LHGK
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        @if(session('missingTable') || isset($missingTable) && $missingTable)
            <div class="alert alert-danger">
                <strong>Kesalahan:</strong> Tabel <code>pendapatan_sap</code> tidak ditemukan pada koneksi <code>dashboard_phinnisi</code>.
                @if(session('missingTableError'))
                    <div class="mt-2"><small class="text-muted">{{ session('missingTableError') }}</small></div>
                @elseif(isset($missingTableError))
                    <div class="mt-2"><small class="text-muted">{{ $missingTableError }}</small></div>
                @endif
            </div>
        @endif
        <div class="filter-section">
            <form method="GET" action="{{ route('pendapatan.sap') }}" class="row align-items-center">
                <div class="col-md-2">
                    <label class="form-label"><i class="bi bi-funnel"></i> Filter:</label>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><i class="bi bi-building"></i> Cabang:</label>
                    <select name="cabang" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ $selectedCabang == 'all' ? 'selected' : '' }}>Semua Cabang</option>
                        @foreach($allBranches as $branch)
                            <option value="{{ $branch }}" {{ $selectedCabang == $branch ? 'selected' : '' }}>{{ Str::limit($branch, 80) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><i class="bi bi-calendar-range"></i> Periode:</label>
                    <select name="periode" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ $selectedPeriode == 'all' ? 'selected' : '' }}>Semua Periode</option>
                        @foreach($periods as $p)
                            <option value="{{ $p }}" {{ $selectedPeriode == $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    @if($selectedPeriode != 'all' || $selectedCabang != 'all')
                        <a href="{{ route('pendapatan.sap') }}" class="btn btn-outline-secondary mt-4"><i class="bi bi-x-circle"></i> Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="row mb-4">
            @if($selectedPeriode == 'all' && $selectedCabang == 'all')
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle fs-1"></i>
                        <h5 class="mt-3">Silakan pilih filter Cabang atau Periode untuk melihat data Pendapatan SAP</h5>
                    </div>
                </div>
            @else
                <div class="col-md-6">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-primary">{{ number_format($totalNota) }}</h3>
                            <p class="mb-0"><i class="bi bi-file-earmark-text"></i> Total Nota (distinct No Faktur)</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="text-success">Rp {{ number_format($pendapatanPandu, 0, ',', '.') }}</h3>
                            <p class="mb-0"><i class="bi bi-cash-coin"></i> Pendapatan Pandu (GL 4010200000 / 4110200000)</p>
                            <small class="text-muted">Mengabaikan baris dengan cust_no kosong</small>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="table-container">
            <h5><i class="bi bi-info-circle"></i> Catatan</h5>
            <p class="text-muted">Halaman ini mengambil data dari koneksi <strong>dashboard_phinnisi</strong> tabel <strong>pendapatan_sap</strong>. Filter Cabang mengacu pada tabel <strong>profit_center</strong>. Jika struktur tabel berbeda, beberapa nilai mungkin kosong.</p>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>