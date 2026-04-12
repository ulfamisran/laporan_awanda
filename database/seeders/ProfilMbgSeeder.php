<?php

namespace Database\Seeders;

use App\Models\ProfilMbg;
use Illuminate\Database\Seeder;

class ProfilMbgSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'nama_dapur' => 'Dapur MBG Jakarta Pusat',
                'kode_dapur' => 'DP-JKT-01',
                'alamat' => 'Jl. Contoh No. 1',
                'kota' => 'Jakarta Pusat',
                'provinsi' => 'DKI Jakarta',
                'no_telp' => '021-00000001',
                'penanggung_jawab' => 'Budi Santoso',
                'status' => 'aktif',
            ],
            [
                'nama_dapur' => 'Dapur MBG Bandung',
                'kode_dapur' => 'DP-BDG-01',
                'alamat' => 'Jl. Contoh No. 2',
                'kota' => 'Bandung',
                'provinsi' => 'Jawa Barat',
                'no_telp' => '022-00000002',
                'penanggung_jawab' => 'Siti Aminah',
                'status' => 'aktif',
            ],
            [
                'nama_dapur' => 'Dapur MBG Surabaya',
                'kode_dapur' => 'DP-SBY-01',
                'alamat' => 'Jl. Contoh No. 3',
                'kota' => 'Surabaya',
                'provinsi' => 'Jawa Timur',
                'no_telp' => '031-00000003',
                'penanggung_jawab' => 'Ahmad Hidayat',
                'status' => 'aktif',
            ],
        ];

        foreach ($rows as $row) {
            ProfilMbg::query()->firstOrCreate(
                ['kode_dapur' => $row['kode_dapur']],
                $row
            );
        }
    }
}
