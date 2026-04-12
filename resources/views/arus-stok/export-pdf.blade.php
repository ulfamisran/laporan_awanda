<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Arus Stok — {{ $barang->kode_barang }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a4a6b; }
        h1 { font-size: 13px; margin: 0 0 4px 0; }
        .meta { margin-bottom: 10px; color: #4a6b7f; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d4e8f4; padding: 4px 5px; text-align: left; }
        th { background: #f0f6fb; font-weight: bold; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Arus stok</h1>
    <div class="meta">
        Barang: {{ $barang->kode_barang }} — {{ $barang->nama_barang }}<br>
        Dapur: {{ $namaDapur ?? '—' }}<br>
        Periode: {{ $dari->format('d/m/Y') }} – {{ $sampai->format('d/m/Y') }}
    </div>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th class="right">Jumlah</th>
                <th class="right">Saldo</th>
                <th>Keterangan</th>
                <th>Oleh</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                @php
                    $arah = (int) ($r['arah'] ?? 0);
                    $jml = (float) ($r['jumlah'] ?? 0);
                    $qtyStr = '—';
                    if ($arah > 0) {
                        $qtyStr = '+'.number_format($jml, 2, ',', '.');
                    } elseif ($arah < 0) {
                        $qtyStr = '−'.number_format($jml, 2, ',', '.');
                    }
                @endphp
                <tr>
                    <td>{{ $r['tanggal_label'] ?? '—' }}</td>
                    <td>{{ $r['label'] ?? '—' }}</td>
                    <td class="right">{{ $qtyStr }}</td>
                    <td class="right">{{ isset($r['saldo']) ? number_format((float) $r['saldo'], 2, ',', '.') : '—' }}</td>
                    <td>{{ $r['keterangan'] ?? '' }}</td>
                    <td>{{ $r['oleh'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
