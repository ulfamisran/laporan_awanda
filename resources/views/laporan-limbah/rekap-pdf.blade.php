<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Limbah</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a4a6b; margin: 16px; }
        h1 { font-size: 13px; margin: 0 0 4px 0; }
        .sub { color: #4a6b7f; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d4e8f4; padding: 4px 5px; }
        th { background: #e8f1f8; text-align: left; font-size: 8px; }
        td.num { text-align: right; font-family: DejaVu Sans Mono, monospace; }
        .pie-wrap { margin-top: 12px; }
    </style>
</head>
<body>
    <h1>Rekapitulasi limbah</h1>
    <p class="sub">Periode {{ \Illuminate\Support\Carbon::parse($dari)->format('d/m/Y') }} — {{ \Illuminate\Support\Carbon::parse($sampai)->format('d/m/Y') }}</p>

    <p style="font-weight:bold;margin-top:8px;">Ringkasan penanganan (kg estimasi)</p>
    <table>
        <thead>
            <tr><th>Jenis</th><th class="num">Volume (kg est.)</th></tr>
        </thead>
        <tbody>
            @foreach ($pie as $p)
                <tr>
                    <td>{{ $p['label'] }}</td>
                    <td class="num">{{ number_format($p['value'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="font-weight:bold;margin-top:14px;">Per kategori</p>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th class="num">Volume</th>
                <th class="num">Dibuang</th>
                <th class="num">Daur</th>
                <th class="num">Dijual</th>
                <th class="num">Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                <tr>
                    <td>{{ $r->nama_kategori }}</td>
                    <td class="num">{{ number_format($r->total_volume_kg, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($r->vol_dibuang, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($r->vol_didaur_ulang, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($r->vol_dijual, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($r->pendapatan, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
