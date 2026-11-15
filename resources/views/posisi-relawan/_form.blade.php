<div class="space-y-5">
    <div>
        <label for="nama_posisi" class="inst-label">Nama posisi <span class="inst-required">*</span></label>
        <input
            type="text"
            name="nama_posisi"
            id="nama_posisi"
            value="{{ old('nama_posisi', $posisi->nama_posisi) }}"
            class="inst-input"
            required
            maxlength="255"
        >
    </div>
    <div>
        <label for="deskripsi" class="inst-label">Deskripsi</label>
        <textarea name="deskripsi" id="deskripsi" rows="4" class="inst-input" maxlength="5000">{{ old('deskripsi', $posisi->deskripsi) }}</textarea>
    </div>
    @if ($posisi->exists)
        <div>
            <label for="urutan" class="inst-label">Urutan tampil</label>
            <input type="number" name="urutan" id="urutan" min="0" max="999999"
                value="{{ old('urutan', $posisi->urutan ?? 0) }}" class="inst-input font-mono w-40">
            <p class="mt-1 text-xs" style="color:#7fa8c9;">Angka lebih kecil tampil lebih atas di data relawan.</p>
        </div>
    @endif
</div>
