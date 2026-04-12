<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Relawan</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #1a4a6b; }
        .card {
            width: 100%;
            height: 100%;
            padding: 10px 14px;
            border: 2px solid #4a9b7a;
            border-radius: 8px;
            background: linear-gradient(135deg, #f8fbfd 0%, #e8f1f8 100%);
        }
        .row { display: table; width: 100%; }
        .cell { display: table-cell; vertical-align: middle; }
        .foto {
            width: 96px;
            height: 96px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #d4e8f4;
            background: #fff;
        }
        .foto-ph {
            width: 96px;
            height: 96px;
            border-radius: 10px;
            background: #4a9b7a;
            color: #fff;
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            line-height: 96px;
        }
        .nama { font-size: 15px; font-weight: bold; margin: 0 0 4px 0; }
        .meta { font-size: 10px; margin: 2px 0; color: #4a6b7f; }
        .label { font-size: 8px; text-transform: uppercase; color: #7fa8c9; letter-spacing: 0.04em; }
        .qr { width: 100px; height: 100px; }
        .brand { font-size: 9px; color: #4a9b7a; font-weight: bold; margin-top: 6px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="row">
            <div class="cell" style="width: 110px;">
                @if (! empty($fotoDataUri))
                    <img src="{{ $fotoDataUri }}" class="foto" alt="">
                @else
                    <div class="foto-ph">R</div>
                @endif
            </div>
            <div class="cell" style="padding: 0 10px;">
                <p class="label">Relawan Dapur MBG</p>
                <p class="nama">{{ $relawan->nama_lengkap }}</p>
                <p class="meta"><strong>NIK</strong> {{ $relawan->nik }}</p>
                <p class="meta"><strong>Posisi</strong> {{ $relawan->posisiRelawan?->nama_posisi ?? '—' }}</p>
                <p class="meta"><strong>Dapur</strong> {{ $relawan->profilMbg?->nama_dapur ?? '—' }}</p>
                <p class="brand">Sistem Pengelolaan Dapur MBG</p>
            </div>
            <div class="cell" style="width: 108px; text-align: center;">
                @if (! empty($qrDataUri))
                    <img src="{{ $qrDataUri }}" class="qr" alt="QR">
                @endif
            </div>
        </div>
    </div>
</body>
</html>
