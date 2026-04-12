@extends('layouts.app')

@section('title', 'Barang')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Master barang</h2>
            <p class="inst-page-desc">Kelola barang, harga, stok minimum, dan status.</p>
        </div>
        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_pusat', 'admin']))
            <a href="{{ route('master.barang.create') }}" class="inst-btn-primary shrink-0">
                <i data-lucide="plus" class="h-[18px] w-[18px]"></i>
                Tambah barang
            </a>
        @endif
    </div>

    <div class="inst-filter-panel mb-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4 md:items-end">
            <div class="md:col-span-2">
                <label for="filter-kategori" class="inst-label-filter">Kategori</label>
                <select id="filter-kategori" class="inst-select mt-2">
                    <option value="">Semua kategori</option>
                    @foreach ($kategoris as $kat)
                        <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-status" class="inst-label-filter">Status</label>
                <select id="filter-status" class="inst-select mt-2">
                    <option value="">Semua</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
        </div>
    </div>

    <div class="inst-panel inst-datatable-wrap overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table id="tabel-barang" class="w-full text-sm" style="width:100%">
                <thead>
                    <tr>
                        <th class="hidden">Id</th>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Stok saat ini</th>
                        <th>Status</th>
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
            const table = jQuery('#tabel-barang').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('master.barang.data')),
                    headers: { 'X-CSRF-TOKEN': csrf },
                    data: function (d) {
                        d.kategori_barang_id = document.getElementById('filter-kategori')?.value || '';
                        d.status_filter = document.getElementById('filter-status')?.value || '';
                    },
                },
                order: [[0, 'desc']],
                columns: [
                    { data: 'id', name: 'barang.id', visible: false, searchable: false },
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '48px' },
                    { data: 'foto_thumb', name: 'foto_thumb', orderable: false, searchable: false },
                    { data: 'kode_barang', name: 'barang.kode_barang' },
                    { data: 'nama_barang', name: 'barang.nama_barang' },
                    { data: 'kategori_label', name: 'kategori_label', orderable: false, searchable: false },
                    { data: 'satuan_label', name: 'satuan_label', orderable: false, searchable: false },
                    { data: 'harga_label', name: 'barang.harga_satuan', orderable: true, searchable: false },
                    { data: 'stok_cell', name: 'stok_cell', orderable: false, searchable: false },
                    { data: 'status_badge', name: 'barang.status', orderable: false, searchable: false },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-right' },
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json',
                },
                drawCallback: function () {
                    if (window.lucide && typeof lucide.createIcons === 'function') {
                        lucide.createIcons();
                    }
                },
            });

            document.getElementById('filter-kategori')?.addEventListener('change', function () {
                table.ajax.reload();
            });
            document.getElementById('filter-status')?.addEventListener('change', function () {
                table.ajax.reload();
            });

            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!(form instanceof HTMLFormElement)) return;
                if (!form.classList.contains('form-hapus-barang')) return;
                e.preventDefault();
                if (typeof window.mbgConfirmDelete !== 'function') {
                    form.submit();
                    return;
                }
                window
                    .mbgConfirmDelete({
                        title: 'Hapus barang?',
                        text: 'Data akan disembunyikan (soft delete).',
                        confirmText: 'Ya, hapus',
                    })
                    .then(function (r) {
                        if (r.isConfirmed) form.submit();
                    });
            });
        })();
    </script>
@endpush
