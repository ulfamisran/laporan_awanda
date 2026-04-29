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
                    ['label' => 'Barang', 'route' => 'master.barang.index', 'match' => ['master.barang.*']],
                    ['label' => 'Supplier', 'route' => 'master.supplier.index', 'match' => ['master.supplier.*']],
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
                    ['label' => 'Order Barang', 'route' => 'stok.order.index', 'match' => ['stok.order.*']],
                    ['label' => 'Penerimaan Barang', 'route' => 'stok.penerimaan.index', 'match' => ['stok.penerimaan.*']],
                    ['label' => 'Barang Masuk', 'route' => 'stok.masuk.index', 'match' => ['stok.masuk.*']],
                    ['label' => 'Barang Keluar', 'route' => 'stok.keluar.index', 'match' => ['stok.keluar.*']],
                    ['label' => 'Mutasi Stok', 'route' => 'stok.mutasi.index', 'match' => ['stok.mutasi.*']],
                    ['label' => 'Arus Stok', 'route' => 'stok.arus.index', 'match' => ['stok.arus.*']],
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
