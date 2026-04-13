<div class="space-y-5">
    <div>
        <label for="nama_supplier" class="inst-label">Nama supplier <span class="inst-required">*</span></label>
        <input type="text" name="nama_supplier" id="nama_supplier" value="{{ old('nama_supplier', $supplier->nama_supplier) }}" class="inst-input" required maxlength="255">
    </div>

    <div>
        <label for="no_hp" class="inst-label">Nomor HP <span class="inst-required">*</span></label>
        <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp', $supplier->no_hp) }}" class="inst-input" required maxlength="32">
    </div>

    <div>
        <label for="alamat" class="inst-label">Alamat <span class="inst-required">*</span></label>
        <textarea name="alamat" id="alamat" rows="3" class="inst-input" required maxlength="5000">{{ old('alamat', $supplier->alamat) }}</textarea>
    </div>
</div>
