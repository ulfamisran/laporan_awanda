<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Mutasi Stok</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a4a6b; }
        h1 { font-size: 14px; margin: 0 0 4px 0; }
        .meta { margin-bottom: 12px; color: #4a6b7f; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d4e8f4; padding: 5px 6px; text-align: left; }
        th { background: #f0f6fb; font-weight: bold; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Rekapitulasi mutasi stok</h1>
    <div class="meta">
        Dapur: {{ $namaDapur ?? '—' }}<br>
        Dicetak: {{ $tanggalCetak }}
    </div>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama barang</th>
                <th>Kategori</th>
                <th>Satuan</th>
                <th class="right">Awal</th>
                <th class="right">Masuk</th>
                <th class="right">Keluar</th>
                <th class="right">Stok</th>
                <th class="right">Min</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                @php
                    $awal = (float) ($row->jumlah_awal ?? 0);
                    $masuk = (float) ($row->jumlah_masuk ?? 0);
                    $keluar = (float) ($row->jumlah_keluar ?? 0);
                    $stok = $awal + $masuk - $keluar;
                    $min = (float) $row->stok_minimum;
                    $status = $stok < $min ? 'Di bawah minimum' : 'Aman';
                @endphp
                <tr>
                    <td>{{ $row->kode_barang }}</td>
                    <td>{{ $row->nama_barang }}</td>
                    <td>{{ $row->kategoriBarang?->nama_kategori }}</td>
                    <td>{{ $row->satuan?->label() }}</td>
                    <td class="right">{{ number_format($awal, 2, ',', '.') }}</td>
                    <td class="right">{{ number_format($masuk, 2, ',', '.') }}</td>
                    <td class="right">{{ number_format($keluar, 2, ',', '.') }}</td>
                    <td class="right">{{ number_format($stok, 2, ',', '.') }}</td>
                    <td class="right">{{ number_format($min, 2, ',', '.') }}</td>
                    <td>{{ $status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
