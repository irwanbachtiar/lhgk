<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis Kelelahan Pandu - LHGK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-size: 0.9rem;
        }
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 0.5rem 1rem;
        }
        .period-filter {
            background: white;
            padding: 10px 15px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        .day-timeline {
            position: relative;
            padding: 15px 0;
        }
        .hour-timeline {
            position: relative;
            padding-left: 35px;
            border-left: 3px solid #667eea;
            margin: 10px 0;
        }
        .hour-item {
            position: relative;
            padding: 10px 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 3px solid #667eea;
        }
        .hour-item:before {
            content: '';
            position: absolute;
            left: -41px;
            top: 15px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #667eea;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #667eea;
            z-index: 1;
        }
        .hour-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .count-badge {
            background: #10b981;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .day-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .fatigue-card {
            margin-bottom: 15px;
        }
        .card-body {
            padding: 1rem;
        }
        .stat-item {
            text-align: center;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .stat-item .label {
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 4px;
        }
        .stat-item .value {
            font-size: 1.2rem;
            font-weight: 600;
            color: #212529;
        }
        .info-row {
            display: flex;
            align-items: center;
            padding: 6px 10px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 6px;
            font-size: 0.85rem;
        }
        .info-row i {
            margin-right: 8px;
            color: #667eea;
        }
        .btn-sm {
            font-size: 0.8rem;
            padding: 4px 10px;
        }
        .badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
        .table-sm {
            font-size: 0.8rem;
        }
        .alert {
            padding: 10px 15px;
            font-size: 0.85rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-activity"></i> Analisis Kelelahan Pandu</span>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
                <a href="{{ route('monitoring.nota') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-file-earmark-text"></i> Monitoring Nota
                </a>
                <a href="{{ route('regional.revenue') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-geo-alt"></i> Pendapatan Wilayah
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="period-filter">
                    <form method="GET" action="{{ route('analisis.kelelahan') }}" class="row align-items-center">
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
                                <a href="{{ route('analisis.kelelahan') }}" class="btn btn-outline-secondary mt-4">
                                    <i class="bi bi-x-circle"></i> Reset Filter
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="row mb-4">
            <div class="col-12">
                @if($selectedPeriode == 'all' || $selectedBranch == 'all')
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                        <h5 class="mt-3">Pilih Cabang dan Periode untuk Menampilkan Data</h5>
                        <p class="mb-0">Silakan pilih <strong>Cabang</strong> dan <strong>Periode</strong> pada filter di atas untuk melihat analisis kelelahan pandu.</p>
                        <p class="mb-0 mt-2 text-muted"><small>Fitur ini dioptimalkan untuk performa yang lebih baik dengan memuat data sesuai kebutuhan.</small></p>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Tingkat Kelelahan:</strong>
                        <span class="badge bg-danger ms-2">Tinggi</span> > 4 jam/layanan |
                        <span class="badge bg-warning text-dark ms-2">Sedang</span> 2-4 jam/layanan |
                        <span class="badge bg-success ms-2">Rendah</span> < 2 jam/layanan
                    </div>
                @endif
            </div>
        </div>

        <!-- Pandu Cards -->
        <div class="row">
            @forelse($panduData as $pandu)
                <div class="col-lg-6 col-xl-4 mb-3">
                    <div class="card stat-card fatigue-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="card-title mb-0 fw-bold">
                                    <i class="bi bi-person-badge"></i> {{ $pandu->NM_PERS_PANDU }}
                                </h6>
                                <span class="badge bg-{{ $pandu->badge_class }}">
                                    {{ $pandu->tingkat_kelelahan }}
                                </span>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="label">Pelayanan</div>
                                        <div class="value">{{ $pandu->total_pelayanan }}</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="label">Jam Kerja</div>
                                        <div class="value">{{ $pandu->total_jam_kerja ?? '0' }}</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="label">Rata-rata</div>
                                        <div class="value">{{ $pandu->rata_rata_jam_per_layanan ?? '0' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="info-row">
                                <i class="bi bi-clock"></i>
                                <span class="badge bg-primary me-1">{{ $pandu->jam_mulai_pertama ?? '-' }}</span>
                                <i class="bi bi-arrow-right"></i>
                                <span class="badge bg-primary ms-1">{{ $pandu->jam_selesai_terakhir ?? '-' }}</span>
                            </div>

                            <div class="info-row">
                                <i class="bi bi-hourglass-split"></i>
                                <strong>Jam Tersibuk:</strong>
                                <span class="badge bg-success ms-2">
                                    {{ $pandu->jam_tersibuk ?? '-' }} ({{ $pandu->jumlah_pelayanan_jam_tersibuk ?? 0 }}x)
                                </span>
                            </div>

                            <div class="info-row">
                                <i class="bi bi-calendar-check"></i>
                                <strong>Hari Tersibuk:</strong>
                                <span class="badge bg-info ms-2">
                                    {{ $pandu->hari_tersibuk ?? '-' }} ({{ $pandu->jumlah_pelayanan_hari_tersibuk ?? 0 }}x)
                                </span>
                            </div>

                            <div class="d-flex gap-1 mt-2">
                                @if(isset($pandu->distribusi_harian) && count($pandu->distribusi_harian) > 0)
                                    <button class="btn btn-sm btn-outline-info flex-fill" type="button" data-bs-toggle="collapse" data-bs-target="#daily-dist-{{ $loop->index }}">
                                        <i class="bi bi-calendar3"></i> Harian ({{ count($pandu->distribusi_harian) }})
                                    </button>
                                @endif
                                @if(isset($pandu->distribusi_jam_per_hari) && count($pandu->distribusi_jam_per_hari) > 0)
                                    <button class="btn btn-sm btn-outline-primary flex-fill" type="button" data-bs-toggle="collapse" data-bs-target="#hourly-detail-{{ $loop->index }}">
                                        <i class="bi bi-clock-history"></i> Detail Jam ({{ count($pandu->distribusi_jam_per_hari) }})
                                    </button>
                                @endif
                            </div>

                            @if(isset($pandu->distribusi_harian) && count($pandu->distribusi_harian) > 0)
                                <div class="collapse mt-2" id="daily-dist-{{ $loop->index }}">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th class="text-center">Jumlah</th>
                                                    <th>Persentase</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pandu->distribusi_harian as $tanggal => $jumlah)
                                                    <tr>
                                                        <td>{{ $tanggal }}</td>
                                                        <td class="text-center">
                                                            <span class="badge bg-primary">{{ $jumlah }}</span>
                                                        </td>
                                                        <td>
                                                            <div class="progress" style="height: 16px;">
                                                                <div class="progress-bar" role="progressbar" 
                                                                     style="width: {{ ($jumlah / $pandu->total_pelayanan * 100) }}%">
                                                                    {{ number_format($jumlah / $pandu->total_pelayanan * 100, 1) }}%
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            @if(isset($pandu->distribusi_jam_per_hari) && count($pandu->distribusi_jam_per_hari) > 0)
                                <div class="collapse mt-2" id="hourly-detail-{{ $loop->index }}">
                                    @foreach($pandu->distribusi_jam_per_hari as $tanggal => $jamData)
                                        <div class="day-timeline">
                                            <div class="day-header">
                                                <i class="bi bi-calendar-event"></i> {{ $tanggal }}
                                                <span class="badge bg-light text-dark ms-2">{{ array_sum($jamData) }} pelayanan</span>
                                            </div>
                                            <div class="hour-timeline">
                                                @foreach($jamData as $jam => $jumlah)
                                                    <div class="hour-item">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="hour-badge">
                                                                    <i class="bi bi-clock"></i> {{ str_pad($jam, 2, '0', STR_PAD_LEFT) }}:00
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <span class="count-badge">
                                                                    <i class="bi bi-check-circle"></i> {{ $jumlah }} pelayanan
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                @if($selectedPeriode != 'all' && $selectedBranch != 'all')
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            <i class="bi bi-exclamation-triangle"></i> Tidak ada data untuk <strong>{{ $selectedBranch }}</strong> periode <strong>{{ $selectedPeriode }}</strong>
                        </div>
                    </div>
                @endif
            @endforelse
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
