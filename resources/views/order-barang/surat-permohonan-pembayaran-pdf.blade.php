<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.35; }
        .wrap { width: 100%; }
        .page-break { page-break-after: always; }
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
        .meta { margin-bottom: 12px; }
        .meta table { border-collapse: collapse; }
        .meta td { border: none; padding: 1px 0; vertical-align: top; font-size: 11px; }
        .meta .lbl { width: 72px; }
        .meta .sep { width: 12px; }
        .tujuan { margin: 14px 0 10px 0; }
        .tujuan p { margin: 0; }
        .body-text { margin: 8px 0; text-align: justify; }
        table.items { width: 100%; border-collapse: collapse; margin: 10px 0 12px 0; }
        table.items th, table.items td { border: 1px solid #374151; padding: 6px; font-size: 10px; }
        table.items th { background: #b6d7f0; text-align: center; font-weight: bold; }
        .center { text-align: center; }
        .right { text-align: right; }
        .rekening { margin: 10px 0 8px 0; }
        .rekening p { margin: 2px 0; }
        .rekening .indent { margin-left: 18px; }
        .sign-wrap { width: 100%; margin-top: 28px; }
        .sign-table { width: 100%; border-collapse: collapse; }
        .sign-table td { border: none; width: 50%; vertical-align: top; padding: 0; }
        .sign-left { text-align: left; }
        .sign-right { text-align: right; }
        .sign-block { display: inline-block; text-align: center; min-width: 180px; }
        .sign-block p { margin: 2px 0; }
        .sign-space { height: 56px; }
    </style>
</head>

<body>
    @foreach ($supplierGroups as $group)
        @php
            $supplier = $group['supplier'] ?? null;
            $namaDapur = trim((string) ($profil?->nama_dapur ?? $order->profilMbg?->nama_dapur ?? ''));
            $kota = trim((string) ($profil?->kota ?? $profil?->tempat_pelaporan ?? ''));
            $namaKotaSppg = trim((string) ($profil?->kota ?? ''));
            $namaAkuntansi = trim((string) ($profil?->nama_akuntansi ?? ''));
            $namaKaSppg = trim((string) ($profil?->penanggung_jawab ?? ''));
            $tanggalSurat = $order->tanggal_order;
        @endphp
        <div class="wrap{{ $loop->last ? '' : ' page-break' }}">
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
                        <td>{{ $group['nomor_spm'] }}</td>
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
                        <th style="width: 6%;">No</th>
                        <th style="width: 48%;">Uraian Tagihan</th>
                        <th style="width: 22%;">Nomor Faktur</th>
                        <th style="width: 24%;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($group['items'] as $idx => $item)
                        @php($total = (float) $item->jumlah_barang * (float) $item->harga_barang)
                        <tr>
                            <td class="center">{{ $idx + 1 }}</td>
                            <td>Pembelian {{ $item->nama_barang }}</td>
                            <td class="center">{{ $group['nomor_nota'] ?: '-' }}</td>
                            <td class="right">Rp. {{ number_format($total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="rekening">
                <p>Mohon kiranya dapat diproses pembayaran sejumlah total tersebut di atas melalui rekening di bawah ini:</p>
                <p class="indent"><strong>Nama Bank</strong> : {{ trim((string) ($supplier?->nama_bank ?? '')) !== '' ? $supplier->nama_bank : '-' }}</p>
                <p class="indent"><strong>No. Rekening</strong> : {{ trim((string) ($supplier?->nomor_rekening ?? '')) !== '' ? $supplier->nomor_rekening : '-' }}</p>
                <p class="indent"><strong>Atas Nama Rekening</strong> : {{ trim((string) ($supplier?->atas_nama_rekening ?? '')) !== '' ? $supplier->atas_nama_rekening : ($group['supplier_nama'] ?? '-') }}</p>
                <p class="indent"><strong>Jumlah Pembayaran</strong> : Rp. {{ number_format((float) $group['grand_total'], 0, ',', '.') }}</p>
                <p class="indent"><strong>Batas waktu pembayaran</strong> : {{ $tanggalSurat?->translatedFormat('d F Y') ?? '-' }}</p>
            </div>

            <p class="body-text">
                Demikian surat permohonan ini kami sampaikan. Dokumen pendukung terlampir dan telah diverifikasi. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.
            </p>

            <div class="sign-wrap">
                <table class="sign-table">
                    <tr>
                        <td class="sign-left">
                            <div class="sign-block">
                                <p>Dibuat oleh,</p>
                                <p>Akuntan</p>
                                <div class="sign-space"></div>
                                <p><strong><u>{{ $namaAkuntansi !== '' ? $namaAkuntansi : '................................' }}</u></strong></p>
                            </div>
                        </td>
                        <td class="sign-right">
                            <div class="sign-block">
                                <p>{{ $kota !== '' ? $kota : '—' }}, {{ $tanggalSurat?->translatedFormat('d F Y') ?? '-' }}</p>
                                <p>Mengetahui,</p>
                                <p>Ka SPPG</p>
                                <div class="sign-space"></div>
                                <p><strong><u>{{ $namaKaSppg !== '' ? $namaKaSppg : '................................' }}</u></strong></p>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @endforeach
</body>

</html>
