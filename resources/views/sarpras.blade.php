<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sarpras - Dashboard LHGK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet" integrity="sha384-QuGBSgV5Im3DzL2z+8Ko9/hqNy/N0O7zwvXAtfd1MvPKWa/UbeLV65cfm4BV5Wgq" crossorigin="anonymous">
    <style>
        body { background-color: #f8f9fa; }
        :root { --card-radius: 12px; --accent-indigo: #667eea; }
        .stat-card { border-radius: var(--card-radius); box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-gear-fill"></i> Sarpras</span>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="{{ route('dashboard.operasional') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-kanban-fill"></i> Operasional
                </a>
                <a href="{{ route('monitoring.nota') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-file-earmark-text"></i> Monitoring Nota
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card stat-card p-4">
                    <h4>Halaman Sarpras</h4>
                    <p class="text-muted">Halaman ini menggunakan style yang sama seperti halaman lain di dashboard. Tambahkan konten sarpras di sini.</p>

                    <div class="mt-3">
                        <form method="GET" action="{{ route('sarpras') }}" class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label">Cabang</label>
                                <select name="cabang" class="form-select" disabled>
                                    <option>Filter non-aktif</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Periode</label>
                                <select name="periode" class="form-select" disabled>
                                    <option>Filter non-aktif</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" class="btn btn-secondary" disabled>Filter dinonaktifkan</button>
                            </div>
                        </form>
                    </div>

                    <hr>
                    <div>
                        @if(!empty($mstError))
                            <div class="alert alert-danger">{{ $mstError }}</div>
                        @else
                            @if(isset($onlyPanduEndorsement) && $onlyPanduEndorsement)
                                <div class="alert alert-success">Memeriksa kolom <strong>MASA BERLAKU ENDORSERMENT PANDU</strong>: <strong>{{ $preferredDateCol }}</strong></div>
                            @endif
                            @if(isset($mstRows) && $mstRows->count() > 0)
                                <div class="table-responsive mt-3">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                                <tr>
                                                    <th>Nama</th>
                                                    <th>Cabang</th>
                                                    <th>Tanggal Expired</th>
                                                    <th>Days Remaining</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($mstRows as $row)
                                                <tr>
                                                    <td>{{ $row->name }}</td>
                                                    <td>{{ $row->branch ?? '-' }}</td>
                                                    <td>{{ $row->next_expiry ?? '-' }}</td>
                                                    <td>
                                                        @if(is_null($row->days_remaining))
                                                            -
                                                        @elseif($row->days_remaining < 0)
                                                            <span class="text-danger">Expired {{ abs($row->days_remaining) }} hari lalu</span>
                                                        @else
                                                            {{ $row->days_remaining }} hari
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">Tidak ada data mst_pandu yang akan expired dalam 3 bulan ke depan.</div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
