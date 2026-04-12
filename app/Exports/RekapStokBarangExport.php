<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapStokBarangExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
        return ['Kode', 'Nama barang', 'Kategori', 'Stok awal', 'Masuk', 'Keluar', 'Saldo akhir'];
    }

    public function map($row): array
    {
        return [
            $row->kode,
            $row->nama,
            $row->kategori,
            (float) $row->stok_awal,
            (float) $row->masuk,
            (float) $row->keluar,
            (float) $row->saldo_akhir,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE8F1F8');

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
