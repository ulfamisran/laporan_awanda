<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ProfilMbgSeeder::class,
            UserSeeder::class,
            KategoriBarangSeeder::class,
            KategoriLimbahSeeder::class,
            KategoriDanaMasukSeeder::class,
            KategoriDanaKeluarSeeder::class,
            AkunDanaSeeder::class,
            BarangSeeder::class,
            PosisiRelawanSeeder::class,
            RelawanSeeder::class,
        ]);
    }
}
