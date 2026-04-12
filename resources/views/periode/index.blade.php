@extends('layouts.app')

@section('title', 'Periode')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Periode laporan</h2>
            <p class="inst-page-desc">Kelola rentang tanggal, tanggal pelaporan, dan status aktif periode untuk pencatatan operasional.</p>
        </div>
        <a href="{{ route('periode.create') }}" class="inst-btn-primary shrink-0">
            <i data-lucide="plus" class="h-[18px] w-[18px]"></i>
            Tambah periode
        </a>
    </div>

    <div class="inst-panel overflow-hidden p-6">
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th class="pr-4">Nama</th>
                        <th class="pr-4">Tanggal awal</th>
                        <th class="pr-4">Tanggal akhir</th>
                        <th class="pr-4">Tgl. pelaporan</th>
                        <th class="pr-4">Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $row)
                        <tr>
                            <td class="pr-4 font-medium">{{ $row->nama ?: '—' }}</td>
                            <td class="pr-4">{{ $row->tanggal_awal?->format('d/m/Y') ?? '—' }}</td>
                            <td class="pr-4">{{ $row->tanggal_akhir?->format('d/m/Y') ?? '—' }}</td>
                            <td class="pr-4">{{ $row->tanggal_pelaporan?->format('d/m/Y') ?? '—' }}</td>
                            <td class="pr-4">
                                @if ($row->status?->value === 'aktif')
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold text-white" style="background:#4a9b7a;">Aktif</span>
                                @else
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" style="background:#e8f1f8;color:#7fa8c9;">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('periode.edit', $row) }}" class="text-xs font-semibold" style="color:#4a9b7a;">Ubah</a>
                                <form method="POST" action="{{ route('periode.destroy', $row) }}" class="ml-3 inline form-hapus-periode">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold" style="color:#c0392b;">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="inst-td-muted py-8 text-center">Belum ada periode. Tambahkan periode pertama untuk mulai mencatat transaksi.</td>
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
            if (!form.classList.contains('form-hapus-periode')) return;
            e.preventDefault();
            window.mbgConfirmDelete({ title: 'Hapus periode?', text: 'Pastikan periode tidak lagi dipakai.' }).then((r) => {
                if (r.isConfirmed) form.submit();
            });
        });
    </script>
@endpush
