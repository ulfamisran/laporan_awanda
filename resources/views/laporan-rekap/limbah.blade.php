@extends('layouts.app')

@section('title', 'Laporan limbah')

@section('content')
    @include('laporan-rekap._tabs', ['tab' => 'limbah'])

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Laporan limbah</h2>
            <p class="inst-page-desc">{{ $f['dari'] }} — {{ $f['sampai'] }} · {{ $namaDapur }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('laporan-rekap.limbah.export-excel') }}" class="inst-btn-outline">Excel</a>
            <a href="{{ route('laporan-rekap.limbah.export-pdf') }}" target="_blank" class="inst-btn-outline">PDF</a>
            <a href="{{ route('laporan-rekap.limbah.export-pdf', ['draft' => 1]) }}" target="_blank" class="inst-btn-outline">PDF (draft)</a>
        </div>
    </div>

    <form method="GET" action="{{ route('laporan-rekap.limbah') }}" class="inst-panel mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-5 sm:p-6">
        <div>
            <label for="dari" class="inst-label-filter">Dari</label>
            <input type="date" name="dari" id="dari" class="inst-input mt-2" value="{{ $f['dari'] }}">
        </div>
        <div>
            <label for="sampai" class="inst-label-filter">Sampai</label>
            <input type="date" name="sampai" id="sampai" class="inst-input mt-2" value="{{ $f['sampai'] }}">
        </div>
        <div>
            <label for="kategori_limbah_id" class="inst-label-filter">Kategori</label>
            <select name="kategori_limbah_id" id="kategori_limbah_id" class="inst-select mt-2">
                <option value="">Semua</option>
                @foreach ($kategoris as $k)
                    <option value="{{ $k->id }}" @selected((string) $f['kategori_limbah_id'] === (string) $k->id)>{{ $k->nama_kategori }}</option>
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
                    <th>Kategori</th>
                    <th class="text-right">Total (kg est.)</th>
                    <th class="text-right">Dibuang</th>
                    <th class="text-right">Daur ulang</th>
                    <th class="text-right">Dijual</th>
                    <th class="text-right">Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                    <tr>
                        <td>{{ $r->nama_kategori }}</td>
                        <td class="text-right font-mono">{{ number_format((float) $r->total_kg, 2, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ number_format((float) $r->dibuang, 2, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ number_format((float) $r->daur, 2, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ number_format((float) $r->dijual, 2, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ formatRupiah($r->pendapatan) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-sm" style="color:#7fa8c9;">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
