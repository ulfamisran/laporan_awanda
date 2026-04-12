@extends('layouts.app')

@section('title', 'Kategori Dana Keluar')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Kategori dana keluar</h2>
            <p class="inst-page-desc">Master kategori untuk pengeluaran keuangan.</p>
        </div>
        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_pusat']))
            <a href="{{ route('master.kategori-dana-keluar.create') }}" class="inst-btn-primary shrink-0">
                <i data-lucide="plus" class="h-[18px] w-[18px]"></i>
                Tambah kategori
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('master.kategori-dana-keluar.index') }}" class="inst-filter-panel mb-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:items-end">
            <div>
                <label for="q" class="inst-label-filter">Pencarian</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="Nama atau deskripsi…" class="inst-input mt-2">
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="inst-btn-primary">Terapkan</button>
                <a href="{{ route('master.kategori-dana-keluar.index') }}" class="inst-btn-outline">Reset</a>
            </div>
        </div>
    </form>

    <div class="inst-panel overflow-hidden p-6">
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th class="pr-4">Nama</th>
                        <th class="pr-4">Deskripsi</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $row)
                        <tr>
                            <td class="pr-4 font-medium">{{ $row->nama_kategori }}</td>
                            <td class="inst-td-muted pr-4">{{ \Illuminate\Support\Str::limit($row->deskripsi ?? '—', 80) }}</td>
                            <td class="text-right">
                                @if (auth()->user()->hasAnyRole(['super_admin', 'admin_pusat']))
                                    <a href="{{ route('master.kategori-dana-keluar.edit', $row) }}" class="text-xs font-semibold" style="color:#4a9b7a;">Ubah</a>
                                    <form method="POST" action="{{ route('master.kategori-dana-keluar.destroy', $row) }}" class="ml-3 inline form-hapus-kategori">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold" style="color:#c0392b;">Hapus</button>
                                    </form>
                                @else
                                    <span class="inst-td-muted text-xs">Hanya lihat</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="inst-td-muted py-8 text-center">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="mt-4 border-t pt-4" style="border-color:#e8f1f8;">
                {{ $items->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.classList.contains('form-hapus-kategori')) return;
            e.preventDefault();
            if (typeof window.mbgConfirmDelete !== 'function') {
                form.submit();
                return;
            }
            window
                .mbgConfirmDelete({
                    title: 'Hapus kategori?',
                    text: 'Data akan disembunyikan (soft delete).',
                    confirmText: 'Ya, hapus',
                })
                .then(function (r) {
                    if (r.isConfirmed) form.submit();
                });
        });
        if (window.lucide) lucide.createIcons();
    </script>
@endpush
