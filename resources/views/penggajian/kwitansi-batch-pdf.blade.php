<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kwitansi Batch Penggajian</title>
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            margin: 10px;
            font-family: DejaVu Serif, serif;
            font-size: 11px;
            line-height: 1;
            color: #1f2937;
        }
        p, h1, td, th { margin: 0; padding: 0; line-height: 1; }
        table { border-collapse: collapse; border-spacing: 0; }
        .page {
            width: 100%;
            page-break-after: always;
            page-break-inside: avoid;
        }
        .page:last-child { page-break-after: auto; }
        .kwitansi {
            border: 1px solid #9ca3af;
            padding: 2mm 3mm;
            margin-bottom: 8mm;
            page-break-inside: avoid;
        }
        .kwitansi:last-child { margin-bottom: 0; }
        .hdr { display: table; width: 100%; }
        .hdr-cell { display: table-cell; vertical-align: middle; line-height: 1.15; }
        .logo { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; display: block; }
        .instansi { text-align: center; }
        .instansi h1 { font-size: 11px; text-transform: uppercase; line-height: 1.15; }
        .instansi p { font-size: 8px; line-height: 1.15; }
        .title { text-align: right; font-size: 11px; font-weight: bold; text-transform: uppercase; line-height: 1.15; }
        table.meta { width: 100%; margin-top: 1mm; }
        table.meta td { vertical-align: top; line-height: 1; }
        table.meta table tr:first-child td { padding-top: 3mm; }
        .kiri { width: 56%; }
        .kanan { width: 44%; }
        .label { width: 88px; }
        .total-row { margin-top: 1mm; font-weight: bold; line-height: 1; font-size: 11px; }
        .total-box {
            border: 1px solid #6b7280;
            padding: 1mm 2mm;
            text-align: center;
            margin-top: 1mm;
            line-height: 1;
            font-size: 11px;
        }
        .ttd { margin-top: 1mm; display: table; width: 100%; table-layout: fixed; font-size: 11px; }
        .ttd-col { display: table-cell; width: 50%; text-align: center; vertical-align: top; line-height: 1; }
        .ttd-head { min-height: 26px; }
        .ttd-space { height: 32px; }
        .ttd-line { margin: 0 auto; width: 70%; border-top: 1px dotted #6b7280; height: 0; }
        .ttd-name { margin-top: 0; line-height: 1; }
    </style>
</head>
<body>
    @foreach ($rows->chunk(4) as $chunkIndex => $chunkRows)
        <div class="page">
            @foreach ($chunkRows as $p)
                <div class="kwitansi">
                    @php
                        $periode = $p->periode_label;
                        $penyebut = function (int $nilai) use (&$penyebut): string {
                            $nilai = abs($nilai);
                            $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
                            if ($nilai < 12) {
                                return ' '.$huruf[$nilai];
                            }
                            if ($nilai < 20) {
                                return $penyebut($nilai - 10).' Belas';
                            }
                            if ($nilai < 100) {
                                return $penyebut((int) floor($nilai / 10)).' Puluh'.$penyebut($nilai % 10);
                            }
                            if ($nilai < 200) {
                                return ' Seratus'.$penyebut($nilai - 100);
                            }
                            if ($nilai < 1000) {
                                return $penyebut((int) floor($nilai / 100)).' Ratus'.$penyebut($nilai % 100);
                            }
                            if ($nilai < 2000) {
                                return ' Seribu'.$penyebut($nilai - 1000);
                            }
                            if ($nilai < 1000000) {
                                return $penyebut((int) floor($nilai / 1000)).' Ribu'.$penyebut($nilai % 1000);
                            }
                            if ($nilai < 1000000000) {
                                return $penyebut((int) floor($nilai / 1000000)).' Juta'.$penyebut($nilai % 1000000);
                            }
                            if ($nilai < 1000000000000) {
                                return $penyebut((int) floor($nilai / 1000000000)).' Miliar'.$penyebut($nilai % 1000000000);
                            }

                            return $penyebut((int) floor($nilai / 1000000000000)).' Triliun'.$penyebut($nilai % 1000000000000);
                        };
                        $terbilang = trim($penyebut((int) round((float) $p->total_gaji))).' Rupiah';
                    @endphp
                    <div class="hdr">
                        <div class="hdr-cell" style="width:42px;">
                            @if (! empty($logoDataUri))
                                <img src="{{ $logoDataUri }}" class="logo" alt="">
                            @endif
                        </div>
                        <div class="hdr-cell instansi">
                            <h1>{{ strtoupper($profil?->nama_dapur ?? 'Dapur MBG') }}</h1>
                            <p>{{ $profil?->alamat ?? '-' }}</p>
                        </div>
                        <div class="hdr-cell" style="width:120px; text-align:right;">
                            <p class="title">Slip Gaji Relawan</p>
                        </div>
                    </div>

                    <table class="meta">
                        <tr>
                            <td class="kiri">
                                <table style="width:100%;" cellspacing="0" cellpadding="0">
                                    <tr><td class="label">Nama</td><td>: {{ $p->relawan?->nama_lengkap }}</td></tr>
                                    <tr><td class="label">Bagian</td><td>: {{ $p->relawan?->posisiRelawan?->nama_posisi ?? '-' }}</td></tr>
                                    <tr><td class="label">Gaji Pokok</td><td>: Rp {{ number_format((float) $p->gaji_pokok, 0, ',', '.') }}</td></tr>
                                </table>
                            </td>
                            <td class="kanan">
                                <table style="width:100%;" cellspacing="0" cellpadding="0">
                                    <tr><td class="label">Periode</td><td>: {{ $periode }}</td></tr>
                                    <tr><td class="label">Jumlah Hari Kerja</td><td>: {{ (int) $p->jumlah_hadir }} Hari Operasional</td></tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <div class="total-row">Total Penerimaan</div>
                    <div class="total-box">{{ $terbilang }}</div>

                    <div class="ttd">
                        <div class="ttd-col">
                            <div class="ttd-head">
                                <p>Penerima</p>
                            </div>
                            <div class="ttd-space"></div>
                            <div class="ttd-line"></div>
                        </div>
                        <div class="ttd-col">
                            <div class="ttd-head">
                                <p>Mengetahui</p>
                                <p>PLO Keuangan</p>
                            </div>
                            <div class="ttd-space"></div>
                            <div class="ttd-line"></div>
                            <p class="ttd-name">{{ $profil?->nama_akuntansi ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</body>
</html>
