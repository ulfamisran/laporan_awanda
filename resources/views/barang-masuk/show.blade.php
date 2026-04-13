@extends('layouts.app')

@section('title', 'Detail Barang Masuk')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Detail barang masuk</h2>
            <p class="inst-form-lead font-mono text-sm" style="color:#4a6b7f;">{{ $masuk->kode_transaksi }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('stok.masuk.edit', $masuk) }}" class="inst-btn-outline">Ubah</a>
            <a href="{{ route('stok.masuk.index') }}" class="inst-btn-outline">Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="inst-panel p-6">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Dapur</dt><dd>{{ $masuk->profilMbg?->nama_dapur }}</dd></div>
                <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Tanggal</dt><dd>{{ $masuk->tanggal->format('d/m/Y') }}</dd></div>
                <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Barang</dt><dd>{{ $masuk->barang?->kode_barang }} — {{ $masuk->barang?->nama_barang }}</dd></div>
                <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Jumlah</dt><dd class="font-mono">{{ number_format((float) $masuk->jumlah, 2, ',', '.') }} {{ $masuk->satuan }}</dd></div>
                <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Harga satuan</dt><dd>{{ formatRupiah($masuk->harga_satuan) }}</dd></div>
                <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Total</dt><dd class="font-semibold">{{ formatRupiah($masuk->total_harga) }}</dd></div>
                <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Sumber</dt><dd>{{ $masuk->sumber?->label() }}</dd></div>
                @if ($masuk->orderItem?->orderBarang)
                    <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Nomor order</dt><dd>{{ $masuk->orderItem->orderBarang->nomor_order }}</dd></div>
                @endif
                @if ($masuk->kondisi_penerimaan)
                    <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Kondisi penerimaan</dt><dd>{{ $masuk->kondisi_penerimaan }}</dd></div>
                @endif
                <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Input oleh</dt><dd>{{ $masuk->creator?->name }}</dd></div>
                @if ($masuk->keterangan)
                    <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Keterangan</dt><dd>{{ $masuk->keterangan }}</dd></div>
                @endif
            </dl>
        </div>
        <div class="inst-panel p-6">
            <h3 class="mb-3 text-sm font-bold" style="color:#1a4a6b;">Gambar / nota</h3>
            @if ($masuk->gambar_url)
                <a href="{{ $masuk->gambar_url }}" target="_blank" rel="noopener">
                    <img src="{{ $masuk->gambar_url }}" alt="" class="max-w-full rounded-lg border object-contain" style="border-color:#d4e8f4;">
                </a>
            @else
                <p class="text-sm" style="color:#7fa8c9;">Tidak ada gambar diunggah.</p>
            @endif
        </div>
    </div>
@endsection
