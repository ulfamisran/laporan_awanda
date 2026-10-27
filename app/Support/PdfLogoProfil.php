<?php

namespace App\Support;

use App\Models\ProfilMbg;
use Illuminate\Support\Facades\Storage;

final class PdfLogoProfil
{
    public static function absolutePath(?ProfilMbg $profil): ?string
    {
        if (! $profil || ! $profil->logo) {
            return null;
        }

        $file = basename((string) $profil->logo);

        foreach (self::candidatePaths($file) as $path) {
            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    public static function publicUrl(?ProfilMbg $profil): ?string
    {
        if (! $profil || ! $profil->logo) {
            return null;
        }

        $file = basename((string) $profil->logo);

        // Hosting: public_html/storage/app/public/logo-mbg/...
        if (is_file(public_path('storage/app/public/logo-mbg/'.$file))) {
            return asset('storage/app/public/logo-mbg/'.$file);
        }

        // Symlink standar: public/storage -> storage/app/public
        if (is_file(public_path('storage/logo-mbg/'.$file))) {
            return asset('storage/logo-mbg/'.$file);
        }

        return Storage::disk('public')->url('logo-mbg/'.$file);
    }

    public static function dataUri(?ProfilMbg $profil): ?string
    {
        $path = self::absolutePath($profil);
        if ($path === null) {
            return null;
        }

        $bin = @file_get_contents($path);
        if ($bin === false || $bin === '') {
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

    /**
     * @return list<string>
     */
    private static function candidatePaths(string $file): array
    {
        return array_values(array_unique([
            Storage::disk('public')->path('logo-mbg/'.$file),
            storage_path('app/public/logo-mbg/'.$file),
            public_path('storage/app/public/logo-mbg/'.$file),
            public_path('storage/logo-mbg/'.$file),
            base_path('public_html/storage/app/public/logo-mbg/'.$file),
        ]));
    }
}
