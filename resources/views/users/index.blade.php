@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Daftar pengguna</h2>
            <p class="inst-page-desc">Kelola akun, peran, dan status pengguna.</p>
        </div>
        <a href="{{ route('master.pengguna.create') }}" class="inst-btn-primary shrink-0">
            <i data-lucide="user-plus" class="h-[18px] w-[18px]"></i>
            Tambah pengguna
        </a>
    </div>

    <div class="inst-filter-panel mb-4">
        <div class="max-w-xs">
            <label for="filter-status" class="inst-label-filter">Status</label>
            <select id="filter-status" class="inst-select mt-2">
                <option value="">Semua</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>
    </div>

    <div class="inst-panel inst-datatable-wrap overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table id="tabel-pengguna" class="w-full text-sm" style="width:100%">
                <thead>
                    <tr>
                        <th class="hidden">Id</th>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Dapur</th>
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
            const table = jQuery('#tabel-pengguna').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('master.pengguna.data')),
                    headers: { 'X-CSRF-TOKEN': csrf },
                    data: function (d) {
                        d.status_filter = document.getElementById('filter-status')?.value || '';
                    },
                },
                order: [[0, 'desc']],
                columns: [
                    { data: 'id', name: 'users.id', visible: false, searchable: false },
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '48px' },
                    { data: 'name', name: 'users.name' },
                    { data: 'email', name: 'users.email' },
                    { data: 'role_badges', name: 'role_badges', orderable: false, searchable: false },
                    { data: 'profil_label', name: 'profil_label', orderable: false, searchable: false },
                    { data: 'status_badge', name: 'users.status', orderable: false, searchable: false },
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

            document.getElementById('filter-status')?.addEventListener('change', function () {
                table.ajax.reload();
            });

            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!(form instanceof HTMLFormElement)) return;
                if (!form.classList.contains('form-hapus-user')) return;
                e.preventDefault();
                if (typeof window.mbgConfirmDelete !== 'function') {
                    form.submit();
                    return;
                }
                window
                    .mbgConfirmDelete({
                        title: 'Hapus pengguna?',
                        text: 'Data akan disembunyikan (soft delete). Anda yakin?',
                        confirmText: 'Ya, hapus',
                    })
                    .then(function (r) {
                        if (r.isConfirmed) form.submit();
                    });
            });

            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!(form instanceof HTMLFormElement)) return;
                if (!form.classList.contains('js-reset-password')) return;
                e.preventDefault();
                Swal.fire({
                    title: 'Reset kata sandi?',
                    text: 'Kata sandi akan diatur ke password123.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4a9b7a',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, reset',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                }).then(function (r) {
                    if (r.isConfirmed) form.submit();
                });
            });
        })();
    </script>
@endpush
