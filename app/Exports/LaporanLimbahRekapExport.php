<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanLimbahRekapExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, object>  $rows
     */
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
            'Kategori',
            'Volume (est. kg)',
            'Dibuang (kg est.)',
            'Didaur ulang (kg est.)',
            'Dijual (kg est.)',
            'Dikembalikan (kg est.)',
            'Lainnya (kg est.)',
            'Pendapatan penjualan',
        ];
    }

    /**
     * @param  object  $row
     */
    public function map($row): array
    {
        return [
            $row->nama_kategori,
            (float) $row->total_volume_kg,
            (float) $row->vol_dibuang,
            (float) $row->vol_didaur_ulang,
            (float) $row->vol_dijual,
            (float) $row->vol_dikembalikan,
            (float) $row->vol_lainnya,
            (float) $row->pendapatan,
        ];
    }
}
