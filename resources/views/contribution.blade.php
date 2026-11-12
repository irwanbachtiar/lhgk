<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ship Contribution Analytics - Dashboard LHGK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet" integrity="sha384-QuGBSgV5Im3DzL2z+8Ko9/hqNy/N0O7zwvXAtfd1MvPKWa/UbeLV65cfm4BV5Wgq" crossorigin="anonymous">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .filter-section, .content-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .search-result-item {
            cursor: pointer;
        }
        .ship-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .timeline {
            position: relative;
            padding-left: 28px;
            margin-top: 10px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 4px;
            bottom: 4px;
            width: 2px;
            background: #e2e2f0;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 16px;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -28px;
            top: 4px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #764ba2;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #764ba2;
        }
        .branch-section + .branch-section {
            margin-top: 24px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-ship"></i> Ship Contribution Analytics</span>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-graph-up-arrow"></i> Dashboard LHGK
                </a>
                <a href="{{ route('gps') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-geo-fill"></i> GPS
                </a>
                <a href="{{ route('summary.tunda') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-life-preserver"></i> Summary Tunda
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Search Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('contribution') }}" class="row align-items-end g-2">
                <div class="col-md-8">
                    <label class="form-label"><i class="bi bi-search"></i> Cari Nama Kapal:</label>
                    <input type="text" name="search" class="form-control" placeholder="Ketik nama kapal, misal: KIRANA VII"
                           value="{{ $search }}" autocomplete="off">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
                <div class="col-md-2">
                    @if($search !== '' || $selectedKapal !== '')
                        <a href="{{ route('contribution') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    @endif
                </div>
            </form>

            @if($search !== '')
                <div class="mt-3">
                    @if($searchResults->isEmpty())
                        <div class="alert alert-secondary mb-0">
                            <i class="bi bi-info-circle"></i> Tidak ditemukan kapal dengan nama mengandung "<strong>{{ $search }}</strong>".
                        </div>
                    @else
                        <label class="form-label text-muted">Ditemukan {{ $searchResults->count() }} kapal — pilih salah satu:</label>
                        <div class="list-group">
                            @foreach($searchResults as $kapal)
                                <a href="{{ route('contribution', ['kapal' => $kapal]) }}"
                                   class="list-group-item list-group-item-action search-result-item d-flex justify-content-between align-items-center {{ $selectedKapal == $kapal ? 'active' : '' }}">
                                    <span><i class="bi bi-ship me-2"></i>{{ $kapal }}</span>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>

        @if($selectedKapal === '')
            <div class="content-card text-center py-5">
                <i class="bi bi-ship" style="font-size: 3rem; color: #764ba2;"></i>
                <h5 class="mt-3">Cari dan pilih nama kapal untuk melihat kontribusinya</h5>
                <p class="text-muted mb-0">Analitik mencakup histori kunjungan, timeline per cabang, dan rekap kontribusi pendapatan.</p>
            </div>
        @else
            <!-- Ship Header + Periode Filter -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <h4 class="mb-1"><i class="bi bi-ship ship-badge rounded p-2 me-2"></i>{{ $selectedKapal }}</h4>
                        @if($shipInfo)
                            <div class="text-muted">
                                <span class="badge bg-light text-dark border me-1">{{ $shipInfo->JN_KAPAL ?? '-' }}</span>
                                @if(!empty($shipInfo->KD_BENDERA))
                                    <span class="badge bg-light text-dark border me-1">Bendera: {{ $shipInfo->KD_BENDERA }}</span>
                                @endif
                                @if(!empty($shipInfo->KP_GRT))
                                    <span class="badge bg-light text-dark border me-1">GRT: {{ number_format((float) $shipInfo->KP_GRT, 0, ',', '.') }}</span>
                                @endif
                                @if(!empty($shipInfo->KP_LOA))
                                    <span class="badge bg-light text-dark border me-1">LOA: {{ number_format((float) $shipInfo->KP_LOA, 0, ',', '.') }}</span>
                                @endif
                            </div>
                        @endif
                        <div class="mt-2">
                            @foreach($branchList as $branch)
                                <span class="badge bg-secondary me-1 mb-1" title="{{ $branch }}"><i class="bi bi-building"></i> {{ $branch }}</span>
                            @endforeach
                        </div>
                    </div>
                    <a href="{{ route('export.contribution', ['kapal' => $selectedKapal, 'periode' => $selectedPeriode]) }}"
                       class="btn btn-success btn-sm">
                        <i class="bi bi-file-earmark-excel"></i> Download Excel
                    </a>
                </div>

                <form method="GET" action="{{ route('contribution') }}" class="row align-items-end g-2 mt-3">
                    <input type="hidden" name="kapal" value="{{ $selectedKapal }}">
                    <div class="col-md-4">
                        <label class="form-label"><i class="bi bi-calendar-range"></i> Periode:</label>
                        <select name="periode" class="form-select">
                            <option value="all" {{ $selectedPeriode == 'all' ? 'selected' : '' }}>Semua Periode</option>
                            @foreach($periods as $period)
                                <option value="{{ $period }}" {{ $selectedPeriode == $period ? 'selected' : '' }}>{{ $period }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Terapkan</button>
                    </div>
                </form>
            </div>

            <!-- Rekap Kontribusi per Branch -->
            <div class="content-card">
                <h5 class="mb-3"><i class="bi bi-bar-chart-fill"></i> Rekap Kontribusi per Branch</h5>
                @if($branchContribution->isEmpty())
                    <div class="alert alert-secondary mb-0">
                        <i class="bi bi-info-circle"></i> Tidak ada data kontribusi untuk filter yang dipilih.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Branch</th>
                                    <th class="text-end">Jumlah Kunjungan</th>
                                    <th class="text-end">Jumlah Gerakan</th>
                                    <th class="text-end">Pendapatan Pandu</th>
                                    <th class="text-end">Pendapatan Tunda</th>
                                    <th class="text-end">Total Kontribusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branchContribution as $row)
                                <tr>
                                    <td>{{ $row->NM_BRANCH }}</td>
                                    <td class="text-end">{{ number_format($row->jumlah_kunjungan) }}</td>
                                    <td class="text-end">{{ number_format($row->jumlah_gerakan) }}</td>
                                    <td class="text-end">Rp {{ number_format((float) $row->total_pandu, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format((float) $row->total_tunda, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">Rp {{ number_format((float) $row->total_kontribusi, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td>Total</td>
                                    <td class="text-end">{{ number_format($totalContribution['jumlah_kunjungan']) }}</td>
                                    <td class="text-end">{{ number_format($totalContribution['jumlah_gerakan']) }}</td>
                                    <td class="text-end">Rp {{ number_format((float) $totalContribution['total_pandu'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format((float) $totalContribution['total_tunda'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format((float) $totalContribution['total_kontribusi'], 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Branch Visit Timeline -->
            <div class="content-card">
                <h5 class="mb-3"><i class="bi bi-clock-history"></i> Branch Visit Timeline</h5>
                @if($branchContribution->isEmpty())
                    <div class="alert alert-secondary mb-0">
                        <i class="bi bi-info-circle"></i> Belum ada kunjungan untuk ditampilkan.
                    </div>
                @else
                    @foreach($branchContribution as $branchRow)
                        @php $visits = $branchTimeline->get($branchRow->NM_BRANCH, collect()); @endphp
                        <div class="branch-section">
                            <h6 class="mb-0"><i class="bi bi-building"></i> {{ $branchRow->NM_BRANCH }}
                                <span class="text-muted small">({{ number_format($branchRow->jumlah_kunjungan) }} kunjungan)</span>
                            </h6>
                            <div class="timeline">
                                @foreach($visits->take(10) as $visit)
                                    @php
                                        $pandu = (float) ($visit->PENDAPATAN_PANDU ?? 0);
                                        $tunda = (float) ($visit->PENDAPATAN_TUNDA ?? 0);
                                    @endphp
                                    <div class="timeline-item">
                                        <div class="small text-muted">{{ $visit->TGL_TIBA ?? '-' }} {{ $visit->JAM_TIBA ?? '' }} &middot; Periode {{ $visit->PERIODE ?? '-' }} &middot; PPKB {{ $visit->PPKB_CODE ?? '-' }}</div>
                                        <div>
                                            Pandu: <strong>Rp {{ number_format($pandu, 0, ',', '.') }}</strong>
                                            &nbsp;|&nbsp; Tunda: <strong>Rp {{ number_format($tunda, 0, ',', '.') }}</strong>
                                            &nbsp;|&nbsp; Total: <strong>Rp {{ number_format($pandu + $tunda, 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($visits->count() > 10)
                                <div class="small text-muted mt-1">+{{ number_format($visits->count() - 10) }} kunjungan lainnya — lihat selengkapnya di Histori Kunjungan.</div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Histori Kunjungan -->
            <div class="content-card">
                <h5 class="mb-3"><i class="bi bi-table"></i> Histori Kunjungan</h5>
                @if($visitHistory && $visitHistory->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Tgl Tiba</th>
                                    <th>Jam Tiba</th>
                                    <th>Periode</th>
                                    <th>Branch</th>
                                    <th>PPKB Code</th>
                                    <th>No. UKK</th>
                                    <th class="text-end">Pendapatan Pandu</th>
                                    <th class="text-end">Pendapatan Tunda</th>
                                    <th class="text-end">Total</th>
                                    <th>Status Nota</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($visitHistory as $index => $row)
                                    @php
                                        $pandu = (float) ($row->PENDAPATAN_PANDU ?? 0);
                                        $tunda = (float) ($row->PENDAPATAN_TUNDA ?? 0);
                                    @endphp
                                    <tr>
                                        <td>{{ $visitHistory->firstItem() + $index }}</td>
                                        <td class="text-nowrap small">{{ $row->TGL_TIBA ?? '-' }}</td>
                                        <td class="text-nowrap small">{{ $row->JAM_TIBA ?? '-' }}</td>
                                        <td class="text-nowrap small">{{ $row->PERIODE ?? '-' }}</td>
                                        <td class="text-nowrap small">{{ $row->NM_BRANCH ?? '-' }}</td>
                                        <td class="text-nowrap small">{{ $row->PPKB_CODE ?? '-' }}</td>
                                        <td class="text-nowrap small">{{ $row->NO_UKK ?? '-' }}</td>
                                        <td class="text-end text-nowrap small">Rp {{ number_format($pandu, 0, ',', '.') }}</td>
                                        <td class="text-end text-nowrap small">Rp {{ number_format($tunda, 0, ',', '.') }}</td>
                                        <td class="text-end text-nowrap small fw-bold">Rp {{ number_format($pandu + $tunda, 0, ',', '.') }}</td>
                                        <td class="text-nowrap small">{{ $row->STATUS_NOTA ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($visitHistory->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan {{ $visitHistory->firstItem() }} - {{ $visitHistory->lastItem() }} dari {{ $visitHistory->total() }} kunjungan
                            </div>
                            <div>
                                {{ $visitHistory->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                @else
                    <div class="alert alert-secondary mb-0">
                        <i class="bi bi-info-circle"></i> Tidak ada histori kunjungan untuk filter yang dipilih.
                    </div>
                @endif
            </div>
        @endif
    </div>
</body>
</html>
