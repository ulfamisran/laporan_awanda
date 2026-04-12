@extends('layouts.app')

@section('title', 'Laporan komprehensif')

@section('content')
    @include('laporan-rekap._tabs', ['tab' => 'komprehensif'])

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="inst-page-title">Laporan komprehensif</h2>
            <p class="inst-page-desc">Satu dokumen PDF untuk pelaporan ke pusat · {{ $namaDapur }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('laporan-rekap.komprehensif.export-pdf') }}" target="_blank" class="inst-btn-primary">Unduh PDF</a>
            <a href="{{ route('laporan-rekap.komprehensif.export-pdf', ['draft' => 1]) }}" target="_blank" class="inst-btn-outline">PDF (draft)</a>
        </div>
    </div>

    <form method="GET" action="{{ route('laporan-rekap.komprehensif') }}" class="inst-panel mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4 sm:p-6">
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

    <div class="inst-panel p-6 text-sm" style="color:#4a6b7f;">
        <p class="mb-2">Isi dokumen PDF mencakup:</p>
        <ul class="list-disc space-y-1 pl-5">
            <li>Rekap stok semua barang aktif pada rentang tanggal.</li>
            <li>Neraca keuangan untuk bulan sesuai tanggal akhir periode.</li>
            <li>Daftar penggajian relawan pada rentang bulan dalam periode.</li>
            <li>Rekap limbah per kategori dan penanganan.</li>
        </ul>
    </div>
@endsection
