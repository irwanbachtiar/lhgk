<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPS - Dashboard LHGK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet" integrity="sha384-QuGBSgV5Im3DzL2z+8Ko9/hqNy/N0O7zwvXAtfd1MvPKWa/UbeLV65cfm4BV5Wgq" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .filter-section {
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .map-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        #gpsMap {
            width: 100%;
            height: 650px;
            border-radius: 10px;
        }
        .gps-mobile-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #198754;
            color: #fff;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 1px 4px rgba(0,0,0,0.4);
            font-size: 14px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-geo-fill"></i> GPS Posisi Kapal</span>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-graph-up-arrow"></i> Dashboard LHGK
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('gps') }}" class="row align-items-center">
                <div class="col-md-2">
                    <label class="form-label"><i class="bi bi-funnel"></i> Filter:</label>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><i class="bi bi-building"></i> Cabang:</label>
                    <select name="cabang" class="form-select">
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
                <div class="col-md-4">
                    <label class="form-label"><i class="bi bi-calendar-range"></i> Periode:</label>
                    <div class="d-flex">
                        <select name="periode" class="form-select">
                            <option value="all" {{ $selectedPeriode == 'all' ? 'selected' : '' }}>Semua Periode</option>
                            @foreach($periods as $period)
                                <option value="{{ $period }}" {{ $selectedPeriode == $period ? 'selected' : '' }}>
                                    {{ $period }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary ms-2">Terapkan</button>
                    </div>
                </div>
                <div class="col-md-2">
                    @if($selectedPeriode != 'all' || $selectedBranch != 'all')
                        <a href="{{ route('gps') }}" class="btn btn-outline-secondary mt-4">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Map -->
        <div class="row">
            <div class="col-12">
                <div class="map-card">
                    @if($selectedPeriode == 'all' || $selectedBranch == 'all')
                        <div class="alert alert-info text-center mb-3">
                            <i class="bi bi-info-circle fs-1"></i>
                            <h5 class="mt-3">Silakan pilih Cabang dan Periode untuk menampilkan titik GPS</h5>
                        </div>
                    @elseif($pointsCount == 0)
                        <div class="alert alert-secondary text-center mb-3">
                            <i class="bi bi-geo-alt fs-1"></i>
                            <h5 class="mt-3">Tidak ada data GPS untuk filter yang dipilih</h5>
                        </div>
                    @else
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-geo-alt-fill"></i>
                            Menampilkan <strong>{{ number_format($pointsCount) }} titik GPS</strong>
                            untuk <strong>{{ $selectedBranch }}</strong> periode <strong>{{ $selectedPeriode }}</strong>
                        </div>
                    @endif
                    <div id="gpsMap"></div>
                </div>
            </div>
        </div>

        <!-- Data Grid Section -->
        @if($selectedPeriode != 'all' && $selectedBranch != 'all' && $gridCount > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="map-card">
                    @if(!$showGrid)
                    <div class="text-center py-4">
                        <i class="bi bi-table" style="font-size: 3rem; color: #764ba2;"></i>
                        <h5 class="mt-3">Data GPS</h5>
                        <p class="text-muted">
                            Ditemukan <strong style="color: #764ba2;">{{ number_format($gridCount) }} data</strong>
                            untuk <strong>{{ $selectedBranch }}</strong> periode <strong>{{ $selectedPeriode }}</strong>
                        </p>
                        <a href="{{ route('gps', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch, 'show_grid' => 1]) }}#gps-grid-section"
                           class="btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <i class="bi bi-eye"></i> Tampilkan Data Grid
                        </a>
                    </div>
                    @else
                    <div class="d-flex justify-content-between align-items-center mb-3" id="gps-grid-section">
                        <h5 class="mb-0">
                            <i class="bi bi-table"></i> Data GPS
                        </h5>
                        <div>
                            <a href="{{ route('export.gps', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch]) }}"
                               class="btn btn-success btn-sm me-2">
                                <i class="bi bi-file-earmark-excel"></i> Download Excel
                            </a>
                            <a href="{{ route('gps', ['periode' => $selectedPeriode, 'cabang' => $selectedBranch]) }}"
                               class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle"></i> Sembunyikan
                            </a>
                        </div>
                    </div>

                    @if($gridData && $gridData->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>PPKB Code</th>
                                    <th>Periode</th>
                                    <th>No. UKK</th>
                                    <th>No. Bukti Pandu</th>
                                    <th>Tgl Jam Tiba</th>
                                    <th>Nama Kapal</th>
                                    <th>Jenis Kapal</th>
                                    <th>Nama Pandu</th>
                                    <th>Mulai Pelaksanaan</th>
                                    <th>Selesai Pelaksanaan</th>
                                    <th>Gerakan</th>
                                    <th>Realisasi Via</th>
                                    <th>Command On</th>
                                    <th>Command Off</th>
                                    <th>Departure</th>
                                    <th>Onboard</th>
                                    <th>Pilot Off</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gridData as $index => $row)
                                <tr>
                                    <td>{{ $gridData->firstItem() + $index }}</td>
                                    <td class="text-nowrap small">{{ $row->PPKB_CODE ?? '-' }}</td>
                                    <td class="text-nowrap small">{{ $row->PERIODE ?? '-' }}</td>
                                    <td class="text-nowrap small">{{ $row->NO_UKK ?? '-' }}</td>
                                    <td class="text-nowrap small">{{ $row->NO_BKT_PANDU ?? '-' }}</td>
                                    <td class="text-nowrap small">{{ $row->TGL_JAM_TIBA ?? '-' }}</td>
                                    <td class="text-nowrap small"><strong title="{{ $row->NM_KAPAL }}">{{ Str::limit($row->NM_KAPAL, 30) }}</strong></td>
                                    <td class="text-nowrap small">{{ $row->JN_KAPAL ?? '-' }}</td>
                                    <td class="text-nowrap small">{{ $row->NM_PERS_PANDU ?? '-' }}</td>
                                    <td class="text-nowrap small">{{ $row->MULAI_PELAKSANAAN ?? '-' }}</td>
                                    <td class="text-nowrap small">{{ $row->SELESAI_PELAKSANAAN ?? '-' }}</td>
                                    <td class="text-nowrap small">{{ $row->GERAKAN ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ strtoupper($row->REALISAS_PILOT_VIA ?? '-') }}</span>
                                    </td>
                                    <td class="text-nowrap small">{{ $row->COMMANDON ?? '-' }}</td>
                                    <td class="text-nowrap small">{{ $row->COMMANDOFF ?? '-' }}</td>
                                    <td class="text-nowrap small">{{ $row->DEPARTURE ?? '-' }}</td>
                                    <td class="text-nowrap small">{{ $row->ONBOARD ?? '-' }}</td>
                                    <td class="text-nowrap small">{{ $row->PILOTOFF ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($gridData->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Menampilkan {{ $gridData->firstItem() }} - {{ $gridData->lastItem() }} dari {{ $gridData->total() }} data
                        </div>
                        <div>
                            {{ $gridData->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                    @endif
                    @else
                    <div class="alert alert-secondary">
                        <i class="bi bi-info-circle"></i> Tidak ada data untuk ditampilkan
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    <script>
        const gpsPoints = @json($points);

        const map = L.map('gpsMap').setView([-2.5, 118], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Ukuran icon mobile mengikuti level zoom peta: makin zoom out, makin kecil.
        function getMobileIconSize(zoom) {
            const size = Math.round(26 * Math.pow(1.25, zoom - 12));
            return Math.max(10, Math.min(36, size));
        }

        function createMobileIcon(zoom) {
            const size = getMobileIconSize(zoom);
            const fontSize = Math.round(size * 0.55);
            return L.divIcon({
                html: '<div class="gps-mobile-icon" style="width:' + size + 'px;height:' + size + 'px;">' +
                      '<i class="bi bi-phone-fill" style="font-size:' + fontSize + 'px;"></i></div>',
                className: '',
                iconSize: [size, size],
                iconAnchor: [size / 2, size / 2],
                popupAnchor: [0, -size / 2]
            });
        }

        if (gpsPoints.length > 0) {
            const markers = [];
            const mobileMarkers = [];
            gpsPoints.forEach(function (p) {
                const isMobile = (p.realisasi_via || '').toUpperCase() === 'MOBILE';
                const marker = L.marker([p.lat, p.lng], isMobile ? { icon: createMobileIcon(map.getZoom()) } : {}).addTo(map);
                if (isMobile) {
                    mobileMarkers.push(marker);
                }
                marker.bindPopup(
                    '<strong>' + (p.nm_kapal || '-') + '</strong><br>' +
                    'PPKB Code: ' + (p.ppkb_code || '-') + '<br>' +
                    'Mulai Pelaksanaan: ' + (p.mulai_pelaksanaan || '-') + '<br>' +
                    'Pandu Dari - Ke: ' + (p.pandu_dari || '-') + ' &rarr; ' + (p.pandu_ke || '-') + '<br>' +
                    'Realisasi Via: ' + (p.realisasi_via || '-') + '<br>' +
                    'Koordinat: ' + p.lat + ', ' + p.lng
                );
                markers.push(marker);
            });

            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds(), { maxZoom: 12, padding: [30, 30] });

            map.on('zoomend', function () {
                const icon = createMobileIcon(map.getZoom());
                mobileMarkers.forEach(function (m) {
                    m.setIcon(icon);
                });
            });
        }
    </script>
</body>
</html>
