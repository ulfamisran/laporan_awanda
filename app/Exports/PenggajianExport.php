<?php

namespace App\Exports;

use App\Enums\StatusPenggajian;
use App\Models\Penggajian;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PenggajianExport implements FromCollection, WithHeadings, WithMapping
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
        return [
            'Nama relawan',
            'NIK',
            'Posisi',
            'Dapur',
            'Periode',
            'Gaji pokok',
            'Tunj. transport',
            'Tunj. makan',
            'Tunj. lainnya',
            'Potongan',
            'Total gaji',
            'Status',
            'Tanggal bayar',
        ];
    }

    /**
     * @param  Penggajian  $row
     */
    public function map($row): array
    {
        $status = $row->status instanceof StatusPenggajian ? $row->status->label() : (string) $row->status;

        return [
            $row->relawan?->nama_lengkap,
            $row->relawan?->nik,
            $row->relawan?->posisiRelawan?->nama_posisi,
            $row->profilMbg?->nama_dapur,
            $row->periode_label,
            (float) $row->gaji_pokok,
            (float) $row->tunjangan_transport,
            (float) $row->tunjangan_makan,
            (float) $row->tunjangan_lainnya,
            (float) $row->potongan,
            (float) $row->total_gaji,
            $status,
            optional($row->tanggal_bayar)->format('Y-m-d'),
        ];
    }
}
