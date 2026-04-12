@extends('layouts.app')

@section('title', 'Transaksi Keuangan')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Transaksi</h2>
            <p class="inst-page-desc">Gabungan dana masuk dan dana keluar periode aktif: debet untuk masuk, kredit untuk keluar.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('keuangan.masuk.create') }}" class="inst-btn-outline shrink-0">Tambah dana masuk</a>
            <a href="{{ route('keuangan.keluar.create') }}" class="inst-btn-outline shrink-0">Tambah dana keluar</a>
        </div>
    </div>

    <div class="inst-panel inst-datatable-wrap overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table id="tabel-keuangan-transaksi" class="w-full text-sm" style="width:100%">
                <thead>
                    <tr>
                        <th class="hidden">trx_key</th>
                        <th class="hidden">arah</th>
                        <th class="hidden">ref_id</th>
                        <th class="hidden">sort_ts</th>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nomor bukti</th>
                        <th>Uraian transaksi</th>
                        <th class="text-right">Debet</th>
                        <th class="text-right">Kredit</th>
                        <th>Jenis Buku Pembantu</th>
                        <th>Jenis Buku Kas</th>
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
            jQuery('#tabel-keuangan-transaksi').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('keuangan.transaksi.data')),
                    headers: { 'X-CSRF-TOKEN': csrf },
                },
                order: [
                    [5, 'desc'],
                    [3, 'desc'],
                ],
                columns: [
                    { data: 'trx_key', name: 'trx_key', visible: false, searchable: false, orderable: false },
                    { data: 'arah', name: 'arah', visible: false, searchable: false, orderable: false },
                    { data: 'ref_id', name: 'ref_id', visible: false, searchable: false, orderable: false },
                    { data: 'sort_ts', name: 'sort_ts', visible: false, searchable: false },
                    { data: 'DT_RowIndex', orderable: false, searchable: false, width: '48px' },
                    { data: 'tanggal', name: 'tanggal' },
                    { data: 'nomor_bukti', name: 'nomor_bukti' },
                    { data: 'uraian_transaksi', name: 'uraian_transaksi' },
                    { data: 'debet', name: 'debet', orderable: true, searchable: false, className: 'text-right' },
                    { data: 'kredit', name: 'kredit', orderable: true, searchable: false, className: 'text-right' },
                    { data: 'jenis_dana_label', name: 'jenis_dana_label' },
                    { data: 'kas_label', name: 'kas_label' },
                    { data: 'aksi', orderable: false, searchable: false, className: 'text-right' },
                ],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json' },
            });
            if (window.lucide) lucide.createIcons();
        })();
    </script>
@endpush
