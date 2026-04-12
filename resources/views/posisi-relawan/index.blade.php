@extends('layouts.app')

@section('title', 'Posisi Relawan')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Posisi relawan</h2>
            <p class="inst-page-desc">Master posisi untuk penugasan relawan dapur. Super Admin / Admin Pusat dapat menyimpan perubahan langsung per baris.</p>
        </div>
        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_pusat']))
            <a href="{{ route('master.posisi-relawan.create') }}" class="inst-btn-primary shrink-0">
                <i data-lucide="plus" class="h-[18px] w-[18px]"></i>
                Tambah posisi
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('master.posisi-relawan.index') }}" class="inst-filter-panel mb-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:items-end">
            <div>
                <label for="q" class="inst-label-filter">Pencarian</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="Nama atau deskripsi…" class="inst-input mt-2">
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="inst-btn-primary">Terapkan</button>
                <a href="{{ route('master.posisi-relawan.index') }}" class="inst-btn-outline">Reset</a>
            </div>
        </div>
    </form>

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th class="pr-4">Nama posisi</th>
                        <th class="pr-4">Deskripsi</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $row)
                        <tr>
                            <td colspan="3" class="py-4">
                                @if (auth()->user()->hasAnyRole(['super_admin', 'admin_pusat']))
                                    <form method="POST" action="{{ route('master.posisi-relawan.update', $row) }}" class="flex flex-col gap-3 md:flex-row md:items-end">
                                        @csrf
                                        @method('PUT')
                                        <div class="min-w-0 flex-1">
                                            <label class="inst-label text-xs">Nama posisi</label>
                                            <input type="text" name="nama_posisi" value="{{ old('nama_posisi', $row->nama_posisi) }}" class="inst-input mt-1 text-sm" required maxlength="255">
                                        </div>
                                        <div class="min-w-0 flex-[2]">
                                            <label class="inst-label text-xs">Deskripsi</label>
                                            <textarea name="deskripsi" rows="2" class="inst-input mt-1 text-sm" maxlength="5000">{{ old('deskripsi', $row->deskripsi) }}</textarea>
                                        </div>
                                        <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">
                                            <button type="submit" class="inst-btn-primary text-sm">Simpan baris</button>
                                            <a href="{{ route('master.posisi-relawan.edit', $row) }}" class="inst-btn-outline text-sm">Form penuh</a>
                                        </div>
                                    </form>
                                    <div class="mt-3 flex justify-end border-t pt-3" style="border-color:#e8f1f8;">
                                        <form method="POST" action="{{ route('master.posisi-relawan.destroy', $row) }}" class="form-hapus-posisi inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold" style="color:#c0392b;">Hapus posisi</button>
                                        </form>
                                    </div>
                                @else
                                    <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
                                        <span class="font-medium">{{ $row->nama_posisi }}</span>
                                        <span class="inst-td-muted text-sm">{{ \Illuminate\Support\Str::limit($row->deskripsi ?? '—', 120) }}</span>
                                    </div>
                                    <p class="inst-td-muted mt-2 text-xs">Hanya lihat.</p>
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
            if (!form.classList.contains('form-hapus-posisi')) return;
            e.preventDefault();
            if (typeof window.mbgConfirmDelete !== 'function') {
                form.submit();
                return;
            }
            window
                .mbgConfirmDelete({
                    title: 'Hapus posisi?',
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
