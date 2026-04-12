<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Barang Masuk</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a4a6b; margin: 14px; }
        h1 { font-size: 14px; margin: 0 0 6px 0; }
        .meta { margin: 0; color: #4a6b7f; font-size: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        th, td { border: 1px solid #d4e8f4; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #e8f1f8; font-size: 8px; text-transform: uppercase; }
        th.col-tgl { width: 11%; }
        th.col-kat { width: 18%; }
        th.col-nama { width: 28%; }
        th.col-jml { width: 15%; }
        th.col-gambar { width: 28%; }
        td.jml { font-family: DejaVu Sans Mono, monospace; font-size: 8px; text-align: right; }
        td.gambar-cell { text-align: center; }
        td.gambar-cell img { max-width: 100%; max-height: 95px; height: auto; object-fit: contain; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <h1>Laporan barang masuk</h1>
    <p class="meta">Diurutkan berdasarkan tanggal. {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th class="col-tgl">Tanggal</th>
                <th class="col-kat">Kategori barang</th>
                <th class="col-nama">Nama barang</th>
                <th class="col-jml">Jumlah</th>
                <th class="col-gambar">Gambar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pdfRows as $r)
                <tr>
                    <td>{{ $r->tanggal_fmt }}</td>
                    <td>{{ $r->kategori }}</td>
                    <td>{{ $r->nama_barang }}</td>
                    <td class="jml">{{ $r->jumlah_label }}</td>
                    <td class="gambar-cell">
                        @if (! empty($r->gambar_data_uri))
                            <img src="{{ $r->gambar_data_uri }}" alt="">
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
