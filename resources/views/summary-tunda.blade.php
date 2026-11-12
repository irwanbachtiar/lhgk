<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary Pendapatan Per Tunda - Dashboard LHGK</title>
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
        .regional-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 16px;
        }
        .branch-table-wrap + .branch-table-wrap {
            margin-top: 28px;
        }
        table.tunda-pivot th, table.tunda-pivot td {
            white-space: nowrap;
        }
        table.tunda-pivot td.nama-tunda, table.tunda-pivot th.nama-tunda {
            white-space: normal;
            min-width: 180px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-life-preserver"></i> Summary Pendapatan Per Tunda</span>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-graph-up-arrow"></i> Dashboard LHGK
                </a>
                <a href="{{ route('gps') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-geo-fill"></i> GPS
                </a>
                <a href="{{ route('contribution') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-ship"></i> Ship Contribution
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('summary.tunda') }}" class="row align-items-end g-2">
                <div class="col-md-3">
                    <label class="form-label"><i class="bi bi-calendar-range"></i> Tahun:</label>
                    <select name="tahun" class="form-select">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ $selectedTahun == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><i class="bi bi-geo-alt"></i> Regional:</label>
                    <select name="regional" class="form-select">
                        <option value="all" {{ $selectedRegional == 'all' ? 'selected' : '' }}>Semua Regional</option>
                        @foreach($regionalKeys as $regional)
                            <option value="{{ $regional }}" {{ $selectedRegional == $regional ? 'selected' : '' }}>{{ $regional }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Terapkan</button>
                </div>
            </form>
        </div>

        @if(empty($regionalData))
            <div class="content-card text-center py-5">
                <i class="bi bi-info-circle" style="font-size: 2.5rem; color: #764ba2;"></i>
                <h5 class="mt-3">Tidak ada data pendapatan tunda untuk tahun {{ $selectedTahun }}@if($selectedRegional != 'all') / {{ $selectedRegional }} @endif</h5>
            </div>
        @else
            @foreach($regionalData as $regional => $branchTables)
                @php
                    $isHidden = !in_array($regional, $shownWilayah);
                    $shownAfterShow = array_values(array_unique(array_merge($shownWilayah, [$regional])));
                    $shownAfterHide = array_values(array_diff($shownWilayah, [$regional]));
                    $anchorId = 'wilayah-' . Str::slug($regional);
                @endphp
                <div class="regional-header d-flex justify-content-between align-items-center flex-wrap gap-2" id="{{ $anchorId }}">
                    <h4 class="mb-0"><i class="bi bi-geo-alt-fill"></i> {{ $regional }}
                        <span class="small fw-normal">({{ count($branchTables) }} branch)</span>
                    </h4>
                    <div>
                        @if($isHidden)
                            <a href="{{ route('summary.tunda', array_merge(request()->except('shown'), ['shown' => $shownAfterShow])) }}#{{ $anchorId }}"
                               class="btn btn-light btn-sm">
                                <i class="bi bi-eye"></i> Tampilkan
                            </a>
                        @else
                            <a href="{{ route('export.summary.tunda', ['tahun' => $selectedTahun, 'wilayah' => $regional]) }}"
                               class="btn btn-success btn-sm me-2">
                                <i class="bi bi-file-earmark-excel"></i> Download Excel
                            </a>
                            <a href="{{ route('summary.tunda', array_merge(request()->except('shown'), ['shown' => $shownAfterHide])) }}#{{ $anchorId }}"
                               class="btn btn-light btn-sm">
                                <i class="bi bi-eye-slash"></i> Sembunyikan
                            </a>
                        @endif
                    </div>
                </div>

                @if($isHidden)
                    <div class="content-card text-center py-3 text-muted mb-4">
                        <i class="bi bi-eye-slash"></i> Data {{ $regional }} disembunyikan ({{ count($branchTables) }} branch).
                    </div>
                @else
                    @foreach($branchTables as $branchTable)
                        <div class="content-card branch-table-wrap">
                            <h5 class="mb-3"><i class="bi bi-building"></i> {{ $branchTable['branch'] }}</h5>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-sm tunda-pivot">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="nama-tunda">Nama Tunda</th>
                                            @foreach($periodeColumns as $periode)
                                                <th class="text-end">{{ $periode }}</th>
                                            @endforeach
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($branchTable['rows'] as $row)
                                            <tr>
                                                <td class="nama-tunda">{{ $row['nama_tunda'] }}</td>
                                                @foreach($periodeColumns as $periode)
                                                    <td class="text-end small">
                                                        {{ $row['cells'][$periode] > 0 ? number_format($row['cells'][$periode], 0, ',', '.') : '-' }}
                                                    </td>
                                                @endforeach
                                                <td class="text-end fw-bold">{{ number_format($row['total'], 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr class="fw-bold">
                                            <td class="nama-tunda">Total Pendapatan / Bulan</td>
                                            @foreach($periodeColumns as $periode)
                                                <td class="text-end">{{ number_format($branchTable['monthly_total'][$periode], 0, ',', '.') }}</td>
                                            @endforeach
                                            <td class="text-end">{{ number_format($branchTable['grand_total'], 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach
        @endif
    </div>
</body>
</html>
