@extends('layouts.app')

@section('title', $pageTitle ?? 'Buat Order Barang Mobile')

@section('content')
    @php
        $initialItemsForJs = collect($initialItems ?? [])->values();
    @endphp

    <div class="mx-auto w-full max-w-2xl px-3 pb-20 sm:px-4">
        <a href="{{ route('stok.order.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title mt-2">{{ $heading ?? 'Buat order barang via HP' }}</h2>
        <p class="inst-form-lead text-sm" style="color:#4a6b7f;">Input cepat order barang versi mobile. Setelah simpan, data masuk ke daftar order periode aktif.</p>
        <p class="mt-2 text-sm" style="color:#4a6b7f;">Nomor order: <span class="font-mono font-semibold">{{ $previewNomorOrder }}</span> (otomatis saat simpan)</p>
        @include('components.periode-aktif-badge')

        @if (session('success'))
            <div class="mt-4 rounded-xl border px-3 py-2 text-sm" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ $formAction ?? route('stok.order.store') }}" class="mt-4 space-y-4" id="form-order-mobile">
            @csrf
            <input type="hidden" name="mobile" value="1">

            <div class="rounded-2xl border bg-white p-4 shadow-sm" style="border-color:#d4e8f4;">
                <label for="tanggal_order" class="inst-label">Tanggal pencatatan</label>
                <input type="date" name="tanggal_order" id="tanggal_order" class="inst-input mt-1" required value="{{ $tanggalOrder ?? old('tanggal_order', now()->toDateString()) }}">
            </div>

            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold" style="color:#1a4a6b;">Daftar barang order</h3>
                    <p class="text-xs" style="color:#7fa8c9;">Satu kartu untuk satu barang.</p>
                </div>
                <button type="button" class="inst-btn-outline text-sm" id="btn-add-item-mobile">+ Tambah</button>
            </div>

            @error('items')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror

            <div class="space-y-4" id="order-items-mobile"></div>

            <div class="sticky bottom-3 z-10 rounded-2xl border bg-white/95 p-3 shadow-lg backdrop-blur" style="border-color:#d4e8f4;">
                <button type="submit" class="inst-btn-primary w-full">{{ $submitLabel ?? 'Simpan order' }}</button>
            </div>
        </form>
    </div>

    <template id="tpl-order-item-mobile">
        <div class="rounded-2xl border bg-white p-4 shadow-sm" style="border-color:#d4e8f4;" data-order-card>
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Barang <span class="item-number"></span></h3>
                <button type="button" class="text-xs font-semibold text-red-600 btn-remove-item">Hapus</button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="inst-label">Nama barang</label>
                    <input type="text" class="inst-input mt-1" data-name="nama_barang" placeholder="Contoh: Beras" required>
                </div>
                <div>
                    <label class="inst-label">Harga</label>
                    <input type="text" class="inst-input mt-1 harga-input font-mono" data-name="harga_barang" placeholder="Contoh: 15000" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="inst-label">Jumlah</label>
                        <input type="number" step="0.01" min="0.01" class="inst-input mt-1" data-name="jumlah_barang" placeholder="0" required>
                    </div>
                    <div>
                        <label class="inst-label">Satuan</label>
                        <input type="text" class="inst-input mt-1" data-name="satuan_barang" placeholder="Kg, pcs, box" required>
                    </div>
                </div>
                <div>
                    <label class="inst-label">Supplier</label>
                    <select class="inst-select mt-1" data-name="supplier_nama">
                        <option value="">— Tanpa supplier —</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->nama_supplier }}">{{ $supplier->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="inst-label">Pemakaian (hari)</label>
                    <input type="number" min="0" max="3650" step="1" class="inst-input mt-1" data-name="jumlah_hari_pemakaian" value="0" required>
                </div>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        (function () {
            const initialItems = @json($initialItemsForJs);
            const container = document.getElementById('order-items-mobile');
            const template = document.getElementById('tpl-order-item-mobile');
            let rowIndex = 0;

            function rupiahRaw(v) {
                return String(v || '').replace(/\D/g, '');
            }

            function formatDigits(v) {
                const d = rupiahRaw(v);
                if (!d) return '';
                return d.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function updateNumbers() {
                Array.from(container.querySelectorAll('[data-order-card]')).forEach(function (card, idx) {
                    const no = card.querySelector('.item-number');
                    if (no) no.textContent = String(idx + 1);
                });
            }

            function buildName(idx, field) {
                return 'items[' + idx + '][' + field + ']';
            }

            function addCard(data) {
                const node = template.content.firstElementChild.cloneNode(true);
                const idx = rowIndex++;

                node.querySelectorAll('[data-name]').forEach(function (input) {
                    const field = input.getAttribute('data-name');
                    input.name = buildName(idx, field);
                    if (data && Object.prototype.hasOwnProperty.call(data, field)) {
                        input.value = data[field] ?? '';
                    }
                });

                const hargaInput = node.querySelector('.harga-input');
                hargaInput?.addEventListener('input', function () {
                    this.value = formatDigits(this.value);
                });

                node.querySelector('.btn-remove-item')?.addEventListener('click', function () {
                    node.remove();
                    updateNumbers();
                });

                container.appendChild(node);
                updateNumbers();
            }

            document.getElementById('btn-add-item-mobile')?.addEventListener('click', function () {
                addCard({
                    nama_barang: '',
                    harga_barang: '',
                    jumlah_barang: '',
                    satuan_barang: '',
                    supplier_nama: '',
                    jumlah_hari_pemakaian: 0,
                });
            });

            if (Array.isArray(initialItems) && initialItems.length) {
                initialItems.forEach(addCard);
            } else {
                document.getElementById('btn-add-item-mobile')?.click();
            }

            document.getElementById('form-order-mobile')?.addEventListener('submit', function () {
                this.querySelectorAll('.harga-input').forEach(function (el) {
                    el.value = rupiahRaw(el.value);
                });
            });
        })();
    </script>
@endpush
