@extends('layouts.app')

@section('title', 'Penerimaan Barang Mobile')

@section('content')
    <div class="mx-auto w-full max-w-2xl px-3 pb-8 sm:px-4">
        <a href="{{ route('stok.penerimaan.index') }}" class="inst-back">← Versi desktop</a>
        <h2 class="inst-form-title mt-2">Penerimaan Barang</h2>
        <p class="inst-form-lead text-sm" style="color:#4a6b7f;">Halaman khusus input via HP. Pilih item order yang belum diterima.</p>
        @include('components.periode-aktif-badge')

        @if (session('success'))
            <div class="mt-4 rounded-xl border px-3 py-2 text-sm" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-4">
            <input type="text" id="search-pending-mobile" class="inst-input" placeholder="Cari barang, supplier, nomor order..." autocomplete="off">
        </div>

        <div class="mt-4 space-y-3" id="pending-list-mobile">
            @forelse ($pendingItems as $item)
                @php
                    $diterima = (float) ($item->qty_diterima ?? 0);
                    $sisa = max((float) $item->jumlah_barang - $diterima, 0);
                @endphp
                <a href="{{ route('stok.penerimaan.create-mobile', $item) }}"
                    class="pending-card block rounded-2xl border bg-white p-4 shadow-sm transition hover:shadow-md"
                    style="border-color:#d4e8f4;">
                    <div class="mb-2 font-mono text-xs font-semibold" style="color:#1a4a6b;">{{ $item->orderBarang?->nomor_order }}</div>
                    <div class="text-base font-bold" style="color:#0f172a;">{{ $item->nama_barang }}</div>
                    <div class="mt-1 text-sm" style="color:#64748b;">{{ $item->supplier_nama ?? $item->supplier?->nama_supplier ?? '-' }}</div>
                    <div class="mt-3 grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="rounded-lg p-2" style="background:#f8fbfd;">
                            <div class="font-semibold uppercase" style="color:#7fa8c9;">Order</div>
                            <div class="mt-1 font-mono font-bold">{{ number_format((float) $item->jumlah_barang, 2, ',', '.') }}</div>
                        </div>
                        <div class="rounded-lg p-2" style="background:#f8fbfd;">
                            <div class="font-semibold uppercase" style="color:#7fa8c9;">Diterima</div>
                            <div class="mt-1 font-mono font-bold">{{ number_format($diterima, 2, ',', '.') }}</div>
                        </div>
                        <div class="rounded-lg p-2" style="background:#fff7ed;">
                            <div class="font-semibold uppercase" style="color:#c2410c;">Sisa</div>
                            <div class="mt-1 font-mono font-bold" style="color:#c2410c;">{{ number_format($sisa, 2, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="mt-3 text-center text-sm font-semibold" style="color:#0f766e;">Terima barang →</div>
                </a>
            @empty
                <div class="rounded-2xl border p-8 text-center text-sm" style="border-color:#d4e8f4;color:#7fa8c9;">
                    Semua item order sudah diterima.
                </div>
            @endforelse
            <div id="pending-no-match-mobile" class="hidden rounded-2xl border p-8 text-center text-sm" style="border-color:#d4e8f4;color:#7fa8c9;">
                Tidak ada data yang cocok.
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const input = document.getElementById('search-pending-mobile');
            const cards = Array.from(document.querySelectorAll('.pending-card'));
            const noMatch = document.getElementById('pending-no-match-mobile');
            if (!input || cards.length === 0) return;

            input.addEventListener('input', function () {
                const keyword = String(this.value || '').trim().toLowerCase();
                let visible = 0;
                cards.forEach(function (card) {
                    const show = keyword === '' || card.textContent.toLowerCase().includes(keyword);
                    card.classList.toggle('hidden', !show);
                    if (show) visible++;
                });
                if (noMatch) noMatch.classList.toggle('hidden', visible > 0);
            });
        })();
    </script>
@endpush
