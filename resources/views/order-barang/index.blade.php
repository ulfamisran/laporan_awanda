@extends('layouts.app')

@section('title', 'Order Barang')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Order barang</h2>
            <p class="inst-page-desc">Permohonan barang berdasarkan periode aktif.</p>
        </div>
        <a href="{{ route('stok.order.create') }}" class="inst-btn-primary">
            <i data-lucide="plus" class="h-[18px] w-[18px]"></i>
            Buat order
        </a>
    </div>

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Nomor order</th>
                        <th>Tanggal order</th>
                        <th>Jumlah barang</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $row)
                        <tr>
                            <td class="font-mono text-xs font-semibold">{{ $row->nomor_order }}</td>
                            <td>{{ $row->tanggal_order?->format('d/m/Y') }}</td>
                            <td>{{ number_format((float) $row->items_count, 0, ',', '.') }} item</td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('stok.order.show', $row) }}" class="text-xs font-semibold" style="color:#1a4a6b;">Detail</a>
                                    <a href="{{ route('stok.order.cetak-nota', $row) }}" target="_blank" rel="noopener" class="text-xs font-semibold" style="color:#4a9b7a;">Cetak nota</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-sm" style="color:#7fa8c9;">Belum ada order barang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="mt-4 border-t pt-4" style="border-color:#e8f1f8;">
                {{ $items->links() }}
            </div>
        @endif
    </div>
@endsection
