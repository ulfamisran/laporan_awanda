<?php

namespace App\Support;

/**
 * URL gambar limbah via route Laravel (bukan /storage/ langsung).
 * Route membaca file dari storage/app/public — tidak bergantung symlink public/storage.
 */
final class LimbahGambarUrl
{
    public static function for(?string $filename): ?string
    {
        if ($filename === null || $filename === '') {
            return null;
        }

        $filename = basename($filename);

        return route('laporan-limbah.gambar', ['filename' => $filename]);
    }
}
