<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Branch - Regional Sharing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet" integrity="sha384-QuGBSgV5Im3DzL2z+8Ko9/hqNy/N0O7zwvXAtfd1MvPKWa/UbeLV65cfm4BV5Wgq" crossorigin="anonymous">
    <style>
        /* Global Loading Animation */
        .global-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
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
            width: 60px;
            height: 60px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        .global-loading-text {
            color: #333;
            font-weight: 500;
            font-size: 16px;
            margin: 0;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Global Loading Overlay -->
    <div id="globalLoading" class="global-loading-overlay">
        <div class="global-loading-content">
            <div class="global-loading-spinner"></div>
            <p class="global-loading-text" id="loadingText">Memproses data...</p>
        </div>
    </div>
    <div class="container mt-4">
        <a href="{{ route('regional.sharing', ['periode' => $selectedPeriode ?? 'all']) }}" class="btn btn-sm btn-secondary mb-3">← Kembali</a>
        <h2>Detail Branch: {{ $wilayah }}</h2>
        <p class="text-muted">Periode: {{ $selectedPeriode ?? 'all' }}</p>

        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Pandu Umum</th>
                        <th>Pandu TUKS</th>
                        <th>Tunda Umum</th>
                        <th>Tunda TUKS</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($wilayah) && $wilayah === 'all' && isset($groupedBranchData))
                        @forelse($groupedBranchData as $region => $rows)
                            <tr class="table-secondary"><td colspan="6"><strong>{{ $region }}</strong></td></tr>
                            @foreach($rows as $b)
                                <tr>
                                    <td>{{ $b->branch }}</td>
                                    <td>{{ number_format($b->pandu_umum ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ number_format($b->pandu_tuks ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ number_format($b->tunda_umum ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ number_format($b->tunda_tuks ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ number_format($b->total ?? (($b->pandu_umum ?? 0) + ($b->pandu_tuks ?? 0) + ($b->tunda_umum ?? 0) + ($b->tunda_tuks ?? 0)), 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="6">Tidak ada data.</td></tr>
                        @endforelse
                    @else
                        @forelse($branchData as $b)
                            <tr>
                                <td>{{ $b->branch }}</td>
                                <td>{{ number_format($b->pandu_umum ?? 0, 0, ',', '.') }}</td>
                                <td>{{ number_format($b->pandu_tuks ?? 0, 0, ',', '.') }}</td>
                                <td>{{ number_format($b->tunda_umum ?? 0, 0, ',', '.') }}</td>
                                <td>{{ number_format($b->tunda_tuks ?? 0, 0, ',', '.') }}</td>
                                <td>{{ number_format($b->total ?? (($b->pandu_umum ?? 0) + ($b->pandu_tuks ?? 0) + ($b->tunda_umum ?? 0) + ($b->tunda_tuks ?? 0)), 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">Tidak ada data untuk wilayah ini.</td></tr>
                        @endforelse
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        // Global Loading Functions
        function showGlobalLoading(message = 'Memproses data...') {
            const overlay = document.getElementById('globalLoading');
            const text = document.getElementById('loadingText');
            if (overlay) {
                if (text) text.textContent = message;
                overlay.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
        
        function hideGlobalLoading() {
            const overlay = document.getElementById('globalLoading');
            if (overlay) {
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
        
        // Auto-show loading for page navigation
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a:not([href^="#"]):not([href^="javascript:"])');
            links.forEach(link => {
                link.addEventListener('click', function() {
                    showGlobalLoading('Memuat halaman...');
                });
            });
        });
    </script>
</body>
</html>
