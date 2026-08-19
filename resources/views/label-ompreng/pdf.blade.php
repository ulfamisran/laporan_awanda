<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Label Ompreng</title>
    @php
        $theme = [
            'frame' => '#0f766e',
            'header' => '#0f172a',
            'headerText' => '#e0f2fe',
            'titleBg' => '#ecfeff',
            'titleText' => '#155e75',
            'warnBorder' => '#f59e0b',
            'warnBg' => '#fff7ed',
            'warnText' => '#9a3412',
            'infoBorder' => '#0f766e',
            'infoBg' => '#f0fdf4',
            'infoText' => '#166534',
        ];
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { margin: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 10px;
        }
        .sheet {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            padding: 0;
        }
        .sheet td {
            vertical-align: top;
            padding: 1cm 0.18cm;
            width: 33.3333%;
            position: relative;
        }
        .sheet td + td:before {
            content: "";
            position: absolute;
            left: -1px;
            top: 1cm;
            bottom: 1cm;
            border-left: 1px dashed #94a3b8;
        }
        .sticker {
            border: 3px solid {{ $theme['frame'] }};
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
        }
        .sticker-head {
            background: {{ $theme['header'] }};
            color: {{ $theme['headerText'] }};
            text-align: center;
            padding: 4px 4px 5px;
            border-bottom: 3px solid {{ $theme['frame'] }};
        }
        .logo {
            width: 50px; height: 50px;
            border-radius: 999px; background: #fff; padding: 1px;
            display: inline-block; margin-bottom: 2px;
        }
        .brand-title {
            font-size: 12px; font-weight: bold;
            text-transform: uppercase; letter-spacing: 0.03em;
        }
        .brand-subtitle {
            margin-top: 2px; font-size: 8.8px; font-weight: bold;
            text-transform: uppercase;
        }
        .body { padding: 4px; }
        .sec {
            margin-top: 3px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
        }
        .sec-head {
            padding: 2px 4px;
            font-size: 8.5px; font-weight: bold;
            text-transform: uppercase;
            color: {{ $theme['titleText'] }};
            background: {{ $theme['titleBg'] }};
            border-bottom: 1px solid #dbe4ea;
            letter-spacing: 0.04em;
        }
        .sec-body { padding: 4px; }
        .menu-list {
            margin: 0; padding-left: 14px;
            font-size: 11px; line-height: 1.32;
        }
        .menu-list li { margin-bottom: 0; }
        .gz {
            width: 100%; border-collapse: collapse;
            font-size: 9.5px; margin-top: 2px;
        }
        .gz td { border: 1px solid #dbe4ea; padding: 1px 2px; }
        .gz .gl { width: 42%; font-weight: bold; }
        .ggrp {
            margin-top: 2px; border: 1px solid #dbe4ea;
            border-radius: 6px; overflow: hidden;
        }
        .ggrp-t {
            padding: 2px 4px; font-size: 8px;
            font-weight: bold; text-transform: uppercase;
            background: #f8fafc; color: #334155;
            border-bottom: 1px solid #dbe4ea;
        }
        .mi {
            border: 1px solid #dbe4ea; border-radius: 6px;
            padding: 2px 4px; background: #f8fbfd; margin-bottom: 2px;
        }
        .ml {
            display: block; font-size: 7.5px; font-weight: bold;
            text-transform: uppercase; color: {{ $theme['titleText'] }};
            letter-spacing: 0.04em; margin-bottom: 1px;
        }
        .mv { font-size: 10px; font-weight: bold; color: #0f172a; }
        .warn { border-color: {{ $theme['warnBorder'] }}; }
        .warn .sec-head {
            color: {{ $theme['warnText'] }}; background: {{ $theme['warnBg'] }};
            border-bottom-color: {{ $theme['warnBorder'] }};
        }
        .info { border-color: {{ $theme['infoBorder'] }}; }
        .info .sec-head {
            color: {{ $theme['infoText'] }}; background: {{ $theme['infoBg'] }};
            border-bottom-color: {{ $theme['infoBorder'] }};
        }
        .nt {
            font-size: 9.6px; font-weight: bold;
            line-height: 1.3; margin: 0;
        }
        .warn .nt { color: {{ $theme['warnText'] }}; }
        .info .nt { color: {{ $theme['infoText'] }}; }
    </style>
</head>
<body>
@foreach ($pages as $page)
    <table class="sheet" @if (!$loop->last) style="page-break-after: always;" @endif>
        <tr>
        @for ($i = 0; $i < 3; $i++)
            @php $label = $page[$i] ?? null; @endphp
            <td>
            @if ($label)
                <div class="sticker">
                    <div class="sticker-head">
                        @if (!empty($logoDataUri))
                            <div><img src="{{ $logoDataUri }}" alt="Logo" class="logo"></div>
                        @endif
                        <div class="brand-title">Badan Gizi Nasional</div>
                        <div class="brand-subtitle">{{ $lokasiSppg }}</div>
                    </div>
                    <div class="body">
                        <div class="sec">
                            <div class="sec-head">Menu Hari Ini</div>
                            <div class="sec-body">
                                <ol class="menu-list">
                                    @foreach ($label['menu'] as $m)
                                        <li>{{ $m }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>

                        <div class="sec">
                            <div class="sec-head">Nilai Gizi</div>
                            <div class="sec-body">
                                @foreach ($label['gizi_groups'] as $group)
                                    <div class="ggrp">
                                        <div class="ggrp-t">{{ $group['label'] }}</div>
                                        <table class="gz">
                                            @foreach ($group['items'] as $g)
                                                <tr>
                                                    <td class="gl">{{ $g['label'] }}</td>
                                                    <td>{{ $g['nilai'] }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="sec">
                            <div class="sec-head">Informasi Konsumsi</div>
                            <div class="sec-body">
                                <div class="mi">
                                    <span class="ml">Tanggal Produksi</span>
                                    <span class="mv">{{ $label['tanggal_produksi'] }}</span>
                                </div>
                                <div class="mi">
                                    <span class="ml">Batas Waktu Konsumsi</span>
                                    <span class="mv">{{ $label['batas_konsumsi'] }} WITA</span>
                                </div>
                            </div>
                        </div>

                        <div class="sec warn">
                            <div class="sec-head">Peringatan</div>
                            <div class="sec-body">
                                <p class="nt">{{ $peringatan }}</p>
                            </div>
                        </div>

                        <div class="sec info">
                            <div class="sec-head">Himbauan</div>
                            <div class="sec-body">
                                <p class="nt">{{ $himbauan }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            </td>
        @endfor
        </tr>
    </table>
@endforeach
</body>
</html>
