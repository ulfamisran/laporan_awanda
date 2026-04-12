<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapLimbahPeriodeExport implements FromCollection, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
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
        return ['Kategori', 'Total (kg est.)', 'Dibuang', 'Daur ulang', 'Dijual', 'Pendapatan'];
    }

    public function map($row): array
    {
        return [
            $row->nama_kategori,
            (float) $row->total_kg,
            (float) $row->dibuang,
            (float) $row->daur,
            (float) $row->dijual,
            (float) $row->pendapatan,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '"Rp" #,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE8F1F8');
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
