@extends('layouts.app')

@section('title', 'Neraca Keuangan')

@section('content')
    @php
        $q = ['bulan' => $bulan, 'tahun' => $tahun];
    @endphp
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Neraca keuangan</h2>
            <p class="inst-page-desc">{{ $data['mulai']->translatedFormat('F Y') }} — {{ $namaDapur }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('keuangan.laporan.neraca.export-excel', $q) }}" class="inst-btn-outline">Excel</a>
            <a href="{{ route('keuangan.laporan.neraca.export-pdf', $q) }}" target="_blank" class="inst-btn-outline">PDF</a>
        </div>
    </div>

    <form method="GET" action="{{ route('keuangan.laporan.neraca') }}" class="inst-panel mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4 sm:p-6">
        <div>
            <label for="bulan" class="inst-label-filter">Bulan</label>
            <select name="bulan" id="bulan" class="inst-select mt-2">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected((int) $bulan === $m)>{{ \Illuminate\Support\Carbon::create(2000, $m, 1)->translatedFormat('F') }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label for="tahun" class="inst-label-filter">Tahun</label>
            <input type="number" name="tahun" id="tahun" class="inst-input mt-2" min="2000" max="2100" value="{{ $tahun }}">
        </div>
        <div class="flex items-end">
            <button type="submit" class="inst-btn-primary">Tampilkan</button>
        </div>
    </form>

    <div class="inst-panel mb-6 p-6">
        <dl class="grid gap-3 sm:grid-cols-3">
            <div>
                <dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Saldo awal periode</dt>
                <dd class="font-mono text-lg font-semibold">{{ formatRupiah($data['saldo_awal']) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Total masuk</dt>
                <dd class="font-mono text-lg font-semibold text-emerald-800">{{ formatRupiah($data['total_masuk_periode']) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Total keluar</dt>
                <dd class="font-mono text-lg font-semibold text-rose-800">{{ formatRupiah($data['total_keluar_periode']) }}</dd>
            </div>
            <div class="sm:col-span-3">
                <dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Saldo akhir periode</dt>
                <dd class="font-mono text-2xl font-bold" style="color:#1a4a6b;">{{ formatRupiah($data['saldo_akhir']) }}</dd>
            </div>
        </dl>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="inst-panel p-4 sm:p-6">
            <h3 class="mb-3 text-sm font-bold uppercase" style="color:#7fa8c9;">Dana masuk per jenis dana</h3>
            <table class="inst-table">
                <thead><tr><th>Akun (Buku Pembantu Jenis Dana)</th><th class="text-right">Jumlah</th></tr></thead>
                <tbody>
                    @forelse ($data['masuk_per_jenis_dana'] as $row)
                        <tr>
                            <td>{{ $row['nama'] }}</td>
                            <td class="text-right font-mono">{{ formatRupiah($row['total']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-sm" style="color:#7fa8c9;">Tidak ada</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="inst-panel p-4 sm:p-6">
            <h3 class="mb-3 text-sm font-bold uppercase" style="color:#7fa8c9;">Dana keluar per jenis dana</h3>
            <table class="inst-table">
                <thead><tr><th>Akun (Buku Pembantu Jenis Dana)</th><th class="text-right">Jumlah</th></tr></thead>
                <tbody>
                    @forelse ($data['keluar_per_jenis_dana'] as $row)
                        <tr>
                            <td>{{ $row['nama'] }}</td>
                            <td class="text-right font-mono">{{ formatRupiah($row['total']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-sm" style="color:#7fa8c9;">Tidak ada</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
