@extends('layouts.app')

@section('title', 'Supplier')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Master supplier</h2>
            <p class="inst-page-desc">Kelola data supplier: nama, nomor HP, dan alamat.</p>
        </div>
        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_pusat']))
            <a href="{{ route('master.supplier.create') }}" class="inst-btn-primary shrink-0">
                <i data-lucide="plus" class="h-[18px] w-[18px]"></i>
                Tambah supplier
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('master.supplier.index') }}" class="inst-filter-panel mb-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:items-end">
            <div>
                <label for="q" class="inst-label-filter">Pencarian</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="Nama, no HP, atau alamat…" class="inst-input mt-2">
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="inst-btn-primary">Terapkan</button>
                <a href="{{ route('master.supplier.index') }}" class="inst-btn-outline">Reset</a>
            </div>
        </div>
    </form>

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Nama supplier</th>
                        <th>No HP</th>
                        <th>Alamat</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $row)
                        <tr>
                            <td class="font-medium">{{ $row->nama_supplier }}</td>
                            <td>{{ $row->no_hp }}</td>
                            <td class="inst-td-muted">{{ \Illuminate\Support\Str::limit($row->alamat, 140) }}</td>
                            <td class="text-right">
                                @if (auth()->user()->hasAnyRole(['super_admin', 'admin_pusat']))
                                    <div class="inline-flex items-center gap-3">
                                        <a href="{{ route('master.supplier.edit', $row) }}" class="text-xs font-semibold" style="color:#4a9b7a;">Ubah</a>
                                        <form method="POST" action="{{ route('master.supplier.destroy', $row) }}" class="form-hapus-supplier inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold" style="color:#c0392b;">Hapus</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="inst-td-muted text-xs">Hanya lihat</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="inst-td-muted py-8 text-center">Belum ada data supplier.</td>
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
            if (!form.classList.contains('form-hapus-supplier')) return;
            e.preventDefault();
            if (typeof window.mbgConfirmDelete !== 'function') {
                form.submit();
                return;
            }
            window
                .mbgConfirmDelete({
                    title: 'Hapus supplier?',
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
