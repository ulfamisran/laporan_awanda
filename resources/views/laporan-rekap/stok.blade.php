@extends('layouts.app')

@section('title', 'Laporan stok barang')

@section('content')
    @include('laporan-rekap._tabs', ['tab' => 'stok'])

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Laporan stok barang</h2>
            <p class="inst-page-desc">{{ $f['dari'] }} — {{ $f['sampai'] }} · {{ $namaDapur }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('laporan-rekap.stok.export-excel') }}" class="inst-btn-outline">Excel</a>
            <a href="{{ route('laporan-rekap.stok.export-pdf') }}" target="_blank" class="inst-btn-outline">PDF</a>
            <a href="{{ route('laporan-rekap.stok.export-pdf', ['draft' => 1]) }}" target="_blank" class="inst-btn-outline">PDF (draft)</a>
        </div>
    </div>

    <form method="GET" action="{{ route('laporan-rekap.stok') }}" class="inst-panel mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-6 sm:p-6">
        <div>
            <label for="dari" class="inst-label-filter">Dari</label>
            <input type="date" name="dari" id="dari" class="inst-input mt-2" value="{{ $f['dari'] }}">
        </div>
        <div>
            <label for="sampai" class="inst-label-filter">Sampai</label>
            <input type="date" name="sampai" id="sampai" class="inst-input mt-2" value="{{ $f['sampai'] }}">
        </div>
        <div>
            <label for="barang_id" class="inst-label-filter">Barang</label>
            <select name="barang_id" id="barang_id" class="inst-select mt-2">
                <option value="">Semua</option>
                @foreach ($barangs as $b)
                    <option value="{{ $b->id }}" @selected((string) $f['barang_id'] === (string) $b->id)>{{ $b->nama_barang }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="kategori_barang_id" class="inst-label-filter">Kategori</label>
            <select name="kategori_barang_id" id="kategori_barang_id" class="inst-select mt-2">
                <option value="">Semua</option>
                @foreach ($kategoris as $k)
                    <option value="{{ $k->id }}" @selected((string) $f['kategori_barang_id'] === (string) $k->id)>{{ $k->nama_kategori }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="inst-btn-primary">Terapkan</button>
        </div>
    </form>

    <div class="inst-panel overflow-x-auto p-4 sm:p-6">
        <table class="inst-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama barang</th>
                    <th>Kategori</th>
                    <th class="text-right">Stok awal</th>
                    <th class="text-right">Masuk</th>
                    <th class="text-right">Keluar</th>
                    <th class="text-right">Saldo akhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                    <tr>
                        <td class="font-mono text-sm">{{ $r->kode }}</td>
                        <td>{{ $r->nama }}</td>
                        <td>{{ $r->kategori }}</td>
                        <td class="text-right font-mono">{{ number_format((float) $r->stok_awal, 2, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ number_format((float) $r->masuk, 2, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ number_format((float) $r->keluar, 2, ',', '.') }}</td>
                        <td class="text-right font-mono font-semibold">{{ number_format((float) $r->saldo_akhir, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-sm" style="color:#7fa8c9;">Tidak ada barang sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
