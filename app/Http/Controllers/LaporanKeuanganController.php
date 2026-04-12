<?php

namespace App\Http\Controllers;

use App\Exports\NeracaKeuanganExport;
use App\Models\DanaKeluar;
use App\Models\DanaMasuk;
use App\Models\ProfilMbg;
use App\Services\NeracaKeuanganService;
use App\Support\PeriodeTenant;
use App\Support\SaldoDana;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class LaporanKeuanganController extends Controller
{
    use Concerns\ManagesKeuanganProfil;

    public function index(Request $request): View
    {
        $profilId = $this->profilMbgIdForKeuanganOrFirst($request);

        $saldoSaatIni = SaldoDana::getSaldoDana($profilId);

        $startBulan = now()->startOfMonth();
        $endBulan = now()->endOfMonth();
        $periodeId = PeriodeTenant::id();
        $totalMasukBulan = (float) DanaMasuk::query()
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->whereBetween('tanggal', [$startBulan->toDateString(), $endBulan->toDateString()])
            ->sum('jumlah');
        $totalKeluarBulan = (float) DanaKeluar::query()
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->whereBetween('tanggal', [$startBulan->toDateString(), $endBulan->toDateString()])
            ->sum('jumlah');

        $chartLabels = [];
        $chartMasuk = [];
        $chartKeluar = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->copy()->subMonths($i)->startOfMonth();
            $me = $m->copy()->endOfMonth();
            $chartLabels[] = $m->translatedFormat('M Y');
            $chartMasuk[] = (float) DanaMasuk::query()
                ->where('profil_mbg_id', $profilId)
                ->where('periode_id', $periodeId)
                ->whereBetween('tanggal', [$m->toDateString(), $me->toDateString()])
                ->sum('jumlah');
            $chartKeluar[] = (float) DanaKeluar::query()
                ->where('profil_mbg_id', $profilId)
                ->where('periode_id', $periodeId)
                ->whereBetween('tanggal', [$m->toDateString(), $me->toDateString()])
                ->sum('jumlah');
        }

        $pieLabels = [];
        $pieValues = [];
        $keluarPerJenis = DanaKeluar::query()
            ->selectRaw('akun_jenis_dana_id, SUM(jumlah) as total')
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->whereBetween('tanggal', [$startBulan->toDateString(), $endBulan->toDateString()])
            ->groupBy('akun_jenis_dana_id')
            ->with('akunJenisDana')
            ->get();
        foreach ($keluarPerJenis as $row) {
            $a = $row->akunJenisDana;
            $pieLabels[] = $a ? ($a->kode.' — '.$a->nama) : '—';
            $pieValues[] = (float) $row->total;
        }

        $recent = $this->recentTransaksi($profilId, $periodeId, 15);

        return view('laporan-keuangan.index', compact(
            'profilId',
            'saldoSaatIni',
            'totalMasukBulan',
            'totalKeluarBulan',
            'chartLabels',
            'chartMasuk',
            'chartKeluar',
            'pieLabels',
            'pieValues',
            'recent'
        ));
    }

    public function neraca(Request $request): View
    {
        $profilId = $this->profilMbgIdForKeuanganOrFirst($request);

        $bulan = max(1, min(12, (int) $request->get('bulan', now()->month)));
        $tahun = max(2000, min(2100, (int) $request->get('tahun', now()->year)));

        $data = NeracaKeuanganService::build($profilId, $bulan, $tahun, PeriodeTenant::id());
        $namaDapur = ProfilMbg::query()->whereKey($profilId)->value('nama_dapur');

        return view('laporan-keuangan.neraca', compact('profilId', 'data', 'namaDapur', 'bulan', 'tahun'));
    }

    public function exportNeracaExcel(Request $request)
    {
        $profilId = $this->profilMbgIdForKeuanganOrFirst($request);
        $bulan = max(1, min(12, (int) $request->get('bulan', now()->month)));
        $tahun = max(2000, min(2100, (int) $request->get('tahun', now()->year)));
        $data = NeracaKeuanganService::build($profilId, $bulan, $tahun, PeriodeTenant::id());
        $namaDapur = ProfilMbg::query()->whereKey($profilId)->value('nama_dapur');

        return Excel::download(
            new NeracaKeuanganExport($data, $namaDapur, $bulan, $tahun),
            'neraca-keuangan-'.$tahun.'-'.str_pad((string) $bulan, 2, '0', STR_PAD_LEFT).'.xlsx'
        );
    }

    public function exportNeracaPdf(Request $request)
    {
        $profilId = $this->profilMbgIdForKeuanganOrFirst($request);
        $bulan = max(1, min(12, (int) $request->get('bulan', now()->month)));
        $tahun = max(2000, min(2100, (int) $request->get('tahun', now()->year)));
        $data = NeracaKeuanganService::build($profilId, $bulan, $tahun, PeriodeTenant::id());
        $namaDapur = ProfilMbg::query()->whereKey($profilId)->value('nama_dapur');

        $pdf = Pdf::loadView('laporan-keuangan.neraca-pdf', [
            'data' => $data,
            'namaDapur' => $namaDapur,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dicetak' => now()->translatedFormat('d F Y H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('neraca-'.$tahun.'-'.$bulan.'.pdf');
    }

    private function recentTransaksi(int $profilId, int $periodeId, int $limit): Collection
    {
        $masuk = DanaMasuk::query()
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->with(['akunJenisDana', 'akunKas'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (DanaMasuk $m) => [
                'jenis' => 'masuk',
                'tanggal' => $m->tanggal,
                'kode' => $m->kode_transaksi,
                'label' => $m->ringkasanBukuPembantu(),
                'uraian' => (string) ($m->uraian_transaksi ?? ''),
                'jumlah' => (float) $m->jumlah,
                'model' => $m,
            ]);

        $keluar = DanaKeluar::query()
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->with(['akunJenisDana', 'akunKas'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (DanaKeluar $k) => [
                'jenis' => 'keluar',
                'tanggal' => $k->tanggal,
                'kode' => $k->kode_transaksi,
                'label' => $k->ringkasanBukuPembantu(),
                'uraian' => (string) ($k->uraian_transaksi ?? ''),
                'jumlah' => (float) $k->jumlah,
                'model' => $k,
            ]);

        return $masuk->concat($keluar)
            ->sortByDesc(fn (array $r) => ($r['tanggal']->timestamp * 100000) + (int) $r['model']->getKey())
            ->values()
            ->take($limit);
    }
}
