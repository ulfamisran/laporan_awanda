<?php

namespace App\Exports;

use App\Enums\StatusPenggajian;
use App\Models\Penggajian;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapPenggajianPeriodeExport implements FromCollection, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    /**
     * @param  Collection<int, Penggajian>  $rows
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
        return ['Relawan', 'Posisi', 'Gaji pokok', 'Tunjangan', 'Potongan', 'Total', 'Status'];
    }

    /**
     * @param  Penggajian  $row
     */
    public function map($row): array
    {
        $tunj = (float) $row->tunjangan_transport + (float) $row->tunjangan_makan + (float) $row->tunjangan_lainnya;
        $st = $row->status instanceof StatusPenggajian ? $row->status->label() : (string) $row->status;

        return [
            $row->relawan?->nama_lengkap,
            $row->relawan?->posisiRelawan?->nama_posisi,
            (float) $row->gaji_pokok,
            $tunj,
            (float) $row->potongan,
            (float) $row->total_gaji,
            $st,
        ];
    }

    public function columnFormats(): array
    {
        $rp = '"Rp" #,##0';

        return [
            'C' => $rp,
            'D' => $rp,
            'E' => $rp,
            'F' => $rp,
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
