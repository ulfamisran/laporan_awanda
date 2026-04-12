<?php

namespace App\Http\Controllers;

use App\Enums\StatusAktif;
use App\Exports\MutasiStokExport;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Models\ProfilMbg;
use App\Models\StokAwalBarang;
use App\Support\PeriodeTenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class MutasiStokController extends Controller
{
    use Concerns\ManagesStokProfil;

    public function index(Request $request): View
    {
        $profilId = $this->profilMbgIdForStokOrFirst($request);
        $periodeId = PeriodeTenant::id();

        $rows = Barang::query()
            ->select('barang.*')
            ->selectRaw('(SELECT COALESCE(SUM(jumlah),0) FROM stok_awal_barang sab WHERE sab.barang_id = barang.id AND sab.profil_mbg_id = ? AND sab.periode_id = ?) as jumlah_awal', [$profilId, $periodeId])
            ->selectRaw('(SELECT COALESCE(SUM(jumlah),0) FROM barang_masuk bm WHERE bm.barang_id = barang.id AND bm.profil_mbg_id = ? AND bm.periode_id = ?) as jumlah_masuk', [$profilId, $periodeId])
            ->selectRaw('(SELECT COALESCE(SUM(jumlah),0) FROM barang_keluar bk WHERE bk.barang_id = barang.id AND bk.profil_mbg_id = ? AND bk.periode_id = ?) as jumlah_keluar', [$profilId, $periodeId])
            ->with('kategoriBarang')
            ->where('barang.status', StatusAktif::Aktif)
            ->orderBy('barang.nama_barang')
            ->get()
            ->map(function (Barang $b) {
                $awal = (float) ($b->jumlah_awal ?? 0);
                $masuk = (float) ($b->jumlah_masuk ?? 0);
                $keluar = (float) ($b->jumlah_keluar ?? 0);
                $b->setAttribute('stok_saat_ini_dapur', $awal + $masuk - $keluar);

                return $b;
            });

        return view('mutasi-stok.index', compact('rows', 'profilId'));
    }

    public function detail(Request $request, Barang $barang): View
    {
        $profilId = $this->profilMbgIdForStokOrFirst($request);
        $periodeId = PeriodeTenant::id();
        $this->ensureBarangVisible($barang);

        $riwayat = collect();

        $sab = StokAwalBarang::query()
            ->where('barang_id', $barang->id)
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->with('creator')
            ->first();

        if ($sab) {
            $riwayat->push([
                'tanggal' => $sab->tanggal,
                'jenis' => 'stok_awal',
                'label' => 'Stok awal',
                'jumlah' => (float) $sab->jumlah,
                'arah' => 1,
                'keterangan' => $sab->keterangan,
                'oleh' => $sab->creator?->name,
                'kode' => 'SA-'.$sab->getKey(),
            ]);
        }

        BarangMasuk::query()
            ->where('barang_id', $barang->id)
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->with('creator')
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get()
            ->each(function (BarangMasuk $m) use ($riwayat) {
                $riwayat->push([
                    'tanggal' => $m->tanggal,
                    'jenis' => 'masuk',
                    'label' => 'Barang masuk',
                    'jumlah' => (float) $m->jumlah,
                    'arah' => 1,
                    'keterangan' => $m->keterangan,
                    'oleh' => $m->creator?->name,
                    'kode' => $m->kode_transaksi,
                ]);
            });

        BarangKeluar::query()
            ->where('barang_id', $barang->id)
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->with('creator')
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get()
            ->each(function (BarangKeluar $k) use ($riwayat) {
                $riwayat->push([
                    'tanggal' => $k->tanggal,
                    'jenis' => 'keluar',
                    'label' => 'Barang keluar',
                    'jumlah' => (float) $k->jumlah,
                    'arah' => -1,
                    'keterangan' => $k->keterangan,
                    'oleh' => $k->creator?->name,
                    'kode' => $k->kode_transaksi,
                ]);
            });

        $jenisOrder = ['stok_awal' => 0, 'masuk' => 1, 'keluar' => 2];
        $riwayat = $riwayat->sort(function (array $a, array $b) use ($jenisOrder): int {
            $c = $a['tanggal'] <=> $b['tanggal'];
            if ($c !== 0) {
                return $c;
            }

            return ($jenisOrder[$a['jenis']] ?? 9) <=> ($jenisOrder[$b['jenis']] ?? 9);
        })->values();

        $saldo = 0;
        $riwayat = $riwayat->map(function (array $r) use (&$saldo) {
            $delta = $r['arah'] * $r['jumlah'];
            $saldo += $delta;
            $r['saldo'] = $saldo;

            return $r;
        });

        return view('mutasi-stok.detail', compact('barang', 'riwayat', 'profilId'));
    }

    public function exportExcel(Request $request)
    {
        $profilId = $this->profilMbgIdForStokOrFirst($request);
        $rows = $this->rekapRows($profilId, PeriodeTenant::id());

        return Excel::download(new MutasiStokExport($rows), 'mutasi-stok-'.now()->format('Ymd_His').'.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $profilId = $this->profilMbgIdForStokOrFirst($request);
        $rows = $this->rekapRows($profilId, PeriodeTenant::id());
        $namaDapur = ProfilMbg::query()->whereKey($profilId)->value('nama_dapur');

        $pdf = Pdf::loadView('mutasi-stok.export-pdf', [
            'rows' => $rows,
            'namaDapur' => $namaDapur,
            'tanggalCetak' => Carbon::now()->translatedFormat('d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('mutasi-stok-'.now()->format('Ymd').'.pdf');
    }

    /**
     * @return Collection<int, Barang>
     */
    private function rekapRows(int $profilId, int $periodeId): Collection
    {
        return Barang::query()
            ->select('barang.*')
            ->selectRaw('(SELECT COALESCE(SUM(jumlah),0) FROM stok_awal_barang sab WHERE sab.barang_id = barang.id AND sab.profil_mbg_id = ? AND sab.periode_id = ?) as jumlah_awal', [$profilId, $periodeId])
            ->selectRaw('(SELECT COALESCE(SUM(jumlah),0) FROM barang_masuk bm WHERE bm.barang_id = barang.id AND bm.profil_mbg_id = ? AND bm.periode_id = ?) as jumlah_masuk', [$profilId, $periodeId])
            ->selectRaw('(SELECT COALESCE(SUM(jumlah),0) FROM barang_keluar bk WHERE bk.barang_id = barang.id AND bk.profil_mbg_id = ? AND bk.periode_id = ?) as jumlah_keluar', [$profilId, $periodeId])
            ->with('kategoriBarang')
            ->where('barang.status', StatusAktif::Aktif)
            ->orderBy('barang.nama_barang')
            ->get()
            ->map(function (Barang $b) {
                $awal = (float) ($b->jumlah_awal ?? 0);
                $masuk = (float) ($b->jumlah_masuk ?? 0);
                $keluar = (float) ($b->jumlah_keluar ?? 0);
                $b->setAttribute('stok_saat_ini_dapur', $awal + $masuk - $keluar);

                return $b;
            });
    }

    private function ensureBarangVisible(Barang $barang): void
    {
        if ($barang->status !== StatusAktif::Aktif) {
            abort(404);
        }
    }
}
