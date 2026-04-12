<?php

namespace App\Exports;

use App\Models\LaporanLimbah;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanLimbahExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly Builder $builder
    ) {}

    public function query(): Builder
    {
        return $this->builder;
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Tanggal',
            'Dapur',
            'Kategori',
            'Jumlah',
            'Satuan',
            'Penanganan',
            'Harga jual',
            'Keterangan',
        ];
    }

    /**
     * @param  LaporanLimbah  $row
     */
    public function map($row): array
    {
        $sat = $row->satuan?->label() ?? (string) $row->satuan;
        $jen = $row->jenis_penanganan?->label() ?? (string) $row->jenis_penanganan;

        return [
            $row->kode_laporan,
            optional($row->tanggal)->format('Y-m-d'),
            $row->profilMbg?->nama_dapur,
            $row->kategoriLimbah?->nama_kategori,
            (float) $row->jumlah,
            $sat,
            $jen,
            $row->harga_jual !== null ? (float) $row->harga_jual : null,
            $row->keterangan,
        ];
    }
}
