@extends('layouts.app')

@section('title', 'Terima Barang')

@section('content')
    <div class="inst-form-page" style="max-width:52rem;">
        <a href="{{ route('stok.penerimaan.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title">Terima barang dari order</h2>
        @include('components.periode-aktif-badge')

        <div class="inst-form-card">
            <div class="mb-5 rounded-lg border p-3 text-sm" style="border-color:#d4e8f4;background:#f8fbfd;">
                <p><span class="font-semibold">Nomor order:</span> <span class="font-mono">{{ $item->orderBarang?->nomor_order }}</span></p>
                <p><span class="font-semibold">Barang:</span> {{ $item->nama_barang }}</p>
                <p><span class="font-semibold">Supplier:</span> {{ $item->supplier?->nama_supplier ?? '-' }}</p>
                <p><span class="font-semibold">Qty order:</span> {{ number_format((float) $item->jumlah_barang, 2, ',', '.') }} {{ $item->satuan_barang }}</p>
                <p><span class="font-semibold">Sudah diterima:</span> {{ number_format($qtyDiterima, 2, ',', '.') }} {{ $item->satuan_barang }}</p>
                <p><span class="font-semibold">Sisa:</span> {{ number_format($sisaQty, 2, ',', '.') }} {{ $item->satuan_barang }}</p>
            </div>

            <form method="POST" action="{{ route('stok.penerimaan.store', $item) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label for="tanggal" class="inst-label">Tanggal penerimaan <span class="inst-required">*</span></label>
                    <input type="date" name="tanggal" id="tanggal" class="inst-input" required value="{{ old('tanggal', now()->toDateString()) }}">
                </div>

                <div>
                    <label for="qty_diterima" class="inst-label">Qty diterima <span class="inst-required">*</span></label>
                    <input type="number" step="0.01" min="0.01" max="{{ $sisaQty }}" name="qty_diterima" id="qty_diterima" class="inst-input font-mono" required value="{{ old('qty_diterima', $sisaQty > 0 ? number_format($sisaQty, 2, '.', '') : '') }}">
                </div>

                <div>
                    <label for="kondisi_penerimaan" class="inst-label">Kondisi barang <span class="inst-required">*</span></label>
                    <input type="text" name="kondisi_penerimaan" id="kondisi_penerimaan" class="inst-input" required value="{{ old('kondisi_penerimaan') }}" placeholder="Contoh: Baik, segel utuh">
                </div>

                <div>
                    <label for="keterangan" class="inst-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="2" class="inst-input" maxlength="5000">{{ old('keterangan') }}</textarea>
                </div>

                <div>
                    <label for="gambar" class="inst-label">Foto bukti penerimaan</label>
                    <input type="file" name="gambar" id="gambar" accept="image/jpeg,image/png,image/webp" class="inst-input">
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">Simpan penerimaan</button>
                    <a href="{{ route('stok.penerimaan.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
