<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.35; }
        .wrap { width: 100%; }
        .page-break { page-break-before: always; }
        .pdf-header { width: 100%; margin: 0 0 10px 0; padding: 0 0 8px 0; text-align: center; font-size: 8.5pt; line-height: 1.2; color: #1a4a6b; border-bottom: 1.5px solid #1a4a6b; }
        .pdf-header .hdr-row { width: 100%; margin: 0; padding: 0; text-align: center; line-height: 1.2; }
        .pdf-header .hdr-logo { margin: 0 0 6px 0; padding: 0; }
        .header-logo { max-height: 84px; max-width: 220px; width: auto; height: auto; object-fit: contain; display: block; margin: 0 auto; }
        .line-bgn { color: #1a4a6b; font-weight: bold; }
        .line-bgn .italic-en { font-style: italic; font-weight: bold; }
        .line-sppg { font-weight: bold; }
        .line-loc { font-weight: bold; }
        .line-alamat { font-weight: normal; font-size: 8pt; line-height: 1.2; }
        .title { text-align: center; margin: 12px 0 14px 0; }
        .title h1 { margin: 0; font-size: 14px; text-decoration: underline; text-transform: uppercase; letter-spacing: 0.3px; }
        .title h2 { margin: 0; font-size: 13px; text-decoration: underline; text-transform: uppercase; letter-spacing: 0.2px; }
        .meta { margin-bottom: 12px; }
        .meta table { border-collapse: collapse; }
        .meta td { border: none; padding: 1px 0; vertical-align: top; font-size: 11px; }
        .meta .lbl { width: 72px; }
        .meta .sep { width: 12px; }
        .tujuan { margin: 14px 0 10px 0; }
        .tujuan p { margin: 0; }
        .body-text { margin: 8px 0; text-align: justify; }
        table.items { width: 100%; border-collapse: collapse; margin: 10px 0 12px 0; }
        table.items th, table.items td { border: 1px solid #374151; padding: 5px; font-size: 9px; }
        table.items th { background: #b6d7f0; text-align: center; font-weight: bold; }
        .center { text-align: center; }
        .right { text-align: right; }
        .left { text-align: left; }
        .batas { margin: 8px 0; }
        .sign-wrap { width: 100%; margin-top: 28px; }
        .sign-table { width: 100%; border-collapse: collapse; }
        .sign-table td { border: none; width: 50%; vertical-align: top; padding: 0; font-size: 11px; line-height: 1.35; }
        .sign-left { text-align: center; }
        .sign-right { text-align: center; }
        .sign-table .sign-date { padding-bottom: 2px; }
        .sign-table .sign-role { padding-top: 0; }
        .sign-space { height: 56px; }
        .lampiran-meta { margin-bottom: 10px; }
        .lampiran-meta p { margin: 2px 0; }
    </style>
</head>

<body>
    @php
        $namaDapur = trim((string) ($profil?->nama_dapur ?? $order->profilMbg?->nama_dapur ?? ''));
        $kota = trim((string) ($profil?->kota ?? $profil?->tempat_pelaporan ?? ''));
        $namaKotaSppg = trim((string) ($profil?->kota ?? ''));
        $namaAkuntansi = trim((string) ($profil?->nama_akuntansi ?? ''));
        $namaKaSppg = trim((string) ($profil?->penanggung_jawab ?? ''));
        $tanggalSurat = $order->tanggal_order;
    @endphp

    {{-- Halaman 1: Surat SPM --}}
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
            <div class="hdr-row line-loc">{{ trim((string) ($profil?->daerah_sppg ?? '')) !== '' ? $profil->daerah_sppg : '—' }}</div>
            <div class="hdr-row line-alamat">{{ trim((string) ($profil?->alamat ?? '')) !== '' ? $profil->alamat : '—' }}</div>
        </div>

        <div class="title">
            <h1>Surat Permohonan Pembayaran</h1>
        </div>

        <div class="meta">
            <table>
                <tr>
                    <td class="lbl">Nomor</td>
                    <td class="sep">:</td>
                    <td>{{ $nomorSpm }}</td>
                </tr>
                <tr>
                    <td class="lbl">Lampiran</td>
                    <td class="sep">:</td>
                    <td>1 lembar</td>
                </tr>
                <tr>
                    <td class="lbl">Perihal</td>
                    <td class="sep">:</td>
                    <td>Permohonan Pembayaran Tagihan</td>
                </tr>
            </table>
        </div>

        <div class="tujuan">
            <p>Kepada Yth.</p>
            <p>Mitra SPPG {{ $namaKotaSppg !== '' ? $namaKotaSppg : '—' }}</p>
            <p>Di Tempat</p>
        </div>

        <p class="body-text">Dengan hormat,</p>
        <p class="body-text">
            Bersama ini kami sampaikan permohonan pembayaran atas kewajiban yang timbul dari transaksi operasional dapur {{ $namaDapur !== '' ? $namaDapur : 'SPPG' }} sesuai rincian sebagai berikut:
        </p>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 14%;">Nomor Faktur</th>
                    <th style="width: 16%;">Nama Supplier</th>
                    <th style="width: 14%;">Nama Bank</th>
                    <th style="width: 14%;">Nomor Rekening</th>
                    <th style="width: 18%;">Atas Nama Rekening</th>
                    <th style="width: 19%;">Jumlah Pembayaran</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rekeningSupplier as $idx => $rek)
                    <tr>
                        <td class="center">{{ $idx + 1 }}</td>
                        <td class="center">{{ $rek['nomor_nota'] ?: '-' }}</td>
                        <td class="left">{{ $rek['supplier_nama'] ?: '-' }}</td>
                        <td class="left">{{ $rek['nama_bank'] !== '' ? $rek['nama_bank'] : '-' }}</td>
                        <td class="center">{{ $rek['nomor_rekening'] !== '' ? $rek['nomor_rekening'] : '-' }}</td>
                        <td class="left">{{ $rek['atas_nama_rekening'] !== '' ? $rek['atas_nama_rekening'] : ($rek['supplier_nama'] ?: '-') }}</td>
                        <td class="right">Rp. {{ number_format((float) $rek['subtotal'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="6" class="right"><strong>Total</strong></td>
                    <td class="right"><strong>Rp. {{ number_format((float) $grandTotal, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <p class="batas"><strong>Batas waktu pembayaran</strong> : {{ $tanggalSurat?->translatedFormat('d F Y') ?? '-' }}</p>

        <p class="body-text">
            Demikian surat permohonan ini kami sampaikan. Dokumen pendukung terlampir dan telah diverifikasi. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.
        </p>

        <div class="sign-wrap">
            <table class="sign-table">
                <tr>
                    <td class="sign-left sign-date">&nbsp;</td>
                    <td class="sign-right sign-date">{{ $kota !== '' ? $kota : '—' }}, {{ $tanggalSurat?->translatedFormat('d F Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="sign-left">Dibuat oleh,</td>
                    <td class="sign-right">Mengetahui,</td>
                </tr>
                <tr>
                    <td class="sign-left sign-role">Akuntan</td>
                    <td class="sign-right sign-role">Ka SPPG</td>
                </tr>
                <tr>
                    <td class="sign-left"><div class="sign-space"></div></td>
                    <td class="sign-right"><div class="sign-space"></div></td>
                </tr>
                <tr>
                    <td class="sign-left"><strong><u>{{ $namaAkuntansi !== '' ? $namaAkuntansi : '................................' }}</u></strong></td>
                    <td class="sign-right"><strong><u>{{ $namaKaSppg !== '' ? $namaKaSppg : '................................' }}</u></strong></td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Halaman 2: Lampiran daftar barang order --}}
    <div class="wrap page-break">
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
            <div class="hdr-row line-loc">{{ trim((string) ($profil?->daerah_sppg ?? '')) !== '' ? $profil->daerah_sppg : '—' }}</div>
            <div class="hdr-row line-alamat">{{ trim((string) ($profil?->alamat ?? '')) !== '' ? $profil->alamat : '—' }}</div>
        </div>

        <div class="title">
            <h2>Lampiran Rincian Barang Order</h2>
        </div>

        <div class="lampiran-meta">
            <p><strong>Nomor SPM</strong> : {{ $nomorSpm }}</p>
            <p><strong>Nomor Order</strong> : {{ $order->nomor_order }}</p>
            <p><strong>Tanggal Order</strong> : {{ $tanggalSurat?->translatedFormat('d F Y') ?? '-' }}</p>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 28%;">Nama Barang</th>
                    <th style="width: 14%;">Qty</th>
                    <th style="width: 14%;">Harga</th>
                    <th style="width: 14%;">Sub Total</th>
                    <th style="width: 15%;">Supplier</th>
                    <th style="width: 10%;">No. Faktur</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $idx => $item)
                    @php($total = (float) $item->jumlah_barang * (float) $item->harga_barang)
                    <tr>
                        <td class="center">{{ $idx + 1 }}</td>
                        <td class="left">{{ $item->nama_barang }}</td>
                        <td class="center">{{ rtrim(rtrim(number_format((float) $item->jumlah_barang, 2, ',', '.'), '0'), ',') }} {{ $item->satuan_barang }}</td>
                        <td class="right">Rp. {{ number_format((float) $item->harga_barang, 0, ',', '.') }}</td>
                        <td class="right">Rp. {{ number_format($total, 0, ',', '.') }}</td>
                        <td class="left">{{ $item->supplier_nama ?? $item->supplier?->nama_supplier ?? '-' }}</td>
                        <td class="center">{{ $item->nomor_nota ?: ($order->nomor_order ?: '-') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4" class="right"><strong>Total</strong></td>
                    <td class="right"><strong>Rp. {{ number_format((float) $grandTotal, 0, ',', '.') }}</strong></td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
