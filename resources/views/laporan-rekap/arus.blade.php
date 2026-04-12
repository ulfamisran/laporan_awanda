@extends('layouts.app')

@section('title', 'Laporan arus stok detail')

@section('content')
    @include('laporan-rekap._tabs', ['tab' => 'arus'])

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Laporan arus stok detail</h2>
            <p class="inst-page-desc">{{ $namaBarang }} · {{ $f['dari'] }} — {{ $f['sampai'] }} · {{ $namaDapur }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('laporan-rekap.arus.export-excel') }}" class="inst-btn-outline">Excel</a>
            <a href="{{ route('laporan-rekap.arus.export-pdf') }}" target="_blank" class="inst-btn-outline">PDF</a>
            <a href="{{ route('laporan-rekap.arus.export-pdf', ['draft' => 1]) }}" target="_blank" class="inst-btn-outline">PDF (draft)</a>
        </div>
    </div>

    <form method="GET" action="{{ route('laporan-rekap.arus') }}" class="inst-panel mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-5 sm:p-6">
        <div class="lg:col-span-2">
            <label for="barang_id" class="inst-label-filter">Barang</label>
            <select name="barang_id" id="barang_id" class="inst-select mt-2">
                @foreach ($barangs as $b)
                    <option value="{{ $b->id }}" @selected((int) $f['barang_id'] === (int) $b->id)>{{ $b->nama_barang }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="dari" class="inst-label-filter">Dari</label>
            <input type="date" name="dari" id="dari" class="inst-input mt-2" value="{{ $f['dari'] }}">
        </div>
        <div>
            <label for="sampai" class="inst-label-filter">Sampai</label>
            <input type="date" name="sampai" id="sampai" class="inst-input mt-2" value="{{ $f['sampai'] }}">
        </div>
        <div class="flex items-end">
            <button type="submit" class="inst-btn-primary">Terapkan</button>
        </div>
    </form>

    <div class="inst-panel overflow-x-auto p-4 sm:p-6">
        <table class="inst-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th class="text-right">Qty</th>
                    <th>Arah</th>
                    <th>Keterangan</th>
                    <th>Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                    <tr>
                        <td class="font-mono text-sm">{{ $r['tanggal'] ?? '—' }}</td>
                        <td>{{ $r['jenis'] }}</td>
                        <td class="text-right font-mono">{{ number_format((float) ($r['qty'] ?? 0), 2, ',', '.') }}</td>
                        <td>{{ $r['arah'] }}</td>
                        <td>{{ $r['keterangan'] }}</td>
                        <td>{{ $r['oleh'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-sm" style="color:#7fa8c9;">Tidak ada mutasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
