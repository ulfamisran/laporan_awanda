<?php

namespace App\Exports;

use App\Models\Relawan;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RelawanExport implements FromQuery, WithHeadings, WithMapping
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
            'NIK',
            'Nama lengkap',
            'Posisi',
            'Dapur',
            'Jenis kelamin',
            'No. HP',
            'Email',
            'Alamat',
            'Tanggal lahir',
            'Tanggal bergabung',
            'Gaji pokok',
            'Status',
            'Keterangan',
        ];
    }

    /**
     * @param  Relawan  $row
     */
    public function map($row): array
    {
        return [
            $row->nik,
            $row->nama_lengkap,
            $row->posisiRelawan?->nama_posisi,
            $row->profilMbg?->nama_dapur,
            $row->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            $row->no_hp,
            $row->email,
            $row->alamat,
            optional($row->tanggal_lahir)->format('Y-m-d'),
            optional($row->tanggal_bergabung)->format('Y-m-d'),
            (float) $row->gaji_pokok,
            $row->status,
            $row->keterangan,
        ];
    }
}
