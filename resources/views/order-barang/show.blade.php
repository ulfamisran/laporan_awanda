@extends('layouts.app')

@section('title', 'Detail Order Barang')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Detail order barang</h2>
            <p class="inst-form-lead text-sm font-mono" style="color:#4a6b7f;">{{ $order->nomor_order }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('stok.order.cetak-nota', $order) }}" target="_blank" rel="noopener" class="inst-btn-outline">Cetak nota</a>
            <a href="{{ route('stok.order.cetak-nota-supplier', $order) }}" target="_blank" rel="noopener" class="inst-btn-outline">Cetak nota supplier</a>
            <a href="{{ route('stok.order.cetak-spm', $order) }}" target="_blank" rel="noopener" class="inst-btn-outline">Cetak SPM</a>
            <a href="{{ route('stok.order.edit', $order) }}" class="inst-btn-outline">Update</a>
            <form method="POST" action="{{ route('stok.order.destroy', $order) }}" class="form-hapus-order inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inst-btn-outline" style="color:#c0392b;border-color:#f5c2c0;">Hapus</button>
            </form>
            <a href="{{ route('stok.order.index') }}" class="inst-btn-outline">Kembali</a>
        </div>
    </div>

    <div class="inst-panel p-6">
        <div class="mb-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
            <div><span class="font-semibold">Tanggal order:</span> {{ $order->tanggal_order?->format('d/m/Y') }}</div>
            <div><span class="font-semibold">Cabang:</span> {{ $order->profilMbg?->nama_dapur }}</div>
            <div><span class="font-semibold">Jumlah item:</span> {{ $order->items->count() }}</div>
        </div>

        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>Supplier</th>
                        <th>No. nota</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->nama_barang }}</td>
                            <td>{{ formatRupiah($item->harga_barang) }}</td>
                            <td class="font-mono">{{ number_format((float) $item->jumlah_barang, 2, ',', '.') }}</td>
                            <td>{{ $item->satuan_barang }}</td>
                            <td>{{ $item->supplier_nama ?? $item->supplier?->nama_supplier ?? '-' }}</td>
                            <td class="font-mono text-xs">{{ $item->nomor_nota ?? '—' }}</td>
                            <td>{{ formatRupiah((float) $item->harga_barang * (float) $item->jumlah_barang) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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
    </script>
@endpush
