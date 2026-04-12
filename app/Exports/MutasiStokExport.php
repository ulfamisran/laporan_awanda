<?php

namespace App\Exports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MutasiStokExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly Collection $rows
    ) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Kode barang',
            'Nama barang',
            'Kategori',
            'Satuan',
            'Stok awal',
            'Total masuk',
            'Total keluar',
            'Stok saat ini',
            'Stok minimum',
            'Status',
        ];
    }

    /**
     * @param  Barang  $row
     */
    public function map($row): array
    {
        $awal = (float) ($row->jumlah_awal ?? 0);
        $masuk = (float) ($row->jumlah_masuk ?? 0);
        $keluar = (float) ($row->jumlah_keluar ?? 0);
        $stok = $awal + $masuk - $keluar;
        $min = (float) $row->stok_minimum;
        $status = $stok < $min ? 'Di bawah minimum' : 'Aman';

        return [
            $row->kode_barang,
            $row->nama_barang,
            $row->kategoriBarang?->nama_kategori,
            $row->satuan?->label(),
            $awal,
            $masuk,
            $keluar,
            $stok,
            $min,
            $status,
        ];
    }
}
