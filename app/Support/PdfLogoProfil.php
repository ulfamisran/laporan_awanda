<?php

namespace App\Support;

use App\Models\ProfilMbg;
use Illuminate\Support\Facades\Storage;

final class PdfLogoProfil
{
    public static function dataUri(?ProfilMbg $profil): ?string
    {
        if (! $profil || ! $profil->logo) {
            return null;
        }

        $path = Storage::disk('public')->path('logo-mbg/'.$profil->logo);
        if (! is_file($path)) {
            return null;
        }

        $bin = @file_get_contents($path);
        if ($bin === false) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        return 'data:'.$mime.';base64,'.base64_encode($bin);
    }
}
