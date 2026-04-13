@extends('layouts.app')

@section('title', 'Tambah Barang Masuk')

@section('content')
    <div class="inst-form-page" style="max-width:48rem;">
        <a href="{{ route('stok.masuk.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title">Tambah barang masuk</h2>
        <p class="inst-form-lead text-sm" style="color:#4a6b7f;">Kode transaksi: <span class="font-mono font-semibold">{{ $previewKode }}</span> (otomatis saat simpan)</p>
        @include('components.periode-aktif-badge')
        <div class="inst-form-card">
            <form method="POST" action="{{ route('stok.masuk.store') }}" enctype="multipart/form-data" class="space-y-5" id="form-masuk">
                @csrf
                <input type="hidden" name="profil_mbg_id" id="profil_mbg_hidden" value="{{ $profilId }}">

                <div>
                    <label for="order_barang_item_id" class="inst-label">Pilih item order barang (opsional)</label>
                    <select name="order_barang_item_id" id="order_barang_item_id" class="inst-select">
                        <option value="">Tidak dari order barang</option>
                        @foreach ($orderItems as $it)
                            <option value="{{ $it->id }}">
                                {{ $it->orderBarang?->nomor_order }} — {{ $it->nama_barang }} ({{ number_format((float) $it->jumlah_barang, 2, ',', '.') }} {{ $it->satuan_barang }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="barang_id" class="inst-label">Barang <span class="inst-required">*</span></label>
                    <select name="barang_id" id="barang_id" class="inst-select select2-stok" required>
                        <option value="">Pilih barang…</option>
                        @foreach ($barangs as $br)
                            @php($st = $br->getStokSaatIni($profilId, $periodeId))
                            <option value="{{ $br->id }}" data-stok="{{ $st }}">{{ $br->kode_barang }} — {{ $br->nama_barang }} (stok: {{ number_format($st, 2, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="rounded-lg border p-3 text-sm" style="border-color:#d4e8f4;background:#f8fbfd;">
                    <span class="inst-label text-xs">Stok saat ini (dapur ini)</span>
                    <p id="info-stok" class="mt-1 font-mono font-semibold" style="color:#1a4a6b;">—</p>
                </div>

                <div>
                    <label for="tanggal" class="inst-label">Tanggal <span class="inst-required">*</span></label>
                    <input type="date" name="tanggal" id="tanggal" class="inst-input" required value="{{ old('tanggal', now()->toDateString()) }}">
                </div>

                <div>
                    <label for="jumlah" class="inst-label">Jumlah <span class="inst-required">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="jumlah" id="jumlah" class="inst-input font-mono" required value="{{ old('jumlah') }}">
                </div>

                <div>
                    <label for="satuan" class="inst-label">Satuan <span class="inst-required">*</span></label>
                    <input type="text" name="satuan" id="satuan" class="inst-input" required readonly value="{{ old('satuan') }}" placeholder="Pilih barang…">
                </div>

                <div>
                    <label for="harga_satuan" class="inst-label">Harga satuan <span class="inst-required">*</span></label>
                    <input type="text" name="harga_satuan" id="harga_satuan" class="inst-input" required value="{{ old('harga_satuan') }}" inputmode="numeric" autocomplete="off">
                </div>

                <div>
                    <span class="inst-label">Total harga</span>
                    <p id="total_harga_preview" class="mt-1 text-lg font-bold" style="color:#2d7a60;">Rp 0</p>
                </div>

                <div>
                    <label for="sumber" class="inst-label">Sumber <span class="inst-required">*</span></label>
                    <select name="sumber" id="sumber" class="inst-select" required>
                        @foreach (\App\Enums\BarangMasukSumber::cases() as $s)
                            <option value="{{ $s->value }}" @selected(old('sumber') === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="keterangan" class="inst-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="2" class="inst-input" maxlength="5000">{{ old('keterangan') }}</textarea>
                </div>

                <div>
                    <label for="kondisi_penerimaan" class="inst-label">Kondisi penerimaan</label>
                    <input type="text" name="kondisi_penerimaan" id="kondisi_penerimaan" class="inst-input" value="{{ old('kondisi_penerimaan') }}" placeholder="Contoh: Baik, kemasan utuh">
                </div>

                <div>
                    <label for="gambar" class="inst-label">Gambar (JPG/PNG/WebP, maks. 2MB)</label>
                    <input type="file" name="gambar" id="gambar" accept="image/jpeg,image/png,image/webp" class="inst-input @error('gambar') border-red-500 @enderror">
                    @error('gambar')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <img id="preview-gambar" src="" alt="" class="mt-3 hidden max-h-48 rounded-lg border object-contain" style="border-color:#d4e8f4;">
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inst-btn-primary">Simpan</button>
                    <a href="{{ route('stok.masuk.index') }}" class="inst-btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const apiTpl = @json(url('/stok/api/barang')).replace(/\/$/, '') + '/';
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const orderApiTpl = @json(route('stok.api.order-item', ['item' => 'ITEM_ID']));

            function formatRupiahDigits(raw) {
                const digits = String(raw || '').replace(/\D/g, '');
                if (!digits) return '';
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function parseNum(v) {
                return parseFloat(String(v || '').replace(/\./g, '').replace(',', '.')) || 0;
            }

            function updateTotal() {
                const j = parseNum(document.getElementById('jumlah')?.value);
                const h = parseNum(document.getElementById('harga_satuan')?.value);
                const total = j * h;
                const el = document.getElementById('total_harga_preview');
                if (el) el.textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');
            }

            function profilParam() {
                const hid = document.getElementById('profil_mbg_hidden');
                return hid ? 'profil_mbg_id=' + encodeURIComponent(hid.value) : '';
            }

            function loadBarang(id) {
                const sat = document.getElementById('satuan');
                const info = document.getElementById('info-stok');
                if (!id) {
                    if (sat) sat.value = '';
                    if (info) info.textContent = '—';
                    return;
                }
                fetch(apiTpl + id + '?' + profilParam(), { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } })
                    .then((r) => r.json())
                    .then((d) => {
                        if (sat) sat.value = d.satuan || '';
                        if (info) info.textContent = (d.stok_saat_ini ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2 });
                    })
                    .catch(() => {});
            }

            function initSelect2() {
                if (!window.jQuery || !jQuery.fn.select2) return;
                jQuery('.select2-stok').each(function () {
                    if (!jQuery(this).data('select2')) {
                        jQuery(this).select2({ width: '100%', language: { noResults: () => 'Tidak ada hasil' } });
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                initSelect2();
                setTimeout(initSelect2, 120);
                const harga = document.getElementById('harga_satuan');
                const jumlah = document.getElementById('jumlah');
                harga?.addEventListener('input', function () {
                    const c = harga.selectionStart;
                    const b = harga.value.length;
                    harga.value = formatRupiahDigits(harga.value);
                    harga.setSelectionRange(c + (harga.value.length - b), c + (harga.value.length - b));
                    updateTotal();
                });
                jumlah?.addEventListener('input', updateTotal);
                jQuery('#barang_id').on('change', function () {
                    loadBarang(this.value);
                });
                document.getElementById('order_barang_item_id')?.addEventListener('change', function () {
                    const id = this.value;
                    if (!id) return;
                    fetch(orderApiTpl.replace('ITEM_ID', encodeURIComponent(id)), { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } })
                        .then((r) => r.json())
                        .then((d) => {
                            const barangSelect = document.getElementById('barang_id');
                            if (barangSelect) {
                                barangSelect.value = String(d.barang_id || '');
                                if (window.jQuery) jQuery(barangSelect).trigger('change');
                            }
                            const satuan = document.getElementById('satuan');
                            if (satuan) satuan.value = d.satuan_barang || '';
                            const jumlah = document.getElementById('jumlah');
                            if (jumlah) jumlah.value = d.jumlah_barang || '';
                            const harga = document.getElementById('harga_satuan');
                            if (harga) harga.value = formatRupiahDigits(Math.round(d.harga_barang || 0));
                            const ket = document.getElementById('keterangan');
                            if (ket && !ket.value) ket.value = `Penerimaan dari order ${d.nomor_order || '-'}${d.supplier ? ' - Supplier: ' + d.supplier : ''}`;
                            updateTotal();
                        })
                        .catch(() => {});
                });
                const bid = document.getElementById('barang_id')?.value;
                if (bid) loadBarang(bid);
                document.getElementById('gambar')?.addEventListener('change', function () {
                    const f = this.files?.[0];
                    const img = document.getElementById('preview-gambar');
                    if (!f || !img) return;
                    const r = new FileReader();
                    r.onload = (e) => {
                        img.src = e.target.result;
                        img.classList.remove('hidden');
                    };
                    r.readAsDataURL(f);
                });
                updateTotal();
            });
        })();
    </script>
@endpush
