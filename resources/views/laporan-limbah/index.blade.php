@extends('layouts.app')

@section('title', 'Laporan Limbah')

@section('content')
    @php
        $chartBar = $chartBar ?? ['labels' => [], 'datasets' => []];
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Laporan limbah</h2>
            <p class="inst-page-desc">Satu baris per hari: menu dan semua kategori limbah (foto + berat) untuk periode aktif.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('laporan-limbah.rekapitulasi', request()->query()) }}" class="inst-btn-secondary text-sm">Rekapitulasi</a>
            <a href="{{ route('laporan-limbah.create') }}" class="inst-btn-primary shrink-0">Tambah laporan harian</a>
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="stat-card rounded-2xl p-4" style="background:#fff;border-color:#d4e8f4;">
            <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Total volume (est. kg) — bulan ini</p>
            <p class="mt-1 text-xl font-bold font-mono" style="color:#1a4a6b;">{{ number_format($totalVolumeKg, 2, ',', '.') }} kg</p>
            <p class="mt-1 text-xs inst-td-muted">Satuan pcs tidak dimasukkan ke estimasi kg.</p>
        </div>
        <div class="stat-card rounded-2xl p-4" style="background:#fff;border-color:#d4e8f4;">
            <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Didaur ulang (est. kg)</p>
            <p class="mt-1 text-xl font-bold font-mono" style="color:#2d7a60;">{{ number_format($volumeDaur, 2, ',', '.') }} kg</p>
        </div>
        <div class="stat-card rounded-2xl p-4" style="background:#fff;border-color:#d4e8f4;">
            <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Dijual (est. kg) + pendapatan</p>
            <p class="mt-1 text-lg font-bold font-mono" style="color:#1a4a6b;">{{ number_format($volumeDijual, 2, ',', '.') }} kg</p>
            <p class="mt-1 text-sm font-mono" style="color:#2d7a60;">{{ formatRupiah($pendapatanDijual) }}</p>
        </div>
    </div>

    <form method="get" action="{{ route('laporan-limbah.index') }}" class="inst-filter-panel mb-4">
        <input type="hidden" id="f-profil" value="{{ $profilIdDefault }}">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="min-w-0">
                <label for="f-kat" class="inst-label-filter">Kategori</label>
                <select id="f-kat" name="kategori_limbah_id" class="inst-select mt-2 w-full min-w-0 select2">
                    <option value="">Semua</option>
                    @foreach ($kategoris as $k)
                        <option value="{{ $k->id }}" @selected((string) request('kategori_limbah_id') === (string) $k->id)>{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-0">
                <label for="f-jenis" class="inst-label-filter">Penanganan</label>
                <select id="f-jenis" name="jenis_penanganan" class="inst-select mt-2 w-full min-w-0">
                    <option value="">Semua</option>
                    @foreach (\App\Enums\JenisPenangananLimbah::cases() as $jen)
                        <option value="{{ $jen->value }}" @selected(request('jenis_penanganan') === $jen->value)>{{ $jen->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-0">
                <label for="f-dari" class="inst-label-filter">Tanggal dari</label>
                <input type="date" id="f-dari" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="inst-input mt-2 w-full min-w-0 max-w-full">
            </div>
            <div class="min-w-0">
                <label for="f-sampai" class="inst-label-filter">Tanggal sampai</label>
                <input type="date" id="f-sampai" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="inst-input mt-2 w-full min-w-0 max-w-full">
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-2 border-t pt-4" style="border-color:#e8f1f8;">
            <button type="submit" class="inst-btn-primary text-sm shrink-0">Terapkan</button>
            <a href="{{ route('laporan-limbah.export-excel', request()->query()) }}" class="inst-btn-secondary text-sm shrink-0">Export Excel</a>
            <a href="{{ route('laporan-limbah.export-pdf', request()->query()) }}" target="_blank" rel="noopener" class="inst-btn-secondary text-sm shrink-0">Export PDF</a>
        </div>
    </form>

    <div class="inst-panel mb-6 p-4 sm:p-6">
        <h3 class="mb-3 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Volume per kategori per bulan</h3>
        <div class="h-72 w-full max-w-5xl">
            <canvas id="chart-limbah-kategori"></canvas>
        </div>
    </div>

    @php
        $dtCols = [
            ['data' => 'id', 'name' => 'laporan_limbah_harian.id', 'visible' => false, 'searchable' => false],
            ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'width' => '48px'],
            ['data' => 'tanggal', 'name' => 'laporan_limbah_harian.tanggal'],
            ['data' => 'menu_makanan', 'name' => 'laporan_limbah_harian.menu_makanan'],
        ];
        foreach ($kategoris as $k) {
            $dtCols[] = [
                'data' => 'kat_'.$k->id,
                'name' => 'kat_'.$k->id,
                'orderable' => false,
                'searchable' => false,
                'defaultContent' => '—',
            ];
        }
        $dtCols[] = ['data' => 'aksi', 'orderable' => false, 'searchable' => false, 'className' => 'text-right'];
    @endphp

    <div class="inst-panel inst-datatable-wrap overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table id="tabel-laporan-limbah" class="w-full text-sm" style="width:100%">
                <thead>
                    <tr>
                        <th class="hidden">Id</th>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Menu</th>
                        @foreach ($kategoris as $k)
                            <th>{{ $k->nama_kategori }}</th>
                        @endforeach
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const chartPayload = @json($chartBar);
            const ctx = document.getElementById('chart-limbah-kategori');
            if (ctx && window.Chart && chartPayload.labels && chartPayload.labels.length && (chartPayload.datasets || []).length) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartPayload.labels,
                        datasets: (chartPayload.datasets || []).map(function (d) {
                            return {
                                label: d.label,
                                data: d.data,
                                backgroundColor: d.backgroundColor || '#1a4a6b',
                            };
                        }),
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { stacked: true },
                            y: { stacked: true, beginAtZero: true, title: { display: true, text: 'kg (estimasi)' } },
                        },
                        plugins: { legend: { position: 'bottom' } },
                    },
                });
            }
            const table = jQuery('#tabel-laporan-limbah').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('laporan-limbah.data')),
                    headers: { 'X-CSRF-TOKEN': csrf },
                    data: function (d) {
                        d.profil_mbg_id = document.getElementById('f-profil')?.value || '';
                        d.kategori_limbah_id = document.getElementById('f-kat')?.value || '';
                        d.jenis_penanganan = document.getElementById('f-jenis')?.value || '';
                        d.tanggal_dari = document.getElementById('f-dari')?.value || '';
                        d.tanggal_sampai = document.getElementById('f-sampai')?.value || '';
                    },
                },
                order: [[0, 'desc']],
                columns: @json($dtCols),
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json' },
            });
            document.getElementById('f-kat')?.addEventListener('change', () => table.ajax.reload());
            document.getElementById('f-jenis')?.addEventListener('change', () => table.ajax.reload());
            document.getElementById('f-dari')?.addEventListener('change', () => table.ajax.reload());
            document.getElementById('f-sampai')?.addEventListener('change', () => table.ajax.reload());
            if (window.jQuery && jQuery.fn.select2) {
                jQuery('#f-kat').select2({ width: '100%' });
            }
            if (window.lucide) lucide.createIcons();
        })();
    </script>
@endpush
