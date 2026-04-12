<?php

namespace App\Support;

use App\Models\DanaKeluar;
use App\Models\DanaMasuk;
use App\Models\StokDanaAwal;
use App\Models\StokDanaAwalAkun;
use Illuminate\Support\Carbon;

class SaldoDana
{
    /**
     * Saldo = stok dana awal (per dapur) + total masuk − total keluar (sampai tanggal).
     */
    public static function getSaldoDana(int $profilMbgId, ?Carbon $sampaiTanggal = null): float
    {
        $sampai = $sampaiTanggal?->toDateString() ?? now()->toDateString();

        $stokId = StokDanaAwal::query()
            ->where('profil_mbg_id', $profilMbgId)
            ->value('id');

        $awal = $stokId
            ? (float) StokDanaAwalAkun::query()->where('stok_dana_awal_id', $stokId)->sum('saldo_awal')
            : 0.0;

        $masuk = (float) DanaMasuk::query()
            ->where('profil_mbg_id', $profilMbgId)
            ->where('tanggal', '<=', $sampai)
            ->sum('jumlah');

        $keluar = (float) DanaKeluar::query()
            ->where('profil_mbg_id', $profilMbgId)
            ->where('tanggal', '<=', $sampai)
            ->sum('jumlah');

        return $awal + $masuk - $keluar;
    }
}
