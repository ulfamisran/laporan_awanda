@extends('layouts.app')

@section('title', 'Tambah pengguna')

@section('content')
    <div class="inst-form-page">
        <a href="{{ route('master.pengguna.index') }}" class="inst-back">← Kembali</a>

        <h2 class="inst-form-title">Form tambah pengguna</h2>
        <p class="inst-form-lead">Lengkapi data di bawah. Peran Admin wajib memilih dapur.</p>

        <div class="inst-form-card">
            <form method="POST" action="{{ route('master.pengguna.store') }}" enctype="multipart/form-data" class="inst-form space-y-5">
                @csrf
                <div>
                    <label for="name" class="inst-label">Nama <span class="inst-required">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="inst-input" placeholder="Nama lengkap">
                </div>
                <div>
                    <label for="email" class="inst-label">Email <span class="inst-required">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required class="inst-input" placeholder="nama@email.com">
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="inst-label">Kata sandi <span class="inst-required">*</span></label>
                        <input type="password" name="password" id="password" required class="inst-input" placeholder="Minimal 8 karakter">
                    </div>
                    <div>
                        <label for="password_confirmation" class="inst-label">Ulangi kata sandi <span class="inst-required">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required class="inst-input">
                    </div>
                </div>
                <div>
                    <label for="role" class="inst-label">Peran <span class="inst-required">*</span></label>
                    <select name="role" id="role" required class="inst-select">
                        <option value="">— Pilih peran —</option>
                        <option value="super_admin" @selected(old('role') === 'super_admin')>Super admin</option>
                        <option value="admin_pusat" @selected(old('role') === 'admin_pusat')>Admin pusat</option>
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    </select>
                </div>
                <div>
                    <label for="profil_mbg_id" class="inst-label">Profil MBG / dapur</label>
                    <select name="profil_mbg_id" id="profil_mbg_id" class="inst-select">
                        <option value="">— Opsional (wajib jika peran Admin) —</option>
                        @foreach ($profils as $p)
                            <option value="{{ $p->id }}" @selected((string) old('profil_mbg_id') === (string) $p->id)>{{ $p->nama_dapur }} ({{ $p->kode_dapur }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="inst-label">Status <span class="inst-required">*</span></label>
                    <select name="status" id="status" required class="inst-select">
                        <option value="aktif" @selected(old('status', 'aktif') === 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected(old('status') === 'nonaktif')>Nonaktif</option>
                    </select>
                </div>
                <div>
                    <p class="inst-label">Foto profil</p>
                    <label for="foto" class="inst-dropzone mt-2 block cursor-pointer">
                        <input type="file" name="foto" id="foto" accept="image/*" class="hidden">
                        <i data-lucide="upload-cloud" class="mx-auto block" style="width:32px;height:32px;color:#4a9b7a;"></i>
                        <p class="mt-2 text-sm font-medium" style="color:#1a4a6b;">Klik untuk memilih foto</p>
                        <p class="mt-1 text-xs" style="color:#7fa8c9;">PNG, JPG hingga 2MB</p>
                    </label>
                    <div class="mt-3 hidden" id="foto-preview-wrap">
                        <p class="inst-label-filter">Pratinjau</p>
                        <img id="foto-preview" src="" alt="" class="mt-2 h-28 w-28 rounded-lg border object-cover" style="border-color:#d4e8f4;">
                    </div>
                </div>
                <div class="flex flex-col gap-3 pt-4 sm:flex-row">
                    <a href="{{ route('master.pengguna.index') }}" class="inst-btn-outline flex-1 justify-center text-center">Batal</a>
                    <button type="submit" class="inst-btn-primary flex-1">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const role = document.getElementById('role');
            const profil = document.getElementById('profil_mbg_id');
            function syncProfilRequired() {
                if (!role || !profil) return;
                if (role.value === 'admin') {
                    profil.setAttribute('required', 'required');
                } else {
                    profil.removeAttribute('required');
                }
            }
            role?.addEventListener('change', syncProfilRequired);
            syncProfilRequired();

            const input = document.getElementById('foto');
            const wrap = document.getElementById('foto-preview-wrap');
            const img = document.getElementById('foto-preview');
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
