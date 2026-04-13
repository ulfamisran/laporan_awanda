@extends('layouts.app')

@section('title', 'Tambah Laporan Limbah Harian')

@section('content')
    <div class="inst-form-page" style="max-width:52rem;">
        <a href="{{ route('laporan-limbah.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title">Tambah laporan limbah harian</h2>
        <p class="inst-form-lead text-sm" style="color:#4a6b7f;">Anda bisa isi sebagian kategori dulu, lalu lengkapi kategori lain saat update.</p>
        @include('components.periode-aktif-badge')

        <form method="POST" action="{{ route('laporan-limbah.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="profil_mbg_id" value="{{ $profilId }}">

            <div class="inst-form-card space-y-4">
                <div>
                    <label for="tanggal" class="inst-label">Tanggal <span class="inst-required">*</span></label>
                    <input type="text" name="tanggal" id="tanggal" class="inst-input flatpickr" required value="{{ old('tanggal', now()->format('d/m/Y')) }}" autocomplete="off">
                    @error('tanggal')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="menu_makanan" class="inst-label">Menu makanan <span class="inst-required">*</span></label>
                    <textarea name="menu_makanan" id="menu_makanan" rows="2" class="inst-input" maxlength="1000" required>{{ old('menu_makanan') }}</textarea>
                    @error('menu_makanan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @foreach ($kategoris as $k)
                @php
                    $kid = $k->id;
                    $j0 = old("kategori.$kid.jenis_penanganan", \App\Enums\JenisPenangananLimbah::Dibuang->value);
                @endphp
                <div class="inst-form-card space-y-4" x-data="{ jenis: '{{ $j0 }}' }">
                    <h3 class="text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">{{ $k->nama_kategori }}</h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="inst-label" for="jumlah_{{ $kid }}">Jumlah</label>
                            <input type="number" step="0.01" min="0.01" name="kategori[{{ $kid }}][jumlah]" id="jumlah_{{ $kid }}" class="inst-input font-mono" value="{{ old("kategori.$kid.jumlah") }}">
                            @error("kategori.$kid.jumlah")
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="inst-label" for="satuan_{{ $kid }}">Satuan</label>
                            <select name="kategori[{{ $kid }}][satuan]" id="satuan_{{ $kid }}" class="inst-select">
                                @foreach (\App\Enums\SatuanLimbah::cases() as $s)
                                    <option value="{{ $s->value }}" @selected(old("kategori.$kid.satuan") === $s->value)>{{ $s->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="inst-label" for="jenis_{{ $kid }}">Jenis penanganan</label>
                        <select name="kategori[{{ $kid }}][jenis_penanganan]" id="jenis_{{ $kid }}" class="inst-select" x-model="jenis">
                            @foreach (\App\Enums\JenisPenangananLimbah::cases() as $j)
                                <option value="{{ $j->value }}" @selected($j0 === $j->value)>{{ $j->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="jenis === '{{ \App\Enums\JenisPenangananLimbah::Dijual->value }}'" x-cloak>
                        <label class="inst-label" for="harga_{{ $kid }}">Harga jual (Rp) <span class="inst-required">*</span></label>
                        <input type="number" step="0.01" min="0" name="kategori[{{ $kid }}][harga_jual]" id="harga_{{ $kid }}" class="inst-input font-mono" value="{{ old("kategori.$kid.harga_jual") }}" :required="jenis === '{{ \App\Enums\JenisPenangananLimbah::Dijual->value }}'">
                    </div>

                    <div>
                        <label class="inst-label" for="gambar_{{ $kid }}">Foto limbah</label>
                        <input type="file" name="kategori[{{ $kid }}][gambar]" id="gambar_{{ $kid }}" accept="image/jpeg,image/png,image/webp" class="inst-input @error("kategori.$kid.gambar") border-red-500 @enderror">
                        @error("kategori.$kid.gambar")
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs" style="color:#7fa8c9;">JPG, PNG, atau WebP, maks. 5MB.</p>
                    </div>

                    <div>
                        <label class="inst-label" for="ket_{{ $kid }}">Keterangan</label>
                        <textarea name="kategori[{{ $kid }}][keterangan]" id="ket_{{ $kid }}" rows="2" class="inst-input" maxlength="5000">{{ old("kategori.$kid.keterangan") }}</textarea>
                    </div>
                </div>
            @endforeach

            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="inst-btn-primary">Simpan</button>
                <a href="{{ route('laporan-limbah.index') }}" class="inst-btn-outline">Batal</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        if (window.lucide) lucide.createIcons();
    </script>
@endpush
