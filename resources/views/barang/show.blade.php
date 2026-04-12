@extends('layouts.app')

@section('title', 'Detail Barang')

@section('header_subtitle')
    Informasi lengkap, stok, dan riwayat mutasi.
@endsection

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('master.barang.index') }}" class="inst-back">← Kembali ke daftar</a>
        <div class="flex flex-wrap gap-2">
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin_pusat', 'admin']))
                <a href="{{ route('master.barang.edit', $barang) }}" class="inst-btn-primary">Ubah data</a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="inst-panel p-6 lg:col-span-2">
            <h3 class="text-sm font-semibold uppercase tracking-wide" style="color:#7fa8c9;">Ringkasan</h3>
            <div class="mt-4 flex flex-col gap-6 sm:flex-row sm:items-start">
                <div class="shrink-0">
                    @if ($barang->foto_url)
                        <img src="{{ $barang->foto_url }}" alt="" class="h-28 w-28 rounded-xl border object-cover" style="border-color:#d4e8f4;">
                    @else
                        <div class="flex h-28 w-28 items-center justify-center rounded-xl text-lg font-bold text-white" style="background:#4a9b7a;">B</div>
                    @endif
                </div>
                <div class="min-w-0 flex-1 space-y-3 text-sm" style="color:#1a4a6b;">
                    <div>
                        <div class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Kode</div>
                        <div class="font-mono text-base font-semibold">{{ $barang->kode_barang }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Nama</div>
                        <div class="text-lg font-semibold">{{ $barang->nama_barang }}</div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <div class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Kategori</div>
                            <div>{{ $barang->kategoriBarang?->nama_kategori ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Satuan</div>
                            <div>{{ $barang->satuan?->label() ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Harga satuan</div>
                            <div class="font-semibold">{{ formatRupiah($barang->harga_satuan) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Stok minimum</div>
                            <div>{{ number_format((float) $barang->stok_minimum, 2, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Status</div>
                            <div>
                                @if ($barang->status?->value === 'aktif')
                                    <span class="rounded-full px-3 py-1 text-[11px] font-semibold" style="background:#d4f0e8;color:#2d7a60;">Aktif</span>
                                @else
                                    <span class="rounded-full px-3 py-1 text-[11px] font-semibold" style="background:#fde8e8;color:#c0392b;">Nonaktif</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if ($barang->deskripsi)
                        <div>
                            <div class="text-xs font-semibold uppercase" style="color:#7fa8c9;">Deskripsi</div>
                            <div class="leading-relaxed" style="color:#7fa8c9;">{{ $barang->deskripsi }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="inst-panel p-6">
            <h3 class="text-sm font-semibold uppercase tracking-wide" style="color:#7fa8c9;">Stok saat ini</h3>
            @php($stok = (float) $barang->stok_saat_ini)
            @php($min = (float) $barang->stok_minimum)
            <div class="mt-4 text-3xl font-bold tracking-tight" style="font-family:'Plus Jakarta Sans',sans-serif;color:#1a4a6b;">
                {{ number_format($stok, 2, ',', '.') }}
            </div>
            <p class="mt-2 text-xs" style="color:#7fa8c9;">
                Dihitung dari stok awal + barang masuk − barang keluar (data mutasi).
            </p>
            @if ($stok < $min)
                <div class="mt-4 rounded-lg border px-3 py-2 text-xs font-semibold" style="border-color:#fecaca;background:#fff1f2;color:#c0392b;">
                    Stok di bawah minimum (warning).
                </div>
            @endif
        </div>
    </div>

    <div class="inst-panel p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide" style="color:#7fa8c9;">Pergerakan stok</h3>
                <p class="text-xs" style="color:#7fa8c9;">30 hari terakhir (masuk vs keluar per hari).</p>
            </div>
        </div>
        <div class="mt-6 h-72 w-full">
            <canvas id="chart-stok-barang"></canvas>
        </div>
    </div>

    <div class="inst-panel overflow-hidden p-6">
        <h3 class="text-sm font-semibold uppercase tracking-wide" style="color:#7fa8c9;">Riwayat transaksi terbaru</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th class="pr-4">Tanggal</th>
                        <th class="pr-4">Tipe</th>
                        <th class="pr-4">Jumlah</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $r)
                        <tr>
                            <td class="pr-4 font-mono text-xs">{{ formatTanggal($r['tanggal']) }}</td>
                            <td class="pr-4">
                                @if ($r['tipe'] === 'masuk')
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold" style="background:#d4f0e8;color:#2d7a60;">Masuk</span>
                                @else
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold" style="background:#fde8e8;color:#c0392b;">Keluar</span>
                                @endif
                            </td>
                            <td class="pr-4 font-mono text-sm">{{ number_format($r['jumlah'], 2, ',', '.') }}</td>
                            <td class="inst-td-muted">{{ $r['keterangan'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="inst-td-muted py-8 text-center">Belum ada riwayat mutasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('chart-stok-barang');
            if (!ctx || !window.Chart) return;

            const labels = @json($chartLabels);
            const masuk = @json($chartMasuk);
            const keluar = @json($chartKeluar);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Masuk',
                            data: masuk,
                            borderColor: '#4a9b7a',
                            backgroundColor: 'rgba(74, 155, 122, 0.12)',
                            tension: 0.25,
                            fill: true,
                        },
                        {
                            label: 'Keluar',
                            data: keluar,
                            borderColor: '#c0392b',
                            backgroundColor: 'rgba(192, 57, 43, 0.08)',
                            tension: 0.25,
                            fill: true,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#7fa8c9' },
                            grid: { color: 'rgba(26, 74, 107, 0.08)' },
                        },
                        x: {
                            ticks: { color: '#7fa8c9', maxRotation: 0, autoSkip: true, maxTicksLimit: 10 },
                            grid: { display: false },
                        },
                    },
                    plugins: {
                        legend: { labels: { color: '#1a4a6b' } },
                    },
                },
            });

            if (window.lucide) lucide.createIcons();
        });
    </script>
@endpush
