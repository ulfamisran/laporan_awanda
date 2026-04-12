@extends('layouts.app')

@section('title', $title ?? 'Dasbor')

@section('header_subtitle')
    Ringkasan operasional — {{ formatTanggal(now()) }}
@endsection

@section('content')
    <div id="dash-skeleton" class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (range(1, 4) as $i)
                <div class="animate-pulse rounded-2xl bg-white p-6" style="border:1px solid #d4e8f4;height:120px;"></div>
            @endforeach
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (range(1, 4) as $i)
                <div class="animate-pulse rounded-2xl bg-white p-6" style="border:1px solid #d4e8f4;height:120px;"></div>
            @endforeach
        </div>
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div class="animate-pulse rounded-2xl bg-white p-6 lg:col-span-1" style="border:1px solid #d4e8f4;height:280px;"></div>
            <div class="animate-pulse rounded-2xl bg-white p-6" style="border:1px solid #d4e8f4;height:280px;"></div>
        </div>
    </div>

    <div id="dash-root" class="hidden space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="stat-card rounded-2xl bg-white p-6" style="border:1px solid #d4e8f4;">
                <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Barang aktif</p>
                <p id="c-barang" class="mt-2 text-3xl font-bold" style="color:#1a4a6b;">—</p>
            </div>
            <div class="stat-card rounded-2xl bg-white p-6" style="border:1px solid #d4e8f4;">
                <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Stok kritis</p>
                <p id="c-kritis" class="mt-2 text-3xl font-bold" style="color:#c0392b;">—</p>
            </div>
            <div class="stat-card rounded-2xl bg-white p-6" style="border:1px solid #d4e8f4;">
                <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Saldo dana</p>
                <p id="c-saldo" class="mt-2 text-xl font-bold font-mono" style="color:#2d7a60;">—</p>
            </div>
            <div class="stat-card rounded-2xl bg-white p-6" style="border:1px solid #d4e8f4;">
                <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Relawan aktif</p>
                <p id="c-relawan" class="mt-2 text-3xl font-bold" style="color:#1a4a6b;">—</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="stat-card rounded-2xl bg-white p-6" style="border:1px solid #d4e8f4;">
                <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Dana masuk (bulan ini)</p>
                <p id="c-dm" class="mt-2 text-lg font-bold font-mono" style="color:#1a4a6b;">—</p>
            </div>
            <div class="stat-card rounded-2xl bg-white p-6" style="border:1px solid #d4e8f4;">
                <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Dana keluar (bulan ini)</p>
                <p id="c-dk" class="mt-2 text-lg font-bold font-mono" style="color:#c0392b;">—</p>
            </div>
            <div class="stat-card rounded-2xl bg-white p-6" style="border:1px solid #d4e8f4;">
                <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Nilai barang masuk</p>
                <p id="c-bm" class="mt-2 text-lg font-bold font-mono" style="color:#2d7a60;">—</p>
            </div>
            <div class="stat-card rounded-2xl bg-white p-6" style="border:1px solid #d4e8f4;">
                <p class="text-xs font-bold uppercase" style="color:#7fa8c9;">Limbah tercatat (kg est.)</p>
                <p id="c-lb" class="mt-2 text-lg font-bold font-mono" style="color:#1a4a6b;">—</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div class="rounded-2xl bg-white p-4 sm:p-6" style="border:1px solid #d4e8f4;">
                <h2 class="mb-2 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Arus dana (6 bulan)</h2>
                <div class="h-64"><canvas id="chart-dana"></canvas></div>
            </div>
            <div class="rounded-2xl bg-white p-4 sm:p-6" style="border:1px solid #d4e8f4;">
                <h2 class="mb-2 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Volume barang masuk vs keluar</h2>
                <div class="h-64"><canvas id="chart-barang"></canvas></div>
            </div>
            <div class="rounded-2xl bg-white p-4 sm:p-6" style="border:1px solid #d4e8f4;">
                <h2 class="mb-2 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Dana keluar per kategori (bulan ini)</h2>
                <div class="h-64 max-w-md mx-auto"><canvas id="chart-pie"></canvas></div>
            </div>
            <div class="rounded-2xl bg-white p-4 sm:p-6" style="border:1px solid #d4e8f4;">
                <h2 class="mb-2 text-sm font-bold uppercase tracking-wide" style="color:#1a4a6b;">Top 5 barang keluar (bulan ini)</h2>
                <div class="h-64"><canvas id="chart-top"></canvas></div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div class="rounded-2xl bg-white p-6" style="border:1px solid #d4e8f4;">
                <h2 class="mb-4 text-lg font-semibold" style="font-family:'Plus Jakarta Sans',sans-serif;color:#1a4a6b;">Aktivitas terbaru</h2>
                <div id="dash-aktivitas" class="space-y-3 text-sm"></div>
            </div>
            <div class="rounded-2xl bg-white p-6" style="border:1px solid #d4e8f4;">
                <h2 class="mb-4 text-lg font-semibold" style="font-family:'Plus Jakarta Sans',sans-serif;color:#1a4a6b;">Perhatian</h2>
                <div id="dash-alerts" class="space-y-4 text-sm"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const url = @json(route('dashboard.data'));
            const qs = '';

            function fmtRp(n) {
                const x = Number(n) || 0;
                return 'Rp ' + x.toLocaleString('id-ID', { maximumFractionDigits: 0 });
            }
            function badgeCls(b) {
                const m = { emerald: 'background:#d4f0e8;color:#2d7a60;', amber: 'background:#fef3c7;color:#92400e;', blue: 'background:#dbeafe;color:#1e40af;', rose: 'background:#ffe4e6;color:#be123c;' };
                return m[b] || 'background:#e8ecef;color:#4a5568;';
            }

            fetch(url + qs, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then((r) => r.json())
                .then((d) => {
                    document.getElementById('dash-skeleton')?.classList.add('hidden');
                    const root = document.getElementById('dash-root');
                    root?.classList.remove('hidden');

                    const c1 = d.cards_row1;
                    document.getElementById('c-barang').textContent = c1.total_barang_aktif;
                    document.getElementById('c-kritis').textContent = c1.stok_kritis;
                    document.getElementById('c-saldo').textContent = fmtRp(c1.saldo_dana);
                    document.getElementById('c-relawan').textContent = c1.relawan_aktif;

                    const c2 = d.cards_row2;
                    document.getElementById('c-dm').textContent = fmtRp(c2.dana_masuk);
                    document.getElementById('c-dk').textContent = fmtRp(c2.dana_keluar);
                    document.getElementById('c-bm').textContent = fmtRp(c2.nilai_barang_masuk);
                    document.getElementById('c-lb').textContent = (Number(c2.limbah_kg) || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' kg';

                    const ch = d.charts;
                    if (window.Chart) {
                        new Chart(document.getElementById('chart-dana'), {
                            type: 'line',
                            data: {
                                labels: ch.dana_6m.labels,
                                datasets: [
                                    { label: 'Masuk', data: ch.dana_6m.masuk, borderColor: '#2d7a60', tension: 0.2, fill: false },
                                    { label: 'Keluar', data: ch.dana_6m.keluar, borderColor: '#c0392b', tension: 0.2, fill: false },
                                ],
                            },
                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
                        });
                        new Chart(document.getElementById('chart-barang'), {
                            type: 'bar',
                            data: {
                                labels: ch.barang_masuk_keluar_6m.labels,
                                datasets: [
                                    { label: 'Masuk', data: ch.barang_masuk_keluar_6m.masuk, backgroundColor: '#4a9b7a' },
                                    { label: 'Keluar', data: ch.barang_masuk_keluar_6m.keluar, backgroundColor: '#7fa8c9' },
                                ],
                            },
                            options: { responsive: true, maintainAspectRatio: false, scales: { x: { stacked: false } }, plugins: { legend: { position: 'bottom' } } },
                        });
                        new Chart(document.getElementById('chart-pie'), {
                            type: 'pie',
                            data: {
                                labels: ch.dana_keluar_pie_bulan_ini.labels,
                                datasets: [{ data: ch.dana_keluar_pie_bulan_ini.values, backgroundColor: ['#1a4a6b', '#4a9b7a', '#7fa8c9', '#2d7a60', '#f59e0b', '#8b5cf6'] }],
                            },
                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
                        });
                        new Chart(document.getElementById('chart-top'), {
                            type: 'bar',
                            data: {
                                labels: ch.top_barang_keluar.map((x) => x.nama),
                                datasets: [{ label: 'Jumlah keluar', data: ch.top_barang_keluar.map((x) => x.jumlah), backgroundColor: '#1a4a6b' }],
                            },
                            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } } },
                        });
                    }

                    const act = document.getElementById('dash-aktivitas');
                    if (act) {
                        act.innerHTML = (d.aktivitas || []).map(function (a) {
                            const val = a.nominal || a.jumlah || '—';
                            return '<div class="flex flex-wrap items-start justify-between gap-2 border-b pb-2" style="border-color:#eef6fb;">'
                                + '<div><span class="rounded px-2 py-0.5 text-[11px] font-semibold" style="' + badgeCls(a.badge) + '">' + a.jenis_label + '</span>'
                                + '<p class="mt-1" style="color:#1a4a6b;">' + a.deskripsi + '</p>'
                                + '<p class="text-xs inst-td-muted">' + (a.oleh || '—') + '</p></div>'
                                + '<div class="text-right font-mono text-sm font-semibold" style="color:#1a4a6b;">' + val + '</div>'
                                + '<div class="w-full text-xs inst-td-muted">' + a.waktu + '</div></div>';
                        }).join('') || '<p class="inst-td-muted">Belum ada aktivitas.</p>';
                    }

                    const al = document.getElementById('dash-alerts');
                    let html = '';
                    const sk = d.alert_stok_kritis || [];
                    if (sk.length) {
                        html += '<p class="font-semibold" style="color:#c0392b;">Stok kritis</p><ul class="mt-1 list-disc pl-5 space-y-1">';
                        sk.slice(0, 8).forEach(function (r) {
                            html += '<li>' + r.nama_barang + (r.nama_dapur ? ' · ' + r.nama_dapur : '') + ' — stok ' + Number(r.stok).toFixed(2) + ' &lt; min ' + Number(r.stok_minimum).toFixed(2) + '</li>';
                        });
                        html += '</ul>';
                    }
                    const pg = d.alert_penggajian || [];
                    if (pg.length) {
                        html += '<p class="mt-4 font-semibold" style="color:#92400e;">Penggajian belum dibayar (bulan ini)</p><ul class="mt-1 list-disc pl-5 space-y-1">';
                        pg.slice(0, 8).forEach(function (p) {
                            html += '<li>' + (p.nama_relawan || '—') + ' — ' + (p.status_label || '') + ' · ' + (p.periode || '') + (p.dapur ? ' · ' + p.dapur : '') + '</li>';
                        });
                        html += '</ul>';
                    }
                    if (!html) html = '<p class="inst-td-muted">Tidak ada alert.</p>';
                    al.innerHTML = html;

                    if (window.lucide) lucide.createIcons();
                })
                .catch(() => {
                    document.getElementById('dash-skeleton')?.classList.add('hidden');
                    document.getElementById('dash-root')?.classList.remove('hidden');
                    document.getElementById('dash-root').innerHTML = '<p class="text-red-600">Gagal memuat data dasbor.</p>';
                });
        })();
    </script>
@endpush
