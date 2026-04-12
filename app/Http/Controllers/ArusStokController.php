<?php

namespace App\Http\Controllers;

use App\Enums\StatusAktif;
use App\Exports\ArusStokExport;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Models\KategoriBarang;
use App\Models\ProfilMbg;
use App\Models\StokAwalBarang;
use App\Support\PeriodeTenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ArusStokController extends Controller
{
    use Concerns\ManagesStokProfil;

    public function index(Request $request): View
    {
        $profilId = $this->profilMbgIdForStokOrFirst($request);
        $periodeId = PeriodeTenant::id();
        $kategoris = KategoriBarang::query()->orderBy('nama_kategori')->get();

        $dari = $request->filled('dari')
            ? Carbon::parse($request->get('dari'))->startOfDay()
            : now()->startOfMonth();
        $sampai = $request->filled('sampai')
            ? Carbon::parse($request->get('sampai'))->endOfDay()
            : now()->endOfDay();

        $barangId = $request->integer('barang_id');
        $kategoriId = $request->integer('kategori_barang_id');

        $barangs = Barang::query()
            ->where('status', StatusAktif::Aktif)
            ->when($kategoriId > 0, fn ($q) => $q->where('kategori_barang_id', $kategoriId))
            ->orderBy('nama_barang')
            ->get();

        $rows = collect();
        $chartLabels = [];
        $chartSaldo = [];
        $barang = null;

        if ($barangId > 0) {
            $barang = Barang::query()->whereKey($barangId)->firstOrFail();
            $rows = $this->buildArusRows($barang->getKey(), $profilId, $periodeId, $dari, $sampai);
            $chartLabels = $rows->pluck('tanggal_label')->all();
            $chartSaldo = $rows->pluck('saldo')->map(fn ($v) => (float) $v)->all();
        }

        return view('arus-stok.index', compact(
            'profilId',
            'kategoris',
            'barangs',
            'barang',
            'barangId',
            'kategoriId',
            'dari',
            'sampai',
            'rows',
            'chartLabels',
            'chartSaldo'
        ));
    }

    public function exportExcel(Request $request)
    {
        $profilId = $this->profilMbgIdForStokOrFirst($request);
        $periodeId = PeriodeTenant::id();
        $dari = Carbon::parse($request->get('dari', now()->startOfMonth()->toDateString()))->startOfDay();
        $sampai = Carbon::parse($request->get('sampai', now()->toDateString()))->endOfDay();
        $barangId = $request->integer('barang_id');
        abort_if($barangId <= 0, 422, 'Pilih barang untuk ekspor arus stok.');

        $rows = $this->buildArusRows($barangId, $profilId, $periodeId, $dari, $sampai);
        $barang = Barang::query()->findOrFail($barangId);

        return Excel::download(
            new ArusStokExport($rows, $barang, $dari, $sampai),
            'arus-stok-'.$barang->kode_barang.'-'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $profilId = $this->profilMbgIdForStokOrFirst($request);
        $periodeId = PeriodeTenant::id();
        $dari = Carbon::parse($request->get('dari', now()->startOfMonth()->toDateString()))->startOfDay();
        $sampai = Carbon::parse($request->get('sampai', now()->toDateString()))->endOfDay();
        $barangId = $request->integer('barang_id');
        abort_if($barangId <= 0, 422, 'Pilih barang untuk ekspor arus stok.');

        $rows = $this->buildArusRows($barangId, $profilId, $periodeId, $dari, $sampai);
        $barang = Barang::query()->findOrFail($barangId);
        $namaDapur = ProfilMbg::query()->whereKey($profilId)->value('nama_dapur');

        $pdf = Pdf::loadView('arus-stok.export-pdf', [
            'rows' => $rows,
            'barang' => $barang,
            'namaDapur' => $namaDapur,
            'dari' => $dari,
            'sampai' => $sampai,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('arus-stok-'.$barang->kode_barang.'.pdf');
    }

    private function buildArusRows(int $barangId, int $profilId, int $periodeId, Carbon $dari, Carbon $sampai): Collection
    {
        $riwayat = collect();

        $sab = StokAwalBarang::query()
            ->where('barang_id', $barangId)
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->with('creator')
            ->first();

        if ($sab && Carbon::parse($sab->tanggal)->between($dari->copy()->startOfDay(), $sampai->copy()->endOfDay())) {
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
            ->where('barang_id', $barangId)
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
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
            ->where('barang_id', $barangId)
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
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

        $saldoAwal = $this->stokSebelumTanggal($barangId, $profilId, $periodeId, $dari);

        $out = collect([
            [
                'tanggal' => $dari,
                'jenis' => 'opening',
                'label' => 'Saldo awal periode',
                'jumlah' => 0.0,
                'arah' => 0,
                'keterangan' => '',
                'oleh' => '',
                'kode' => '—',
                'saldo' => $saldoAwal,
                'tanggal_label' => $dari->format('d/m/Y'),
            ],
        ]);

        $saldo = $saldoAwal;
        foreach ($riwayat as $r) {
            $delta = $r['arah'] * $r['jumlah'];
            $saldo += $delta;
            $r['saldo'] = $saldo;
            $r['tanggal_label'] = $r['tanggal'] instanceof Carbon
                ? $r['tanggal']->format('d/m/Y')
                : Carbon::parse($r['tanggal'])->format('d/m/Y');
            $out->push($r);
        }

        return $out;
    }

    private function stokSebelumTanggal(int $barangId, int $profilId, int $periodeId, Carbon $tanggal): float
    {
        $sab = StokAwalBarang::query()
            ->where('barang_id', $barangId)
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->first();

        $awal = 0.0;
        if ($sab && $sab->tanggal->lt($tanggal->toDateString())) {
            $awal = (float) $sab->jumlah;
        }

        $masuk = (float) BarangMasuk::query()
            ->where('barang_id', $barangId)
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->where('tanggal', '<', $tanggal->toDateString())
            ->sum('jumlah');

        $keluar = (float) BarangKeluar::query()
            ->where('barang_id', $barangId)
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->where('tanggal', '<', $tanggal->toDateString())
            ->sum('jumlah');

        return $awal + $masuk - $keluar;
    }
}
