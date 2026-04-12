<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Neraca Keuangan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a4a6b; }
        h1 { font-size: 14px; margin: 0 0 8px 0; }
        .meta { margin-bottom: 14px; color: #4a6b7f; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #d4e8f4; padding: 5px 6px; text-align: left; }
        th { background: #f0f6fb; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Neraca keuangan</h1>
    <div class="meta">
        Dapur: {{ $namaDapur ?? '—' }}<br>
        Periode: {{ ($data['mulai'] ?? \Illuminate\Support\Carbon::createFromDate($tahun, $bulan, 1))->translatedFormat('F Y') }}<br>
        Dicetak: {{ $dicetak }}
    </div>
    <table>
        <tr><th>Saldo awal periode</th><td class="right">{{ number_format((float) ($data['saldo_awal'] ?? 0), 0, ',', '.') }}</td></tr>
        <tr><th>Total masuk</th><td class="right">{{ number_format((float) ($data['total_masuk_periode'] ?? 0), 0, ',', '.') }}</td></tr>
        <tr><th>Total keluar</th><td class="right">{{ number_format((float) ($data['total_keluar_periode'] ?? 0), 0, ',', '.') }}</td></tr>
        <tr><th>Saldo akhir</th><td class="right"><strong>{{ number_format((float) ($data['saldo_akhir'] ?? 0), 0, ',', '.') }}</strong></td></tr>
    </table>
    <h2 style="font-size:11px;">Rincian masuk (per jenis dana)</h2>
    <table>
        <thead><tr><th>Akun</th><th class="right">Jumlah</th></tr></thead>
        <tbody>
            @foreach ($data['masuk_per_jenis_dana'] ?? [] as $row)
                <tr><td>{{ $row['nama'] }}</td><td class="right">{{ number_format((float) $row['total'], 0, ',', '.') }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <h2 style="font-size:11px;">Rincian keluar (per jenis dana)</h2>
    <table>
        <thead><tr><th>Akun</th><th class="right">Jumlah</th></tr></thead>
        <tbody>
            @foreach ($data['keluar_per_jenis_dana'] ?? [] as $row)
                <tr><td>{{ $row['nama'] }}</td><td class="right">{{ number_format((float) $row['total'], 0, ',', '.') }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
