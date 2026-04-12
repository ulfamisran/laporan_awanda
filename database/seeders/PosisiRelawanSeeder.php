<?php

namespace Database\Seeders;

use App\Models\PosisiRelawan;
use Illuminate\Database\Seeder;

class PosisiRelawanSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['nama_posisi' => 'Koordinator Dapur', 'deskripsi' => 'Mengkoordinasikan operasional dapur harian.'],
            ['nama_posisi' => 'Juru Masak', 'deskripsi' => 'Menyiapkan menu dan memasak.'],
            ['nama_posisi' => 'Asisten Masak', 'deskripsi' => 'Membantu persiapan bahan dan masak.'],
            ['nama_posisi' => 'Petugas Kebersihan', 'deskripsi' => 'Menjaga kebersihan area dapur.'],
            ['nama_posisi' => 'Petugas Distribusi', 'deskripsi' => 'Mengatur distribusi makanan.'],
            ['nama_posisi' => 'Admin Dapur', 'deskripsi' => 'Administrasi dan pencatatan dapur.'],
        ];

        foreach ($rows as $row) {
            PosisiRelawan::query()->firstOrCreate(
                ['nama_posisi' => $row['nama_posisi']],
                $row
            );
        }
    }
}
