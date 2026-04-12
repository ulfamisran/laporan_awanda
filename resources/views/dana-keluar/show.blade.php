@extends('layouts.app')

@section('title', 'Detail Dana Keluar')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
@endpush

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="inst-page-title">Detail dana keluar</h2>
            <p class="font-mono text-sm" style="color:#4a6b7f;">{{ $keluar->kode_transaksi }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('keuangan.keluar.bukti-pdf', $keluar) }}" target="_blank" class="inst-btn-outline">Cetak PDF</a>
            <a href="{{ route('keuangan.keluar.edit', $keluar) }}" class="inst-btn-outline">Ubah</a>
            <a href="{{ route('keuangan.keluar.index') }}" class="inst-btn-outline">Kembali</a>
        </div>
    </div>

    <div class="inst-panel mb-6 p-6">
        <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
            <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Tanggal</dt><dd>{{ $keluar->tanggal->format('d/m/Y') }}</dd></div>
            <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Jenis Buku Pembantu</dt><dd>{{ $keluar->akunJenisDana ? ($keluar->akunJenisDana->kode.' — '.$keluar->akunJenisDana->nama) : '—' }} <span class="text-xs font-normal" style="color:#7fa8c9;">(Jenis Dana)</span></dd></div>
            <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Jenis Buku Kas</dt><dd>{{ $keluar->akunKas ? ($keluar->akunKas->kode.' — '.$keluar->akunKas->nama) : '—' }}</dd></div>
            <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Nomor bukti</dt><dd class="font-mono">{{ $keluar->nomor_bukti ?? '—' }}</dd></div>
            <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Jumlah</dt><dd class="font-mono font-semibold">{{ formatRupiah($keluar->jumlah) }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Uraian transaksi</dt><dd class="whitespace-pre-wrap">{{ $keluar->uraian_transaksi ?: '—' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Keperluan</dt><dd>{{ $keluar->keperluan }}</dd></div>
            <div><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Input oleh</dt><dd>{{ $keluar->creator?->name }}</dd></div>
            @if ($keluar->keterangan)
                <div class="sm:col-span-2"><dt class="text-xs font-bold uppercase" style="color:#7fa8c9;">Keterangan</dt><dd>{{ $keluar->keterangan }}</dd></div>
            @endif
        </dl>
    </div>

    @if (count($keluar->gambarNotaUrls()) > 0)
        <div class="inst-panel p-6">
            <h3 class="mb-3 text-sm font-bold uppercase" style="color:#7fa8c9;">Nota</h3>
            <div class="flex flex-wrap gap-3">
                @foreach ($keluar->gambarNotaUrls() as $url)
                    <div class="text-center">
                        <a href="{{ $url }}" class="glightbox block" data-gallery="nota-keluar">
                            <img src="{{ $url }}" alt="" class="h-32 w-32 rounded-lg border object-cover" style="border-color:#d4e8f4;">
                        </a>
                        <a href="{{ $url }}" download class="mt-1 inline-block text-xs font-semibold" style="color:#1a4a6b;">Unduh</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.GLightbox) GLightbox({ selector: '.glightbox' });
        });
    </script>
@endpush
