@extends('layouts.app')

@section('title', 'Rekapitulasi Limbah')

@section('content')
    @php
        $pie = $pie ?? [];
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Rekapitulasi limbah</h2>
            <p class="inst-page-desc">Agregat per kategori dan komposisi penanganan.</p>
        </div>
        <a href="{{ route('laporan-limbah.index', request()->query()) }}" class="inst-btn-secondary shrink-0 text-sm">Daftar laporan</a>
    </div>

    <form method="get" action="{{ route('laporan-limbah.rekapitulasi') }}" class="inst-filter-panel mb-6 space-y-3">
        <input type="hidden" id="r-profil" value="{{ $profilIdDefault }}">
        <div class="flex flex-wrap gap-4">
            <div>
                <label for="r-kat" class="inst-label-filter">Kategori</label>
                <select id="r-kat" name="kategori_limbah_id" class="inst-select mt-2 w-52 select2">
                    <option value="">Semua</option>
                    @foreach ($kategoris as $k)
                        <option value="{{ $k->id }}" @selected((string) request('kategori_limbah_id') === (string) $k->id)>{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="r-dari" class="inst-label-filter">Dari</label>
                <input type="date" id="r-dari" name="dari" value="{{ $dari }}" class="inst-input mt-2 w-40">
            </div>
            <div>
                <label for="r-sampai" class="inst-label-filter">Sampai</label>
                <input type="date" id="r-sampai" name="sampai" value="{{ $sampai }}" class="inst-input mt-2 w-40">
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="submit" class="inst-btn-secondary text-sm">Terapkan</button>
            <a href="{{ route('laporan-limbah.export-excel', array_merge(request()->query(), ['rekap' => 1])) }}" class="inst-btn-secondary text-sm">Export Excel</a>
            <a href="{{ route('laporan-limbah.export-pdf', array_merge(request()->query(), ['rekap' => 1])) }}" target="_blank" class="inst-btn-secondary text-sm">Export PDF</a>
        </div>
    </form>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <div class="inst-panel p-4 sm:p-6">
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Komposisi penanganan (est. kg)</h3>
            <div class="mx-auto h-64 max-w-sm">
                <canvas id="chart-pie-penanganan"></canvas>
            </div>
        </div>
        <div class="inst-panel p-4 sm:p-6 flex items-center">
            <p class="text-sm inst-td-muted">Diagram memakai estimasi kg yang sama dengan ringkasan utama (karung × 25 kg; pcs tidak dijumlahkan ke kg).</p>
        </div>
    </div>

    <div class="inst-panel overflow-hidden p-4 sm:p-6">
        <h3 class="mb-4 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Rekap per kategori</h3>
        <div class="overflow-x-auto">
            <table class="inst-table">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th class="text-right">Volume (kg est.)</th>
                        <th class="text-right">Dibuang</th>
                        <th class="text-right">Daur ulang</th>
                        <th class="text-right">Dijual</th>
                        <th class="text-right">Dikembalikan</th>
                        <th class="text-right">Lainnya</th>
                        <th class="text-right">Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $r)
                        <tr>
                            <td class="font-medium">{{ $r->nama_kategori }}</td>
                            <td class="text-right font-mono">{{ number_format($r->total_volume_kg, 2, ',', '.') }}</td>
                            <td class="text-right font-mono text-xs">{{ number_format($r->vol_dibuang, 2, ',', '.') }}</td>
                            <td class="text-right font-mono text-xs">{{ number_format($r->vol_didaur_ulang, 2, ',', '.') }}</td>
                            <td class="text-right font-mono text-xs">{{ number_format($r->vol_dijual, 2, ',', '.') }}</td>
                            <td class="text-right font-mono text-xs">{{ number_format($r->vol_dikembalikan, 2, ',', '.') }}</td>
                            <td class="text-right font-mono text-xs">{{ number_format($r->vol_lainnya, 2, ',', '.') }}</td>
                            <td class="text-right font-mono">{{ formatRupiah($r->pendapatan) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="inst-td-muted py-8 text-center">Tidak ada data pada filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const pie = @json($pie);
            const ctx = document.getElementById('chart-pie-penanganan');
            if (ctx && window.Chart && pie.length) {
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: pie.map(function (p) { return p.label; }),
                        datasets: [{
                            data: pie.map(function (p) { return p.value; }),
                            backgroundColor: ['#1a4a6b', '#4a9b7a', '#7fa8c9', '#2d7a60', '#c0392b'],
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                    },
                });
            }
            if (window.jQuery && jQuery.fn.select2) {
                jQuery('#r-kat').select2({ width: '100%' });
            }
            if (window.lucide) lucide.createIcons();
        })();
    </script>
@endpush
