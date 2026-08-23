<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Label Ompreng</title>
    @php
        $tpl = in_array(($template ?? 'premium'), ['premium', 'toska', 'krem'], true)
            ? (string) $template
            : 'premium';

        $themes = [
            'premium' => [
                'navy' => '#1a4a6b',
                'green' => '#4a9b7a',
                'greenDeep' => '#2d7a60',
                'sky' => '#d4e8f4',
                'mist' => '#f0f6fb',
                'warn' => '#c2410c',
                'warnBg' => '#fff7ed',
                'infoBg' => '#ecfdf5',
                'text' => '#0f172a',
                'headText' => '#e8f4fb',
                'subText' => '#d4e8f4',
                'paper' => '#ffffff',
            ],
            'toska' => [
                'navy' => '#1e3a8a',
                'green' => '#1e3a8a',
                'greenDeep' => '#1e40af',
                'sky' => '#facc15',
                'mist' => '#fef08a',
                'warn' => '#dc2626',
                'warnBg' => '#fef2f2',
                'infoBg' => '#eff6ff',
                'text' => '#1e3a8a',
                'headBg' => '#facc15',
                'headText' => '#1e3a8a',
                'subText' => '#1e40af',
                'paper' => '#ffffff',
                'orange' => '#1e3a8a',
                'orangeSoft' => '#dbeafe',
                'gold' => '#fde047',
            ],
            'krem' => [
                'navy' => '#123b5d',
                'green' => '#4f895c',
                'greenDeep' => '#3f704a',
                'sky' => '#dce6dc',
                'mist' => '#f5f9f2',
                'warn' => '#b45309',
                'warnBg' => '#fff8e8',
                'infoBg' => '#eef6f8',
                'text' => '#304239',
                'headText' => '#fffdf6',
                'subText' => '#e4f0e5',
                'paper' => '#fffdf6',
                'gold' => '#f4ca63',
                'goldSoft' => '#f9dc8c',
            ],
        ];

        $theme = $themes[$tpl];

        $iconSvg = function (string $kind, string $fill) {
            $paths = [
                'menu' => '<circle cx="12" cy="12" r="9" fill="none" stroke="'.$fill.'" stroke-width="2"/><path d="M8 9h8M8 12h8M8 15h5" stroke="'.$fill.'" stroke-width="2" stroke-linecap="round"/>',
                'gizi' => '<path d="M12 3c3.5 4 6 6.8 6 10a6 6 0 1 1-12 0c0-3.2 2.5-6 6-10z" fill="'.$fill.'"/>',
                'clock' => '<circle cx="12" cy="12" r="9" fill="none" stroke="'.$fill.'" stroke-width="2"/><path d="M12 7v5l3 2" stroke="'.$fill.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
                'warn' => '<path d="M12 3l9 16H3L12 3z" fill="none" stroke="'.$fill.'" stroke-width="2" stroke-linejoin="round"/><path d="M12 10v4M12 16.5h.01" stroke="'.$fill.'" stroke-width="2" stroke-linecap="round"/>',
                'info' => '<circle cx="12" cy="12" r="9" fill="none" stroke="'.$fill.'" stroke-width="2"/><path d="M12 10v6M12 7h.01" stroke="'.$fill.'" stroke-width="2" stroke-linecap="round"/>',
                'pin' => '<path d="M12 21s-6-5.5-6-10a6 6 0 1 1 12 0c0 4.5-6 10-6 10z" fill="'.$fill.'"/><circle cx="12" cy="11" r="2" fill="#fff"/>',
                'energi' => '<path d="M13 2L4 14h7l-1 8 10-14h-7l0-6z" fill="'.$fill.'"/>',
                'protein' => '<path d="M9 6v12M15 6v12M6 9h12M6 15h12" stroke="'.$fill.'" stroke-width="2.2" stroke-linecap="round"/><circle cx="6" cy="9" r="1.6" fill="'.$fill.'"/><circle cx="18" cy="9" r="1.6" fill="'.$fill.'"/><circle cx="6" cy="15" r="1.6" fill="'.$fill.'"/><circle cx="18" cy="15" r="1.6" fill="'.$fill.'"/>',
                'lemak' => '<path d="M12 3c3 4 6 7 6 11a6 6 0 1 1-12 0c0-4 3-7 6-11z" fill="'.$fill.'"/>',
                'karbo' => '<path d="M12 2.2l8 4.6v10.4L12 21.8 4 17.2V6.8L12 2.2z" fill="'.$fill.'"/><path d="M12 6.8v10.4M8.6 9.2h6.8M8.6 12h6.8M8.6 14.8h6.8" stroke="#fff" stroke-width="1.7" stroke-linecap="round"/>',
                'serat' => '<path d="M12 20V10M12 10c-3-1-5-4-5-7 2 1 4 3 5 7zm0 0c3-1 5-4 5-7-2 1-4 3-5 7z" fill="none" stroke="'.$fill.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
            ];
            $inner = $paths[$kind] ?? $paths['info'];
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">'.$inner.'</svg>';

            return 'data:image/svg+xml;base64,'.base64_encode($svg);
        };

        $icons = [
            'menu' => $iconSvg('menu', $tpl === 'toska' ? '#1e3a8a' : $theme['green']),
            'gizi' => $iconSvg('gizi', $tpl === 'toska' ? '#1e3a8a' : $theme['green']),
            'clock' => $iconSvg('clock', $tpl === 'toska' ? '#1e3a8a' : $theme['navy']),
            'clockLight' => $iconSvg('clock', '#ffffff'),
            'warn' => $iconSvg('warn', $theme['warn']),
            'info' => $iconSvg('info', $tpl === 'toska' ? '#1e3a8a' : ($theme['orange'] ?? $theme['greenDeep'])),
            'warnRed' => $iconSvg('warn', '#dc2626'),
            'infoOrange' => $iconSvg('info', '#ea580c'),
            'pin' => $iconSvg('pin', $tpl === 'krem' ? $theme['navy'] : ($theme['gold'] ?? $theme['green'])),
            'energi' => $iconSvg('energi', '#d98b36'),
            'protein' => $iconSvg('protein', '#bd5e51'),
            'lemak' => $iconSvg('lemak', '#4f895c'),
            'karbo' => $iconSvg('karbo', '#b78637'),
            'serat' => $iconSvg('serat', '#4f895c'),
        ];

        $nutrientIcons = [
            'Energi' => $icons['energi'],
            'Protein' => $icons['protein'],
            'Lemak' => $icons['lemak'],
            'Karbohidrat' => $icons['karbo'],
            'Serat' => $icons['serat'],
        ];
        $nutrientShort = [
            'Energi' => 'ENERGI',
            'Protein' => 'PROTEIN',
            'Lemak' => 'LEMAK',
            'Karbohidrat' => 'KARBO',
            'Serat' => 'SERAT',
        ];

        $watermarkText = trim((string) ($lokasiSppg ?? 'SPPG'));
        $isKrem = $tpl === 'krem';
        $isToska = $tpl === 'toska';
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { margin: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: {{ $theme['text'] }};
            font-size: 10px;
        }
        .sheet {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            padding: 0;
        }
        .sheet td {
            vertical-align: middle;
            text-align: center;
            padding: 0.85cm 0.25cm;
            width: 33.3333%;
        }
        .sheet td.cut {
            border-left: 1px dashed #94a3b8;
        }

        /* ===== Template premium / toska ===== */
        .sticker {
            position: relative;
            border: 3px solid {{ $isToska ? '#1e3a8a' : $theme['navy'] }};
            border-radius: 14px;
            overflow: hidden;
            background: {{ $theme['paper'] }};
            text-align: left;
            display: inline-block;
            width: 96%;
            box-sizing: border-box;
        }
        .sticker:after {
            content: "";
            position: absolute;
            left: 0; right: 0; bottom: 0;
            height: 6px;
            background: {{ $isToska ? '#facc15' : $theme['green'] }};
        }
        .wm-wrap {
            position: absolute;
            left: 0; right: 0; top: 70px; bottom: 10px;
            overflow: hidden;
            z-index: 0;
        }
        .wm {
            position: absolute;
            left: -18%;
            top: 38%;
            width: 136%;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            color: {{ $theme['navy'] }};
            opacity: 0.07;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            transform: rotate(-32deg);
            line-height: 1.35;
        }
        .wm-line { display: block; margin-bottom: 10px; }
        .content-layer { position: relative; z-index: 1; }
        .sticker-head {
            background: {{ $isToska ? ($theme['headBg'] ?? '#facc15') : $theme['navy'] }};
            color: {{ $theme['headText'] }};
            text-align: center;
            padding: {{ $isToska ? '7px 5px 8px' : '5px 4px 6px' }};
            border-bottom: 4px solid {{ $isToska ? '#1e3a8a' : $theme['green'] }};
        }
        .logo {
            width: {{ $isToska ? '70px' : '48px' }};
            height: {{ $isToska ? '70px' : '48px' }};
            border-radius: 999px; background: #fff; padding: 1px;
            display: inline-block; margin-bottom: 2px;
            border: 2px solid {{ $isToska ? '#1e3a8a' : $theme['green'] }};
        }
        .brand-title {
            font-size: {{ $isToska ? '15.5px' : '11.5px' }}; font-weight: bold;
            text-transform: uppercase; letter-spacing: 0.04em;
            color: {{ $isToska ? '#1e3a8a' : '#ffffff' }};
        }
        .brand-subtitle {
            margin-top: 2px;
            font-size: {{ $isToska ? '12px' : '8.2px' }};
            font-weight: bold;
            text-transform: uppercase; color: {{ $theme['subText'] }};
            line-height: 1.25;
        }
        .body { padding: {{ $isToska ? '4px 3px 7px' : '4px 4px 8px' }}; }
        .sec {
            margin-top: {{ $isToska ? '6px' : '3px' }};
            border: 1.5px solid {{ $theme['sky'] }};
            border-radius: 7px;
            overflow: hidden;
            background: #ffffff;
        }
        .sec-head {
            padding: {{ $isToska ? '5px 5px 6px' : '2px 4px' }};
            font-size: {{ $isToska ? '10.5px' : '8.2px' }};
            font-weight: bold;
            text-transform: uppercase;
            color: {{ $theme['navy'] }};
            background: #ffffff;
            border-bottom: 1px solid {{ $theme['sky'] }};
            letter-spacing: 0.04em;
            line-height: 1.35;
        }
        .sec-ico {
            width: {{ $isToska ? '14px' : '11px' }};
            height: {{ $isToska ? '14px' : '11px' }};
            vertical-align: -2px;
            margin-right: 3px;
        }
        .sec-body { padding: {{ $isToska ? '4px 5px' : '4px' }}; }
        .menu-list {
            margin: 0;
            padding-left: {{ $isToska ? '18px' : '14px' }};
            font-size: {{ $isToska ? '12.5px' : '10.5px' }};
            line-height: {{ $isToska ? '1.4' : '1.3' }};
            color: {{ $theme['text'] }};
        }
        .gz {
            width: 100%; border-collapse: collapse;
            font-size: 9.2px; margin-top: 2px;
        }
        .gz td { border: 1px solid {{ $theme['sky'] }}; padding: 1px 2px; }
        .gz .gl { width: 42%; font-weight: bold; color: {{ $theme['navy'] }}; }
        .ggrp {
            margin-top: {{ $isToska ? '3px' : '2px' }};
            border: 1px solid {{ $theme['sky'] }};
            border-radius: 6px; overflow: hidden;
        }
        .ggrp-t {
            padding: {{ $isToska ? '1px 3px' : '2px 4px' }};
            font-size: {{ $isToska ? '9.5px' : '7.8px' }};
            font-weight: bold; text-transform: uppercase;
            background: #ffffff; color: {{ $theme['greenDeep'] }};
            border-bottom: 1px solid {{ $theme['sky'] }};
        }
        .mi {
            border: 1px solid {{ $isToska ? '#93c5fd' : $theme['sky'] }};
            border-radius: 6px;
            padding: 3px 4px;
            background: #ffffff;
            margin-bottom: {{ $isToska ? '4px' : '2px' }};
        }
        .mi-batas {
            border: 2px solid #dc2626;
            background: #ffffff;
            border-radius: 6px;
        }
        .ml {
            display: block;
            font-size: {{ $isToska ? '9px' : '7.3px' }};
            font-weight: bold;
            text-transform: uppercase;
            color: {{ $isToska ? '#1e3a8a' : $theme['navy'] }};
            letter-spacing: 0.04em; margin-bottom: 1px;
        }
        .mv {
            display: block;
            font-size: {{ $isToska ? '12px' : '10px' }};
            font-weight: bold;
            color: {{ $isToska ? '#1e3a8a' : $theme['text'] }};
        }
        .mi-batas .ml {
            font-size: {{ $isToska ? '10px' : '7.3px' }};
            color: #dc2626;
        }
        .mi-batas .mv {
            font-size: {{ $isToska ? '13.5px' : '10px' }};
            font-weight: bold;
            color: #dc2626;
        }
        .warn { border-color: #fca5a5; background: #ffffff; }
        .warn .sec-head {
            color: {{ $theme['warn'] }};
            background: #ffffff;
            border-bottom-color: #fca5a5;
        }
        .info {
            border-color: {{ $isToska ? '#93c5fd' : ($theme['orange'] ?? $theme['green']) }};
            background: #ffffff;
        }
        .info .sec-head {
            color: {{ $isToska ? '#1e3a8a' : ($theme['orange'] ?? $theme['greenDeep']) }};
            background: #ffffff;
            border-bottom-color: {{ $isToska ? '#93c5fd' : ($theme['orange'] ?? $theme['green']) }};
        }
        .sec.warn,
        .sec.info {
            margin-top: {{ $isToska ? '10px' : '3px' }};
        }
        .nt {
            font-size: 9.2px; font-weight: bold;
            line-height: 1.28; margin: 0;
        }
        .warn .nt { color: {{ $theme['warn'] }}; }
        .info .nt { color: {{ $isToska ? '#1e3a8a' : ($theme['orange'] ?? $theme['greenDeep']) }}; }
        .toska-nutri-card {
            width: 20%;
            text-align: center;
            vertical-align: top;
            border: 1px solid {{ $theme['sky'] }};
            background: #ffffff;
            padding: 4px 3px 5px !important;
        }
        .toska-nutri-inner {
            width: 100%;
            border-collapse: collapse;
        }
        .toska-nutri-inner td {
            padding: 0 !important;
            margin: 0;
            text-align: center;
            border: none;
            line-height: 1;
            height: 9px;
            overflow: hidden;
        }
        .toska-nutri-inner td.toska-nutri-v {
            height: 11px;
            font-size: 9px;
            font-weight: bold;
            color: {{ $theme['navy'] }};
        }
        .toska-nutri-l {
            font-size: 6.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: {{ $theme['greenDeep'] }};
            line-height: 1;
        }
        .toska-nutri-u {
            font-size: 6.5px;
            font-weight: bold;
            color: {{ $theme['orange'] ?? $theme['green'] }};
            line-height: 1;
        }
        .foot {
            margin-top: 5px;
            text-align: center;
            font-size: {{ $isToska ? '9.5px' : '6.5px' }};
            font-weight: bold;
            color: {{ $isToska ? '#1e3a8a' : $theme['navy'] }};
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        /* ===== Template krem (desain baru) ===== */
        .krem-sticker {
            border: 3px solid {{ $theme['navy'] }};
            border-radius: 14px;
            overflow: hidden;
            background: {{ $theme['paper'] }};
            text-align: left;
            display: inline-block;
            width: 96%;
            box-sizing: border-box;
        }
        .krem-head {
            background: {{ $theme['navy'] }};
            color: {{ $theme['headText'] }};
            text-align: center;
            padding: 8px 6px 10px;
            position: relative;
        }
        .krem-logo {
            width: 76px; height: 76px;
            border-radius: 999px;
            background: #fff;
            padding: 1px;
            display: inline-block;
            margin-bottom: 3px;
            border: none;
        }
        .krem-agency {
            font-size: 12px; font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #fff;
            line-height: 1.2;
        }
        .krem-tag {
            margin-top: 2px;
            font-size: 13.5px;
            font-weight: bold;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: {{ $theme['gold'] ?? '#f4ca63' }};
        }
        .krem-pill {
            display: inline-block;
            margin-top: 5px;
            padding: 5px 10px;
            border-radius: 999px;
            background: {{ $theme['gold'] ?? '#f4ca63' }};
            color: {{ $theme['navy'] }};
            font-size: 11.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            line-height: 1.25;
            max-width: 95%;
        }
        .krem-pill img {
            width: 14px; height: 14px;
            vertical-align: -2px;
            margin-right: 3px;
        }
        .krem-body { padding: 5px 5px 6px; }
        .krem-body .sec {
            margin-top: 6px;
            text-align: left;
        }
        .krem-body .sec.warn,
        .krem-body .sec.info {
            margin-top: 6px;
        }
        .krem-card {
            margin-top: 6px;
            border: 1px solid #dce9dd;
            border-radius: 8px;
            background: #ffffff;
            padding: 3px 4px 4px;
            overflow: hidden;
        }
        .krem-card-t {
            font-size: 13.5px;
            font-weight: bold;
            color: {{ $theme['navy'] }};
            margin-bottom: 2px;
            line-height: 1.15;
        }
        .krem-card-t img {
            width: 18px;
            height: 18px;
            vertical-align: -3px;
            margin-right: 4px;
        }
        .krem-menu-line {
            font-size: 12px;
            font-weight: bold;
            color: {{ $theme['text'] }};
            line-height: 1.3;
            padding: 0;
        }
        .krem-gizi-block {
            margin-top: 3px;
        }
        .krem-gizi-block-t {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            color: {{ $theme['greenDeep'] }};
            margin: 0;
            line-height: 1.15;
            padding: 1px 0 0;
        }
        .krem-nutri-row {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
            padding: 0;
        }
        .krem-nutri-card {
            width: 20%;
            text-align: center;
            vertical-align: top;
            border: 1px solid #e5ebe5;
            background: #ffffff;
            padding: 5px 3px 5px !important;
            margin: 0;
        }
        .krem-nutri-inner {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        .krem-nutri-inner td {
            padding: 0 !important;
            margin: 0;
            text-align: center;
            border: none;
            line-height: 1;
            overflow: hidden;
        }
        .krem-nutri-inner td.krem-nutri-ico {
            height: 16px;
            vertical-align: bottom;
        }
        .krem-nutri-inner td.krem-nutri-ico img {
            width: 15px;
            height: 15px;
            display: block;
            margin: 0 auto;
            border: 0;
        }
        .krem-nutri-inner td.krem-nutri-l {
            height: 9px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #6b7280;
            vertical-align: middle;
        }
        .krem-nutri-inner td.krem-nutri-v {
            height: 12px;
            font-size: 11px;
            font-weight: bold;
            color: {{ $theme['navy'] }};
            vertical-align: middle;
        }
        .krem-nutri-inner td.krem-nutri-u {
            height: 9px;
            font-size: 8px;
            font-weight: bold;
            color: {{ $theme['navy'] }};
            vertical-align: top;
        }
        .krem-card-gizi {
            padding: 1px 2px 1px;
        }
        .krem-card-gizi .krem-card-t {
            margin-bottom: 0;
        }
        .krem-time {
            margin-top: 8px;
            border-radius: 10px;
            background: #ffffff;
            color: {{ $theme['navy'] }};
            border: 1px solid {{ $theme['navy'] }};
            padding: 5px 6px;
        }
        .krem-time-t {
            font-size: 12.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 4px;
            color: {{ $theme['navy'] }};
        }
        .krem-time-t img {
            width: 16px; height: 16px;
            vertical-align: -2px;
            margin-right: 4px;
        }
        .krem-time-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .krem-time-grid td {
            width: 100%;
            vertical-align: top;
            padding: 0 0 3px 0;
        }
        .krem-time-grid tr + tr td {
            padding-top: 4px;
            padding-bottom: 0;
            border-top: none;
        }
        .krem-time-l {
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            opacity: 0.85;
            color: {{ $theme['navy'] }};
        }
        .krem-time-v {
            font-size: 13px;
            font-weight: bold;
            margin-top: 1px;
            color: {{ $theme['navy'] }};
        }
        .krem-time-batas {
            background: #ffffff;
            border: 2px solid #dc2626;
            border-radius: 6px;
            padding: 4px 5px !important;
        }
        .krem-time-batas .krem-time-l {
            opacity: 1;
            font-size: 9.5px;
            color: #dc2626;
        }
        .krem-time-batas .krem-time-v {
            font-size: 14.5px;
            font-weight: bold;
            letter-spacing: 0.02em;
            color: #dc2626;
        }
        .krem-banner {
            margin-top: 9px;
            border-radius: 8px;
            padding: 4px 5px;
            background: #ffffff;
            border: 1px solid #fca5a5;
            border-left: 3px solid #dc2626;
        }
        .krem-banner.info {
            background: #ffffff;
            border-color: #fdba74;
            border-left-color: #ea580c;
        }
        .krem-banner img {
            width: 18px; height: 18px;
            vertical-align: top;
            margin-right: 4px;
        }
        .krem-banner-t {
            font-size: 13px;
            font-weight: bold;
            color: #b91c1c;
        }
        .krem-banner.info .krem-banner-t {
            color: #c2410c;
        }
        .krem-banner-p {
            font-size: 10.5px;
            font-weight: bold;
            line-height: 1.3;
            margin-top: 2px;
            color: #991b1b;
        }
        .krem-banner.info .krem-banner-p {
            color: #9a3412;
        }
        .krem-foot {
            margin-top: 6px;
            text-align: center;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: {{ $theme['navy'] }};
            opacity: 0.95;
        }
    </style>
</head>
<body>
@foreach ($pages as $page)
    <table class="sheet" @if (!$loop->last) style="page-break-after: always;" @endif>
        <tr>
        @for ($i = 0; $i < 3; $i++)
            @php $label = $page[$i] ?? null; @endphp
            <td class="{{ $i > 0 ? 'cut' : '' }}" align="center" valign="middle">
            @if ($label)
                @if ($isKrem)
                    {{-- Template 3: Krem Emas --}}
                    <div class="krem-sticker">
                        <div class="krem-head">
                            @if (!empty($logoDataUri))
                                <div><img src="{{ $logoDataUri }}" alt="Logo BGN" class="krem-logo"></div>
                            @endif
                            <div class="krem-tag">Badan Gizi Nasional</div>
                            <div class="krem-pill">
                                <img src="{{ $icons['pin'] }}" alt="">
                                {{ $lokasiSppg }}
                            </div>
                        </div>
                        <div class="krem-body">
                            <div class="krem-card">
                                <div class="krem-card-t">
                                    <img src="{{ $icons['menu'] }}" alt="">
                                    Menu Hari Ini
                                </div>
                                @foreach ($label['menu'] as $m)
                                    <div class="krem-menu-line">{{ $m }}</div>
                                @endforeach
                            </div>

                            <div class="krem-card krem-card-gizi">
                                <div class="krem-card-t">
                                    <img src="{{ $icons['gizi'] }}" alt="">
                                    Nilai Gizi
                                </div>
                                @foreach ($label['gizi_groups'] as $group)
                                    <div class="krem-gizi-block">
                                        <div class="krem-gizi-block-t">{{ $group['label'] }}</div>
                                        <table class="krem-nutri-row" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                @foreach ($group['items'] as $g)
                                                    @php
                                                        $iconKey = $g['label'] ?? '';
                                                        $ico = $nutrientIcons[$iconKey] ?? $icons['gizi'];
                                                        $short = $nutrientShort[$iconKey] ?? strtoupper((string) $iconKey);
                                                        $nilaiRaw = trim((string) ($g['nilai'] ?? ''));
                                                        $nilaiParts = preg_split('/\s+/', $nilaiRaw, 2) ?: [];
                                                        $nilaiAngka = $nilaiParts[0] ?? '-';
                                                        $nilaiSatuan = $nilaiParts[1] ?? '';
                                                        $icoSize = ($iconKey === 'Karbohidrat') ? 17 : 15;
                                                    @endphp
                                                    <td class="krem-nutri-card" valign="top">
                                                        <table class="krem-nutri-inner" cellpadding="0" cellspacing="0" width="100%">
                                                            <tr><td class="krem-nutri-ico"><img src="{{ $ico }}" width="{{ $icoSize }}" height="{{ $icoSize }}" alt=""></td></tr>
                                                            <tr><td class="krem-nutri-l">{{ $short }}</td></tr>
                                                            <tr><td class="krem-nutri-v">{{ $nilaiAngka }}</td></tr>
                                                            <tr><td class="krem-nutri-u">{{ $nilaiSatuan }}</td></tr>
                                                        </table>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        </table>
                                    </div>
                                @endforeach
                            </div>

                            <div class="sec">
                                <div class="sec-head">
                                    <img src="{{ $icons['clock'] }}" class="sec-ico" alt="">
                                    Informasi Konsumsi
                                </div>
                                <div class="sec-body">
                                    <div class="mi">
                                        <div class="ml">Tanggal Produksi</div>
                                        <div class="mv">{{ $label['tanggal_produksi'] }}</div>
                                    </div>
                                    <div class="mi mi-batas">
                                        <div class="ml">Batas Waktu Konsumsi</div>
                                        <div class="mv"><strong>{{ $label['batas_konsumsi'] }} WITA</strong></div>
                                    </div>
                                </div>
                            </div>

                            <div class="sec warn">
                                <div class="sec-head">
                                    <img src="{{ $icons['warn'] }}" class="sec-ico" alt="">
                                    Peringatan
                                </div>
                                <div class="sec-body">
                                    <p class="nt">{{ $peringatan }}</p>
                                </div>
                            </div>

                            <div class="sec info">
                                <div class="sec-head">
                                    <img src="{{ $icons['info'] }}" class="sec-ico" alt="">
                                    Himbauan
                                </div>
                                <div class="sec-body">
                                    <p class="nt">{{ $himbauan }}</p>
                                </div>
                            </div>

                            <div class="krem-foot">{{ $watermarkText }}</div>
                        </div>
                    </div>
                @else
                    {{-- Template 1 & 2: premium / toska --}}
                    <div class="sticker">
                        <div class="wm-wrap">
                            <div class="wm">
                                <span class="wm-line">{{ $watermarkText }}</span>
                                <span class="wm-line">MBG · {{ $watermarkText }}</span>
                                <span class="wm-line">{{ $watermarkText }}</span>
                            </div>
                        </div>

                        <div class="content-layer">
                            <div class="sticker-head">
                                @if (!empty($logoDataUri))
                                    <div><img src="{{ $logoDataUri }}" alt="Logo" class="logo"></div>
                                @endif
                                <div class="brand-title">Badan Gizi Nasional</div>
                                <div class="brand-subtitle">{{ $lokasiSppg }}</div>
                            </div>
                            <div class="body">
                                <div class="sec">
                                    <div class="sec-head">
                                        <img src="{{ $icons['menu'] }}" class="sec-ico" alt="">
                                        Menu Hari Ini
                                    </div>
                                    <div class="sec-body">
                                        <ol class="menu-list">
                                            @foreach ($label['menu'] as $m)
                                                <li>{{ $m }}</li>
                                            @endforeach
                                        </ol>
                                    </div>
                                </div>

                                <div class="sec">
                                    <div class="sec-head">
                                        <img src="{{ $icons['gizi'] }}" class="sec-ico" alt="">
                                        Nilai Gizi
                                    </div>
                                    <div class="sec-body">
                                        @foreach ($label['gizi_groups'] as $group)
                                            <div class="ggrp">
                                                <div class="ggrp-t">{{ $group['label'] }}</div>
                                                @if ($isToska)
                                                    <table class="krem-nutri-row" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            @foreach ($group['items'] as $g)
                                                                @php
                                                                    $iconKey = $g['label'] ?? '';
                                                                    $short = $nutrientShort[$iconKey] ?? strtoupper((string) $iconKey);
                                                                    $nilaiRaw = trim((string) ($g['nilai'] ?? ''));
                                                                    $nilaiParts = preg_split('/\s+/', $nilaiRaw, 2) ?: [];
                                                                    $nilaiAngka = $nilaiParts[0] ?? '-';
                                                                    $nilaiSatuan = $nilaiParts[1] ?? '';
                                                                @endphp
                                                                <td class="toska-nutri-card" valign="top">
                                                                    <table class="toska-nutri-inner" cellpadding="0" cellspacing="0" width="100%">
                                                                        <tr><td class="toska-nutri-l">{{ $short }}</td></tr>
                                                                        <tr><td class="toska-nutri-v">{{ $nilaiAngka }}</td></tr>
                                                                        <tr><td class="toska-nutri-u">{{ $nilaiSatuan }}</td></tr>
                                                                    </table>
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    </table>
                                                @else
                                                    <table class="gz">
                                                        @foreach ($group['items'] as $g)
                                                            <tr>
                                                                <td class="gl">{{ $g['label'] }}</td>
                                                                <td>{{ $g['nilai'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="sec">
                                    <div class="sec-head">
                                        <img src="{{ $icons['clock'] }}" class="sec-ico" alt="">
                                        Informasi Konsumsi
                                    </div>
                                    <div class="sec-body">
                                        <div class="mi">
                                            <div class="ml">Tanggal Produksi</div>
                                            <div class="mv">{{ $label['tanggal_produksi'] }}</div>
                                        </div>
                                        <div class="mi mi-batas">
                                            <div class="ml">Batas Waktu Konsumsi</div>
                                            <div class="mv"><strong>{{ $label['batas_konsumsi'] }} WITA</strong></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sec warn">
                                    <div class="sec-head">
                                        <img src="{{ $icons['warn'] }}" class="sec-ico" alt="">
                                        Peringatan
                                    </div>
                                    <div class="sec-body">
                                        <p class="nt">{{ $peringatan }}</p>
                                    </div>
                                </div>

                                <div class="sec info">
                                    <div class="sec-head">
                                        <img src="{{ $icons['info'] }}" class="sec-ico" alt="">
                                        Himbauan
                                    </div>
                                    <div class="sec-body">
                                        <p class="nt">{{ $himbauan }}</p>
                                    </div>
                                </div>

                                <div class="foot">{{ $watermarkText }}</div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
            </td>
        @endfor
        </tr>
    </table>
@endforeach
</body>
</html>
