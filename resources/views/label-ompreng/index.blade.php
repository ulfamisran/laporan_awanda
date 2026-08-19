@extends('layouts.app')

@section('title', 'Label Ompreng')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Label Ompreng</h2>
            <p class="inst-page-desc">Riwayat data label ompreng yang sudah tersimpan.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('label-ompreng.create-mobile') }}" class="inst-btn-outline">
                Input via HP
            </a>
            <a href="{{ route('label-ompreng.create') }}" class="inst-btn-primary">
                <i data-lucide="plus" class="h-[18px] w-[18px]"></i>
                Tambah data
            </a>
        </div>
    </div>

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Input oleh</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $row)
                        <tr>
                            <td>{{ $row->created_at?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $row->creator?->name ?? '—' }}</td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('label-ompreng.show', $row) }}" class="inst-btn-outline text-xs !px-3 !py-1.5">Detail</a>
                                    <a href="{{ route('label-ompreng.edit', $row) }}" class="inst-btn-outline text-xs !px-3 !py-1.5" style="border-color:#d97706;color:#d97706;">Update</a>
                                    <form method="GET" action="{{ route('label-ompreng.preview', $row) }}" target="_blank" class="inline-flex items-center gap-2">
                                        <input type="hidden" name="template" value="premium">
                                        <input type="number" name="jumlah_cetak" min="1" max="999" value="{{ max(1, (int) $row->jumlah_item) }}" class="inst-input text-center text-xs" style="width: 4.5rem;">
                                        <button type="submit" class="inst-btn-outline text-xs !px-3 !py-1.5" style="border-color:#0f766e;color:#0f766e;">Lihat</button>
                                        <button type="submit" formaction="{{ route('label-ompreng.cetak', $row) }}" class="inst-btn-primary text-xs !px-3 !py-1.5">Cetak</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-8 text-center text-sm" style="color:#7fa8c9;">Belum ada data label ompreng.</td>
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
