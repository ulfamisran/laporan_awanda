@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Daftar pengguna</h2>
            <p class="inst-page-desc">Kelola akun, peran, dan status pengguna. Hanya super admin yang dapat menambah pengguna baru.</p>
        </div>
        <a href="{{ route('master.pengguna.create') }}" class="inst-btn-primary shrink-0">
            <i data-lucide="user-plus" class="h-[18px] w-[18px]"></i>
            Tambah pengguna
        </a>
    </div>

    <form method="GET" action="{{ route('master.pengguna.index') }}" class="inst-filter-panel mb-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:items-end">
            <div>
                <label for="q" class="inst-label-filter">Pencarian</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="Nama atau email…" class="inst-input mt-2">
            </div>
            <div>
                <label for="status" class="inst-label-filter">Status</label>
                <select name="status" id="status" class="inst-select mt-2">
                    <option value="">Semua</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="inst-btn-primary">Terapkan</button>
                <a href="{{ route('master.pengguna.index') }}" class="inst-btn-outline">Reset</a>
            </div>
        </div>
    </form>

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Dapur</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="inst-td-muted">{{ $users->firstItem() + $loop->index }}</td>
                            <td class="font-medium">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>@include('users.partials.role-badges', ['user' => $user])</td>
                            <td>{{ $user->profilMbg?->nama_dapur ?? '—' }}</td>
                            <td>@include('users.partials.status-badge', ['user' => $user])</td>
                            <td class="text-right">@include('users.partials.actions', ['user' => $user])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="inst-td-muted py-8 text-center">Belum ada data pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="mt-4 border-t pt-4" style="border-color:#e8f1f8;">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            if (window.lucide) lucide.createIcons();

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
