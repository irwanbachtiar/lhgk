<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Nota (Printable)</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111827; }
        .container { width: 100%; padding: 12px; }
        .header { text-align: center; margin-bottom: 12px; }
        .cards { display: flex; gap: 8px; margin-bottom: 12px; }
        .card { flex: 1; padding: 10px; border: 1px solid #e5e7eb; border-radius: 6px; }
        .h3 { font-size: 20px; margin: 0 0 6px 0; font-weight: 800; }
        .muted { color: #6b7280; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; font-size: 12px; }
        th { background: #f3f4f6; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Monitoring Nota</h2>
            <div class="muted">Periode: {{ $selectedPeriode }} &middot; Cabang: {{ $selectedBranch }}</div>
        </div>

        <div class="cards">
            <div class="card">
                <div class="h3">{{ number_format($totalNota) }}</div>
                <div class="muted">Total Nota</div>
            </div>
            <div class="card">
                <div class="h3">Rp {{ number_format($totalPendapatanPandu,0,',','.') }}</div>
                <div class="muted">Pendapatan Pandu</div>
            </div>
            <div class="card">
                <div class="h3">Rp {{ number_format($totalPendapatanTunda,0,',','.') }}</div>
                <div class="muted">Pendapatan Tunda</div>
            </div>
            <div class="card">
                <div class="h3">Rp {{ number_format($totalPendapatanPandu + $totalPendapatanTunda,0,',','.') }}</div>
                <div class="muted">Total Pendapatan</div>
            </div>
        </div>

        @if($revenuePerPandu && $revenuePerPandu->count() > 0)
            <h4>Pendapatan Per Pandu</h4>
            <table>
                <thead>
                    <tr><th>No</th><th>Pilot</th><th class="text-right">Total Pendapatan</th><th class="text-right">Transaksi</th></tr>
                </thead>
                <tbody>
                @foreach($revenuePerPandu as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $p->PILOT }}</td>
                        <td class="text-right">Rp {{ number_format($p->total_revenue,0,',','.') }}</td>
                        <td class="text-right">{{ $p->total_transaksi }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        @if($revenuePerTunda && $revenuePerTunda->count() > 0)
            <h4 style="margin-top:14px;">Pendapatan Per Tunda</h4>
            <table>
                <thead>
                    <tr><th>No</th><th>Tunda</th><th class="text-right">Total Pendapatan</th><th class="text-right">Transaksi</th></tr>
                </thead>
                <tbody>
                @foreach($revenuePerTunda as $i => $t)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $t->tunda_name ?? 'N/A' }}</td>
                        <td class="text-right">Rp {{ number_format($t->total_revenue,0,',','.') }}</td>
                        <td class="text-right">{{ $t->total_transaksi }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        @if($topShippingAgents && $topShippingAgents->count() > 0)
            <h4 style="margin-top:14px;">Top Shipping Agents</h4>
            <table>
                <thead>
                    <tr><th>No</th><th>Agent</th><th class="text-right">Total Pendapatan</th></tr>
                </thead>
                <tbody>
                @foreach($topShippingAgents as $i => $a)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $a->SHIPPING_AGENT }}</td>
                        <td class="text-right">Rp {{ number_format($a->total_revenue,0,',','.') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

    </div>
</body>
</html>
