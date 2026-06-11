@php
    $gajiOld = old('gaji_pokok', $relawan->exists ? number_format((float) $relawan->gaji_pokok, 0, ',', '.') : '');
    $gajiHarianOld = old('gaji_per_hari', $relawan->exists ? number_format((float) $relawan->gaji_per_hari, 0, ',', '.') : '');
@endphp

<div class="space-y-5">
    <input type="hidden" name="profil_mbg_id" value="{{ old('profil_mbg_id', $relawan->profil_mbg_id ?? \App\Support\ProfilMbgTenant::id()) }}">

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

    <div>
        <label for="status" class="inst-label">Status <span class="inst-required">*</span></label>
        <select name="status" id="status" class="inst-select" required>
            @foreach (['aktif' => 'Aktif', 'cuti' => 'Cuti', 'nonaktif' => 'Nonaktif'] as $val => $label)
                <option value="{{ $val }}" @selected(old('status', $relawan->status ?? 'aktif') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
