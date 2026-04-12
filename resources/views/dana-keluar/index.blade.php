@extends('layouts.app')

@section('title', 'Dana Keluar')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Dana keluar</h2>
            <p class="inst-page-desc">Validasi saldo tidak boleh negatif.</p>
        </div>
        <a href="{{ route('keuangan.keluar.create') }}" class="inst-btn-primary shrink-0">Tambah transaksi</a>
    </div>

    <div class="mb-4 grid gap-4 sm:grid-cols-3">
        <div class="stat-card rounded-2xl p-4" style="background:#fff;border-color:#d4e8f4;">
            <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Total keluar bulan ini</p>
            <p class="mt-1 text-xl font-bold" style="color:#c0392b;">{{ formatRupiah($totalBulanIni) }}</p>
        </div>
    </div>

    <div class="inst-panel inst-datatable-wrap overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table id="tabel-dana-keluar" class="w-full text-sm" style="width:100%">
                <thead>
                    <tr>
                        <th class="hidden">Id</th>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Jenis Buku Pembantu</th>
                        <th>Jenis Buku Kas</th>
                        <th>No. bukti</th>
                        <th>Uraian transaksi</th>
                        <th>Jumlah</th>
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
            const table = jQuery('#tabel-dana-keluar').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('keuangan.keluar.data')),
                    headers: { 'X-CSRF-TOKEN': csrf },
                },
                order: [[0, 'desc']],
                columns: [
                    { data: 'id', name: 'dana_keluar.id', visible: false, searchable: false },
                    { data: 'DT_RowIndex', orderable: false, searchable: false, width: '48px' },
                    { data: 'kode_transaksi', name: 'dana_keluar.kode_transaksi' },
                    { data: 'tanggal', name: 'dana_keluar.tanggal' },
                    { data: 'jenis_dana_cell', orderable: false, searchable: false },
                    { data: 'kas_cell', orderable: false, searchable: false },
                    { data: 'nomor_bukti', name: 'dana_keluar.nomor_bukti' },
                    { data: 'uraian_transaksi', name: 'dana_keluar.uraian_transaksi' },
                    { data: 'jumlah_cell', orderable: false, searchable: false },
                    { data: 'aksi', orderable: false, searchable: false, className: 'text-right' },
                ],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json' },
            });
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!(form instanceof HTMLFormElement) || !form.classList.contains('form-hapus-keuangan')) return;
                e.preventDefault();
                window.mbgConfirmDelete?.({ title: 'Hapus transaksi?', text: 'Hanya super admin.', confirmText: 'Ya, hapus' }).then((r) => {
                    if (r.isConfirmed) form.submit();
                }) || form.submit();
            });
            if (window.lucide) lucide.createIcons();
        })();
    </script>
@endpush
