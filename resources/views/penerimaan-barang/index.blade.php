@extends('layouts.app')

@section('title', 'Penerimaan Barang')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Penerimaan barang</h2>
            <p class="inst-page-desc">Proses terima barang berdasarkan order barang periode aktif.</p>
        </div>
        <a href="{{ route('stok.penerimaan.report-pdf') }}" target="_blank" rel="noopener" class="inst-btn-secondary text-sm">Laporan PDF</a>
    </div>

    <div class="inst-panel mb-6 overflow-hidden p-4 sm:p-6">
        <h3 class="mb-4 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Order barang belum diterima / belum lengkap</h3>
        <div class="mb-4">
            <input
                type="text"
                id="search-pending-items"
                class="inst-input"
                placeholder="Cari nomor order, barang, supplier..."
                autocomplete="off"
            >
        </div>
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Nomor order</th>
                        <th>Tanggal order</th>
                        <th>Barang</th>
                        <th>Supplier</th>
                        <th>Qty order</th>
                        <th>Qty diterima</th>
                        <th>Sisa</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="pending-items-body">
                    @forelse ($pendingItems as $item)
                        @php
                            $diterima = (float) ($item->qty_diterima ?? 0);
                            $sisa = max((float) $item->jumlah_barang - $diterima, 0);
                        @endphp
                        <tr class="pending-item-row">
                            <td class="font-mono text-xs font-semibold">{{ $item->orderBarang?->nomor_order }}</td>
                            <td>{{ $item->orderBarang?->tanggal_order?->format('d/m/Y') }}</td>
                            <td>{{ $item->nama_barang }}</td>
                            <td>{{ $item->supplier?->nama_supplier ?? '-' }}</td>
                            <td class="font-mono">{{ number_format((float) $item->jumlah_barang, 2, ',', '.') }} {{ $item->satuan_barang }}</td>
                            <td class="font-mono">{{ number_format($diterima, 2, ',', '.') }} {{ $item->satuan_barang }}</td>
                            <td class="font-mono font-semibold" style="color:#c0392b;">{{ number_format($sisa, 2, ',', '.') }} {{ $item->satuan_barang }}</td>
                            <td class="text-right">
                                <a href="{{ route('stok.penerimaan.create', $item) }}" class="text-xs font-semibold" style="color:#1a4a6b;">Terima barang</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="pending-empty-row">
                            <td colspan="8" class="py-8 text-center text-sm" style="color:#7fa8c9;">Semua item order sudah diterima.</td>
                        </tr>
                    @endforelse
                    <tr class="pending-no-match-row hidden">
                        <td colspan="8" class="py-8 text-center text-sm" style="color:#7fa8c9;">Tidak ada data yang cocok.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <h3 class="mb-4 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Riwayat barang sudah diterima</h3>
        <div class="mb-4">
            <input
                type="text"
                id="search-received-items"
                class="inst-input"
                placeholder="Cari kode masuk, nomor order, barang, kondisi..."
                autocomplete="off"
            >
        </div>
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Tanggal terima</th>
                        <th>Kode masuk</th>
                        <th>Nomor order</th>
                        <th>Barang</th>
                        <th>Qty diterima</th>
                        <th>Kondisi</th>
                        <th>Input oleh</th>
                        <th>Bukti</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="received-items-body">
                    @forelse ($receivedRows as $row)
                        <tr class="received-item-row">
                            <td>{{ $row->tanggal?->format('d/m/Y') }}</td>
                            <td class="font-mono text-xs">{{ $row->kode_transaksi }}</td>
                            <td class="font-mono text-xs">{{ $row->orderItem?->orderBarang?->nomor_order ?? '-' }}</td>
                            <td>{{ $row->barang?->nama_barang ?? $row->orderItem?->nama_barang ?? '-' }}</td>
                            <td class="font-mono">{{ number_format((float) $row->jumlah, 2, ',', '.') }} {{ $row->satuan }}</td>
                            <td>{{ $row->kondisi_penerimaan ?: '-' }}</td>
                            <td>{{ $row->creator?->name ?? '-' }}</td>
                            <td>
                                @if ($row->gambar_url)
                                    <a href="{{ $row->gambar_url }}" target="_blank" rel="noopener" class="text-xs font-semibold" style="color:#4a9b7a;">Lihat</a>
                                @else
                                    <span class="text-xs" style="color:#94a3b8;">-</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('stok.masuk.edit', $row) }}" class="inline-flex items-center rounded-md border px-2.5 py-1 text-xs font-semibold" style="border-color:#d4e8f4;color:#1a4a6b;background:#fff;">Ubah</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="received-empty-row">
                            <td colspan="9" class="py-8 text-center text-sm" style="color:#7fa8c9;">Belum ada data penerimaan barang.</td>
                        </tr>
                    @endforelse
                    <tr class="received-no-match-row hidden">
                        <td colspan="9" class="py-8 text-center text-sm" style="color:#7fa8c9;">Tidak ada data yang cocok.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if ($receivedRows->hasPages())
            <div class="mt-4 border-t pt-4" style="border-color:#e8f1f8;">
                {{ $receivedRows->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            function initTableSearch(config) {
                const input = document.getElementById(config.inputId);
                const rows = Array.from(document.querySelectorAll(config.rowSelector));
                const emptyRow = document.querySelector(config.emptyRowSelector);
                const noMatchRow = document.querySelector(config.noMatchRowSelector);
                if (!input || rows.length === 0) return;

                input.addEventListener('input', function () {
                    const keyword = String(this.value || '').trim().toLowerCase();
                    let visibleCount = 0;

                    rows.forEach((row) => {
                        const haystack = row.textContent.toLowerCase();
                        const visible = keyword === '' || haystack.includes(keyword);
                        row.classList.toggle('hidden', !visible);
                        if (visible) visibleCount++;
                    });

                    if (emptyRow) emptyRow.classList.add('hidden');
                    if (noMatchRow) noMatchRow.classList.toggle('hidden', visibleCount > 0);
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                initTableSearch({
                    inputId: 'search-pending-items',
                    rowSelector: '.pending-item-row',
                    emptyRowSelector: '.pending-empty-row',
                    noMatchRowSelector: '.pending-no-match-row',
                });

                initTableSearch({
                    inputId: 'search-received-items',
                    rowSelector: '.received-item-row',
                    emptyRowSelector: '.received-empty-row',
                    noMatchRowSelector: '.received-no-match-row',
                });
            });
        })();
    </script>
@endpush
