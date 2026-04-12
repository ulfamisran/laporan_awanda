<div class="space-y-5">
    <div>
        <label for="nama_kategori" class="inst-label">Nama kategori <span class="inst-required">*</span></label>
        <input
            type="text"
            name="nama_kategori"
            id="nama_kategori"
            value="{{ old('nama_kategori', $kategori->nama_kategori) }}"
            class="inst-input"
            required
            maxlength="255"
        >
    </div>
    <div>
        <label for="deskripsi" class="inst-label">Deskripsi</label>
        <textarea name="deskripsi" id="deskripsi" rows="4" class="inst-textarea" maxlength="5000">{{ old('deskripsi', $kategori->deskripsi) }}</textarea>
    </div>
</div>
