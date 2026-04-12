<?php

namespace Database\Seeders;

use App\Models\KategoriLimbah;
use Illuminate\Database\Seeder;

class KategoriLimbahSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nama_kategori' => 'Organik', 'deskripsi' => 'Sisa makanan, sayur, dan material biodegradable.'],
            ['nama_kategori' => 'Anorganik', 'deskripsi' => 'Plastik, logam, kaca, dan sejenisnya.'],
            ['nama_kategori' => 'B3', 'deskripsi' => 'Limbah B3 sesuai regulasi (mis. oli bekas).'],
        ];

        foreach ($items as $row) {
            KategoriLimbah::query()->updateOrCreate(
                ['nama_kategori' => $row['nama_kategori']],
                ['deskripsi' => $row['deskripsi']]
            );
        }
    }
}
