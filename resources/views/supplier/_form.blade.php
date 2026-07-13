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

    <div class="border-t pt-5" style="border-color:#e8f1f8;">
        <p class="mb-4 text-sm font-semibold" style="color:#1a4a6b;">Data rekening (untuk surat permohonan pembayaran)</p>
        <div class="space-y-5">
            <div>
                <label for="nama_bank" class="inst-label">Nama bank</label>
                <input type="text" name="nama_bank" id="nama_bank" value="{{ old('nama_bank', $supplier->nama_bank) }}" class="inst-input" maxlength="255" placeholder="Contoh: Bank BNI">
            </div>
            <div>
                <label for="nomor_rekening" class="inst-label">Nomor rekening</label>
                <input type="text" name="nomor_rekening" id="nomor_rekening" value="{{ old('nomor_rekening', $supplier->nomor_rekening) }}" class="inst-input" maxlength="64">
            </div>
            <div>
                <label for="atas_nama_rekening" class="inst-label">Atas nama rekening</label>
                <input type="text" name="atas_nama_rekening" id="atas_nama_rekening" value="{{ old('atas_nama_rekening', $supplier->atas_nama_rekening) }}" class="inst-input" maxlength="255">
            </div>
        </div>
    </div>
</div>
