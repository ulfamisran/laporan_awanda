<?php

namespace App\Enums;

enum SatuanLimbah: string
{
    case Kg = 'kg';
    case Liter = 'liter';
    case Pcs = 'pcs';
    case Karung = 'karung';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Kg => 'kg',
            self::Liter => 'liter',
            self::Pcs => 'pcs',
            self::Karung => 'karung',
            self::Lainnya => 'lainnya',
        };
    }
}
