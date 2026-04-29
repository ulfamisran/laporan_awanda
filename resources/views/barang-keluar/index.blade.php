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
                        <th>Jumlah</th>
                        <th>Tujuan</th>
                        <th>Input oleh</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        #tabel-keluar_wrapper .dataTables_length {
            float: left !important;
            margin-bottom: 0.5rem;
        }

        #tabel-keluar_wrapper .dataTables_filter {
            float: right !important;
            text-align: right !important;
            margin-bottom: 0.5rem;
        }

        #tabel-keluar_wrapper .dataTables_info {
            float: left !important;
            clear: both;
            padding-top: 0.65rem !important;
        }

        #tabel-keluar_wrapper .dataTables_paginate {
            float: right !important;
            text-align: right !important;
            padding-top: 0.35rem !important;
        }

        #tabel-keluar_wrapper .dataTables_length,
        #tabel-keluar_wrapper .dataTables_filter {
            font-size: 0.8125rem !important;
        }

        #tabel-keluar_wrapper .dataTables_info,
        #tabel-keluar_wrapper .dataTables_paginate,
        #tabel-keluar_wrapper .dataTables_paginate .paginate_button {
            font-size: 0.75rem !important;
        }

        #tabel-keluar_wrapper .dataTables_paginate .paginate_button.previous,
        #tabel-keluar_wrapper .dataTables_paginate .paginate_button.next {
            border: 1px solid #d4e8f4 !important;
            border-radius: 0.375rem !important;
            background: #ffffff !important;
            color: #1a4a6b !important;
            padding: 0.2rem 0.6rem !important;
        }

        #tabel-keluar_wrapper .dataTables_paginate .paginate_button.previous:hover,
        #tabel-keluar_wrapper .dataTables_paginate .paginate_button.next:hover {
            background: #f0f6fb !important;
            border-color: #c5dce8 !important;
        }

        #tabel-keluar_wrapper .dataTables_paginate .paginate_button.current {
            border: none !important;
            background: transparent !important;
            color: #7fa8c9 !important;
            box-shadow: none !important;
            pointer-events: none;
            cursor: default !important;
            padding: 0.2rem 0.5rem !important;
        }
    </style>
@endpush

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
                    { data: 'jumlah_cell', orderable: false, searchable: false },
                    { data: 'tujuan_label', orderable: false, searchable: false },
                    { data: 'creator_name', orderable: false, searchable: false },
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
