<?php

namespace App\Enums;

enum StatusPenggajian: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Dibayar = 'dibayar';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Approved => 'Disetujui',
            self::Dibayar => 'Dibayar',
        };
    }
}
