<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class NeracaKeuanganExport implements FromArray, WithEvents, WithTitle
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        private readonly array $data,
        private readonly ?string $namaDapur,
        private readonly int $bulan,
        private readonly int $tahun
    ) {}

    public function array(): array
    {
        $mulai = $this->data['mulai'] ?? Carbon::createFromDate($this->tahun, $this->bulan, 1);
        $rows = [];
        $rows[] = ['Neraca keuangan — '.$this->namaDapur];
        $rows[] = ['Periode', $mulai->translatedFormat('F Y')];
        $rows[] = [];
        $rows[] = ['Saldo awal periode', (float) ($this->data['saldo_awal'] ?? 0)];
        $rows[] = [];
        $rows[] = ['Dana masuk per jenis dana (Buku Pembantu Jenis Dana)', 'Jumlah'];

        foreach ($this->data['masuk_per_jenis_dana'] ?? [] as $r) {
            $rows[] = [$r['nama'] ?? '', (float) ($r['total'] ?? 0)];
        }
        $rows[] = ['Total masuk', (float) ($this->data['total_masuk_periode'] ?? 0)];
        $rows[] = [];
        $rows[] = ['Dana keluar per jenis dana (Buku Pembantu Jenis Dana)', 'Jumlah'];

        foreach ($this->data['keluar_per_jenis_dana'] ?? [] as $r) {
            $rows[] = [$r['nama'] ?? '', (float) ($r['total'] ?? 0)];
        }
        $rows[] = ['Total keluar', (float) ($this->data['total_keluar_periode'] ?? 0)];
        $rows[] = [];
        $rows[] = ['Saldo akhir periode', (float) ($this->data['saldo_akhir'] ?? 0)];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $highest = (int) $sheet->getHighestRow();
                $fmt = '"Rp" #,##0';
                for ($r = 1; $r <= $highest; $r++) {
                    $v = $sheet->getCell('B'.$r)->getValue();
                    if (is_numeric($v)) {
                        $sheet->getStyle('B'.$r)->getNumberFormat()->setFormatCode($fmt);
                    }
                }
            },
        ];
    }

    public function title(): string
    {
        return 'Neraca';
    }
}
