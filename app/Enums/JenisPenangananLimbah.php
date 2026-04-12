<?php

namespace App\Enums;

enum JenisPenangananLimbah: string
{
    case Dibuang = 'dibuang';
    case DidaurUlang = 'didaur_ulang';
    case Dijual = 'dijual';
    case Dikembalikan = 'dikembalikan';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Dibuang => 'Dibuang',
            self::DidaurUlang => 'Didaur ulang',
            self::Dijual => 'Dijual',
            self::Dikembalikan => 'Dikembalikan',
            self::Lainnya => 'Lainnya',
        };
    }
}
