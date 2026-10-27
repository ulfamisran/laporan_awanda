<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $item->judul_surat }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.4; }
        .pdf-header { width: 100%; margin: 0 0 12px 0; padding: 0 0 8px 0; text-align: center; font-size: 8.5pt; line-height: 1.2; color: #1a4a6b; border-bottom: 1.5px solid #1a4a6b; }
        .pdf-header .hdr-row { width: 100%; margin: 0; padding: 0; text-align: center; }
        .pdf-header .hdr-logo { margin: 0 0 6px 0; }
        .header-logo { max-height: 84px; max-width: 220px; width: auto; height: auto; display: block; margin: 0 auto; }
        .line-bgn { font-weight: bold; }
        .line-bgn .italic-en { font-style: italic; font-weight: bold; }
        .line-sppg, .line-loc { font-weight: bold; }
        .line-alamat { font-weight: normal; font-size: 8pt; }
        .title { text-align: center; margin: 14px 0 16px 0; }
        .title h1 { margin: 0; font-size: 13px; text-decoration: underline; text-transform: uppercase; letter-spacing: 0.2px; }
        .meta table, .ident table { border-collapse: collapse; }
        .meta td, .ident td { border: none; padding: 1px 0; vertical-align: top; font-size: 11px; }
        .lbl { width: 70px; }
        .sep { width: 12px; }
        .body-text { margin: 12px 0; text-align: justify; }
        .catatan { margin-top: 10px; }
        .catatan .lbl-note { font-weight: bold; }
        .sign-wrap { width: 100%; margin-top: 28px; }
        .sign-table { width: 100%; border-collapse: collapse; }
        .sign-table td { border: none; width: 50%; vertical-align: top; text-align: center; font-size: 11px; }
        .sign-space { height: 58px; }
    </style>
</head>
<body>
@php
    $tglPerihal = optional($item->tanggal_perihal)->translatedFormat('d F Y') ?: '—';
    $tglTtd = optional($item->tanggal_ttd)->translatedFormat('d F Y') ?: $tglPerihal;
    $jumlahLabel = 'Rp. '.number_format((float) $item->jumlah, 0, ',', '.');
    $perihal = trim((string) $item->perihal_teks);
    if ($perihal === '') {
        $perihal = 'Dengan ini mengajukan Pembayaran Insentif PIC Sekolah untuk Operasional SPPG pada Program '.$item->program.' '.$tglPerihal.' sebesar '.$jumlahLabel.'.';
    }
    $tempatTtd = trim((string) ($item->tempat_ttd ?: ($profil->tempat_pelaporan ?: $profil->kota ?: '—')));
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
    <div class="hdr-row line-loc">{{ trim((string) ($profil?->daerah_sppg ?? '')) !== '' ? $profil->daerah_sppg : '—' }}</div>
    <div class="hdr-row line-alamat">{{ trim((string) ($profil?->alamat ?? '')) !== '' ? $profil->alamat : '—' }}</div>
</div>

<div class="title">
    <h1>{{ $item->judul_surat }}</h1>
</div>

<div class="meta">
    <table>
        <tr>
            <td class="lbl">Periode</td>
            <td class="sep">:</td>
            <td>{{ $item->periode_label }}</td>
        </tr>
        <tr>
            <td class="lbl">Program</td>
            <td class="sep">:</td>
            <td>{{ $item->program }}</td>
        </tr>
    </table>
</div>

<p style="margin: 10px 0 4px 0;">Yang Bertanda Tangan di bawah ini :</p>
<div class="ident">
    <table>
        <tr>
            <td class="lbl">SPPG</td>
            <td class="sep">:</td>
            <td>{{ $item->nama_sppg }}</td>
        </tr>
        <tr>
            <td class="lbl">Nama</td>
            <td class="sep">:</td>
            <td>{{ $item->nama_peminta }}</td>
        </tr>
        <tr>
            <td class="lbl">Jabatan</td>
            <td class="sep">:</td>
            <td>{{ $item->jabatan_peminta }}</td>
        </tr>
    </table>
</div>

<p class="body-text">
    @php
        $escaped = e($perihal);
        $escaped = str_replace(e($tglPerihal), '<strong>'.e($tglPerihal).'</strong>', $escaped);
        $escaped = str_replace(e($jumlahLabel), '<strong>'.e($jumlahLabel).'</strong>', $escaped);
    @endphp
    {!! $escaped !!}
</p>

<div class="catatan">
    <div class="lbl-note">Catatan :</div>
    <div>{{ $item->catatan ?: '—' }}</div>
</div>

<div class="sign-wrap">
    <table class="sign-table">
        <tr>
            <td></td>
            <td>
                <div>{{ $tempatTtd }}, {{ $tglTtd }}</div>
                <div>Pemohon,</div>
                <div>{{ $item->jabatan_peminta }}</div>
                <div class="sign-space"></div>
                <div><strong>({{ $item->nama_peminta }})</strong></div>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 22px;">
                <div><strong>Mengetahui</strong></div>
                <div>{{ $item->ttd_mengetahui_jabatan ?: '—' }}</div>
                <div class="sign-space"></div>
                <div><strong>({{ $item->ttd_mengetahui_nama ?: '………………' }})</strong></div>
            </td>
            <td style="padding-top: 22px;">
                <div><strong>Menyetujui</strong></div>
                <div>{{ $item->ttd_menyetujui_jabatan ?: 'Kepala SPPG' }}</div>
                <div class="sign-space"></div>
                <div><strong>({{ $item->ttd_menyetujui_nama ?: '………………' }})</strong></div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
