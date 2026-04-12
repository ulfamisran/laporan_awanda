<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Slip Gaji — {{ $p->relawan?->nama_lengkap }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 24px; font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a4a6b; }
        .hdr { display: table; width: 100%; margin-bottom: 16px; }
        .hdr-cell { display: table-cell; vertical-align: middle; }
        .logo { max-height: 56px; max-width: 120px; }
        h1 { margin: 0; font-size: 16px; letter-spacing: 0.06em; text-transform: uppercase; color: #1a4a6b; }
        .sub { margin: 4px 0 0 0; font-size: 10px; color: #4a6b7f; }
        table.meta { width: 100%; margin-bottom: 14px; }
        table.meta td { padding: 3px 0; vertical-align: top; }
        .label { color: #7fa8c9; width: 120px; }
        table.komp { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.komp td { padding: 6px 4px; border-bottom: 1px solid #d4e8f4; }
        table.komp td:last-child { text-align: right; font-family: DejaVu Sans Mono, monospace; }
        .sep td { border-bottom: 1px solid #1a4a6b; font-weight: bold; }
        .total td { font-size: 12px; font-weight: bold; color: #2d7a60; border-bottom: none; }
        .foot { margin-top: 28px; display: table; width: 100%; }
        .sign { display: table-cell; width: 45%; vertical-align: top; text-align: center; font-size: 10px; }
        .line { margin: 36px auto 6px; border-top: 1px solid #1a4a6b; width: 70%; }
    </style>
</head>
<body>
    <div class="hdr">
        <div class="hdr-cell" style="width:130px;">
            @if (! empty($logoDataUri))
                <img src="{{ $logoDataUri }}" class="logo" alt="">
            @endif
        </div>
        <div class="hdr-cell">
            <h1>Slip Gaji</h1>
            <p class="sub">{{ $p->profilMbg?->nama_dapur ?? 'Dapur MBG' }}</p>
        </div>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Nama</td>
            <td>{{ $p->relawan?->nama_lengkap }}</td>
        </tr>
        <tr>
            <td class="label">NIK</td>
            <td>{{ $p->relawan?->nik }}</td>
        </tr>
        <tr>
            <td class="label">Posisi</td>
            <td>{{ $p->relawan?->posisiRelawan?->nama_posisi ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Periode</td>
            <td>{{ $p->periode_label }}</td>
        </tr>
    </table>

    <table class="komp">
        <tr><td>Gaji pokok</td><td>Rp {{ number_format((float) $p->gaji_pokok, 2, ',', '.') }}</td></tr>
        <tr><td>Tunjangan transport</td><td>Rp {{ number_format((float) $p->tunjangan_transport, 2, ',', '.') }}</td></tr>
        <tr><td>Tunjangan makan</td><td>Rp {{ number_format((float) $p->tunjangan_makan, 2, ',', '.') }}</td></tr>
        <tr><td>Tunjangan lainnya</td><td>Rp {{ number_format((float) $p->tunjangan_lainnya, 2, ',', '.') }}</td></tr>
        <tr class="sep"><td>Potongan</td><td>Rp {{ number_format((float) $p->potongan, 2, ',', '.') }}</td></tr>
        <tr class="total"><td>TOTAL GAJI</td><td>Rp {{ number_format((float) $p->total_gaji, 2, ',', '.') }}</td></tr>
    </table>

    <p style="margin-top:14px;font-size:10px;">Tanggal pembayaran: {{ $p->tanggal_bayar?->format('d F Y') ?? '—' }}</p>

    <div class="foot">
        <div class="sign">
            <p>Dibuat oleh sistem</p>
            <div class="line"></div>
            <p>{{ config('app.name') }}</p>
        </div>
        <div class="sign">
            <p>Mengetahui</p>
            <div class="line"></div>
            <p>{{ $p->profilMbg?->penanggung_jawab ?? 'Penanggung jawab dapur' }}</p>
        </div>
    </div>
</body>
</html>
