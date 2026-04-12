@extends('layouts.app')

@section('title', 'Laporan Keuangan')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h2 class="inst-page-title">Dashboard keuangan</h2>
            <p class="inst-page-desc">Ringkasan saldo, arus bulan ini, dan grafik 6 bulan terakhir.</p>
        </div>
        <a href="{{ route('keuangan.laporan.neraca', ['bulan' => now()->month, 'tahun' => now()->year]) }}" class="inst-btn-outline shrink-0">Neraca keuangan</a>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="stat-card rounded-2xl p-5" style="background:linear-gradient(135deg,#ecfdf5,#d4f0e8);border-color:#4a9b7a;">
            <p class="text-xs font-bold uppercase" style="color:#2d7a60;">Saldo dana saat ini</p>
            <p class="mt-2 text-2xl font-bold tracking-tight" style="color:#14532d;">{{ formatRupiah($saldoSaatIni) }}</p>
        </div>
        <div class="stat-card rounded-2xl p-5" style="background:#fff;border-color:#d4e8f4;">
            <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Masuk bulan ini</p>
            <p class="mt-2 text-xl font-bold" style="color:#2d7a60;">{{ formatRupiah($totalMasukBulan) }}</p>
        </div>
        <div class="stat-card rounded-2xl p-5" style="background:#fff;border-color:#d4e8f4;">
            <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Keluar bulan ini</p>
            <p class="mt-2 text-xl font-bold" style="color:#c0392b;">{{ formatRupiah($totalKeluarBulan) }}</p>
        </div>
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <div class="inst-panel p-4 sm:p-6">
            <h3 class="mb-3 text-sm font-bold uppercase" style="color:#7fa8c9;">Masuk vs keluar (6 bulan)</h3>
            <div style="max-height:280px;">
                <canvas id="chart-bar-keuangan"></canvas>
            </div>
        </div>
        <div class="inst-panel p-4 sm:p-6">
            <h3 class="mb-3 text-sm font-bold uppercase" style="color:#7fa8c9;">Komposisi keluar per jenis dana (bulan ini)</h3>
            <div style="max-height:280px;">
                <canvas id="chart-pie-keuangan"></canvas>
            </div>
        </div>
    </div>

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <h3 class="mb-3 text-sm font-bold uppercase" style="color:#7fa8c9;">Transaksi terbaru</h3>
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Kode</th>
                        <th>Label</th>
                        <th>Uraian transaksi</th>
                        <th class="text-right">Jumlah</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recent as $r)
                        <tr>
                            <td>{{ $r['tanggal']->format('d/m/Y') }}</td>
                            <td>
                                @if ($r['jenis'] === 'masuk')
                                    <span class="text-xs font-semibold text-emerald-700">Masuk</span>
                                @else
                                    <span class="text-xs font-semibold text-rose-700">Keluar</span>
                                @endif
                            </td>
                            <td class="font-mono text-xs">{{ $r['kode'] }}</td>
                            <td>{{ $r['label'] }}</td>
                            <td class="max-w-xs text-xs" style="color:#4a6b7f;">{{ $r['uraian'] !== '' ? \Illuminate\Support\Str::limit($r['uraian'], 160) : '—' }}</td>
                            <td class="text-right font-mono">{{ formatRupiah($r['jumlah']) }}</td>
                            <td class="text-right">
                                @if ($r['jenis'] === 'masuk')
                                    <a href="{{ route('keuangan.masuk.show', $r['model']) }}" class="text-xs font-semibold" style="color:#1a4a6b;">Detail</a>
                                @else
                                    <a href="{{ route('keuangan.keluar.show', $r['model']) }}" class="text-xs font-semibold" style="color:#1a4a6b;">Detail</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-sm" style="color:#7fa8c9;">Belum ada transaksi.</td>
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
            if (!window.Chart) return;
            const barCtx = document.getElementById('chart-bar-keuangan');
            if (barCtx) {
                new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [
                            { label: 'Masuk', data: @json($chartMasuk), backgroundColor: 'rgba(74, 155, 122, 0.75)' },
                            { label: 'Keluar', data: @json($chartKeluar), backgroundColor: 'rgba(192, 57, 43, 0.65)' },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true } },
                    },
                });
            }
            const pieCtx = document.getElementById('chart-pie-keuangan');
            const pieLabels = @json($pieLabels);
            const pieValues = @json($pieValues);
            if (pieCtx && pieLabels.length) {
                new Chart(pieCtx, {
                    type: 'pie',
                    data: {
                        labels: pieLabels,
                        datasets: [{ data: pieValues, backgroundColor: ['#1a4a6b', '#4a9b7a', '#7fa8c9', '#d4a574', '#c0392b', '#5c6bc0', '#8e7cc3'] }],
                    },
                    options: { responsive: true, maintainAspectRatio: false },
                });
            } else if (pieCtx) {
                pieCtx.replaceWith(Object.assign(document.createElement('p'), { className: 'text-sm', style: 'color:#7fa8c9', textContent: 'Belum ada data keluar bulan ini.' }));
            }
        });
    </script>
@endpush
