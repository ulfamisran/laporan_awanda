<?php

namespace Database\Seeders;

use App\Models\AkunDana;
use Illuminate\Database\Seeder;

class AkunDanaSeeder extends Seeder
{
    public function run(): void
    {
        if (AkunDana::query()->exists()) {
            return;
        }

        $t = now();
        $id1000 = (int) AkunDana::query()->create([
            'kode' => '1000', 'nama' => 'BUKU KAS UMUM', 'parent_id' => null, 'urutan' => 10, 'is_grup' => true,
        ])->getKey();
        $id1100 = (int) AkunDana::query()->create([
            'kode' => '1100', 'nama' => 'BUKU PEMBANTU KAS', 'parent_id' => $id1000, 'urutan' => 20, 'is_grup' => true,
        ])->getKey();
        AkunDana::query()->insert([
            ['kode' => '1101', 'nama' => 'Petty Cash / Kas kecil', 'parent_id' => $id1100, 'urutan' => 30, 'is_grup' => false, 'created_at' => $t, 'updated_at' => $t],
            ['kode' => '1102', 'nama' => 'Kas di Bank', 'parent_id' => $id1100, 'urutan' => 40, 'is_grup' => false, 'created_at' => $t, 'updated_at' => $t],
        ]);
        $id2000 = (int) AkunDana::query()->create([
            'kode' => '2000', 'nama' => 'BUKU PEMBANTU JENIS DANA', 'parent_id' => $id1000, 'urutan' => 50, 'is_grup' => true,
        ])->getKey();
        AkunDana::query()->insert([
            ['kode' => '2110', 'nama' => 'Dana Bantuan Pemerintah', 'parent_id' => $id2000, 'urutan' => 60, 'is_grup' => false, 'created_at' => $t, 'updated_at' => $t],
            ['kode' => '2120', 'nama' => 'Biaya Bahan Baku', 'parent_id' => $id2000, 'urutan' => 70, 'is_grup' => false, 'created_at' => $t, 'updated_at' => $t],
            ['kode' => '2130', 'nama' => 'Biaya Operasional', 'parent_id' => $id2000, 'urutan' => 80, 'is_grup' => false, 'created_at' => $t, 'updated_at' => $t],
            ['kode' => '2140', 'nama' => 'Biaya Insentif Fasilitas', 'parent_id' => $id2000, 'urutan' => 90, 'is_grup' => false, 'created_at' => $t, 'updated_at' => $t],
        ]);
    }
}
