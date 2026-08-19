@extends('layouts.app')

@section('title', 'Terima Barang Mobile')

@section('content')
    <div class="mx-auto w-full max-w-2xl px-3 pb-20 sm:px-4">
        <a href="{{ route('stok.penerimaan.mobile') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title mt-2">Terima barang</h2>
        @include('components.periode-aktif-badge')

        <div class="mt-4 rounded-2xl border bg-white p-4 text-sm shadow-sm" style="border-color:#d4e8f4;background:#f8fbfd;">
            <p><span class="font-semibold">Nomor order:</span> <span class="font-mono">{{ $item->orderBarang?->nomor_order }}</span></p>
            <p class="mt-1"><span class="font-semibold">Barang:</span> {{ $item->nama_barang }}</p>
            <p class="mt-1"><span class="font-semibold">Supplier:</span> {{ $item->supplier_nama ?? $item->supplier?->nama_supplier ?? '-' }}</p>
            <p class="mt-1"><span class="font-semibold">Qty order:</span> {{ number_format((float) $item->jumlah_barang, 2, ',', '.') }} {{ $item->satuan_barang }}</p>
            <p class="mt-1"><span class="font-semibold">Sudah diterima:</span> {{ number_format($qtyDiterima, 2, ',', '.') }} {{ $item->satuan_barang }}</p>
            <p class="mt-1 font-semibold" style="color:#c2410c;">Sisa: {{ number_format($sisaQty, 2, ',', '.') }} {{ $item->satuan_barang }}</p>
        </div>

        <form method="POST" action="{{ route('stok.penerimaan.store', $item) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="mobile" value="1">

            <div class="rounded-2xl border bg-white p-4 shadow-sm" style="border-color:#d4e8f4;">
                <div>
                    <label for="tanggal" class="inst-label">Tanggal penerimaan</label>
                    <input type="date" name="tanggal" id="tanggal" class="inst-input mt-1" required value="{{ old('tanggal', now()->toDateString()) }}">
                </div>

                <div class="mt-4">
                    <label for="qty_diterima" class="inst-label">Qty diterima</label>
                    <input type="number" step="0.01" min="{{ $qtyOrder }}" name="qty_diterima" id="qty_diterima" class="inst-input mt-1 font-mono text-lg" required value="{{ old('qty_diterima', number_format($qtyOrder, 2, '.', '')) }}">
                    <p class="mt-1 text-xs" style="color:#7fa8c9;">Minimal {{ number_format($qtyOrder, 2, ',', '.') }} {{ $item->satuan_barang }}</p>
                    @error('qty_diterima')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <label for="kondisi_penerimaan" class="inst-label">Kondisi barang</label>
                    <input type="text" name="kondisi_penerimaan" id="kondisi_penerimaan" class="inst-input mt-1" required value="{{ old('kondisi_penerimaan', 'Baik') }}" placeholder="Contoh: Baik, segel utuh">
                </div>

                <div class="mt-4">
                    <label for="keterangan" class="inst-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="2" class="inst-input mt-1" maxlength="5000">{{ old('keterangan') }}</textarea>
                </div>

                <div class="mt-4">
                    <label for="gambar" class="inst-label">Foto bukti penerimaan</label>
                    <input type="file" name="gambar" id="gambar" accept="image/jpeg,image/png,image/webp" capture="environment" class="inst-input mt-1">
                    <p class="mt-1 text-xs" style="color:#7fa8c9;">Bisa langsung ambil foto dari kamera HP.</p>
                </div>
            </div>

            <div class="sticky bottom-3 z-10 rounded-2xl border bg-white/95 p-3 shadow-lg backdrop-blur" style="border-color:#d4e8f4;">
                <button type="submit" class="inst-btn-primary w-full">Simpan penerimaan</button>
            </div>
        </form>
    </div>
@endsection
