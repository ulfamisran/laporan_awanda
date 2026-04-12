<?php

namespace App\Enums;

enum SatuanBarang: string
{
    case Kg = 'kg';
    case Gram = 'gram';
    case Liter = 'liter';
    case Ml = 'ml';
    case Pcs = 'pcs';
    case Lusin = 'lusin';
    case Karton = 'karton';
    case Sak = 'sak';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Kg => 'Kilogram (kg)',
            self::Gram => 'Gram',
            self::Liter => 'Liter',
            self::Ml => 'Mililiter (ml)',
            self::Pcs => 'Pieces (pcs)',
            self::Lusin => 'Lusin',
            self::Karton => 'Karton',
            self::Sak => 'Sak',
            self::Lainnya => 'Lainnya',
        };
    }
}
