<?php

namespace App\Support;

use App\Models\ProfilMbg;

/**
 * Satu instalasi = satu profil cabang MBG (identitas laporan).
 */
final class ProfilMbgTenant
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

        $id = ProfilMbg::query()->orderBy('id')->value('id');
        if (! $id) {
            abort(503, 'Profil cabang MBG belum tersedia. Jalankan migrasi atau hubungi super admin untuk mengisi data profil.');
        }

        self::$cachedId = (int) $id;

        return self::$cachedId;
    }

    public static function model(): ProfilMbg
    {
        return ProfilMbg::query()->findOrFail(self::id());
    }
}
