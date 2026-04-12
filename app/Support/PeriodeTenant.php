<?php

namespace App\Support;

use App\Enums\StatusAktif;
use App\Models\Periode;

/**
 * Periode laporan operasional (stok, keuangan transaksi, limbah, penggajian) per cabang.
 */
final class PeriodeTenant
{
    private static ?int $cachedId = null;

    public static function forgetCached(): void
    {
        self::$cachedId = null;
    }

    public static function id(): int
    {
        if (self::$cachedId !== null) {
            return self::$cachedId;
        }

        $profilId = ProfilMbgTenant::id();
        $sid = (int) session('periode_id', 0);
        if ($sid > 0 && Periode::query()->whereKey($sid)->where('profil_mbg_id', $profilId)->exists()) {
            self::$cachedId = $sid;

            return self::$cachedId;
        }

        $aktif = Periode::query()
            ->where('profil_mbg_id', $profilId)
            ->where('status', StatusAktif::Aktif)
            ->orderByDesc('tanggal_awal')
            ->value('id');

        if ($aktif) {
            session(['periode_id' => (int) $aktif]);
            self::$cachedId = (int) $aktif;

            return self::$cachedId;
        }

        $fallback = Periode::query()
            ->where('profil_mbg_id', $profilId)
            ->orderByDesc('tanggal_awal')
            ->value('id');

        if (! $fallback) {
            abort(503, 'Belum ada periode laporan. Buat periode melalui menu Periode.');
        }

        session(['periode_id' => (int) $fallback]);
        self::$cachedId = (int) $fallback;

        return self::$cachedId;
    }

    public static function model(): Periode
    {
        return Periode::query()->findOrFail(self::id());
    }
}
