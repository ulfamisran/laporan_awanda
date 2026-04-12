<?php

namespace App\Exports;

use App\Models\Barang;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ArusStokExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly Collection $rows,
        private readonly Barang $barang,
        private readonly Carbon $dari,
        private readonly Carbon $sampai
    ) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Barang',
            'Periode',
            'Tanggal',
            'Jenis',
            'Jumlah',
            'Saldo',
            'Keterangan',
            'Input oleh',
            'Kode',
        ];
    }

    public function map($row): array
    {
        return [
            $this->barang->kode_barang.' — '.$this->barang->nama_barang,
            $this->dari->format('d/m/Y').' – '.$this->sampai->format('d/m/Y'),
            $row['tanggal_label'] ?? '',
            $row['label'] ?? '',
            $row['arah'] * $row['jumlah'],
            $row['saldo'] ?? '',
            $row['keterangan'] ?? '',
            $row['oleh'] ?? '',
            $row['kode'] ?? '',
        ];
    }
}
