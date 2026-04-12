<?php

namespace Database\Seeders;

use App\Models\KategoriDanaKeluar;
use Illuminate\Database\Seeder;

class KategoriDanaKeluarSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nama_kategori' => 'Pembelian Bahan', 'deskripsi' => 'Pengeluaran untuk pembelian bahan baku.'],
            ['nama_kategori' => 'Operasional', 'deskripsi' => 'Listrik, air, ATK, transport, dll.'],
            ['nama_kategori' => 'Gaji', 'deskripsi' => 'Honor atau gaji karyawan / relawan tetap.'],
            ['nama_kategori' => 'Lain-lain', 'deskripsi' => 'Pengeluaran lain di luar kategori utama.'],
        ];

        foreach ($items as $row) {
            KategoriDanaKeluar::query()->updateOrCreate(
                ['nama_kategori' => $row['nama_kategori']],
                ['deskripsi' => $row['deskripsi']]
            );
        }
    }
}
