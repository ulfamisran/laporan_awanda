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
        @if ($errors->has('order'))
            <div class="mb-4 rounded-lg border px-3 py-2 text-sm text-red-700" style="border-color:#fecaca;background:#fef2f2;">
                {{ $errors->first('order') }}
            </div>
        @endif
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Nomor order</th>
                        <th>Tanggal order</th>
                        <th>Jumlah barang</th>
                        <th>Input oleh</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $row)
                        <tr>
                            <td class="font-mono text-xs font-semibold">{{ $row->nomor_order }}</td>
                            <td>{{ $row->tanggal_order?->format('d/m/Y') }}</td>
                            <td>{{ number_format((float) $row->items_count, 0, ',', '.') }} item</td>
                            <td>{{ $row->creator?->name ?? '—' }}</td>
                            <td class="text-right">
                                <div class="inline-flex flex-wrap items-center justify-end gap-3">
                                    <a href="{{ route('stok.order.show', $row) }}" class="text-xs font-semibold" style="color:#1a4a6b;">Detail</a>
                                    <a href="{{ route('stok.order.edit', $row) }}" class="text-xs font-semibold" style="color:#d97706;">Update</a>
                                    <a href="{{ route('stok.order.cetak-nota', $row) }}" target="_blank" rel="noopener" class="text-xs font-semibold" style="color:#4a9b7a;">Cetak nota</a>
                                    <a href="{{ route('stok.order.cetak-nota-supplier', $row) }}" target="_blank" rel="noopener" class="text-xs font-semibold" style="color:#0f766e;">Cetak nota supplier</a>
                                    <a href="{{ route('stok.order.cetak-spm', $row) }}" target="_blank" rel="noopener" class="text-xs font-semibold" style="color:#1d4ed8;">Cetak SPM</a>
                                    <form method="POST" action="{{ route('stok.order.destroy', $row) }}" class="form-hapus-order inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold" style="color:#c0392b;">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-sm" style="color:#7fa8c9;">Belum ada order barang.</td>
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

@push('scripts')
    <script>
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.classList.contains('form-hapus-order')) return;
            e.preventDefault();
            if (typeof window.mbgConfirmDelete !== 'function') {
                form.submit();
                return;
            }
            window
                .mbgConfirmDelete({
                    title: 'Hapus order?',
                    text: 'Data order dan itemnya akan dihapus permanen.',
                    confirmText: 'Ya, hapus',
                })
                .then(function (r) {
                    if (r.isConfirmed) form.submit();
                });
        });
        if (window.lucide) lucide.createIcons();
    </script>
@endpush
