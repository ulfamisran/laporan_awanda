<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kwitansi Batch Penggajian</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 10px; font-family: DejaVu Serif, serif; font-size: 10px; color: #1f2937; }
        .page { width: 100%; }
        .kwitansi { border: 1px solid #9ca3af; padding: 8px 10px; margin-bottom: 10px; }
        .hdr { display: table; width: 100%; }
        .hdr-cell { display: table-cell; vertical-align: top; }
        .logo { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; }
        .instansi { text-align: center; }
        .instansi h1 { margin: 0; font-size: 11px; line-height: 1.1; text-transform: uppercase; }
        .instansi p { margin: 1px 0; font-size: 8px; }
        .title { text-align: right; font-size: 11px; font-weight: bold; text-transform: uppercase; margin: 0; }
        table.meta { width: 100%; margin-top: 5px; }
        table.meta td { padding: 1px 0; vertical-align: top; }
        .kiri { width: 56%; }
        .kanan { width: 44%; }
        .label { width: 115px; }
        .total-row { margin-top: 4px; font-weight: bold; }
        .total-box { border: 1px solid #6b7280; padding: 2px 6px; text-align: center; margin-top: 2px; }
        .ttd { margin-top: 2px; display: table; width: 100%; }
        .ttd-col { display: table-cell; width: 50%; text-align: center; }
        .ttd-col p { margin: 0; }
        .ttd-line { margin: 22px auto 0; width: 70%; border-top: 1px dotted #6b7280; }
        .ttd-name { margin-top: 0; }
        .page-break { page-break-after: always; }
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
                        <div class="hdr-cell" style="width:48px;">
                            @if (! empty($logoDataUri))
                                <img src="{{ $logoDataUri }}" class="logo" alt="">
                            @endif
                        </div>
                        <div class="hdr-cell instansi">
                            <h1>{{ strtoupper($profil?->nama_dapur ?? 'Dapur MBG') }}</h1>
                            <p>{{ $profil?->alamat ?? '-' }}</p>
                        </div>
                        <div class="hdr-cell" style="width:150px; text-align:right;">
                            <p class="title">Slip Gaji Relawan</p>
                        </div>
                    </div>

                    <table class="meta">
                        <tr>
                            <td class="kiri">
                                <table style="width:100%;">
                                    <tr><td class="label">Nama</td><td>: {{ $p->relawan?->nama_lengkap }}</td></tr>
                                    <tr><td class="label">Bagian</td><td>: {{ $p->relawan?->posisiRelawan?->nama_posisi ?? '-' }}</td></tr>
                                    <tr><td class="label">Gaji Pokok</td><td>: Rp {{ number_format((float) $p->gaji_pokok, 0, ',', '.') }}</td></tr>
                                </table>
                            </td>
                            <td class="kanan">
                                <table style="width:100%;">
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
                            <p>Penerima</p>
                            <div class="ttd-line"></div>
                        </div>
                        <div class="ttd-col">
                            <p>Mengetahui</p>
                            <div class="ttd-line"></div>
                            <p class="ttd-name">{{ $profil?->nama_akuntansi ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @if (! $loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
