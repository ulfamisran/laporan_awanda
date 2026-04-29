@extends('layouts.app')

@section('title', 'Arus Stok')

@section('content')
    @php
        $qBase = array_filter([
            'barang_id' => $barangId > 0 ? $barangId : null,
            'kategori_barang_id' => $kategoriId > 0 ? $kategoriId : null,
            'dari' => $dari->toDateString(),
            'sampai' => $sampai->toDateString(),
        ]);
    @endphp
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h2 class="inst-page-title">Laporan arus stok</h2>
            <p class="inst-page-desc">Kronologi mutasi per barang dengan saldo berjalan dan grafik.</p>
        </div>
        @if ($barangId > 0 && $barang)
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('stok.arus.export-excel', $qBase) }}" class="inst-btn-outline shrink-0">Excel</a>
                <a href="{{ route('stok.arus.export-pdf', $qBase) }}" target="_blank" class="inst-btn-outline shrink-0">PDF</a>
            </div>
        @endif
    </div>

    <form method="GET" action="{{ route('stok.arus.index') }}" class="inst-panel mb-6 p-4 sm:p-6">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
            <div>
                <label for="f_kategori" class="inst-label-filter">Kategori</label>
                <select name="kategori_barang_id" id="f_kategori" class="inst-select mt-2">
                    <option value="">Semua</option>
                    @foreach ($kategoris as $kat)
                        <option value="{{ $kat->id }}" @selected((int) $kategoriId === (int) $kat->id)>{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="f_barang" class="inst-label-filter">Barang</label>
                <select name="barang_id" id="f_barang" class="inst-select select2-stok mt-2">
                    <option value="">Pilih barang…</option>
                    @foreach ($barangs as $br)
                        <option value="{{ $br->id }}" @selected((int) $barangId === (int) $br->id)>{{ $br->kode_barang }} — {{ $br->nama_barang }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="f_dari" class="inst-label-filter">Dari</label>
                <input type="date" name="dari" id="f_dari" class="inst-input mt-2" value="{{ $dari->toDateString() }}">
            </div>
            <div>
                <label for="f_sampai" class="inst-label-filter">Sampai</label>
                <input type="date" name="sampai" id="f_sampai" class="inst-input mt-2" value="{{ $sampai->toDateString() }}">
            </div>
            <div class="flex items-end">
                <button type="submit" class="inst-btn-primary w-full lg:w-auto">Tampilkan</button>
            </div>
        </div>
    </form>

    @if ($barangId > 0 && $barang)
        <div class="inst-panel mb-6 p-4 sm:p-6">
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wide" style="color:#7fa8c9;">Grafik saldo</h3>
            <div style="max-height:320px;">
                <canvas id="chart-arus"></canvas>
            </div>
        </div>

        <div class="inst-panel overflow-hidden p-4 sm:p-6">
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wide" style="color:#7fa8c9;">Tabel kronologis</h3>
            <div class="overflow-x-auto">
                <table class="inst-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th class="text-right">Jumlah</th>
                            <th class="text-right">Saldo</th>
                            <th>Keterangan</th>
                            <th>Input oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $r)
                            @php
                                $arah = (int) ($r['arah'] ?? 0);
                                $jml = (float) ($r['jumlah'] ?? 0);
                                $lbl = $r['label'] ?? '—';
                            @endphp
                            <tr class="{{ ($r['jenis'] ?? '') === 'opening' ? 'bg-slate-50 font-semibold' : '' }}">
                                <td>{{ $r['tanggal_label'] ?? '—' }}</td>
                                <td>{{ $lbl }}</td>
                                <td class="text-right font-mono">
                                    @if ($arah > 0)
                                        <span class="text-emerald-700">+{{ number_format($jml, 2, ',', '.') }}</span>
                                    @elseif ($arah < 0)
                                        <span class="text-rose-700">−{{ number_format($jml, 2, ',', '.') }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-right font-mono">{{ isset($r['saldo']) ? number_format((float) $r['saldo'], 2, ',', '.') : '—' }}</td>
                                <td class="max-w-xs text-sm">{{ $r['keterangan'] ?? '—' }}</td>
                                <td class="text-sm">{{ $r['oleh'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="inst-panel p-8 text-center text-sm" style="color:#7fa8c9;">
            Pilih barang lalu klik <strong>Tampilkan</strong> untuk melihat arus stok.
        </div>
    @endif
@endsection

@push('styles')
    <style>
        #f_barang + .select2-container .select2-selection--single {
            height: 42px;
            border: 1px solid #d4e8f4;
            border-radius: 0.5rem;
            background-color: #ffffff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        #f_barang + .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 40px;
            color: #1a4a6b;
            padding-left: 1rem;
            padding-right: 2rem;
            font-size: 0.875rem;
        }

        #f_barang + .select2-container .select2-selection--single .select2-selection__arrow {
            height: 40px;
            right: 0.5rem;
        }

        #f_barang + .select2-container.select2-container--focus .select2-selection--single {
            border-color: #4a9b7a;
            box-shadow: 0 0 0 1px #4a9b7a;
        }
    </style>
@endpush

@push('scripts')
    @if ($barangId > 0 && $barang && count($chartLabels))
        <script>
            (function () {
                const ctx = document.getElementById('chart-arus');
                if (!ctx || !window.Chart) return;
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            label: @json('Saldo '.$barang->nama_barang),
                            data: @json($chartSaldo),
                            borderColor: '#1a4a6b',
                            backgroundColor: 'rgba(26, 74, 107, 0.08)',
                            fill: true,
                            tension: 0.2,
                            pointRadius: 3,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: false },
                        },
                    },
                });
            })();
        </script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery && jQuery.fn.select2) {
                jQuery('#f_barang').select2({ width: '100%', language: { noResults: () => 'Tidak ada hasil' } });
            }
        });
    </script>
@endpush
