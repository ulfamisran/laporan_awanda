@extends('layouts.app')

@section('title', 'Mutasi Stok')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h2 class="inst-page-title">Rekapitulasi mutasi stok</h2>
            <p class="inst-page-desc">Ringkasan stok awal, masuk, keluar, dan saldo per barang cabang MBG.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('stok.mutasi.export-excel') }}" class="inst-btn-outline shrink-0">Excel</a>
            <a href="{{ route('stok.mutasi.export-pdf') }}" target="_blank" class="inst-btn-outline shrink-0">PDF</a>
        </div>
    </div>

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th class="text-right">Stok awal</th>
                        <th class="text-right">Total masuk</th>
                        <th class="text-right">Total keluar</th>
                        <th class="text-right">Stok saat ini</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $b)
                        @php
                            $awal = (float) ($b->jumlah_awal ?? 0);
                            $masuk = (float) ($b->jumlah_masuk ?? 0);
                            $keluar = (float) ($b->jumlah_keluar ?? 0);
                            $stok = (float) ($b->stok_saat_ini_dapur ?? 0);
                            $min = (float) $b->stok_minimum;
                            $below = $stok < $min;
                            $detailUrl = route('stok.mutasi.detail', ['barang' => $b]);
                        @endphp
                        <tr class="cursor-pointer transition hover:bg-slate-50 {{ $below ? 'bg-rose-50/80' : '' }}"
                            onclick="window.location.href='{{ $detailUrl }}'">
                            <td>
                                <span class="font-mono text-xs" style="color:#7fa8c9;">{{ $b->kode_barang }}</span><br>
                                <span class="font-medium" style="color:#1a4a6b;">{{ $b->nama_barang }}</span><br>
                                <span class="text-xs" style="color:#7fa8c9;">{{ $b->satuan?->label() }}</span>
                            </td>
                            <td>{{ $b->kategoriBarang?->nama_kategori ?? '—' }}</td>
                            <td class="text-right font-mono">{{ number_format($awal, 2, ',', '.') }}</td>
                            <td class="text-right font-mono">{{ number_format($masuk, 2, ',', '.') }}</td>
                            <td class="text-right font-mono">{{ number_format($keluar, 2, ',', '.') }}</td>
                            <td class="text-right font-mono font-semibold">{{ number_format($stok, 2, ',', '.') }}</td>
                            <td>
                                @if ($below)
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" style="background:#fee2e2;color:#b91c1c;">Di bawah minimum</span>
                                @else
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" style="background:#dcfce7;color:#166534;">Aman</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-sm" style="color:#7fa8c9;">Tidak ada data barang aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="mt-3 text-xs" style="color:#7fa8c9;">Klik baris untuk melihat riwayat transaksi.</p>
    </div>
@endsection
