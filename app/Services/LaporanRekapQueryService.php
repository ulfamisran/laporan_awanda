<?php

namespace App\Services;

use App\Enums\JenisPenangananLimbah;
use App\Enums\SatuanLimbah;
use App\Enums\StatusAktif;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Models\LaporanLimbah;
use App\Models\Penggajian;
use App\Models\StokAwalBarang;
use App\Support\LimbahVolumeKg;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LaporanRekapQueryService
{
    public static function stokPadaTanggal(int $barangId, int $profilId, Carbon $tanggal, int $periodeId): float
    {
        $s = $tanggal->toDateString();
        $awal = (float) StokAwalBarang::query()
            ->where('barang_id', $barangId)
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->sum('jumlah');

        $masuk = (float) BarangMasuk::query()
            ->where('barang_id', $barangId)
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->whereDate('tanggal', '<=', $s)
            ->sum('jumlah');

        $keluar = (float) BarangKeluar::query()
            ->where('barang_id', $barangId)
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->whereDate('tanggal', '<=', $s)
            ->sum('jumlah');

        return $awal + $masuk - $keluar;
    }

    /**
     * @return Collection<int, object>
     */
    public static function stokBarangPeriode(
        int $profilId,
        string $dari,
        string $sampai,
        ?int $barangId,
        ?int $kategoriBarangId,
        int $periodeId,
    ): Collection {
        $dariC = Carbon::parse($dari)->startOfDay();
        $sampaiC = Carbon::parse($sampai)->endOfDay();
        $sebelumDari = $dariC->copy()->subDay();

        $q = Barang::query()
            ->with('kategoriBarang')
            ->where('status', StatusAktif::Aktif)
            ->orderBy('nama_barang');

        if ($barangId) {
            $q->whereKey($barangId);
        }
        if ($kategoriBarangId) {
            $q->where('kategori_barang_id', $kategoriBarangId);
        }

        return $q->get()->map(function (Barang $b) use ($profilId, $dariC, $sampaiC, $sebelumDari, $periodeId) {
            $stokAwal = self::stokPadaTanggal((int) $b->getKey(), $profilId, $sebelumDari, $periodeId);
            $masuk = (float) BarangMasuk::query()
                ->where('barang_id', $b->getKey())
                ->where('profil_mbg_id', $profilId)
                ->where('periode_id', $periodeId)
                ->whereBetween('tanggal', [$dariC->toDateString(), $sampaiC->toDateString()])
                ->sum('jumlah');
            $keluar = (float) BarangKeluar::query()
                ->where('barang_id', $b->getKey())
                ->where('profil_mbg_id', $profilId)
                ->where('periode_id', $periodeId)
                ->whereBetween('tanggal', [$dariC->toDateString(), $sampaiC->toDateString()])
                ->sum('jumlah');
            $saldoAkhir = $stokAwal + $masuk - $keluar;

            return (object) [
                'kode' => $b->kode_barang,
                'nama' => $b->nama_barang,
                'kategori' => $b->kategoriBarang?->nama_kategori ?? '—',
                'stok_awal' => $stokAwal,
                'masuk' => $masuk,
                'keluar' => $keluar,
                'saldo_akhir' => $saldoAkhir,
            ];
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function arusStokDetail(int $profilId, int $barangId, string $dari, string $sampai, int $periodeId): Collection
    {
        $dariC = Carbon::parse($dari)->toDateString();
        $sampaiC = Carbon::parse($sampai)->toDateString();
        $rows = collect();

        $sab = StokAwalBarang::query()
            ->where('barang_id', $barangId)
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->with('creator')
            ->first();
        if ($sab) {
            $rows->push([
                'tanggal' => $sab->tanggal?->format('Y-m-d'),
                'jenis' => 'Stok awal',
                'qty' => (float) $sab->jumlah,
                'arah' => '+',
                'keterangan' => $sab->keterangan,
                'oleh' => $sab->creator?->name,
            ]);
        }

        BarangMasuk::query()
            ->where('barang_id', $barangId)
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->whereBetween('tanggal', [$dariC, $sampaiC])
            ->with('creator')
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get()
            ->each(function (BarangMasuk $m) use ($rows) {
                $rows->push([
                    'tanggal' => $m->tanggal?->format('Y-m-d'),
                    'jenis' => 'Masuk',
                    'qty' => (float) $m->jumlah,
                    'arah' => '+',
                    'keterangan' => $m->kode_transaksi.' — '.$m->keterangan,
                    'oleh' => $m->creator?->name,
                ]);
            });

        BarangKeluar::query()
            ->where('barang_id', $barangId)
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->whereBetween('tanggal', [$dariC, $sampaiC])
            ->with('creator')
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get()
            ->each(function (BarangKeluar $k) use ($rows) {
                $rows->push([
                    'tanggal' => $k->tanggal?->format('Y-m-d'),
                    'jenis' => 'Keluar',
                    'qty' => (float) $k->jumlah,
                    'arah' => '−',
                    'keterangan' => $k->kode_transaksi.' — '.$k->keterangan,
                    'oleh' => $k->creator?->name,
                ]);
            });

        return $rows->sortBy('tanggal')->values();
    }

    /**
     * @return Collection<int, object>
     */
    public static function limbahRekap(int $profilId, string $dari, string $sampai, ?int $kategoriId, int $periodeId): Collection
    {
        $q = LaporanLimbah::query()->with('kategoriLimbah')
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->whereBetween('tanggal', [$dari, $sampai]);
        if ($kategoriId) {
            $q->where('kategori_limbah_id', $kategoriId);
        }

        return $q->get()
            ->groupBy('kategori_limbah_id')
            ->map(function (Collection $items) {
                $nama = $items->first()?->kategoriLimbah?->nama_kategori ?? '—';
                $vol = fn (JenisPenangananLimbah $j) => (float) $items
                    ->filter(fn (LaporanLimbah $r) => $r->jenis_penanganan === $j)
                    ->sum(fn (LaporanLimbah $r) => LimbahVolumeKg::estimate(
                        (float) $r->jumlah,
                        $r->satuan instanceof SatuanLimbah ? $r->satuan : SatuanLimbah::from((string) $r->satuan)
                    ));

                return (object) [
                    'nama_kategori' => $nama,
                    'total_kg' => (float) $items->sum(fn (LaporanLimbah $r) => LimbahVolumeKg::estimate(
                        (float) $r->jumlah,
                        $r->satuan instanceof SatuanLimbah ? $r->satuan : SatuanLimbah::from((string) $r->satuan)
                    )),
                    'dibuang' => $vol(JenisPenangananLimbah::Dibuang),
                    'daur' => $vol(JenisPenangananLimbah::DidaurUlang),
                    'dijual' => $vol(JenisPenangananLimbah::Dijual),
                    'pendapatan' => (float) $items
                        ->filter(fn (LaporanLimbah $r) => $r->jenis_penanganan === JenisPenangananLimbah::Dijual)
                        ->sum(fn (LaporanLimbah $r) => (float) ($r->harga_jual ?? 0)),
                ];
            })
            ->values();
    }

    /**
     * @return Collection<int, Penggajian>
     */
    public static function penggajianRekap(int $profilId, int $bulan, int $tahun, ?string $status, int $periodeId): Collection
    {
        $q = Penggajian::query()
            ->with(['relawan.posisiRelawan'])
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->orderBy('relawan_id');
        if ($status) {
            $q->where('status', $status);
        }

        return $q->get();
    }

    /**
     * Penggajian dalam rentang tanggal (per bulan relawan), inklusif ujung.
     *
     * @return Collection<int, Penggajian>
     */
    public static function penggajianRekapRange(int $profilId, string $dari, string $sampai, ?string $status, int $periodeId): Collection
    {
        $from = Carbon::parse($dari)->startOfMonth();
        $to = Carbon::parse($sampai)->startOfMonth();
        $fromKey = $from->year * 12 + $from->month;
        $toKey = $to->year * 12 + $to->month;

        $q = Penggajian::query()
            ->with(['relawan.posisiRelawan'])
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->whereRaw('(periode_tahun * 12 + periode_bulan) BETWEEN ? AND ?', [$fromKey, $toKey])
            ->orderBy('periode_tahun')
            ->orderBy('periode_bulan')
            ->orderBy('relawan_id');
        if ($status) {
            $q->where('status', $status);
        }

        return $q->get();
    }
}
