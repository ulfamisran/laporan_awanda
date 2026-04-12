@extends('layouts.app')

@section('title', 'Tambah profil MBG')

@section('content')
    <div class="inst-form-page">
        <a href="{{ route('master.profil-mbg.index') }}" class="inst-back">← Kembali</a>

        <h2 class="inst-form-title">Tambah profil dapur</h2>
        <p class="inst-form-lead">Isi data identitas dapur / entitas MBG.</p>

        <div class="inst-form-card">
            <form method="POST" action="{{ route('master.profil-mbg.store') }}" enctype="multipart/form-data" class="inst-form space-y-5">
                @csrf
                <div>
                    <label for="nama_dapur" class="inst-label">Nama dapur <span class="inst-required">*</span></label>
                    <input type="text" name="nama_dapur" id="nama_dapur" value="{{ old('nama_dapur') }}" required class="inst-input" placeholder="Nama dapur">
                </div>
                <div>
                    <label for="kode_dapur" class="inst-label">Kode dapur <span class="inst-required">*</span></label>
                    <input type="text" name="kode_dapur" id="kode_dapur" value="{{ old('kode_dapur') }}" required class="inst-input" placeholder="Contoh: DP-JKT-01">
                </div>
                <div>
                    <label for="alamat" class="inst-label">Alamat</label>
                    <textarea name="alamat" id="alamat" rows="3" class="inst-textarea" placeholder="Alamat lengkap">{{ old('alamat') }}</textarea>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="kota" class="inst-label">Kota</label>
                        <input type="text" name="kota" id="kota" value="{{ old('kota') }}" class="inst-input" placeholder="Kota">
                    </div>
                    <div>
                        <label for="provinsi" class="inst-label">Provinsi</label>
                        <input type="text" name="provinsi" id="provinsi" value="{{ old('provinsi') }}" class="inst-input" placeholder="Provinsi">
                    </div>
                </div>
                <div>
                    <label for="no_telp" class="inst-label">Nomor telepon</label>
                    <input type="text" name="no_telp" id="no_telp" value="{{ old('no_telp') }}" class="inst-input" placeholder="08XX…">
                </div>
                <div>
                    <label for="penanggung_jawab" class="inst-label">Penanggung jawab</label>
                    <input type="text" name="penanggung_jawab" id="penanggung_jawab" value="{{ old('penanggung_jawab') }}" class="inst-input" placeholder="Nama penanggung jawab">
                </div>
                <div>
                    <label for="status" class="inst-label">Status <span class="inst-required">*</span></label>
                    <select name="status" id="status" required class="inst-select">
                        <option value="aktif" @selected(old('status', 'aktif') === 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected(old('status') === 'nonaktif')>Nonaktif</option>
                    </select>
                </div>
                <div>
                    <p class="inst-label">Logo</p>
                    <label for="logo" class="inst-dropzone mt-2 block cursor-pointer">
                        <input type="file" name="logo" id="logo" accept="image/*" class="hidden">
                        <i data-lucide="upload-cloud" class="mx-auto block" style="width:32px;height:32px;color:#4a9b7a;"></i>
                        <p class="mt-2 text-sm font-medium" style="color:#1a4a6b;">Klik untuk unggah logo</p>
                        <p class="mt-1 text-xs" style="color:#7fa8c9;">PNG, JPG hingga 2MB</p>
                    </label>
                    <div class="mt-3 hidden" id="logo-preview-wrap">
                        <p class="inst-label-filter">Pratinjau</p>
                        <img id="logo-preview" src="" alt="" class="mt-2 max-h-32 max-w-xs rounded-lg border object-contain" style="border-color:#d4e8f4;">
                    </div>
                </div>
                <div class="flex flex-col gap-3 pt-4 sm:flex-row">
                    <a href="{{ route('master.profil-mbg.index') }}" class="inst-btn-outline flex-1 justify-center text-center">Batal</a>
                    <button type="submit" class="inst-btn-primary flex-1">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const input = document.getElementById('logo');
            const wrap = document.getElementById('logo-preview-wrap');
            const img = document.getElementById('logo-preview');
            input?.addEventListener('change', function () {
                const file = this.files && this.files[0];
                if (!file || !wrap || !img) return;
                wrap.classList.remove('hidden');
                img.src = URL.createObjectURL(file);
            });
            if (window.lucide) lucide.createIcons();
        })();
    </script>
@endpush
