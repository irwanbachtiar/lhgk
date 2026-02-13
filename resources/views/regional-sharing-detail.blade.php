<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Branch - Regional Sharing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
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
</body>
</html>
