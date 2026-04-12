<?php

namespace App\Http\Controllers;

use App\Support\PeriodeTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class KeuanganTransaksiController extends Controller
{
    use Concerns\ManagesKeuanganProfil;

    public function index(): View
    {
        return view('keuangan.transaksi.index');
    }

    public function data(Request $request): JsonResponse
    {
        $profilId = $this->profilMbgIdForKeuanganOrFirst($request);
        $periodeId = PeriodeTenant::id();

        $label = fn (string $kodeCol, string $namaCol): string => $this->sqlAkunLabel($kodeCol, $namaCol);
        $key = fn (string $prefix, string $idCol): string => $this->sqlTrxKey($prefix, $idCol);

        $masuk = DB::table('dana_masuk as dm')
            ->join('akun_dana as aj', 'dm.akun_jenis_dana_id', '=', 'aj.id')
            ->join('akun_dana as ak', 'dm.akun_kas_id', '=', 'ak.id')
            ->where('dm.profil_mbg_id', $profilId)
            ->where('dm.periode_id', $periodeId)
            ->selectRaw(
                $key('masuk-', 'dm.id').' as trx_key, ? as arah, dm.id as ref_id, dm.tanggal, dm.nomor_bukti, dm.uraian_transaksi, dm.jumlah as debet, 0 as kredit, '
                .$label('aj.kode', 'aj.nama').' as jenis_dana_label, '
                .$label('ak.kode', 'ak.nama').' as kas_label, dm.created_at as sort_ts',
                ['masuk']
            );

        $keluar = DB::table('dana_keluar as dk')
            ->join('akun_dana as aj', 'dk.akun_jenis_dana_id', '=', 'aj.id')
            ->join('akun_dana as ak', 'dk.akun_kas_id', '=', 'ak.id')
            ->where('dk.profil_mbg_id', $profilId)
            ->where('dk.periode_id', $periodeId)
            ->selectRaw(
                $key('keluar-', 'dk.id').' as trx_key, ? as arah, dk.id as ref_id, dk.tanggal, dk.nomor_bukti, dk.uraian_transaksi, 0 as debet, dk.jumlah as kredit, '
                .$label('aj.kode', 'aj.nama').' as jenis_dana_label, '
                .$label('ak.kode', 'ak.nama').' as kas_label, dk.created_at as sort_ts',
                ['keluar']
            );

        $union = $masuk->unionAll($keluar);
        $wrapped = DB::query()->fromSub($union, 'u');

        return DataTables::of($wrapped)
            ->addIndexColumn()
            ->editColumn('tanggal', fn ($row) => $row->tanggal ? Carbon::parse($row->tanggal)->format('d/m/Y') : '')
            ->editColumn('nomor_bukti', fn ($row) => e((string) ($row->nomor_bukti ?? '')))
            ->editColumn('uraian_transaksi', function ($row) {
                $t = trim((string) ($row->uraian_transaksi ?? ''));

                return $t !== '' ? e($t) : '<span class="inst-td-muted">—</span>';
            })
            ->editColumn('debet', function ($row) {
                $v = (float) ($row->debet ?? 0);

                return $v > 0 ? '<span class="font-mono">'.e(formatRupiah($v)).'</span>' : '<span class="inst-td-muted">—</span>';
            })
            ->editColumn('kredit', function ($row) {
                $v = (float) ($row->kredit ?? 0);

                return $v > 0 ? '<span class="font-mono">'.e(formatRupiah($v)).'</span>' : '<span class="inst-td-muted">—</span>';
            })
            ->editColumn('jenis_dana_label', fn ($row) => e((string) ($row->jenis_dana_label ?? '')))
            ->editColumn('kas_label', fn ($row) => e((string) ($row->kas_label ?? '')))
            ->addColumn('aksi', function ($row) {
                $arah = (string) ($row->arah ?? '');
                $id = (int) ($row->ref_id ?? 0);
                if ($arah === 'masuk' && $id > 0) {
                    $url = route('keuangan.masuk.show', ['masuk' => $id]);

                    return '<a href="'.e($url).'" class="text-xs font-semibold" style="color:#1a4a6b;">Detail</a>';
                }
                if ($arah === 'keluar' && $id > 0) {
                    $url = route('keuangan.keluar.show', ['keluar' => $id]);

                    return '<a href="'.e($url).'" class="text-xs font-semibold" style="color:#1a4a6b;">Detail</a>';
                }

                return '—';
            })
            ->rawColumns(['uraian_transaksi', 'debet', 'kredit', 'aksi'])
            ->orderColumn('tanggal', 'tanggal $1')
            ->orderColumn('nomor_bukti', 'nomor_bukti $1')
            ->orderColumn('uraian_transaksi', 'uraian_transaksi $1')
            ->orderColumn('jenis_dana_label', 'jenis_dana_label $1')
            ->orderColumn('kas_label', 'kas_label $1')
            ->orderColumn('debet', 'debet $1')
            ->orderColumn('kredit', 'kredit $1')
            ->orderColumn('sort_ts', 'sort_ts $1')
            ->toJson();
    }

    private function sqlAkunLabel(string $kodeCol, string $namaCol): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "({$kodeCol} || ' — ' || {$namaCol})"
            : "CONCAT({$kodeCol}, ' — ', {$namaCol})";
    }

    private function sqlTrxKey(string $prefix, string $idCol): string
    {
        $escaped = str_replace("'", "''", $prefix);

        return DB::connection()->getDriverName() === 'sqlite'
            ? "('{$escaped}' || {$idCol})"
            : "CONCAT('{$escaped}', {$idCol})";
    }
}
