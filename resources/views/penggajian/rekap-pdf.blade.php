<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Penggajian — {{ $periodeLabel }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 20px; font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a4a6b; }
        .hdr { display: table; width: 100%; margin-bottom: 12px; }
        .hdr-cell { display: table-cell; vertical-align: middle; }
        .logo { max-height: 48px; max-width: 100px; }
        h1 { margin: 0; font-size: 13px; text-transform: uppercase; }
        .sub { margin: 2px 0 0 0; font-size: 9px; color: #4a6b7f; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #d4e8f4; padding: 4px 5px; }
        table.data th { background: #e8f1f8; text-align: left; font-size: 8px; text-transform: uppercase; }
        table.data td.num { text-align: right; font-family: DejaVu Sans Mono, monospace; }
        .total-row td { font-weight: bold; background: #f0fdf4; font-size: 10px; }
    </style>
</head>
<body>
    <div class="hdr">
        <div class="hdr-cell" style="width:110px;">
            @if (! empty($logoDataUri))
                <img src="{{ $logoDataUri }}" class="logo" alt="">
            @endif
        </div>
        <div class="hdr-cell">
            <h1>Rekap penggajian relawan</h1>
            <p class="sub">{{ $profil->nama_dapur }} — {{ $periodeLabel }}</p>
        </div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Posisi</th>
                <th class="num">Gaji pokok</th>
                <th class="num">Tunjangan</th>
                <th class="num">Potongan</th>
                <th class="num">Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $r)
                @php
                    $tunj = (float) $r->tunjangan_transport + (float) $r->tunjangan_makan + (float) $r->tunjangan_lainnya;
                    $st = $r->status instanceof \App\Enums\StatusPenggajian ? $r->status->label() : (string) $r->status;
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->relawan?->nama_lengkap }}</td>
                    <td>{{ $r->relawan?->posisiRelawan?->nama_posisi ?? '—' }}</td>
                    <td class="num">{{ number_format((float) $r->gaji_pokok, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($tunj, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->potongan, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->total_gaji, 0, ',', '.') }}</td>
                    <td>{{ $st }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" style="text-align:right;">Total keseluruhan</td>
                <td class="num">Rp {{ number_format($totalKeseluruhan, 2, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
