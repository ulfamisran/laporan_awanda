<?php

namespace App\Enums;

enum BarangKeluarTujuan: string
{
    case Produksi = 'produksi';
    case Distribusi = 'distribusi';
    case Rusak = 'rusak';
    case TransferKeluar = 'transfer_keluar';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Produksi => 'Produksi',
            self::Distribusi => 'Distribusi',
            self::Rusak => 'Rusak',
            self::TransferKeluar => 'Transfer keluar',
            self::Lainnya => 'Lainnya',
        };
    }
}
