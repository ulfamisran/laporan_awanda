<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

if (! function_exists('formatRupiah')) {
    /**
     * Memformat angka menjadi mata uang Rupiah (contoh: Rp 1.000.000).
     */
    function formatRupiah(null|int|float|string $angka = 0): string
    {
        $nilai = is_numeric($angka) ? (float) $angka : (float) preg_replace('/[^\d.-]/', '', (string) $angka);

        return 'Rp '.number_format($nilai, 0, ',', '.');
    }
}

if (! function_exists('formatTanggal')) {
    /**
     * Memformat tanggal ke DD/MM/YYYY.
     */
    function formatTanggal(null|string|DateTimeInterface $date): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        if ($date instanceof DateTimeInterface) {
            return $date->format('d/m/Y');
        }

        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (Throwable) {
            return '';
        }
    }
}

if (! function_exists('generateKode')) {
    /**
     * Menghasilkan kode unik untuk transaksi berdasarkan awalan.
     */
    function generateKode(string $prefix): string
    {
        $slug = Str::upper(Str::slug($prefix, '_'));

        return $slug.'_'.now()->format('YmdHis').'_'.Str::upper(Str::random(4));
    }
}
