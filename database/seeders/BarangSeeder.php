<?php

namespace Database\Seeders;

use App\Enums\SatuanBarang;
use App\Enums\StatusAktif;
use App\Models\Barang;
use App\Models\BarangKeluarItem;
use App\Models\BarangMasukItem;
use App\Models\KategoriBarang;
use App\Models\StokAwal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BarangSeeder extends Seeder
{
    public function run(): void
    {
        $kat = static fn (string $nama): ?int => KategoriBarang::query()->where('nama_kategori', $nama)->value('id');

        $rows = [
            ['nama' => 'Beras Medium', 'kategori' => 'Bahan Pokok', 'satuan' => SatuanBarang::Kg, 'harga' => 13500, 'stok_min' => 50, 'status' => StatusAktif::Aktif, 'deskripsi' => 'Beras kemasan medium untuk kebutuhan harian.'],
            ['nama' => 'Minyak Goreng Curah', 'kategori' => 'Bahan Pokok', 'satuan' => SatuanBarang::Liter, 'harga' => 18000, 'stok_min' => 10, 'status' => StatusAktif::Aktif, 'deskripsi' => null],
            ['nama' => 'Gula Pasir', 'kategori' => 'Bahan Pokok', 'satuan' => SatuanBarang::Kg, 'harga' => 14500, 'stok_min' => 20, 'status' => StatusAktif::Aktif, 'deskripsi' => null],
            ['nama' => 'Garam Halus', 'kategori' => 'Bumbu', 'satuan' => SatuanBarang::Kg, 'harga' => 6000, 'stok_min' => 5, 'status' => StatusAktif::Aktif, 'deskripsi' => null],
            ['nama' => 'Kecap Manis', 'kategori' => 'Bumbu', 'satuan' => SatuanBarang::Ml, 'harga' => 22000, 'stok_min' => 12, 'status' => StatusAktif::Aktif, 'deskripsi' => null],
            ['nama' => 'Wortel', 'kategori' => 'Sayuran', 'satuan' => SatuanBarang::Kg, 'harga' => 12000, 'stok_min' => 8, 'status' => StatusAktif::Aktif, 'deskripsi' => null],
            ['nama' => 'Bayam', 'kategori' => 'Sayuran', 'satuan' => SatuanBarang::Kg, 'harga' => 8000, 'stok_min' => 6, 'status' => StatusAktif::Aktif, 'deskripsi' => null],
            ['nama' => 'Telur Ayam', 'kategori' => 'Protein', 'satuan' => SatuanBarang::Pcs, 'harga' => 2000, 'stok_min' => 60, 'status' => StatusAktif::Aktif, 'deskripsi' => 'Per butir.'],
            ['nama' => 'Tahu Putih', 'kategori' => 'Protein', 'satuan' => SatuanBarang::Pcs, 'harga' => 800, 'stok_min' => 40, 'status' => StatusAktif::Aktif, 'deskripsi' => null],
            ['nama' => 'Air Mineral 600ml', 'kategori' => 'Minuman', 'satuan' => SatuanBarang::Pcs, 'harga' => 3000, 'stok_min' => 24, 'status' => StatusAktif::Nonaktif, 'deskripsi' => 'Contoh status nonaktif.'],
        ];

        $barangs = [];

        foreach ($rows as $row) {
            $kid = $kat($row['kategori']);
            if (! $kid) {
                continue;
            }

            $barangs[] = Barang::query()->create([
                'nama_barang' => $row['nama'],
                'kategori_barang_id' => $kid,
                'satuan' => $row['satuan'],
                'harga_satuan' => $row['harga'],
                'stok_minimum' => $row['stok_min'],
                'status' => $row['status'],
                'deskripsi' => $row['deskripsi'],
            ]);
        }

        if ($barangs === []) {
            return;
        }

        $today = Carbon::today();

        foreach (array_slice($barangs, 0, 5) as $i => $b) {
            StokAwal::query()->create([
                'barang_id' => $b->id,
                'tanggal' => $today->copy()->subDays(45),
                'jumlah' => 40 + ($i * 5),
                'keterangan' => 'Seeder stok awal',
            ]);
        }

        $this->seedMutasiContoh($barangs[0], $today);
        if (isset($barangs[1])) {
            $this->seedMutasiContoh($barangs[1], $today);
        }
    }

    private function seedMutasiContoh(Barang $barang, Carbon $today): void
    {
        for ($d = 0; $d < 20; $d++) {
            $tgl = $today->copy()->subDays($d);
            if ($d % 3 === 0) {
                BarangMasukItem::query()->create([
                    'barang_id' => $barang->id,
                    'tanggal' => $tgl,
                    'jumlah' => 5 + ($d % 4),
                    'keterangan' => 'Seeder barang masuk',
                ]);
            }
            if ($d % 4 === 0) {
                BarangKeluarItem::query()->create([
                    'barang_id' => $barang->id,
                    'tanggal' => $tgl,
                    'jumlah' => 2 + ($d % 3),
                    'keterangan' => 'Seeder barang keluar',
                ]);
            }
        }
    }
}
