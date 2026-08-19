@extends('layouts.app')

@section('title', 'Penerimaan Barang Mobile')

@section('content')
    @include('components.mobile-expand-accordion')

    <div class="mx-auto w-full max-w-2xl px-3 pb-20 sm:px-4">
        <a href="{{ route('stok.penerimaan.index') }}" class="inst-back">← Versi desktop</a>
        <h2 class="inst-form-title mt-2">Penerimaan Barang</h2>
        <p class="inst-form-lead text-sm" style="color:#4a6b7f;">Cari nomor order terlebih dahulu, lalu ketuk barang untuk input penerimaan.</p>
        @include('components.periode-aktif-badge')

        @if (session('success'))
            <div class="mt-4 rounded-xl border px-3 py-2 text-sm" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-4 rounded-2xl border bg-white p-4 shadow-sm" style="border-color:#d4e8f4;">
            <label for="lookup-nomor-order" class="inst-label">Nomor order</label>
            <div class="mt-1 flex gap-2">
                <input type="text" id="lookup-nomor-order" class="inst-input flex-1 font-mono" list="order-numbers-list" placeholder="Ketik atau pilih nomor order..." autocomplete="off" value="{{ request('nomor_order') }}">
                <button type="button" id="btn-lookup-order" class="inst-btn-primary shrink-0 px-4">Cari</button>
            </div>
            <datalist id="order-numbers-list">
                @foreach ($orderNumbers as $nomor)
                    <option value="{{ $nomor }}"></option>
                @endforeach
            </datalist>
            @if ($orderNumbers->isEmpty())
                <p class="mt-2 text-xs" style="color:#7fa8c9;">Tidak ada order dengan barang belum diterima penuh.</p>
            @else
                <p class="mt-2 text-xs" style="color:#7fa8c9;">{{ $orderNumbers->count() }} nomor order menunggu penerimaan.</p>
            @endif
        </div>

        <div id="order-result-section" class="mt-4 hidden">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h3 class="text-base font-bold" style="color:#1a4a6b;">Barang order</h3>
                <span id="order-result-label" class="font-mono text-xs font-semibold" style="color:#1a4a6b;"></span>
            </div>

            <div class="space-y-3" id="pending-items-mobile">
                @foreach ($pendingItems as $item)
                    @php
                        $diterima = (float) ($item->qty_diterima ?? 0);
                        $sisa = max((float) $item->jumlah_barang - $diterima, 0);
                        $qtyOrder = (float) $item->jumlah_barang;
                        $nomorOrder = $item->orderBarang?->nomor_order ?? '';
                    @endphp
                    <div class="mbg-expand-item pending-item-card hidden"
                        data-expand-item
                        data-nomor-order="{{ $nomorOrder }}"
                        data-item-id="{{ $item->id }}">
                        <div data-expand-header role="button" tabindex="0" aria-expanded="false">
                            <button type="button" class="mbg-expand-trigger">
                                <span class="min-w-0 flex-1">
                                    <span class="mbg-expand-title">{{ $item->nama_barang }}</span>
                                    <span class="mbg-expand-summary">
                                        {{ $item->supplier_nama ?? $item->supplier?->nama_supplier ?? '-' }}
                                        · Sisa {{ number_format($sisa, 2, ',', '.') }} {{ $item->satuan_barang }}
                                    </span>
                                </span>
                                <svg class="mbg-expand-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                            </button>
                        </div>
                        <div class="mbg-expand-body" data-expand-body hidden>
                            <div class="mb-3 rounded-xl border p-3 text-sm" style="border-color:#d4e8f4;background:#f8fbfd;">
                                <p><span class="font-semibold">Supplier:</span> {{ $item->supplier_nama ?? $item->supplier?->nama_supplier ?? '-' }}</p>
                                <p class="mt-1"><span class="font-semibold">Qty order:</span> {{ number_format($qtyOrder, 2, ',', '.') }} {{ $item->satuan_barang }}</p>
                                <p class="mt-1"><span class="font-semibold">Sudah diterima:</span> {{ number_format($diterima, 2, ',', '.') }} {{ $item->satuan_barang }}</p>
                                <p class="mt-1 font-semibold" style="color:#c2410c;">Sisa: {{ number_format($sisa, 2, ',', '.') }} {{ $item->satuan_barang }}</p>
                            </div>

                            <form method="POST" action="{{ route('stok.penerimaan.store', $item) }}" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <input type="hidden" name="mobile" value="1">
                                <input type="hidden" name="nomor_order" value="{{ $nomorOrder }}">

                                <div>
                                    <label class="inst-label" for="tanggal_{{ $item->id }}">Tanggal penerimaan</label>
                                    <input type="date" name="tanggal" id="tanggal_{{ $item->id }}" class="inst-input mt-1" required value="{{ old('tanggal', now()->toDateString()) }}">
                                </div>

                                <div>
                                    <label class="inst-label" for="qty_{{ $item->id }}">Qty diterima</label>
                                    <input type="number" step="0.01" min="{{ $qtyOrder }}" name="qty_diterima" id="qty_{{ $item->id }}" class="inst-input mt-1 font-mono text-lg" required value="{{ old('qty_diterima', number_format($qtyOrder, 2, '.', '')) }}">
                                    <p class="mt-1 text-xs" style="color:#7fa8c9;">Minimal {{ number_format($qtyOrder, 2, ',', '.') }} {{ $item->satuan_barang }}</p>
                                </div>

                                <div>
                                    <label class="inst-label" for="kondisi_{{ $item->id }}">Kondisi barang</label>
                                    <input type="text" name="kondisi_penerimaan" id="kondisi_{{ $item->id }}" class="inst-input mt-1" required value="{{ old('kondisi_penerimaan', 'Baik') }}" placeholder="Contoh: Baik, segel utuh">
                                </div>

                                <div>
                                    <label class="inst-label" for="ket_{{ $item->id }}">Keterangan</label>
                                    <textarea name="keterangan" id="ket_{{ $item->id }}" rows="2" class="inst-input mt-1" maxlength="5000">{{ old('keterangan') }}</textarea>
                                </div>

                                <div>
                                    <label class="inst-label" for="gambar_{{ $item->id }}">Foto bukti penerimaan</label>
                                    <input type="file" name="gambar" id="gambar_{{ $item->id }}" accept="image/jpeg,image/png,image/webp" capture="environment" class="inst-input mt-1">
                                    <p class="mt-1 text-xs" style="color:#7fa8c9;">Bisa langsung ambil foto dari kamera HP.</p>
                                </div>

                                <button type="submit" class="inst-btn-primary w-full">Simpan penerimaan</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="order-no-items" class="hidden rounded-2xl border p-8 text-center text-sm" style="border-color:#d4e8f4;color:#7fa8c9;">
                Tidak ada barang menunggu penerimaan untuk nomor order ini.
            </div>
        </div>

        @if ($orderNumbers->isEmpty())
            <div class="mt-4 rounded-2xl border p-8 text-center text-sm" style="border-color:#d4e8f4;color:#7fa8c9;">
                Semua item order sudah diterima.
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const lookupInput = document.getElementById('lookup-nomor-order');
            const lookupBtn = document.getElementById('btn-lookup-order');
            const resultSection = document.getElementById('order-result-section');
            const resultLabel = document.getElementById('order-result-label');
            const noItems = document.getElementById('order-no-items');
            const itemsContainer = document.getElementById('pending-items-mobile');
            const allCards = Array.from(document.querySelectorAll('.pending-item-card'));
            let accordion = null;

            if (itemsContainer && allCards.length) {
                accordion = window.MbgMobileAccordion.initGroup(itemsContainer, { expandFirst: false });
            }

            function normalizeOrderNo(value) {
                return String(value || '').trim().toLowerCase();
            }

            function showOrderItems() {
                const keyword = normalizeOrderNo(lookupInput?.value);
                if (!keyword) {
                    resultSection?.classList.add('hidden');
                    return;
                }

                let visible = 0;
                allCards.forEach(function (card) {
                    const nomor = normalizeOrderNo(card.getAttribute('data-nomor-order'));
                    const show = nomor === keyword || nomor.includes(keyword);
                    card.classList.toggle('hidden', !show);
                    if (!show) {
                        card.classList.remove('is-expanded');
                        const body = card.querySelector('[data-expand-body]');
                        if (body) body.hidden = true;
                    }
                    if (show) visible++;
                });

                resultSection?.classList.remove('hidden');
                if (resultLabel) resultLabel.textContent = lookupInput.value.trim();
                if (noItems) noItems.classList.toggle('hidden', visible > 0);

                if (visible > 0) {
                    accordion?.collapseAll();
                    const firstVisible = allCards.find(function (card) {
                        return !card.classList.contains('hidden');
                    });
                    if (firstVisible) accordion?.expandItem(firstVisible);
                }
            }

            lookupBtn?.addEventListener('click', showOrderItems);
            lookupInput?.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    showOrderItems();
                }
            });
            lookupInput?.addEventListener('change', showOrderItems);

            if (lookupInput?.value.trim()) {
                showOrderItems();
            }
        })();
    </script>
@endpush
