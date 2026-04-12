@extends('layouts.app')

@section('title', 'Detail Laporan Limbah Harian')

@section('content')
    @php
        $h = $harian;
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Detail laporan limbah harian</h2>
            <p class="inst-page-desc">{{ $h->tanggal?->format('d/m/Y') }} · {{ $h->profilMbg?->nama_dapur ?? '—' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('laporan-limbah.index') }}" class="inst-btn-secondary text-sm">Kembali</a>
            <a href="{{ route('laporan-limbah.harian.edit', $h) }}" class="inst-btn-primary text-sm">Ubah</a>
        </div>
    </div>

    <div class="inst-panel mb-6 space-y-3 p-6">
        <h3 class="text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Ringkasan</h3>
        <dl class="grid gap-2 text-sm">
            <div class="flex justify-between gap-4 items-start"><dt class="inst-td-muted shrink-0">Menu</dt><dd class="text-right font-medium">{{ $h->menu_makanan }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="inst-td-muted">Oleh</dt><dd>{{ $h->creator?->name ?? '—' }}</dd></div>
        </dl>
    </div>

    <div class="inst-panel overflow-x-auto p-4 sm:p-6">
        <h3 class="mb-4 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Per kategori</h3>
        <table class="w-full min-w-[640px] text-sm">
            <thead>
                <tr class="border-b" style="border-color:#d4e8f4;">
                    <th class="py-2 pr-4 text-left font-semibold" style="color:#1a4a6b;">Kategori</th>
                    <th class="py-2 pr-4 text-left font-semibold" style="color:#1a4a6b;">Jumlah</th>
                    <th class="py-2 pr-4 text-left font-semibold" style="color:#1a4a6b;">Penanganan</th>
                    <th class="py-2 pr-4 text-left font-semibold" style="color:#1a4a6b;">Kode</th>
                    <th class="py-2 text-left font-semibold" style="color:#1a4a6b;">Foto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($h->details->sortBy('kategori_limbah_id') as $d)
                    @php
                        $sat = $d->satuan instanceof \App\Enums\SatuanLimbah ? $d->satuan->label() : (string) $d->satuan;
                        $jen = $d->jenis_penanganan instanceof \App\Enums\JenisPenangananLimbah ? $d->jenis_penanganan->label() : (string) $d->jenis_penanganan;
                    @endphp
                    <tr class="border-b" style="border-color:#eef6fb;">
                        <td class="py-3 pr-4 align-top">{{ $d->kategoriLimbah?->nama_kategori ?? '—' }}</td>
                        <td class="py-3 pr-4 align-top font-mono">{{ number_format((float) $d->jumlah, 2, ',', '.') }} {{ $sat }}</td>
                        <td class="py-3 pr-4 align-top">{{ $jen }}</td>
                        <td class="py-3 pr-4 align-top font-mono text-xs">{{ $d->kode_laporan }}</td>
                        <td class="py-3 align-top">
                            @if ($d->gambar_url)
                                <a href="{{ $d->gambar_url }}" target="_blank" rel="noopener">
                                    <img src="{{ $d->gambar_url }}" alt="" class="h-16 w-16 rounded object-cover" style="border:1px solid #d4e8f4;">
                                </a>
                            @else
                                <span class="inst-td-muted text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                    @if ($d->keterangan)
                        <tr class="border-b" style="border-color:#eef6fb;">
                            <td colspan="5" class="pb-3 pl-0 text-xs inst-td-muted">{{ $d->keterangan }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <form method="POST" action="{{ route('laporan-limbah.harian.destroy', $h) }}" class="mt-6" onsubmit="return confirm('Hapus seluruh laporan harian ini beserta semua kategori?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm font-semibold" style="color:#c0392b;">Hapus laporan harian</button>
    </form>
@endsection

@push('scripts')
    <script>
        if (window.lucide) lucide.createIcons();
    </script>
@endpush
