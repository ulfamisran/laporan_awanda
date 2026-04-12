<?php

namespace App\Support;

class SidebarMenu
{
    /**
     * Struktur menu sidebar (Bahasa Indonesia).
     *
     * @return list<array<string, mixed>>
     */
    public static function items(): array
    {
        return [
            [
                'type' => 'link',
                'label' => 'Dasbor',
                'route' => 'dashboard',
                'match' => ['dashboard'],
                'icon' => 'home',
            ],
            [
                'type' => 'link',
                'label' => 'Periode',
                'route' => 'periode.index',
                'match' => ['periode.*'],
                'icon' => 'calendar-range',
            ],
            [
                'type' => 'group',
                'key' => 'master',
                'label' => 'Data Master',
                'icon' => 'database',
                'children' => [
                    ['label' => 'Pengguna', 'route' => 'master.pengguna.index', 'match' => ['master.pengguna.*'], 'roles' => ['super_admin']],
                    ['label' => 'Peran', 'route' => 'master.peran.index', 'match' => ['master.peran.*'], 'roles' => ['super_admin']],
                    ['label' => 'Profil cabang MBG', 'route' => 'master.profil-mbg.edit', 'match' => ['master.profil-mbg.*'], 'roles' => ['super_admin']],
                    ['label' => 'Kategori Barang', 'route' => 'master.kategori-barang.index', 'match' => ['master.kategori-barang.*']],
                    ['label' => 'Kategori Limbah', 'route' => 'master.kategori-limbah.index', 'match' => ['master.kategori-limbah.*']],
                    ['label' => 'Kategori Dana Masuk', 'route' => 'master.kategori-dana-masuk.index', 'match' => ['master.kategori-dana-masuk.*']],
                    ['label' => 'Kategori Dana Keluar', 'route' => 'master.kategori-dana-keluar.index', 'match' => ['master.kategori-dana-keluar.*']],
                    ['label' => 'Akun Dana', 'route' => 'master.akun-dana.index', 'match' => ['master.akun-dana.*']],
                    ['label' => 'Barang', 'route' => 'master.barang.index', 'match' => ['master.barang.*']],
                    ['label' => 'Posisi Relawan', 'route' => 'master.posisi-relawan.index', 'match' => ['master.posisi-relawan.*']],
                    ['label' => 'Relawan', 'route' => 'master.relawan.index', 'match' => ['master.relawan.*']],
                ],
            ],
            [
                'type' => 'group',
                'key' => 'stok',
                'label' => 'Stok Barang',
                'icon' => 'boxes',
                'children' => [
                    ['label' => 'Stok Awal', 'route' => 'stok.awal.index', 'match' => ['stok.awal.*']],
                    ['label' => 'Barang Masuk', 'route' => 'stok.masuk.index', 'match' => ['stok.masuk.*']],
                    ['label' => 'Barang Keluar', 'route' => 'stok.keluar.index', 'match' => ['stok.keluar.*']],
                    ['label' => 'Mutasi Stok', 'route' => 'stok.mutasi.index', 'match' => ['stok.mutasi.*']],
                    ['label' => 'Arus Stok', 'route' => 'stok.arus.index', 'match' => ['stok.arus.*']],
                ],
            ],
            [
                'type' => 'group',
                'key' => 'keuangan',
                'label' => 'Keuangan',
                'icon' => 'wallet',
                'children' => [
                    ['label' => 'Saldo Dana Awal', 'route' => 'keuangan.stok-dana-awal.index', 'match' => ['keuangan.stok-dana-awal.*']],
                    ['label' => 'Dana Masuk', 'route' => 'keuangan.masuk.index', 'match' => ['keuangan.masuk.*']],
                    ['label' => 'Dana Keluar', 'route' => 'keuangan.keluar.index', 'match' => ['keuangan.keluar.*']],
                    ['label' => 'Transaksi', 'route' => 'keuangan.transaksi.index', 'match' => ['keuangan.transaksi.*']],
                    ['label' => 'Buku Kas Umum', 'route' => 'keuangan.buku-kas-umum.index', 'match' => ['keuangan.buku-kas-umum.*']],
                    ['label' => 'Laporan Keuangan', 'route' => 'keuangan.laporan.index', 'match' => ['keuangan.laporan.*']],
                ],
            ],
            [
                'type' => 'link',
                'label' => 'Penggajian',
                'route' => 'penggajian.index',
                'match' => ['penggajian.*'],
                'icon' => 'cash',
            ],
            [
                'type' => 'link',
                'label' => 'Laporan Limbah',
                'route' => 'laporan-limbah.index',
                'match' => ['laporan-limbah.*'],
                'icon' => 'trash',
            ],
            [
                'type' => 'link',
                'label' => 'Laporan & Rekap',
                'route' => 'laporan-rekap.stok',
                'match' => ['laporan-rekap.*'],
                'icon' => 'chart',
            ],
            [
                'type' => 'link',
                'label' => 'Pengaturan',
                'route' => 'pengaturan.index',
                'match' => ['pengaturan.*'],
                'icon' => 'cog',
                'roles' => ['super_admin'],
            ],
        ];
    }
}
