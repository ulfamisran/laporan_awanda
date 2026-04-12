@extends('layouts.app')

@section('title', 'Ubah profil MBG')

@section('content')
    <div class="inst-form-page">
        <a href="{{ route('master.profil-mbg.index') }}" class="inst-back">← Kembali</a>

        <h2 class="inst-form-title">Profil SPPG / MBG</h2>
        <p class="inst-form-lead">Data identitas sesuai pelaporan. Kode dapur: <span class="font-mono font-semibold">{{ $profil->kode_dapur }}</span></p>

        <div class="inst-form-card">
            <form method="POST" action="{{ route('master.profil-mbg.update') }}" enctype="multipart/form-data" class="inst-form space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label for="nama_dapur" class="inst-label">Nama SPPG <span class="inst-required">*</span></label>
                    <input type="text" name="nama_dapur" id="nama_dapur" value="{{ old('nama_dapur', $profil->nama_dapur) }}" required class="inst-input" placeholder="Nama SPPG">
                </div>
                <div>
                    <label for="id_sppg" class="inst-label">ID SPPG</label>
                    <input type="text" name="id_sppg" id="id_sppg" value="{{ old('id_sppg', $profil->id_sppg) }}" class="inst-input font-mono" maxlength="64" placeholder="Contoh: VOZM0FRH">
                </div>
                <div>
                    <label for="kode_dapur" class="inst-label">Kode dapur (sistem) <span class="inst-required">*</span></label>
                    <input type="text" name="kode_dapur" id="kode_dapur" value="{{ old('kode_dapur', $profil->kode_dapur) }}" required class="inst-input font-mono" maxlength="50" placeholder="Kode unik internal">
                    <p class="mt-1 text-xs" style="color:#7fa8c9;">Digunakan sebagai pengenal unik di aplikasi (boleh berbeda dari ID SPPG).</p>
                </div>
                <div>
                    <label for="alamat" class="inst-label">Alamat</label>
                    <textarea name="alamat" id="alamat" rows="3" class="inst-textarea" placeholder="Alamat lengkap">{{ old('alamat', $profil->alamat) }}</textarea>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="kota" class="inst-label">Kota</label>
                        <input type="text" name="kota" id="kota" value="{{ old('kota', $profil->kota) }}" class="inst-input">
                    </div>
                    <div>
                        <label for="provinsi" class="inst-label">Provinsi</label>
                        <input type="text" name="provinsi" id="provinsi" value="{{ old('provinsi', $profil->provinsi) }}" class="inst-input">
                    </div>
                </div>
                <div>
                    <label for="no_telp" class="inst-label">Nomor telepon</label>
                    <input type="text" name="no_telp" id="no_telp" value="{{ old('no_telp', $profil->no_telp) }}" class="inst-input">
                </div>
                <div>
                    <label for="penanggung_jawab" class="inst-label">Nama Kepala SPPG</label>
                    <input type="text" name="penanggung_jawab" id="penanggung_jawab" value="{{ old('penanggung_jawab', $profil->penanggung_jawab) }}" class="inst-input" maxlength="255">
                </div>
                <div>
                    <label for="nama_akuntansi" class="inst-label">Nama Akuntan SPPG</label>
                    <input type="text" name="nama_akuntansi" id="nama_akuntansi" value="{{ old('nama_akuntansi', $profil->nama_akuntansi) }}" class="inst-input" maxlength="255">
                </div>
                <div>
                    <label for="nama_ahli_gizi" class="inst-label">Nama Ahli Gizi</label>
                    <input type="text" name="nama_ahli_gizi" id="nama_ahli_gizi" value="{{ old('nama_ahli_gizi', $profil->nama_ahli_gizi) }}" class="inst-input" maxlength="255">
                </div>
                <div>
                    <label for="nama_yayasan" class="inst-label">Nama Yayasan</label>
                    <input type="text" name="nama_yayasan" id="nama_yayasan" value="{{ old('nama_yayasan', $profil->nama_yayasan) }}" class="inst-input" maxlength="255">
                </div>
                <div>
                    <label for="ketua_yayasan" class="inst-label">Ketua Yayasan / yang mewakili</label>
                    <input type="text" name="ketua_yayasan" id="ketua_yayasan" value="{{ old('ketua_yayasan', $profil->ketua_yayasan) }}" class="inst-input" maxlength="255">
                </div>
                <div>
                    <label for="nomor_rekening_va" class="inst-label">Nomor Rekening / VA</label>
                    <input type="text" name="nomor_rekening_va" id="nomor_rekening_va" value="{{ old('nomor_rekening_va', $profil->nomor_rekening_va) }}" class="inst-input font-mono" maxlength="128" placeholder="Nomor rekening atau virtual account">
                </div>
                <div>
                    <label for="tahun_anggaran" class="inst-label">Tahun Anggaran</label>
                    <input type="number" name="tahun_anggaran" id="tahun_anggaran" value="{{ old('tahun_anggaran', $profil->tahun_anggaran) }}" class="inst-input" min="2000" max="2100" step="1" placeholder="Contoh: {{ now()->year }}">
                </div>
                <div>
                    <label for="tempat_pelaporan" class="inst-label">Tempat Pelaporan</label>
                    <input type="text" name="tempat_pelaporan" id="tempat_pelaporan" value="{{ old('tempat_pelaporan', $profil->tempat_pelaporan) }}" class="inst-input" maxlength="255" placeholder="Kota / instansi / keterangan tempat laporan">
                </div>
                <div>
                    <label for="status" class="inst-label">Status <span class="inst-required">*</span></label>
                    <select name="status" id="status" required class="inst-select">
                        <option value="aktif" @selected(old('status', $profil->status) === 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected(old('status', $profil->status) === 'nonaktif')>Nonaktif</option>
                    </select>
                </div>
                <div>
                    <p class="inst-label">Logo</p>
                    @if ($profil->logo_url)
                        <p class="inst-label-filter mb-1">Logo saat ini</p>
                        <img src="{{ $profil->logo_url }}" alt="" class="mb-3 max-h-24 max-w-xs rounded-lg border object-contain" style="border-color:#d4e8f4;">
                    @endif
                    <label for="logo" class="inst-dropzone mt-2 block cursor-pointer">
                        <input type="file" name="logo" id="logo" accept="image/*" class="hidden">
                        <i data-lucide="upload-cloud" class="mx-auto block" style="width:32px;height:32px;color:#4a9b7a;"></i>
                        <p class="mt-2 text-sm font-medium" style="color:#1a4a6b;">Ganti logo (opsional)</p>
                        <p class="mt-1 text-xs" style="color:#7fa8c9;">PNG, JPG hingga 2MB</p>
                    </label>
                    <div class="mt-3 hidden" id="logo-preview-wrap">
                        <p class="inst-label-filter">Pratinjau baru</p>
                        <img id="logo-preview" src="" alt="" class="mt-2 max-h-32 max-w-xs rounded-lg border object-contain" style="border-color:#d4e8f4;">
                    </div>
                </div>
                <div class="flex flex-col gap-3 pt-4 sm:flex-row">
                    <a href="{{ route('master.profil-mbg.index') }}" class="inst-btn-outline flex-1 justify-center text-center">Batal</a>
                    <button type="submit" class="inst-btn-primary flex-1">Simpan perubahan</button>
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
