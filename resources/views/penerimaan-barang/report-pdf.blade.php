<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penerimaan Barang</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 7px; color: #1a4a6b; margin: 4px; }
        .pdf-header { width: 100%; margin: 0 0 4px 0; padding: 0; text-align: center; font-size: 7.5pt; line-height: 1; color: #1a4a6b; }
        .pdf-header .hdr-row { width: 100%; margin: 0; padding: 0; text-align: center; line-height: 1; }
        .pdf-header .hdr-logo { margin: 0 0 0.5em 0; padding: 0; }
        .header-logo { max-height: 84px; max-width: 220px; width: auto; height: auto; object-fit: contain; display: block; margin: 0 auto; }
        .line-bgn { color: #b5e0ea; font-weight: bold; }
        .line-bgn .italic-en { font-style: italic; font-weight: bold; }
        .line-sppg { font-weight: bold; }
        .line-loc { font-weight: bold; }
        .line-alamat { font-weight: normal; font-size: 7.5pt; line-height: 1; }
        h1 { font-size: 7.5pt; margin: 0 0 3px 0; padding: 0; font-weight: bold; color: #1a4a6b; text-align: center; line-height: 1; }
        table.data { width: 100%; border-collapse: collapse; margin: 0; table-layout: auto; }
        table.data th, table.data td { border: 1px solid #d4e8f4; padding: 2px 2px; text-align: center; vertical-align: middle; }
        table.data th { background: #e8f1f8; font-size: 7px; text-transform: uppercase; line-height: 1; padding: 3px 2px; }
        th.col-no, td.col-no { white-space: nowrap; padding-left: 0; padding-right: 0; font-size: 6px; }
        th.col-date, td.col-date { white-space: nowrap; font-size: 6.5px; padding-left: 1px; padding-right: 1px; }
        td.txt { text-align: left; font-size: 8px; line-height: 1.1; }
        td.qty { white-space: nowrap; font-family: DejaVu Sans Mono, monospace; font-size: 8px; }
        th.col-gambar, td.gambar-cell { width: 260px; min-width: 260px; }
        td.gambar-cell img { width: 100%; max-width: 100%; max-height: 140px; height: auto; object-fit: cover; display: block; margin: 0 auto; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    @php
        $kota = trim((string) ($profil?->kota ?? ''));
        $prov = trim((string) ($profil?->provinsi ?? ''));
        $kotaProv = $kota !== '' && $prov !== '' ? $kota.'–'.$prov : ($kota !== '' ? $kota : ($prov !== '' ? $prov : '—'));
        $alamatTampil = trim((string) ($profil?->alamat ?? ''));
    @endphp
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

    <h1>Laporan Penerimaan Barang - Periode {{ $periodeAktif }}</h1>
    <table class="data">
        <thead>
            <tr>
                <th class="col-no" style="width:14px;">No</th>
                <th style="width:88px;">No Order</th>
                <th class="col-date" style="width:52px;">Tgl Order</th>
                <th class="col-date" style="width:52px;">Tgl Terima</th>
                <th style="width:110px;">Barang</th>
                <th style="width:72px;">Qty Order</th>
                <th style="width:72px;">Qty Diterima</th>
                <th style="width:70px;">Kondisi</th>
                <th style="width:78px;">Supplier</th>
                <th class="col-gambar" style="width:260px;">Gambar</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $idx => $r)
                <tr>
                    <td class="col-no" style="width:14px;">{{ $idx + 1 }}</td>
                    <td>{{ $r->nomor_order }}</td>
                    <td class="col-date" style="width:52px;">{{ str_replace(' ', '', $r->tanggal_order) }}</td>
                    <td class="col-date" style="width:52px;">{{ str_replace(' ', '', $r->tanggal_terima) }}</td>
                    <td class="txt">{{ $r->barang }}</td>
                    <td class="qty">{{ $r->qty_order }}</td>
                    <td class="qty">{{ $r->qty_diterima }}</td>
                    <td class="txt">{{ $r->kondisi }}</td>
                    <td class="txt">{{ $r->supplier }}</td>
                    <td class="gambar-cell" style="width:260px;">
                        @if (! empty($r->gambar_data_uri))
                            <img src="{{ $r->gambar_data_uri }}" alt="">
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="padding:8px;">Belum ada data order/penerimaan barang pada periode aktif.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
