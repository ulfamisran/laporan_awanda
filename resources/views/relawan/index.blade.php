@extends('layouts.app')

@section('title', 'Relawan')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Data relawan</h2>
            <p class="inst-page-desc">Kelola relawan per cabang MBG, posisi, dan status kepegawaian.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('master.relawan.export-excel', request()->query()) }}" class="inst-btn-outline shrink-0">
                <i data-lucide="download" class="h-[18px] w-[18px]"></i>
                Export Excel
            </a>
            <a href="{{ route('master.relawan.create') }}" class="inst-btn-primary shrink-0">
                <i data-lucide="plus" class="h-[18px] w-[18px]"></i>
                Tambah relawan
            </a>
        </div>
    </div>

    <div class="inst-filter-panel mb-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4 md:items-end">
            <div>
                <label for="filter-posisi" class="inst-label-filter">Posisi</label>
                <select id="filter-posisi" class="inst-select mt-2">
                    <option value="">Semua posisi</option>
                    @foreach ($posisis as $p)
                        <option value="{{ $p->id }}">{{ $p->nama_posisi }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-status" class="inst-label-filter">Status</label>
                <select id="filter-status" class="inst-select mt-2">
                    <option value="">Semua</option>
                    <option value="aktif">Aktif</option>
                    <option value="cuti">Cuti</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
        </div>
    </div>

    <div class="inst-panel inst-datatable-wrap overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table id="tabel-relawan" class="w-full text-sm" style="width:100%">
                <thead>
                    <tr>
                        <th class="hidden">Id</th>
                        <th>No</th>
                        <th>Foto</th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>Posisi</th>
                        <th>Cabang</th>
                        <th>Status</th>
                        <th>Gaji pokok</th>
                        <th>Gaji per hari</th>
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
            const table = jQuery('#tabel-relawan').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('master.relawan.data')),
                    headers: { 'X-CSRF-TOKEN': csrf },
                    data: function (d) {
                        d.posisi_relawan_id = document.getElementById('filter-posisi')?.value || '';
                        d.status = document.getElementById('filter-status')?.value || '';
                    },
                },
                order: [[0, 'desc']],
                columns: [
                    { data: 'id', name: 'relawans.id', visible: false, searchable: false },
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '48px' },
                    { data: 'foto_thumb', name: 'foto_thumb', orderable: false, searchable: false },
                    { data: 'nik', name: 'relawans.nik' },
                    { data: 'nama_lengkap', name: 'relawans.nama_lengkap' },
                    { data: 'posisi_label', name: 'posisi_label', orderable: false, searchable: false },
                    { data: 'dapur_label', name: 'dapur_label', orderable: false, searchable: false },
                    { data: 'status_badge', name: 'relawans.status', orderable: false, searchable: false },
                    { data: 'gaji_label', name: 'relawans.gaji_pokok', orderable: true, searchable: false },
                    { data: 'gaji_harian_label', name: 'relawans.gaji_per_hari', orderable: true, searchable: false },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-right' },
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json',
                },
            });

            ['filter-posisi', 'filter-status'].forEach(function (id) {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('change', function () {
                        table.ajax.reload();
                    });
                }
            });

            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!(form instanceof HTMLFormElement)) return;
                if (!form.classList.contains('form-hapus-relawan')) return;
                e.preventDefault();
                if (typeof window.mbgConfirmDelete !== 'function') {
                    form.submit();
                    return;
                }
                window
                    .mbgConfirmDelete({
                        title: 'Hapus relawan?',
                        text: 'Data akan disembunyikan (soft delete). Riwayat penggajian tetap tersimpan.',
                        confirmText: 'Ya, hapus',
                    })
                    .then(function (r) {
                        if (r.isConfirmed) form.submit();
                    });
            });
            if (window.lucide) lucide.createIcons();
        })();
    </script>
@endpush
