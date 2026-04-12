<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bukti {{ $trx->kode_transaksi }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a4a6b; }
        h1 { font-size: 15px; margin: 0 0 12px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d4e8f4; padding: 6px 8px; text-align: left; }
        th { width: 32%; background: #f0f6fb; }
    </style>
</head>
<body>
    <h1>Bukti transaksi {{ $jenis === 'masuk' ? 'dana masuk' : 'dana keluar' }}</h1>
    <table>
        <tr><th>Kode</th><td>{{ $trx->kode_transaksi }}</td></tr>
        <tr><th>Tanggal</th><td>{{ $trx->tanggal?->format('d/m/Y') }}</td></tr>
        <tr><th>Jenis Buku Pembantu</th><td>{{ $trx->akunJenisDana ? ($trx->akunJenisDana->kode.' — '.$trx->akunJenisDana->nama) : '—' }} <span style="color:#7fa8c9;">(Jenis Dana)</span></td></tr>
        <tr><th>Jenis Buku Kas</th><td>{{ $trx->akunKas ? ($trx->akunKas->kode.' — '.$trx->akunKas->nama) : '—' }}</td></tr>
        <tr><th>Nomor bukti</th><td>{{ $trx->nomor_bukti ?? '—' }}</td></tr>
        <tr><th>Uraian transaksi</th><td>{{ $trx->uraian_transaksi ?: '—' }}</td></tr>
        <tr><th>Jumlah</th><td><strong>{{ formatRupiah($trx->jumlah) }}</strong></td></tr>
        @if ($jenis === 'masuk')
            <tr><th>Sumber / donatur</th><td>{{ $trx->sumber }}</td></tr>
        @else
            <tr><th>Keperluan</th><td>{{ $trx->keperluan }}</td></tr>
        @endif
        @if ($trx->keterangan)
            <tr><th>Keterangan</th><td>{{ $trx->keterangan }}</td></tr>
        @endif
        <tr><th>Input oleh</th><td>{{ $trx->creator?->name }}</td></tr>
    </table>
    <p style="margin-top:16px;font-size:9px;color:#7fa8c9;">Dicetak {{ now()->translatedFormat('d F Y H:i') }} — {{ config('app.name') }}</p>
</body>
</html>
