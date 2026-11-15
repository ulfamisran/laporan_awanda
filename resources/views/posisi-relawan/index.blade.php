@extends('layouts.app')

@section('title', 'Posisi Relawan')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Posisi relawan</h2>
            <p class="inst-page-desc">Atur nama posisi dan urutannya. Urutan di sini menentukan tampilan data relawan, penggajian, dan laporan.</p>
        </div>
        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_pusat']))
            <a href="{{ route('master.posisi-relawan.create') }}" class="inst-btn-primary shrink-0">
                <i data-lucide="plus" class="h-[18px] w-[18px]"></i>
                Tambah posisi
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="border-color:#fecaca;background:#fef2f2;color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

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
        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_pusat']))
            <form method="POST" action="{{ route('master.posisi-relawan.reorder') }}">
                @csrf
                @foreach (request()->only('q', 'page') as $key => $val)
                    @if ($val !== null && $val !== '')
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endif
                @endforeach

                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm" style="color:#7fa8c9;">Nomor kecil = tampil lebih atas. Gunakan tombol naik/turun atau ubah angka urutan lalu simpan.</p>
                    <button type="submit" class="inst-btn-primary text-sm">Simpan urutan</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="inst-table w-full" style="min-width: 40rem;">
                        <thead>
                            <tr>
                                <th class="pr-3" style="width:7rem;">Urutan</th>
                                <th class="pr-4">Nama posisi</th>
                                <th class="pr-4">Deskripsi</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $index => $row)
                                <tr>
                                    <td class="pr-3 align-top">
                                        <div class="flex items-center gap-1">
                                            <input type="number" name="urutan[{{ $row->id }}]" value="{{ old('urutan.'.$row->id, $row->urutan) }}"
                                                min="0" max="999999" class="inst-input w-20 font-mono text-sm" required>
                                            <div class="flex flex-col gap-1">
                                                <button type="submit" formaction="{{ route('master.posisi-relawan.move', $row) }}" formmethod="post"
                                                    name="arah" value="up" class="inst-btn-outline !h-7 !px-2 !py-0 text-xs"
                                                    @disabled($items->firstItem() + $index <= 1)
                                                    title="Naikkan">▲</button>
                                                <button type="submit" formaction="{{ route('master.posisi-relawan.move', $row) }}" formmethod="post"
                                                    name="arah" value="down" class="inst-btn-outline !h-7 !px-2 !py-0 text-xs"
                                                    @disabled($items->firstItem() + $index >= $items->total())
                                                    title="Turunkan">▼</button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="pr-4 align-top">
                                        <input type="text" form="form-posisi-{{ $row->id }}" name="nama_posisi"
                                            value="{{ old('nama_posisi', $row->nama_posisi) }}"
                                            class="inst-input text-sm" required maxlength="255">
                                    </td>
                                    <td class="pr-4 align-top">
                                        <textarea form="form-posisi-{{ $row->id }}" name="deskripsi" rows="2"
                                            class="inst-input text-sm" maxlength="5000">{{ old('deskripsi', $row->deskripsi) }}</textarea>
                                    </td>
                                    <td class="align-top text-right">
                                        <div class="inline-flex flex-col items-end gap-2">
                                            <button type="submit" form="form-posisi-{{ $row->id }}" class="inst-btn-primary text-sm">Simpan baris</button>
                                            <a href="{{ route('master.posisi-relawan.edit', $row) }}" class="text-xs font-semibold" style="color:#1a4a6b;">Form penuh</a>
                                            <button type="submit" form="form-hapus-{{ $row->id }}" class="text-xs font-semibold" style="color:#c0392b;">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="inst-td-muted py-8 text-center">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            @foreach ($items as $row)
                <form id="form-posisi-{{ $row->id }}" method="POST" action="{{ route('master.posisi-relawan.update', $row) }}" class="hidden">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="urutan" value="{{ $row->urutan }}">
                </form>
                <form id="form-hapus-{{ $row->id }}" method="POST" action="{{ route('master.posisi-relawan.destroy', $row) }}" class="form-hapus-posisi hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        @else
            <div class="overflow-x-auto">
                <table class="inst-table w-full">
                    <thead>
                        <tr>
                            <th class="pr-3">Urutan</th>
                            <th class="pr-4">Nama posisi</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $row)
                            <tr>
                                <td class="pr-3 font-mono">{{ $row->urutan }}</td>
                                <td class="pr-4 font-medium">{{ $row->nama_posisi }}</td>
                                <td class="inst-td-muted">{{ \Illuminate\Support\Str::limit($row->deskripsi ?? '—', 120) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="inst-td-muted py-8 text-center">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

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
