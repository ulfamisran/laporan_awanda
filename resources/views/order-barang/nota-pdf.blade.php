<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        .wrap { width: 100%; }
        .pdf-header { width: 100%; margin: 0 0 6px 0; padding: 0; text-align: center; font-size: 8.5pt; line-height: 1.2; color: #1a4a6b; }
        .pdf-header .hdr-row { width: 100%; margin: 0; padding: 0; text-align: center; line-height: 1.2; }
        .pdf-header .hdr-logo { margin: 0 0 6px 0; padding: 0; }
        .header-logo { max-height: 84px; max-width: 220px; width: auto; height: auto; object-fit: contain; display: block; margin: 0 auto; }
        .line-bgn { color: #1a4a6b; font-weight: bold; }
        .line-bgn .italic-en { font-style: italic; font-weight: bold; }
        .line-sppg { font-weight: bold; }
        .line-loc { font-weight: bold; }
        .line-alamat { font-weight: normal; font-size: 8pt; line-height: 1.2; }
        .top { text-align: center; margin-bottom: 8px; }
        .top h1 { margin: 0; font-size: 14px; letter-spacing: 0.2px; text-transform: uppercase; }
        .top p { margin: 2px 0; font-size: 11px; }
        .meta { margin-top: 8px; margin-bottom: 8px; }
        .meta p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #374151; padding: 6px; font-size: 10px; }
        th { background: #b6d7f0; text-align: center; }
        .right { text-align: right; }
        .center { text-align: center; }
        .sign { margin-top: 28px; width: 100%; text-align: right; }
        .sign p { margin: 3px 0; }
    </style>
</head>

<body>
    <div class="wrap">
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
            <div class="hdr-row line-loc">
                {{ trim((string) ($profil?->kota ?? '')) !== '' || trim((string) ($profil?->provinsi ?? '')) !== ''
                    ? trim((string) ($profil?->kota ?? '')) . (trim((string) ($profil?->kota ?? '')) !== '' && trim((string) ($profil?->provinsi ?? '')) !== '' ? ' - ' : '') . trim((string) ($profil?->provinsi ?? ''))
                    : '-' }}
            </div>
            <div class="hdr-row line-alamat">{{ trim((string) ($profil?->alamat ?? '')) !== '' ? $profil->alamat : '—' }}</div>
        </div>

        <div class="top">
            <h1>Nota Pemesanan Operasional</h1>
            <p>No. {{ $order->nomor_order }}</p>
        </div>

        <div class="meta">
            <p><strong>Dari</strong> : {{ $order->profilMbg?->nama_dapur ?? '-' }}</p>
            <p><strong>Waktu</strong> : {{ $order->tanggal_order?->translatedFormat('l, d F Y') }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Uraian Jenis Bahan Makanan</th>
                    <th>Kuantitas</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Supplier</th>
                    <th>Pemakaian</th>
                </tr>
            </thead>
            <tbody>
                @php($grandTotal = 0)
                @foreach ($order->items as $idx => $item)
                    @php($total = (float) $item->jumlah_barang * (float) $item->harga_barang)
                    @php($grandTotal += $total)
                    <tr>
                        <td class="center">{{ $idx + 1 }}</td>
                        <td>{{ $item->nama_barang }}</td>
                        <td class="center">{{ rtrim(rtrim(number_format((float) $item->jumlah_barang, 2, ',', '.'), '0'), ',') }} {{ $item->satuan_barang }}</td>
                        <td class="right">Rp{{ number_format((float) $item->harga_barang, 0, ',', '.') }}</td>
                        <td class="right">Rp{{ number_format($total, 0, ',', '.') }}</td>
                        <td>{{ $item->supplier?->nama_supplier ?? '-' }}</td>
                        <td class="center">{{ $item->jumlah_hari_pemakaian }} hari</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4" class="right"><strong>Total</strong></td>
                    <td class="right"><strong>Rp{{ number_format($grandTotal, 0, ',', '.') }}</strong></td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>

        <div class="sign">
            <p>{{ $order->profilMbg?->kabupaten ?? 'Sidrap' }}, {{ $order->tanggal_order?->translatedFormat('d F Y') }}</p>
            <p>Kepala Satuan Pelayanan Pemenuhan Gizi</p>
            <br><br><br>
            <p><strong>{{ $order->profilMbg?->nama_pimpinan ?? '-' }}</strong></p>
        </div>
    </div>
</body>

</html>
