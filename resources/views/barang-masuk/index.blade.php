@extends('layouts.app')

@section('title', 'Barang Masuk')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Barang masuk</h2>
            <p class="inst-page-desc">Transaksi masuk cabang MBG dengan kode otomatis.</p>
        </div>
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <a href="{{ route('stok.masuk.export-pdf') }}" class="inst-btn-secondary text-sm" target="_blank" rel="noopener">Export PDF</a>
            <a href="{{ route('stok.masuk.export-word') }}" class="inst-btn-secondary text-sm">Export Word</a>
            <a href="{{ route('stok.masuk.create') }}" class="inst-btn-primary">
                <i data-lucide="plus" class="h-[18px] w-[18px]"></i>
                Tambah transaksi
            </a>
        </div>
    </div>

    <div class="inst-panel inst-datatable-wrap overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table id="tabel-masuk" class="w-full text-sm" style="width:100%">
                <thead>
                    <tr>
                        <th class="hidden">Id</th>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Barang</th>
                        <th>Cabang</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>Sumber</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const table = jQuery('#tabel-masuk').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('stok.masuk.data')),
                    headers: { 'X-CSRF-TOKEN': csrf },
                },
                order: [[0, 'desc']],
                columns: [
                    { data: 'id', name: 'barang_masuk.id', visible: false, searchable: false },
                    { data: 'DT_RowIndex', orderable: false, searchable: false, width: '48px' },
                    { data: 'kode_transaksi', name: 'barang_masuk.kode_transaksi' },
                    { data: 'tanggal', name: 'barang_masuk.tanggal' },
                    { data: 'barang_cell', orderable: false, searchable: false },
                    { data: 'dapur_cell', orderable: false, searchable: false },
                    { data: 'jumlah_cell', orderable: false, searchable: false },
                    { data: 'total_cell', name: 'barang_masuk.total_harga', searchable: false },
                    { data: 'sumber_label', orderable: false, searchable: false },
                    { data: 'aksi', orderable: false, searchable: false, className: 'text-right' },
                ],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json' },
            });
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!(form instanceof HTMLFormElement) || !form.classList.contains('form-hapus-stok')) return;
                e.preventDefault();
                window.mbgConfirmDelete?.({ title: 'Hapus transaksi?', text: 'Data akan dihapus permanen.', confirmText: 'Ya, hapus' }).then((r) => {
                    if (r.isConfirmed) form.submit();
                }) || form.submit();
            });
            if (window.lucide) lucide.createIcons();
        })();
    </script>
@endpush
