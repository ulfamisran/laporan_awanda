<?php

namespace App\Services;

use App\Models\AkunDana;
use App\Models\DanaKeluar;
use App\Models\DanaMasuk;
use App\Support\SaldoDana;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class NeracaKeuanganService
{
    /**
     * @return array{saldo_awal: float, masuk_per_jenis_dana: Collection<int, array{nama: string, total: float}>, keluar_per_jenis_dana: Collection<int, array{nama: string, total: float}>, total_masuk_periode: float, total_keluar_periode: float, saldo_akhir: float, mulai: Carbon, akhir: Carbon}
     */
    public static function build(int $profilId, int $bulan, int $tahun, int $periodeId): array
    {
        $mulai = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $akhir = $mulai->copy()->endOfMonth();

        $saldoAwal = SaldoDana::getSaldoDana($profilId, $mulai->copy()->subDay());

        $masukPerJenis = DanaMasuk::query()
            ->selectRaw('akun_jenis_dana_id, SUM(jumlah) as total')
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->whereBetween('tanggal', [$mulai->toDateString(), $akhir->toDateString()])
            ->groupBy('akun_jenis_dana_id')
            ->get()
            ->map(function ($row) {
                $a = AkunDana::query()->find($row->akun_jenis_dana_id);

                return [
                    'nama' => $a ? ($a->kode.' — '.$a->nama) : '—',
                    'total' => (float) $row->total,
                ];
            });

        $keluarPerJenis = DanaKeluar::query()
            ->selectRaw('akun_jenis_dana_id, SUM(jumlah) as total')
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->whereBetween('tanggal', [$mulai->toDateString(), $akhir->toDateString()])
            ->groupBy('akun_jenis_dana_id')
            ->get()
            ->map(function ($row) {
                $a = AkunDana::query()->find($row->akun_jenis_dana_id);

                return [
                    'nama' => $a ? ($a->kode.' — '.$a->nama) : '—',
                    'total' => (float) $row->total,
                ];
            });

        $totalMasukPeriode = (float) $masukPerJenis->sum('total');
        $totalKeluarPeriode = (float) $keluarPerJenis->sum('total');
        $saldoAkhir = $saldoAwal + $totalMasukPeriode - $totalKeluarPeriode;

        return [
            'saldo_awal' => $saldoAwal,
            'masuk_per_jenis_dana' => $masukPerJenis,
            'keluar_per_jenis_dana' => $keluarPerJenis,
            'total_masuk_periode' => $totalMasukPeriode,
            'total_keluar_periode' => $totalKeluarPeriode,
            'saldo_akhir' => $saldoAkhir,
            'mulai' => $mulai,
            'akhir' => $akhir,
        ];
    }
}
