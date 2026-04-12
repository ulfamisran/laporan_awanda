<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $judul ?? 'Laporan' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 24px 28px 40px 28px; font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a4a6b; position: relative; }
        .watermark {
            position: fixed; top: 40%; left: 15%; width: 70%; text-align: center;
            font-size: 48px; font-weight: bold; color: rgba(200, 80, 80, 0.12);
            transform: rotate(-35deg); z-index: 0; pointer-events: none;
        }
        .wrap { position: relative; z-index: 1; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid #d4e8f4; padding: 4px 5px; }
        table.data th { background: #e8f1f8; text-align: left; font-size: 8px; text-transform: uppercase; }
        table.data td.num { text-align: right; font-family: DejaVu Sans Mono, monospace; }
        h2.section { font-size: 11px; margin: 18px 0 6px 0; color: #1a4a6b; border-bottom: 1px solid #d4e8f4; padding-bottom: 4px; page-break-after: avoid; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
@if (! empty($draft))
    <div class="watermark">DRAFT</div>
@endif
<div class="wrap">
    @include('laporan-rekap.pdf.partials.header')
    @yield('pdf_body')
</div>
@php
    $footerMetaJson = json_encode(($dicetak ?? '').' — '.($pencetak ?? ''), JSON_UNESCAPED_UNICODE);
@endphp
<script type="text/php">
    if (isset($pdf) && isset($fontMetrics)) {
        $font = $fontMetrics->get_font('DejaVu Sans', 'normal');
        $size = 7;
        $h = $pdf->get_height();
        $meta = {!! $footerMetaJson !!};
        $pdf->page_text(24, $h - 28, $meta, $font, $size);
        $pdf->page_text(24, $h - 18, 'Halaman {PAGE_NUM} dari {PAGE_COUNT}', $font, $size);
    }
</script>
</body>
</html>
