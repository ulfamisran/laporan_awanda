@extends('layouts.app')

@section('title', 'Detail Barang Keluar')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="inst-page-title">Detail barang keluar</h2>
            <p class="font-mono text-sm" style="color:#4a6b7f;">{{ $keluar->kode_transaksi }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('stok.keluar.edit', $keluar) }}" class="inst-btn-outline">Ubah</a>
            <a href="{{ route('stok.keluar.index') }}" class="inst-btn-outline">Kembali</a>
        </div>
    </div>

    <div class="inst-panel p-6">
        <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
            <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Dapur</dt><dd>{{ $keluar->profilMbg?->nama_dapur }}</dd></div>
            <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Tanggal</dt><dd>{{ $keluar->tanggal->format('d/m/Y') }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Barang</dt><dd>{{ $keluar->barang?->kode_barang }} — {{ $keluar->barang?->nama_barang }}</dd></div>
            <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Jumlah</dt><dd class="font-mono">{{ number_format((float) $keluar->jumlah, 2, ',', '.') }} {{ $keluar->satuan }}</dd></div>
            <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Tujuan</dt><dd>{{ $keluar->tujuan_penggunaan?->label() }}</dd></div>
            <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Input oleh</dt><dd>{{ $keluar->creator?->name }}</dd></div>
            @if ($keluar->keterangan)
                <div class="sm:col-span-2"><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Keterangan</dt><dd>{{ $keluar->keterangan }}</dd></div>
            @endif
        </dl>
    </div>
@endsection
