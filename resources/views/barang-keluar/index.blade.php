@extends('layouts.app')

@section('title', 'Barang Keluar')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Barang keluar</h2>
            <p class="inst-page-desc">Transaksi keluar dengan validasi stok tidak boleh minus.</p>
        </div>
        <a href="{{ route('stok.keluar.create') }}" class="inst-btn-primary shrink-0">
            <i data-lucide="plus" class="h-[18px] w-[18px]"></i>
            Tambah transaksi
        </a>
    </div>

    <div class="inst-panel inst-datatable-wrap overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table id="tabel-keluar" class="w-full text-sm" style="width:100%">
                <thead>
                    <tr>
                        <th class="hidden">Id</th>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Barang</th>
                        <th>Cabang</th>
                        <th>Jumlah</th>
                        <th>Tujuan</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const table = jQuery('#tabel-keluar').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('stok.keluar.data')),
                    headers: { 'X-CSRF-TOKEN': csrf },
                },
                order: [[0, 'desc']],
                columns: [
                    { data: 'id', name: 'barang_keluar.id', visible: false, searchable: false },
                    { data: 'DT_RowIndex', orderable: false, searchable: false, width: '48px' },
                    { data: 'kode_transaksi', name: 'barang_keluar.kode_transaksi' },
                    { data: 'tanggal', name: 'barang_keluar.tanggal' },
                    { data: 'barang_cell', orderable: false, searchable: false },
                    { data: 'dapur_cell', orderable: false, searchable: false },
                    { data: 'jumlah_cell', orderable: false, searchable: false },
                    { data: 'tujuan_label', orderable: false, searchable: false },
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
