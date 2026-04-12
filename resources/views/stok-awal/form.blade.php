@extends('layouts.app')

@section('title', $mode === 'edit' ? 'Ubah Stok Awal' : 'Tambah Stok Awal')

@section('content')
    <div class="inst-form-page" style="max-width:40rem;">
        <a href="{{ route('stok.awal.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title">{{ $mode === 'edit' ? 'Ubah stok awal' : 'Tambah stok awal' }}</h2>
        <div class="inst-form-card">
            <form method="POST" action="{{ $mode === 'edit' ? route('stok.awal.update', $stokAwal) : route('stok.awal.store') }}" class="space-y-5">
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif
                <input type="hidden" name="profil_mbg_id" value="{{ $profilId }}">

                <div>
                    <label class="inst-label">Barang</label>
                    @if ($mode === 'edit')
                        <input type="text" class="inst-input bg-slate-50" readonly value="{{ $stokAwal->barang?->kode_barang }} — {{ $stokAwal->barang?->nama_barang }}" style="background:#f0f6fb;">
                    @else
                        <select name="barang_id" id="barang_id" class="inst-select select2-stok" required>
                            <option value="">Pilih barang…</option>
                            @foreach ($barangs as $br)
                                <option value="{{ $br->id }}" @selected((int) old('barang_id', $selectedBarangId) === (int) $br->id)>
                                    {{ $br->kode_barang }} — {{ $br->nama_barang }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div>
                    <label for="tanggal" class="inst-label">Tanggal <span class="inst-required">*</span></label>
                    <input type="date" name="tanggal" id="tanggal" class="inst-input" required value="{{ old('tanggal', ($stokAwal->tanggal ?? now())->format('Y-m-d')) }}">
                </div>

                <div>
                    <label for="jumlah" class="inst-label">Jumlah <span class="inst-required">*</span></label>
                    <input type="number" step="0.01" min="0" name="jumlah" id="jumlah" class="inst-input font-mono" required value="{{ old('jumlah', $stokAwal->jumlah) }}">
                </div>

                <div>
                    <label for="keterangan" class="inst-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="2" class="inst-input" maxlength="5000">{{ old('keterangan', $stokAwal->keterangan) }}</textarea>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">Simpan</button>
                    <a href="{{ route('stok.awal.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            function initSelect2() {
                if (!window.jQuery || !jQuery.fn.select2) return;
                const $el = jQuery('.select2-stok');
                if ($el.length && !$el.data('select2')) {
                    $el.select2({ width: '100%', language: { noResults: () => 'Tidak ada hasil' } });
                }
            }
            document.addEventListener('DOMContentLoaded', function () {
                initSelect2();
                setTimeout(initSelect2, 120);
            });
        })();
    </script>
@endpush
