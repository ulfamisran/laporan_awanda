@php
    $kota = trim((string) ($profil?->kota ?? ''));
    $prov = trim((string) ($profil?->provinsi ?? ''));
    $kotaProv = $kota !== '' && $prov !== ''
        ? $kota.'–'.$prov
        : ($kota !== '' ? $kota : ($prov !== '' ? $prov : '—'));
    $alamatTampil = trim((string) ($profil?->alamat ?? ''));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Limbah Harian</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 7px; color: #1a4a6b; margin: 4px; }
        .pdf-header { width: 100%; margin: 0 0 4px 0; padding: 0; text-align: center; font-size: 7.5pt; line-height: 1; color: #1a4a6b; }
        .pdf-header .hdr-row { width: 100%; margin: 0; padding: 0; text-align: center; line-height: 1; }
        /* Satu spasi vertikal antara logo dan baris teks pertama */
        .pdf-header .hdr-logo { margin: 0 0 0.5em 0; padding: 0; }
        .header-logo { max-height: 100px; max-width: 260px; width: auto; height: auto; object-fit: contain; display: block; margin: 0 auto; }
        .line-bgn { color: #b5e0ea; font-weight: bold; }
        .line-bgn .italic-en { font-style: italic; font-weight: bold; }
        .line-sppg { font-weight: bold; }
        .line-loc { font-weight: bold; }
        .line-alamat { font-weight: normal; font-size: 7.5pt; line-height: 1; }
        h1 { font-size: 7.5pt; margin: 0 0 3px 0; padding: 0; font-weight: bold; color: #1a4a6b; text-align: center; line-height: 1; }
        table.data { width: 100%; border-collapse: collapse; margin: 0; table-layout: fixed; }
        table.data th, table.data td { border: 1px solid #d4e8f4; padding: 2px 2px; text-align: center; vertical-align: middle; }
        table.data th { background: #e8f1f8; font-size: 7px; text-transform: uppercase; line-height: 1; padding: 3px 2px; }
        table.data th.col-tgl,
        table.data th.col-menu { font-size: 9px; }
        th.col-tgl { width: 6%; }
        th.col-menu { width: 10%; }
        td.tgl { font-size: 10px; white-space: nowrap; line-height: 1.1; }
        td.menu { font-size: 10px; word-wrap: break-word; line-height: 1.1; }
        td.kat-cell { font-size: 6px; line-height: 1; padding: 1px 1px; }
        td.kat-cell img { width: 100%; max-width: 100%; max-height: 130px; height: auto; object-fit: contain; display: block; margin: 0 auto; }
        td.kat-cell .jml { font-family: DejaVu Sans Mono, monospace; white-space: nowrap; margin: 0; padding: 0; line-height: 1; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="pdf-header">
        @if (! empty($logoDataUri))
            <div class="hdr-row hdr-logo">
                <img src="{{ $logoDataUri }}" alt="" class="header-logo">
            </div>
        @endif
        <div class="hdr-row line-bgn">
            BADAN GIZI NASIONAL (<span class="italic-en">NATIONAL NUTRITION AGENCY</span>)
        </div>
        <div class="hdr-row line-sppg">SATUAN PELAYANAN PEMENUHAN GIZI (SPPG)</div>
        <div class="hdr-row line-loc">{{ $kotaProv }}</div>
        <div class="hdr-row line-alamat">{{ $alamatTampil !== '' ? $profil?->alamat : '—' }}</div>
    </div>

    <h1>Laporan Limbah - Periode {{ $periodeAktif }}</h1>
    <table class="data">
        <thead>
            <tr>
                <th class="col-tgl">Tanggal</th>
                <th class="col-menu">Menu</th>
                @foreach ($kategoris as $k)
                    <th class="col-kat">{{ $k->nama_kategori }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($pdfRows as $r)
                <tr>
                    <td class="tgl">{{ $r->tanggal_fmt }}</td>
                    <td class="menu">{{ $r->menu !== null && $r->menu !== '' ? $r->menu : '—' }}</td>
                    @foreach ($r->cells as $cell)
                        <td class="kat-cell">
                            @if (! empty($cell->gambar_data_uri))
                                <img src="{{ $cell->gambar_data_uri }}" alt="">
                            @else
                                <span style="color:#94a3b8;">—</span>
                            @endif
                            <div class="jml">{{ $cell->jumlah_satuan }}</div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
