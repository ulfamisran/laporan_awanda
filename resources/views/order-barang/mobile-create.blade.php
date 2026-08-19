@extends('layouts.app')

@section('title', $pageTitle ?? 'Buat Order Barang Mobile')

@section('content')
    @php
        $initialItemsForJs = collect($initialItems ?? [])->values();
    @endphp

    @include('components.mobile-expand-accordion')

    <div class="mx-auto w-full max-w-2xl px-3 pb-20 sm:px-4">
        <a href="{{ route('stok.order.index') }}" class="inst-back">← Kembali</a>
        <h2 class="inst-form-title mt-2">{{ $heading ?? 'Buat order barang via HP' }}</h2>
        <p class="inst-form-lead text-sm" style="color:#4a6b7f;">Input cepat order barang versi mobile. Ketuk barang untuk membuka form; hanya satu form terbuka sekaligus.</p>
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
                    <p class="text-xs" style="color:#7fa8c9;">Ketuk kartu untuk isi / edit barang.</p>
                </div>
                <button type="button" class="inst-btn-outline text-sm" id="btn-add-item-mobile">+ Tambah</button>
            </div>

            @error('items')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror

            <div class="space-y-3" id="order-items-mobile"></div>

            <div class="sticky bottom-3 z-10 rounded-2xl border bg-white/95 p-3 shadow-lg backdrop-blur" style="border-color:#d4e8f4;">
                <button type="submit" class="inst-btn-primary w-full">{{ $submitLabel ?? 'Simpan order' }}</button>
            </div>
        </form>
    </div>

    <template id="tpl-order-item-mobile">
        <div class="mbg-expand-item" data-expand-item data-order-card>
            <div class="flex items-stretch" data-expand-header role="button" tabindex="0" aria-expanded="false">
                <button type="button" class="mbg-expand-trigger flex-1">
                    <span class="min-w-0 flex-1">
                        <span class="mbg-expand-title">Barang <span class="item-number"></span></span>
                        <span class="mbg-expand-summary item-summary">Belum diisi</span>
                    </span>
                    <svg class="mbg-expand-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <button type="button" class="mbg-no-expand px-3 text-xs font-semibold text-red-600 btn-remove-item">Hapus</button>
            </div>
            <div class="mbg-expand-body" data-expand-body hidden>
                <div class="space-y-4">
                    <div>
                        <label class="inst-label">Nama barang</label>
                        <input type="text" class="inst-input mt-1 summary-source" data-name="nama_barang" placeholder="Contoh: Beras" required>
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
            let accordion = null;

            function refreshAccordion(expandLast) {
                accordion = window.MbgMobileAccordion.initGroup(container, {
                    expandFirst: !expandLast && container.querySelectorAll('[data-order-card]').length === 1,
                });
                if (expandLast) {
                    const cards = container.querySelectorAll('[data-order-card]');
                    if (cards.length) accordion.expandItem(cards[cards.length - 1]);
                }
            }

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

            function addCard(data, options) {
                options = options || {};
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

                const summaryEl = node.querySelector('.item-summary');
                const namaInput = node.querySelector('[data-name="nama_barang"]');
                window.MbgMobileAccordion.bindSummary(namaInput, summaryEl, 'Belum diisi');

                node.querySelector('.btn-remove-item')?.addEventListener('click', function () {
                    node.remove();
                    updateNumbers();
                    refreshAccordion(false);
                });

                container.appendChild(node);
                updateNumbers();

                if (options.refresh !== false) {
                    refreshAccordion(options.expand !== false);
                }

                return node;
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
                initialItems.forEach(function (item) {
                    addCard(item, { refresh: false });
                });
                refreshAccordion(false);
                const first = container.querySelector('[data-order-card]');
                if (first) accordion?.expandItem(first);
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
