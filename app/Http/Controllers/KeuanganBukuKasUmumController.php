<?php

namespace App\Http\Controllers;

use App\Models\DanaKeluar;
use App\Models\DanaMasuk;
use App\Models\Periode;
use App\Support\PeriodeTenant;
use App\Support\SaldoDana;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KeuanganBukuKasUmumController extends Controller
{
    use Concerns\ManagesKeuanganProfil;

    public function index(Request $request): View
    {
        $profilId = $this->profilMbgIdForKeuanganOrFirst($request);
        $periodeId = PeriodeTenant::id();
        $periode = Periode::query()->whereKey($periodeId)->where('profil_mbg_id', $profilId)->firstOrFail();

        $hariSebelumPeriode = Carbon::parse($periode->tanggal_awal)->startOfDay()->subDay();
        $saldoAwal = SaldoDana::getSaldoDana($profilId, $hariSebelumPeriode);

        $totDebet = (float) DanaMasuk::query()
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->sum('jumlah');
        $totKredit = (float) DanaKeluar::query()
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->sum('jumlah');
        $saldoAkhir = $saldoAwal + $totDebet - $totKredit;

        $key = fn (string $prefix, string $idCol): string => $this->sqlTrxKey($prefix, $idCol);

        $masuk = DB::table('dana_masuk as dm')
            ->where('dm.profil_mbg_id', $profilId)
            ->where('dm.periode_id', $periodeId)
            ->selectRaw(
                $key('masuk-', 'dm.id').' as trx_key, dm.tanggal, dm.nomor_bukti, dm.uraian_transaksi, dm.jumlah as debet, 0 as kredit, dm.created_at as sort_ts'
            );

        $keluar = DB::table('dana_keluar as dk')
            ->where('dk.profil_mbg_id', $profilId)
            ->where('dk.periode_id', $periodeId)
            ->selectRaw(
                $key('keluar-', 'dk.id').' as trx_key, dk.tanggal, dk.nomor_bukti, dk.uraian_transaksi, 0 as debet, dk.jumlah as kredit, dk.created_at as sort_ts'
            );

        $urut = DB::query()
            ->fromSub($masuk->unionAll($keluar), 'u')
            ->orderBy('u.tanggal')
            ->orderBy('u.sort_ts')
            ->orderBy('u.trx_key');

        $baris = [];
        $running = $saldoAwal;
        foreach ($urut->get() as $r) {
            $debet = (float) ($r->debet ?? 0);
            $kredit = (float) ($r->kredit ?? 0);
            $running += $debet - $kredit;
            $baris[] = [
                'tanggal' => $r->tanggal ? Carbon::parse($r->tanggal) : null,
                'nomor_bukti' => (string) ($r->nomor_bukti ?? ''),
                'uraian_transaksi' => (string) ($r->uraian_transaksi ?? ''),
                'debet' => $debet,
                'kredit' => $kredit,
                'saldo' => $running,
            ];
        }

        return view('keuangan.buku-kas-umum.index', compact(
            'periode',
            'saldoAwal',
            'saldoAkhir',
            'baris',
        ));
    }

    private function sqlTrxKey(string $prefix, string $idCol): string
    {
        $escaped = str_replace("'", "''", $prefix);

        return DB::connection()->getDriverName() === 'sqlite'
            ? "('{$escaped}' || {$idCol})"
            : "CONCAT('{$escaped}', {$idCol})";
    }
}
