@extends('layouts.app')

@section('title', 'Profil MBG')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Profil dapur MBG</h2>
            <p class="inst-page-desc">Daftar dapur / entitas MBG.</p>
        </div>
        <a href="{{ route('master.profil-mbg.create') }}" class="inst-btn-primary shrink-0">
            <i data-lucide="plus" class="h-[18px] w-[18px]"></i>
            Tambah profil
        </a>
    </div>

    <form method="GET" action="{{ route('master.profil-mbg.index') }}" class="inst-filter-panel mb-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:items-end">
            <div>
                <label for="q" class="inst-label-filter">Pencarian</label>
                <input
                    type="search"
                    name="q"
                    id="q"
                    value="{{ request('q') }}"
                    placeholder="Nama, kode, kota…"
                    class="inst-input mt-2"
                >
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
                <a href="{{ route('master.profil-mbg.index') }}" class="inst-btn-outline">Reset</a>
            </div>
        </div>
    </form>

    <div class="inst-panel overflow-hidden p-6">
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th class="pr-4">Logo</th>
                        <th class="pr-4">Nama dapur</th>
                        <th class="pr-4">Kode</th>
                        <th class="pr-4">Kota</th>
                        <th class="pr-4">PJ</th>
                        <th class="pr-4">Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $row)
                        <tr>
                            <td class="pr-4">
                                @if ($row->logo_url)
                                    <img src="{{ $row->logo_url }}" alt="" class="h-10 w-10 rounded-lg border object-cover" style="border-color:#d4e8f4;">
                                @else
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-xs font-bold text-white" style="background:#4a9b7a;">M</span>
                                @endif
                            </td>
                            <td class="pr-4 font-medium">{{ $row->nama_dapur }}</td>
                            <td class="inst-td-muted pr-4 font-mono text-xs">{{ $row->kode_dapur }}</td>
                            <td class="inst-td-muted pr-4">{{ $row->kota ?? '—' }}</td>
                            <td class="inst-td-muted pr-4">{{ $row->penanggung_jawab ?? '—' }}</td>
                            <td class="pr-4">
                                @if ($row->status === 'aktif')
                                    <span class="rounded-full px-3 py-1 text-[11px] font-semibold" style="background:#d4f0e8;color:#2d7a60;">Aktif</span>
                                @else
                                    <span class="rounded-full px-3 py-1 text-[11px] font-semibold" style="background:#fde8e8;color:#c0392b;">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('master.profil-mbg.edit', $row) }}" class="text-xs font-semibold" style="color:#4a9b7a;">Ubah</a>
                                <form method="POST" action="{{ route('master.profil-mbg.destroy', $row) }}" class="ml-3 inline form-hapus-profil">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold" style="color:#c0392b;">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="inst-td-muted py-8 text-center">Belum ada data.</td>
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
            if (!form.classList.contains('form-hapus-profil')) return;
            e.preventDefault();
            if (typeof window.mbgConfirmDelete !== 'function') {
                form.submit();
                return;
            }
            window
                .mbgConfirmDelete({
                    title: 'Hapus profil MBG?',
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
