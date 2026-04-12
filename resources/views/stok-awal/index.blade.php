@extends('layouts.app')

@section('title', 'Stok Awal Barang')

@section('content')
    @php
        $canDelSab = function (?App\Models\StokAwalBarang $s) {
            if (! $s) {
                return false;
            }
            $u = auth()->user();
            if (! $u) {
                return false;
            }
            if ($u->hasRole('super_admin')) {
                return true;
            }

            return (int) $u->getKey() === (int) $s->created_by && $s->created_at->greaterThan(now()->subHours(24));
        };
    @endphp
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Stok awal barang</h2>
            <p class="inst-page-desc">Stok awal per barang untuk periode laporan yang sedang dipilih (satu entri per barang per periode).</p>
            @isset($periodeLabel)
                <p class="mt-2 text-sm font-medium" style="color:#1a4a6b;">Periode: {{ $periodeLabel }}</p>
            @endisset
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            <form method="POST" action="{{ route('stok.awal.generate-prev') }}" class="inline" onsubmit="return confirm('Isi stok awal periode ini dari stok akhir periode sebelumnya? Nilai yang sudah ada akan ditimpa per barang.');">
                @csrf
                <input type="hidden" name="profil_mbg_id" value="{{ $profilId }}">
                <button type="submit" class="inst-btn-outline">
                    <i data-lucide="refresh-ccw" class="h-[18px] w-[18px]"></i>
                    Generate dari periode sebelumnya
                </button>
            </form>
            <a href="{{ route('stok.awal.batch') }}" class="inst-btn-outline">
                <i data-lucide="table" class="h-[18px] w-[18px]"></i>
                Input massal (tabel)
            </a>
            <a href="{{ route('stok.awal.create') }}" class="inst-btn-primary">
                <i data-lucide="plus" class="h-[18px] w-[18px]"></i>
                Input stok awal
            </a>
        </div>
    </div>

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th class="text-right">Stok awal</th>
                        <th>Tanggal input</th>
                        <th>Input oleh</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $b)
                        @php($sab = $stokMap->get($b->id))
                        <tr class="{{ ! $sab ? 'bg-red-50/40' : '' }}">
                            <td class="font-medium">{{ $b->kode_barang }} — {{ $b->nama_barang }}</td>
                            <td>{{ $b->kategoriBarang?->nama_kategori ?? '—' }}</td>
                            <td>{{ $b->satuan?->label() ?? '—' }}</td>
                            <td class="text-right font-mono">
                                @if ($sab)
                                    {{ number_format((float) $sab->jumlah, 2, ',', '.') }}
                                @else
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase" style="background:#fde8e8;color:#c0392b;">Belum ada stok awal</span>
                                @endif
                            </td>
                            <td>{{ $sab ? $sab->tanggal->format('d/m/Y') : '—' }}</td>
                            <td>{{ $sab?->creator?->name ?? '—' }}</td>
                            <td class="text-right">
                                @if ($sab)
                                    <a href="{{ route('stok.awal.edit', $sab) }}" class="text-xs font-semibold" style="color:#4a9b7a;">Ubah</a>
                                    @if ($canDelSab($sab))
                                        <form method="POST" action="{{ route('stok.awal.destroy', $sab) }}" class="ml-3 inline form-hapus-stok-awal">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold" style="color:#c0392b;">Hapus</button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('stok.awal.create', ['barang_id' => $b->id]) }}" class="text-xs font-semibold" style="color:#1a4a6b;">Isi stok awal</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.classList.contains('form-hapus-stok-awal')) return;
            e.preventDefault();
            if (typeof window.mbgConfirmDelete !== 'function') {
                form.submit();
                return;
            }
            window
                .mbgConfirmDelete({ title: 'Hapus stok awal?', text: 'Data akan dihapus permanen.', confirmText: 'Ya, hapus' })
                .then(function (r) {
                    if (r.isConfirmed) form.submit();
                });
        });
        if (window.lucide) lucide.createIcons();
    </script>
@endpush
