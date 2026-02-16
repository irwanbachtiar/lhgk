<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trafik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>body{background:#f8f9fa}</style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-graph-up-arrow"></i> Trafik</span>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-2"><i class="bi bi-house"></i> Dashboard</a>
                <a href="{{ route('dashboard.operasional') }}" class="btn btn-light btn-sm me-2">Operasional</a>
                <a href="{{ route('monitoring.nota') }}" class="btn btn-light btn-sm">Monitoring Nota</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <div class="period-filter" style="background:white;padding:15px;border-radius:15px;box-shadow:0 2px 4px rgba(0,0,0,0.08);">
                    <form id="trafikFilterForm" method="GET" action="{{ route('trafik') }}" class="row align-items-center">
                        <div class="col-md-2">
                            <label class="form-label"><i class="bi bi-funnel"></i> Filter:</label>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><i class="bi bi-building"></i> Cabang:</label>
                            <select name="cabang" class="form-select" onchange="this.form.submit()">
                                <option value="all" {{ ($selectedBranch ?? 'all') == 'all' ? 'selected' : '' }}>Semua Cabang</option>
                                @foreach($regionalGroups as $group => $branches)
                                    @if(count($branches) > 0)
                                        <optgroup label="{{ $group }}">
                                            @foreach($branches as $b)
                                                <option value="{{ $b }}" {{ ($selectedBranch ?? '') == $b ? 'selected' : '' }} title="{{ $b }}">{{ Str::limit($b, 50) }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach

                                @if(!empty($otherBranches))
                                    <optgroup label="Lainnya">
                                        @foreach($otherBranches as $b)
                                            <option value="{{ $b }}" {{ ($selectedBranch ?? '') == $b ? 'selected' : '' }} title="{{ $b }}">{{ Str::limit($b, 50) }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><i class="bi bi-calendar-range"></i> Periode:</label>
                            <select name="periode" class="form-select" onchange="this.form.submit()">
                                <option value="all" {{ ($selectedPeriode ?? 'all') == 'all' ? 'selected' : '' }}>Semua Periode</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period }}" {{ ($selectedPeriode ?? '') == $period ? 'selected' : '' }}>{{ $period }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex justify-content-end align-items-center">
                            <div>
                                @if(($selectedPeriode ?? 'all') != 'all' || ($selectedBranch ?? 'all') != 'all')
                                    <a href="{{ route('trafik') }}" class="btn btn-outline-secondary me-2">
                                        <i class="bi bi-x-circle"></i> Reset
                                    </a>
                                @endif
                            </div>
                            <div class="btn-toolbar" role="toolbar" aria-label="Trafik actions">
                                <div class="btn-group" role="group" aria-label="Export and refresh">
                                    <button type="button" id="btnDownloadPdf" class="btn btn-sm btn-outline-primary" title="Download current page as PDF">
                                        <i class="bi bi-download"></i> Download
                                    </button>
                                    <button type="button" id="btnRefresh" class="btn btn-sm btn-outline-secondary" title="Refresh">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                                <div class="btn-group ms-2" role="group">
                                    <button type="button" id="btnApplyFilters" class="btn btn-sm btn-primary">
                                        <i class="bi bi-funnel-fill"></i> Apply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @if(($selectedPeriode ?? 'all') == 'all' || ($selectedBranch ?? 'all') == 'all')
        <div class="alert alert-info">Pilih periode dan cabang untuk menampilkan data trafik.</div>
    @else
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Data Trafik — Periode: {{ $selectedPeriode }} · Cabang: {{ $selectedBranch }}</h5>

                @if($rows->isEmpty())
                    <p class="text-muted">Tidak ada data untuk filter yang dipilih.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    @foreach(array_keys((array)$rows->first()) as $col)
                                        <th>{{ $col }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $r)
                                    <tr>
                                        @foreach((array)$r as $v)
                                            <td>{{ $v }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
    </div>
</body>
</html>
