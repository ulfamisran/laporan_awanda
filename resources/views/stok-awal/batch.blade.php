@extends('layouts.app')

@section('title', 'Input Stok Awal Massal')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Input stok awal (tabel)</h2>
            <p class="inst-page-desc">Isi jumlah untuk barang yang belum memiliki stok awal. Baris dikosongkan akan dilewati.</p>
        </div>
        <a href="{{ route('stok.awal.index') }}" class="inst-btn-outline shrink-0">← Kembali ke daftar</a>
    </div>

    @if ($rows->isEmpty())
        <div class="inst-panel p-8 text-center">
            <p class="text-sm" style="color:#4a6b7f;">Semua barang aktif sudah memiliki stok awal.</p>
            <a href="{{ route('stok.awal.index') }}" class="mt-4 inline-block text-sm font-semibold" style="color:#1a4a6b;">Ke halaman stok awal</a>
        </div>
    @else
        <div class="inst-form-card mb-4 p-4 sm:p-6">
            <form method="POST" action="{{ route('stok.awal.batch.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="profil_mbg_id" value="{{ $profilId }}">

                <div class="max-w-xs">
                    <label for="tanggal" class="inst-label">Tanggal stok awal (semua baris) <span class="inst-required">*</span></label>
                    <input type="date" name="tanggal" id="tanggal" class="inst-input" required value="{{ old('tanggal', now()->toDateString()) }}">
                    @error('tanggal')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @error('rows')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="overflow-x-auto rounded-lg border" style="border-color:#d4e8f4;">
                    <table class="inst-table min-w-[48rem]">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Kategori</th>
                                <th>Satuan</th>
                                <th class="text-right">Jumlah stok awal <span class="inst-required">*</span></th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $b)
                                <tr>
                                    <td class="font-medium">{{ $b->kode_barang }} — {{ $b->nama_barang }}</td>
                                    <td>{{ $b->kategoriBarang?->nama_kategori ?? '—' }}</td>
                                    <td>{{ $b->satuan?->label() ?? '—' }}</td>
                                    <td class="text-right">
                                        <div class="flex flex-col items-end gap-1">
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                name="rows[{{ $b->id }}][jumlah]"
                                                class="inst-input font-mono text-right @error('rows.'.$b->id.'.jumlah') border-red-500 @enderror"
                                                value="{{ old('rows.'.$b->id.'.jumlah') }}"
                                                placeholder="—"
                                                style="min-width:7rem;"
                                            >
                                            @error('rows.'.$b->id.'.jumlah')
                                                <span class="text-xs text-red-600">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            name="rows[{{ $b->id }}][keterangan]"
                                            class="inst-input"
                                            maxlength="5000"
                                            value="{{ old('rows.'.$b->id.'.keterangan') }}"
                                            placeholder="Opsional"
                                        >
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="text-xs" style="color:#7fa8c9;">Hanya baris dengan jumlah yang diisi yang akan disimpan. Tanggal yang sama dipakai untuk semua entri.</p>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">Simpan semua yang diisi</button>
                    <a href="{{ route('stok.awal.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    @endif
@endsection
