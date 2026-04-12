<?php

namespace App\Support;

use App\Enums\SatuanLimbah;

/**
 * Estimasi agregat volume untuk ringkasan "kg" (heuristik sederhana).
 */
final class LimbahVolumeKg
{
    /** Asumsi: 1 karung ≈ 25 kg limbah padat. */
    private const KG_PER_KARUNG = 25.0;

    public static function estimate(float $jumlah, SatuanLimbah $satuan): float
    {
        return match ($satuan) {
            SatuanLimbah::Kg => $jumlah,
            SatuanLimbah::Liter => $jumlah,
            SatuanLimbah::Karung => $jumlah * self::KG_PER_KARUNG,
            SatuanLimbah::Pcs => 0.0,
            SatuanLimbah::Lainnya => $jumlah,
        };
    }
}
