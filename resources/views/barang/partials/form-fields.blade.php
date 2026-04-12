@php
    use App\Enums\SatuanBarang;
    use App\Enums\StatusAktif;

    $satuanVal = old('satuan', $barang->satuan?->value ?? SatuanBarang::Kg->value);
    $statusVal = old('status', $barang->status?->value ?? StatusAktif::Aktif->value);
@endphp

<div class="space-y-5">
    <div>
        <label class="inst-label">Kode barang</label>
        @if ($barang->exists)
            <input type="text" class="inst-input bg-slate-50" value="{{ $barang->kode_barang }}" readonly disabled style="background:#f0f6fb;color:#7fa8c9;">
            <p class="mt-1 text-xs" style="color:#7fa8c9;">Kode digenerate sistem dan tidak dapat diubah.</p>
        @else
            <input type="text" class="inst-input bg-slate-50" value="{{ $nextKodePreview }}" readonly style="background:#f0f6fb;color:#7fa8c9;">
            <p class="mt-1 text-xs" style="color:#7fa8c9;">Kode final dihasilkan otomatis saat simpan (format BRG-YYYYMMDD-XXX).</p>
        @endif
    </div>

    <div>
        <label for="nama_barang" class="inst-label">Nama barang <span class="inst-required">*</span></label>
        <input
            type="text"
            name="nama_barang"
            id="nama_barang"
            value="{{ old('nama_barang', $barang->nama_barang) }}"
            class="inst-input"
            required
            maxlength="255"
        >
    </div>

    <div>
        <label for="kategori_barang_id" class="inst-label">Kategori <span class="inst-required">*</span></label>
        <select name="kategori_barang_id" id="kategori_barang_id" class="inst-select select2-barang" required>
            <option value="">Pilih kategori…</option>
            @foreach ($kategoris as $kat)
                <option value="{{ $kat->id }}" @selected((string) old('kategori_barang_id', $barang->kategori_barang_id) === (string) $kat->id)>
                    {{ $kat->nama_kategori }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="satuan" class="inst-label">Satuan <span class="inst-required">*</span></label>
        <select name="satuan" id="satuan" class="inst-select" required>
            @foreach (SatuanBarang::cases() as $satuan)
                <option value="{{ $satuan->value }}" @selected($satuanVal === $satuan->value)>
                    {{ $satuan->label() }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="harga_satuan" class="inst-label">Harga satuan <span class="inst-required">*</span></label>
        <input
            type="text"
            name="harga_satuan"
            id="harga_satuan"
            inputmode="numeric"
            autocomplete="off"
            class="inst-input"
            value="{{ old('harga_satuan', $barang->exists ? number_format((float) $barang->harga_satuan, 0, ',', '.') : '') }}"
            required
        >
    </div>

    <div>
        <label for="stok_minimum" class="inst-label">Stok minimum <span class="inst-required">*</span></label>
        <input
            type="number"
            name="stok_minimum"
            id="stok_minimum"
            step="0.0001"
            min="0"
            class="inst-input"
            value="{{ old('stok_minimum', $barang->stok_minimum ?? 0) }}"
            required
        >
    </div>

    <div>
        <label for="status" class="inst-label">Status <span class="inst-required">*</span></label>
        <select name="status" id="status" class="inst-select" required>
            @foreach (StatusAktif::cases() as $st)
                <option value="{{ $st->value }}" @selected($statusVal === $st->value)>
                    {{ $st->label() }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="deskripsi" class="inst-label">Deskripsi</label>
        <textarea name="deskripsi" id="deskripsi" rows="4" class="inst-textarea" maxlength="5000">{{ old('deskripsi', $barang->deskripsi) }}</textarea>
    </div>

    <div>
        <label for="foto-barang" class="inst-label">Foto barang</label>
        <input type="file" name="foto" id="foto-barang" accept="image/*" class="inst-input">
        <p class="mt-1 text-xs" style="color:#7fa8c9;">Maks. 2 MB. Format gambar umum (JPG, PNG, WebP).</p>
        <div class="mt-3">
            <div class="text-xs font-semibold" style="color:#7fa8c9;">Pratinjau</div>
            <img
                id="preview-foto-barang"
                src="{{ $barang->exists ? $barang->foto_url : '' }}"
                alt=""
                class="mt-2 hidden h-28 w-28 rounded-lg border object-cover"
                style="border-color:#d4e8f4;"
                @if ($barang->exists && $barang->foto_url)
                    data-has-existing="1"
                @endif
            >
        </div>
    </div>
</div>
