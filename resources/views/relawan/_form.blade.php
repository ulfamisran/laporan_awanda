@php
    $isSuper = auth()->user()->hasRole('super_admin');
    $tl = old('tanggal_lahir', $relawan->tanggal_lahir?->format('d/m/Y'));
    $tb = old('tanggal_bergabung', $relawan->tanggal_bergabung?->format('d/m/Y'));
    $gajiOld = old('gaji_pokok', $relawan->exists ? number_format((float) $relawan->gaji_pokok, 0, ',', '.') : '');
    $gajiHarianOld = old('gaji_per_hari', $relawan->exists ? number_format((float) $relawan->gaji_per_hari, 0, ',', '.') : '');
@endphp

<div class="space-y-5">
    <div>
        <label for="nik" class="inst-label">NIK (16 digit) <span class="inst-required">*</span></label>
        <input
            type="text"
            name="nik"
            id="nik"
            inputmode="numeric"
            autocomplete="off"
            pattern="\d{16}"
            maxlength="16"
            value="{{ old('nik', $relawan->nik) }}"
            class="inst-input font-mono"
            required
        >
        <p class="mt-1 text-xs" style="color:#7fa8c9;">Wajib 16 digit angka, unik di seluruh sistem.</p>
    </div>

    <div>
        <label for="nama_lengkap" class="inst-label">Nama lengkap <span class="inst-required">*</span></label>
        <input type="text" name="nama_lengkap" id="nama_lengkap" value="{{ old('nama_lengkap', $relawan->nama_lengkap) }}" class="inst-input" required maxlength="255">
    </div>

    <div>
        <label for="posisi_relawan_id" class="inst-label">Posisi <span class="inst-required">*</span></label>
        <select name="posisi_relawan_id" id="posisi_relawan_id" class="inst-select select2-relawan" required>
            <option value="">Pilih posisi…</option>
            @foreach ($posisis as $p)
                <option value="{{ $p->id }}" @selected((string) old('posisi_relawan_id', $relawan->posisi_relawan_id) === (string) $p->id)>
                    {{ $p->nama_posisi }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="rounded-lg border p-4 text-sm" style="border-color:#d4e8f4;background:#f8fbfd;">
        <p class="font-semibold" style="color:#1a4a6b;">Cabang MBG</p>
        <p class="mt-1" style="color:#4a6b7f;">Penempatan relawan mengikuti profil cabang MBG tunggal pada sistem.</p>
        <input type="hidden" name="profil_mbg_id" value="{{ old('profil_mbg_id', $relawan->profil_mbg_id ?? \App\Support\ProfilMbgTenant::id()) }}">
    </div>

    <div>
        <span class="inst-label">Jenis kelamin <span class="inst-required">*</span></span>
        <div class="mt-2 flex flex-wrap gap-4">
            @php($jkVal = old('jenis_kelamin', $relawan->jenis_kelamin ?? 'L'))
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="radio" name="jenis_kelamin" value="L" @checked($jkVal === 'L') required>
                Laki-laki
            </label>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="radio" name="jenis_kelamin" value="P" @checked($jkVal === 'P')>
                Perempuan
            </label>
        </div>
    </div>

    <div>
        <label for="no_hp" class="inst-label">Nomor HP <span class="inst-required">*</span></label>
        <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp', $relawan->no_hp) }}" class="inst-input" required maxlength="32">
    </div>

    <div>
        <label for="email" class="inst-label">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email', $relawan->email) }}" class="inst-input" maxlength="255">
    </div>

    <div>
        <label for="alamat" class="inst-label">Alamat <span class="inst-required">*</span></label>
        <textarea name="alamat" id="alamat" rows="3" class="inst-input" required maxlength="5000">{{ old('alamat', $relawan->alamat) }}</textarea>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label for="tanggal_lahir" class="inst-label">Tanggal lahir <span class="inst-required">*</span></label>
            <input type="text" name="tanggal_lahir" id="tanggal_lahir" value="{{ $tl }}" class="inst-input flatpickr" required autocomplete="off">
        </div>
        <div>
            <label for="tanggal_bergabung" class="inst-label">Tanggal bergabung <span class="inst-required">*</span></label>
            <input type="text" name="tanggal_bergabung" id="tanggal_bergabung" value="{{ $tb }}" class="inst-input flatpickr" required autocomplete="off">
        </div>
    </div>

    @if ($isSuper)
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="gaji_pokok" class="inst-label">Gaji pokok <span class="inst-required">*</span></label>
                <input type="text" name="gaji_pokok" id="gaji_pokok" value="{{ $gajiOld }}" class="inst-input" required inputmode="numeric" autocomplete="off">
            </div>
            <div>
                <label for="gaji_per_hari" class="inst-label">Gaji per hari <span class="inst-required">*</span></label>
                <input type="text" name="gaji_per_hari" id="gaji_per_hari" value="{{ $gajiHarianOld }}" class="inst-input" required inputmode="numeric" autocomplete="off">
            </div>
        </div>
        <p class="mt-1 text-xs" style="color:#7fa8c9;">Hanya Super Admin yang dapat mengatur nominal gaji.</p>
    @endif

    <div>
        <label for="status" class="inst-label">Status <span class="inst-required">*</span></label>
        <select name="status" id="status" class="inst-select" required>
            @foreach (['aktif' => 'Aktif', 'cuti' => 'Cuti', 'nonaktif' => 'Nonaktif'] as $val => $label)
                <option value="{{ $val }}" @selected(old('status', $relawan->status ?? 'aktif') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="keterangan" class="inst-label">Keterangan</label>
        <textarea name="keterangan" id="keterangan" rows="2" class="inst-input" maxlength="5000">{{ old('keterangan', $relawan->keterangan) }}</textarea>
    </div>

    <div>
        <label for="foto-relawan" class="inst-label">Foto</label>
        <input type="file" name="foto" id="foto-relawan" accept="image/*" class="inst-input">
        <input type="hidden" name="crop_x" id="crop_x" value="{{ old('crop_x') }}">
        <input type="hidden" name="crop_y" id="crop_y" value="{{ old('crop_y') }}">
        <input type="hidden" name="crop_w" id="crop_w" value="{{ old('crop_w') }}">
        <input type="hidden" name="crop_h" id="crop_h" value="{{ old('crop_h') }}">
        <p class="mt-1 text-xs" style="color:#7fa8c9;">Unggah foto lalu sesuaikan area crop (1:1). Opsional.</p>
        <div class="mt-3 max-w-md">
            <div class="overflow-hidden rounded-lg border bg-white" style="border-color:#d4e8f4; max-height: 320px;">
                <img id="crop-image-relawan" alt="" class="hidden max-h-[320px] w-full object-contain">
            </div>
        </div>
        <div class="mt-3">
            <img id="preview-foto-relawan" src="{{ $relawan->foto_url }}" alt="Pratinjau" class="{{ $relawan->foto_url ? '' : 'hidden' }} h-28 w-28 rounded-lg border object-cover" style="border-color:#d4e8f4;">
        </div>
    </div>
</div>
