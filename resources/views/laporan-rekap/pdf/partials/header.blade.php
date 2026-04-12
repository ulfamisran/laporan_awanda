@php
    $judul = $judul ?? 'Laporan';
    $periode = $periode ?? '—';
@endphp
<div style="display: table; width: 100%; margin-bottom: 14px; border-bottom: 1px solid #d4e8f4; padding-bottom: 10px;">
    <div style="display: table-cell; width: 100px; vertical-align: middle;">
        @if (! empty($logoDataUri))
            <img src="{{ $logoDataUri }}" alt="" style="max-height: 52px; max-width: 92px;">
        @endif
    </div>
    <div style="display: table-cell; vertical-align: middle;">
        <div style="font-size: 8px; color: #4a6b7f; text-transform: uppercase; letter-spacing: 0.04em;">Dapur MBG</div>
        <div style="font-size: 12px; font-weight: bold; color: #1a4a6b;">{{ $profil->nama_dapur ?? '—' }}</div>
        <div style="font-size: 11px; font-weight: bold; margin-top: 4px;">{{ $judul }}</div>
        <div style="font-size: 8px; color: #4a6b7f; margin-top: 2px;">Periode: {{ $periode }}</div>
    </div>
</div>
