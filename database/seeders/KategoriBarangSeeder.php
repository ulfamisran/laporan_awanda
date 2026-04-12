<?php

namespace Database\Seeders;

use App\Models\KategoriBarang;
use Illuminate\Database\Seeder;

class KategoriBarangSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nama_kategori' => 'Bahan Pokok', 'deskripsi' => 'Beras, minyak, gula, dan sejenisnya.'],
            ['nama_kategori' => 'Bumbu', 'deskripsi' => 'Bumbu dapur dan penyedap.'],
            ['nama_kategori' => 'Sayuran', 'deskripsi' => 'Sayur segar dan olahan ringan.'],
            ['nama_kategori' => 'Protein', 'deskripsi' => 'Telur, ikan, ayam, daging, tahu/tempe.'],
            ['nama_kategori' => 'Minuman', 'deskripsi' => 'Air mineral, teh, dan minuman lain.'],
        ];

        foreach ($items as $row) {
            KategoriBarang::query()->updateOrCreate(
                ['nama_kategori' => $row['nama_kategori']],
                ['deskripsi' => $row['deskripsi']]
            );
        }
    }
}
