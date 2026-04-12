<?php

namespace App\Exports;

use App\Enums\JenisPenangananLimbah;
use App\Enums\SatuanLimbah;
use App\Models\KategoriLimbah;
use App\Models\LaporanLimbahHarian;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanLimbahHarianListExport implements FromCollection, WithHeadings, WithMapping
{
    /** @var Collection<int, KategoriLimbah> */
    private Collection $kategoris;

    /**
     * @param  Collection<int, LaporanLimbahHarian>  $harians
     */
    public function __construct(
        private readonly Collection $harians,
    ) {
        $this->kategoris = KategoriLimbah::query()->orderBy('nama_kategori')->get();
    }

    public function collection(): Collection
    {
        return $this->harians;
    }

    public function headings(): array
    {
        $head = ['Tanggal', 'Menu'];
        foreach ($this->kategoris as $k) {
            $head[] = $k->nama_kategori.' (jumlah)';
            $head[] = $k->nama_kategori.' (satuan)';
            $head[] = $k->nama_kategori.' (penanganan)';
            $head[] = $k->nama_kategori.' (harga jual)';
        }

        return $head;
    }

    /**
     * @param  LaporanLimbahHarian  $h
     */
    public function map($h): array
    {
        $byKat = $h->details->keyBy('kategori_limbah_id');
        $row = [
            optional($h->tanggal)->format('Y-m-d'),
            $h->menu_makanan,
        ];
        foreach ($this->kategoris as $k) {
            $d = $byKat->get($k->id);
            if (! $d) {
                $row[] = '';
                $row[] = '';
                $row[] = '';
                $row[] = '';

                continue;
            }
            $sat = $d->satuan instanceof SatuanLimbah ? $d->satuan->label() : (string) $d->satuan;
            $jen = $d->jenis_penanganan instanceof JenisPenangananLimbah
                ? $d->jenis_penanganan->label()
                : (string) $d->jenis_penanganan;
            $row[] = (float) $d->jumlah;
            $row[] = $sat;
            $row[] = $jen;
            $row[] = $d->harga_jual !== null ? (float) $d->harga_jual : '';
        }

        return $row;
    }
}
