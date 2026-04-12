<?php

namespace Database\Seeders;

use App\Models\KategoriDanaMasuk;
use Illuminate\Database\Seeder;

class KategoriDanaMasukSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nama_kategori' => 'Dana Pemerintah', 'deskripsi' => 'Alokasi atau bantuan dari pemerintah.'],
            ['nama_kategori' => 'Donasi', 'deskripsi' => 'Sumbangan dari masyarakat atau mitra.'],
            ['nama_kategori' => 'Iuran', 'deskripsi' => 'Iuran rutin atau kontribusi terjadwal.'],
        ];

        foreach ($items as $row) {
            KategoriDanaMasuk::query()->updateOrCreate(
                ['nama_kategori' => $row['nama_kategori']],
                ['deskripsi' => $row['deskripsi']]
            );
        }
    }
}
