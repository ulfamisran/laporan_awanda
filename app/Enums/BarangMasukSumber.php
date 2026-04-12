<?php

namespace App\Enums;

enum BarangMasukSumber: string
{
    case Pembelian = 'pembelian';
    case Donasi = 'donasi';
    case TransferMasuk = 'transfer_masuk';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Pembelian => 'Pembelian',
            self::Donasi => 'Donasi',
            self::TransferMasuk => 'Transfer masuk',
            self::Lainnya => 'Lainnya',
        };
    }
}
