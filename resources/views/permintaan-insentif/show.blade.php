@extends('layouts.app')

@section('title', 'Detail Permintaan Insentif')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="inst-page-title">Detail permintaan insentif</h2>
            <p class="inst-page-desc">{{ $item->judul_surat }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('permintaan-insentif.edit', $item) }}" class="inst-btn-outline">Ubah</a>
            <a href="{{ route('permintaan-insentif.cetak-pdf', $item) }}" target="_blank" class="inst-btn-primary">Cetak PDF</a>
            <a href="{{ route('permintaan-insentif.index') }}" class="inst-btn-outline">Kembali</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;">
            {{ session('success') }}
        </div>
    @endif

    @php
        $tglPerihal = optional($item->tanggal_perihal)->translatedFormat('d F Y');
        $jumlahLabel = 'Rp. '.number_format((float) $item->jumlah, 0, ',', '.');
        $perihal = trim((string) $item->perihal_teks);
        if ($perihal === '') {
            $perihal = 'Dengan ini mengajukan Pembayaran Insentif PIC Sekolah untuk Operasional SPPG pada Program '.$item->program.' '.$tglPerihal.' sebesar '.$jumlahLabel.'.';
        }
    @endphp

    <div class="mx-auto max-w-4xl overflow-hidden rounded-sm border bg-white shadow-sm" style="border-color:#cbd5e1;">
        <div class="border-b px-6 py-5 text-center" style="border-color:#1a4a6b;color:#1a4a6b;">
            @if ($profil?->logo)
                <img src="{{ asset('storage/logo-mbg/'.$profil->logo) }}" alt="Logo SPPG" class="mx-auto mb-3 max-h-20 object-contain">
            @endif
            <div class="text-sm font-bold">BADAN GIZI NASIONAL (<em>NATIONAL NUTRITION AGENCY</em>)</div>
            <div class="text-sm font-bold">SATUAN PELAYANAN PEMENUHAN GIZI (SPPG)</div>
            <div class="text-sm font-bold">{{ $profil->daerah_sppg ?: $profil->nama_dapur }}</div>
            <div class="text-xs">{{ $profil->alamat ?: '—' }}</div>
        </div>

        <div class="space-y-4 px-8 py-6 text-sm leading-relaxed" style="color:#1f2937;">
            <h1 class="text-center text-base font-bold uppercase underline">{{ $item->judul_surat }}</h1>

            <table class="text-sm">
                <tr><td class="pr-2 align-top" style="width:5.5rem;">Periode</td><td class="pr-2">:</td><td>{{ $item->periode_label }}</td></tr>
                <tr><td class="pr-2 align-top">Program</td><td class="pr-2">:</td><td>{{ $item->program }}</td></tr>
            </table>

            <div>
                <p class="mb-1">Yang Bertanda Tangan di bawah ini :</p>
                <table class="text-sm">
                    <tr><td class="pr-2" style="width:5.5rem;">SPPG</td><td class="pr-2">:</td><td>{{ $item->nama_sppg }}</td></tr>
                    <tr><td class="pr-2">Nama</td><td class="pr-2">:</td><td>{{ $item->nama_peminta }}</td></tr>
                    <tr><td class="pr-2">Jabatan</td><td class="pr-2">:</td><td>{{ $item->jabatan_peminta }}</td></tr>
                </table>
            </div>

            <p class="text-justify">
                {!! preg_replace(
                    ['/'.preg_quote($tglPerihal, '/').'/', '/'.preg_quote($jumlahLabel, '/').'/'],
                    ['<strong>'.$tglPerihal.'</strong>', '<strong>'.$jumlahLabel.'</strong>'],
                    e($perihal),
                    1
                ) !!}
            </p>

            <div>
                <p class="font-semibold">Catatan :</p>
                <p class="text-justify">{{ $item->catatan ?: '—' }}</p>
            </div>

            <div class="grid gap-8 pt-6 sm:grid-cols-2">
                <div class="hidden sm:block"></div>
                <div class="text-center text-sm">
                    <p>{{ trim(($item->tempat_ttd ?: '').', '.(optional($item->tanggal_ttd)->translatedFormat('d F Y') ?: '')) }}</p>
                    <p>Pemohon,</p>
                    <p>{{ $item->jabatan_peminta }}</p>
                    <div class="h-16"></div>
                    <p class="font-semibold">({{ $item->nama_peminta }})</p>
                </div>
            </div>

            <div class="grid gap-8 pt-8 sm:grid-cols-2">
                <div class="text-center text-sm">
                    <p class="font-semibold">Mengetahui</p>
                    <p>{{ $item->ttd_mengetahui_jabatan ?: '—' }}</p>
                    <div class="h-16"></div>
                    <p class="font-semibold">({{ $item->ttd_mengetahui_nama ?: '………………' }})</p>
                </div>
                <div class="text-center text-sm">
                    <p class="font-semibold">Menyetujui</p>
                    <p>{{ $item->ttd_menyetujui_jabatan ?: 'Kepala SPPG' }}</p>
                    <div class="h-16"></div>
                    <p class="font-semibold">({{ $item->ttd_menyetujui_nama ?: '………………' }})</p>
                </div>
            </div>
        </div>
    </div>
@endsection
