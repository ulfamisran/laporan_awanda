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
</div>
