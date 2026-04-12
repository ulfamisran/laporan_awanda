<?php

namespace App\Http\Controllers;

use App\Enums\StatusAktif;
use App\Exports\NeracaKeuanganExport;
use App\Exports\RekapArusStokExport;
use App\Exports\RekapLimbahPeriodeExport;
use App\Exports\RekapPenggajianPeriodeExport;
use App\Exports\RekapStokBarangExport;
use App\Http\Controllers\Concerns\PersistsRekapFilters;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Models\KategoriBarang;
use App\Models\KategoriLimbah;
use App\Models\Penggajian;
use App\Models\ProfilMbg;
use App\Services\LaporanRekapQueryService;
use App\Services\NeracaKeuanganService;
use App\Support\PdfLogoProfil;
use App\Support\PeriodeTenant;
use App\Support\ProfilMbgTenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanRekapController extends Controller
{
    use PersistsRekapFilters;

    public function redirectIndex(): RedirectResponse
    {
        return redirect()->route('laporan-rekap.stok');
    }

    public function stokBarang(Request $request): View
    {
        $defaults = [
            'dari' => now()->startOfMonth()->toDateString(),
            'sampai' => now()->endOfMonth()->toDateString(),
            'barang_id' => '',
            'kategori_barang_id' => '',
        ];
        $f = $this->rekapFilters($request, 'stok', array_keys($defaults), $defaults);
        $profilId = ProfilMbgTenant::id();

        $barangId = $f['barang_id'] !== '' && $f['barang_id'] !== null ? (int) $f['barang_id'] : null;
        $katId = $f['kategori_barang_id'] !== '' && $f['kategori_barang_id'] !== null ? (int) $f['kategori_barang_id'] : null;

        $rows = LaporanRekapQueryService::stokBarangPeriode(
            $profilId,
            (string) $f['dari'],
            (string) $f['sampai'],
            $barangId,
            $katId,
            PeriodeTenant::id(),
        );

        $barangs = Barang::query()->where('status', StatusAktif::Aktif)->orderBy('nama_barang')->get();
        $kategoris = KategoriBarang::query()->orderBy('nama_kategori')->get();
        $namaDapur = ProfilMbg::query()->whereKey($profilId)->value('nama_dapur');

        return view('laporan-rekap.stok', compact('rows', 'f', 'barangs', 'kategoris', 'namaDapur'));
    }

    public function arusStok(Request $request): View
    {
        $defaults = [
            'dari' => now()->startOfMonth()->toDateString(),
            'sampai' => now()->endOfMonth()->toDateString(),
            'barang_id' => $this->firstBarangIdForProfil(),
        ];
        $f = $this->rekapFilters($request, 'arus', array_keys($defaults), $defaults);
        if ((int) $f['barang_id'] <= 0) {
            $f['barang_id'] = $this->firstBarangIdForProfil();
        }
        $profilId = ProfilMbgTenant::id();
        $barangId = (int) $f['barang_id'];

        $rows = LaporanRekapQueryService::arusStokDetail(
            $profilId,
            $barangId,
            (string) $f['dari'],
            (string) $f['sampai'],
            PeriodeTenant::id(),
        );
        $barangs = Barang::query()->where('status', StatusAktif::Aktif)->orderBy('nama_barang')->get();
        $namaBarang = Barang::query()->whereKey($barangId)->value('nama_barang');
        $namaDapur = ProfilMbg::query()->whereKey($profilId)->value('nama_dapur');

        return view('laporan-rekap.arus', compact('rows', 'f', 'barangs', 'namaBarang', 'namaDapur'));
    }

    public function keuangan(Request $request): View
    {
        $defaults = [
            'bulan' => now()->month,
            'tahun' => now()->year,
        ];
        $f = $this->rekapFilters($request, 'keuangan', array_keys($defaults), $defaults);
        $profilId = ProfilMbgTenant::id();
        $bulan = max(1, min(12, (int) $f['bulan']));
        $tahun = max(2000, min(2100, (int) $f['tahun']));

        $data = NeracaKeuanganService::build($profilId, $bulan, $tahun, PeriodeTenant::id());
        $namaDapur = ProfilMbg::query()->whereKey($profilId)->value('nama_dapur');

        return view('laporan-rekap.keuangan', compact('data', 'f', 'namaDapur', 'bulan', 'tahun'));
    }

    public function penggajian(Request $request): View
    {
        $defaults = [
            'bulan' => now()->month,
            'tahun' => now()->year,
            'status' => '',
        ];
        $f = $this->rekapFilters($request, 'penggajian', array_keys($defaults), $defaults);
        $profilId = ProfilMbgTenant::id();
        $bulan = max(1, min(12, (int) $f['bulan']));
        $tahun = max(2000, min(2100, (int) $f['tahun']));
        $status = $f['status'] !== '' ? (string) $f['status'] : null;

        $rows = LaporanRekapQueryService::penggajianRekap($profilId, $bulan, $tahun, $status, PeriodeTenant::id());
        $totalGaji = (float) $rows->sum('total_gaji');
        $namaDapur = ProfilMbg::query()->whereKey($profilId)->value('nama_dapur');

        return view('laporan-rekap.penggajian', compact('rows', 'f', 'totalGaji', 'namaDapur'));
    }

    public function limbah(Request $request): View
    {
        $defaults = [
            'dari' => now()->startOfMonth()->toDateString(),
            'sampai' => now()->endOfMonth()->toDateString(),
            'kategori_limbah_id' => '',
        ];
        $f = $this->rekapFilters($request, 'limbah', array_keys($defaults), $defaults);
        $profilId = ProfilMbgTenant::id();
        $kat = $f['kategori_limbah_id'] !== '' && $f['kategori_limbah_id'] !== null ? (int) $f['kategori_limbah_id'] : null;

        $rows = LaporanRekapQueryService::limbahRekap($profilId, (string) $f['dari'], (string) $f['sampai'], $kat, PeriodeTenant::id());
        $kategoris = KategoriLimbah::query()->orderBy('nama_kategori')->get();
        $namaDapur = ProfilMbg::query()->whereKey($profilId)->value('nama_dapur');

        return view('laporan-rekap.limbah', compact('rows', 'f', 'kategoris', 'namaDapur'));
    }

    public function komprehensif(Request $request): View
    {
        $defaults = [
            'dari' => now()->startOfMonth()->toDateString(),
            'sampai' => now()->endOfMonth()->toDateString(),
        ];
        $f = $this->rekapFilters($request, 'komprehensif', array_keys($defaults), $defaults);
        $profilId = ProfilMbgTenant::id();
        $namaDapur = ProfilMbg::query()->whereKey($profilId)->value('nama_dapur');

        return view('laporan-rekap.komprehensif', compact('f', 'namaDapur'));
    }

    public function exportStokExcel(Request $request): BinaryFileResponse
    {
        [$profilId, $f, $rows] = $this->stokQueryFromSession($request);

        return Excel::download(
            new RekapStokBarangExport($rows),
            $this->excelName('Laporan_Stok_Barang', $profilId, (string) $f['dari'], (string) $f['sampai'])
        );
    }

    public function exportStokPdf(Request $request)
    {
        [$profilId, $f, $rows] = $this->stokQueryFromSession($request);
        $profil = ProfilMbg::query()->findOrFail($profilId);

        return $this->streamPdf('laporan-rekap.pdf-stok', [
            'rows' => $rows,
            'judul' => 'Laporan stok barang',
            'periode' => $f['dari'].' — '.$f['sampai'],
            'profil' => $profil,
            'logoDataUri' => PdfLogoProfil::dataUri($profil),
            'draft' => $request->boolean('draft'),
            'pencetak' => $request->user()?->name,
            'dicetak' => now()->translatedFormat('d F Y H:i'),
        ], 'landscape');
    }

    public function exportArusExcel(Request $request): BinaryFileResponse
    {
        [$profilId, $f, $rows] = $this->arusQueryFromSession($request);

        return Excel::download(
            new RekapArusStokExport($rows),
            $this->excelName('Laporan_Arus_Stok', $profilId, (string) $f['dari'], (string) $f['sampai'])
        );
    }

    public function exportArusPdf(Request $request)
    {
        [$profilId, $f, $rows] = $this->arusQueryFromSession($request);
        $profil = ProfilMbg::query()->findOrFail($profilId);
        $namaBarang = Barang::query()->whereKey((int) $f['barang_id'])->value('nama_barang');

        return $this->streamPdf('laporan-rekap.pdf-arus', [
            'rows' => $rows,
            'judul' => 'Laporan arus stok detail',
            'periode' => $f['dari'].' — '.$f['sampai'],
            'profil' => $profil,
            'logoDataUri' => PdfLogoProfil::dataUri($profil),
            'draft' => $request->boolean('draft'),
            'pencetak' => $request->user()?->name,
            'dicetak' => now()->translatedFormat('d F Y H:i'),
            'namaBarang' => $namaBarang,
        ], 'landscape');
    }

    public function exportKeuanganExcel(Request $request): BinaryFileResponse
    {
        [$profilId, $bulan, $tahun, $data, $namaDapur] = $this->keuanganQueryFromSession($request);
        $periode = $tahun.'-'.str_pad((string) $bulan, 2, '0', STR_PAD_LEFT);

        return Excel::download(
            new NeracaKeuanganExport($data, $namaDapur, $bulan, $tahun),
            $this->excelName('Laporan_Keuangan', $profilId, $periode, 'xlsx')
        );
    }

    public function exportKeuanganPdf(Request $request)
    {
        [$profilId, $bulan, $tahun, $data, $namaDapur] = $this->keuanganQueryFromSession($request);
        $profil = ProfilMbg::query()->findOrFail($profilId);

        $mulai = $data['mulai'] ?? Carbon::createFromDate($tahun, $bulan, 1);

        return $this->streamPdf('laporan-rekap.pdf-keuangan', [
            'data' => $data,
            'namaDapur' => $namaDapur,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'profil' => $profil,
            'judul' => 'Laporan keuangan (neraca)',
            'periode' => $mulai->translatedFormat('F Y'),
            'logoDataUri' => PdfLogoProfil::dataUri($profil),
            'draft' => $request->boolean('draft'),
            'pencetak' => $request->user()?->name,
            'dicetak' => now()->translatedFormat('d F Y H:i'),
        ], 'portrait');
    }

    public function exportPenggajianExcel(Request $request): BinaryFileResponse
    {
        [$profilId, $f, $rows] = $this->penggajianQueryFromSession($request);
        $periode = $f['tahun'].'-'.str_pad((string) $f['bulan'], 2, '0', STR_PAD_LEFT);

        return Excel::download(
            new RekapPenggajianPeriodeExport($rows),
            $this->excelName('Laporan_Penggajian', $profilId, $periode)
        );
    }

    public function exportPenggajianPdf(Request $request)
    {
        [$profilId, $f, $rows] = $this->penggajianQueryFromSession($request);
        $profil = ProfilMbg::query()->findOrFail($profilId);
        $periodeLabel = Carbon::createFromDate((int) $f['tahun'], (int) $f['bulan'], 1)->translatedFormat('F Y');
        $totalKeseluruhan = (float) $rows->sum('total_gaji');

        return $this->streamPdf('laporan-rekap.pdf-penggajian', [
            'rows' => $rows,
            'periodeLabel' => $periodeLabel,
            'judul' => 'Laporan penggajian',
            'periode' => $periodeLabel,
            'profil' => $profil,
            'logoDataUri' => PdfLogoProfil::dataUri($profil),
            'draft' => $request->boolean('draft'),
            'pencetak' => $request->user()?->name,
            'dicetak' => now()->translatedFormat('d F Y H:i'),
            'totalKeseluruhan' => $totalKeseluruhan,
        ], 'landscape');
    }

    public function exportLimbahExcel(Request $request): BinaryFileResponse
    {
        [$profilId, $f, $rows] = $this->limbahQueryFromSession($request);

        return Excel::download(
            new RekapLimbahPeriodeExport($rows),
            $this->excelName('Laporan_Limbah', $profilId, (string) $f['dari'], (string) $f['sampai'])
        );
    }

    public function exportLimbahPdf(Request $request)
    {
        [$profilId, $f, $rows] = $this->limbahQueryFromSession($request);
        $profil = ProfilMbg::query()->findOrFail($profilId);

        return $this->streamPdf('laporan-rekap.pdf-limbah', [
            'rows' => $rows,
            'dari' => $f['dari'],
            'sampai' => $f['sampai'],
            'judul' => 'Laporan limbah',
            'periode' => $f['dari'].' — '.$f['sampai'],
            'profil' => $profil,
            'logoDataUri' => PdfLogoProfil::dataUri($profil),
            'draft' => $request->boolean('draft'),
            'pencetak' => $request->user()?->name,
            'dicetak' => now()->translatedFormat('d F Y H:i'),
        ], 'landscape');
    }

    public function exportKomprehensifPdf(Request $request)
    {
        $defaults = [
            'dari' => now()->startOfMonth()->toDateString(),
            'sampai' => now()->endOfMonth()->toDateString(),
        ];
        $f = $this->rekapFilters($request, 'komprehensif', array_keys($defaults), $defaults);
        $profilId = ProfilMbgTenant::id();
        $profil = ProfilMbg::query()->findOrFail($profilId);

        $stokRows = LaporanRekapQueryService::stokBarangPeriode($profilId, (string) $f['dari'], (string) $f['sampai'], null, null, PeriodeTenant::id());
        $limbahRows = LaporanRekapQueryService::limbahRekap($profilId, (string) $f['dari'], (string) $f['sampai'], null, PeriodeTenant::id());
        $pengRows = LaporanRekapQueryService::penggajianRekapRange($profilId, (string) $f['dari'], (string) $f['sampai'], null, PeriodeTenant::id());
        $sampaiC = Carbon::parse((string) $f['sampai']);
        $neraca = NeracaKeuanganService::build($profilId, (int) $sampaiC->month, (int) $sampaiC->year, PeriodeTenant::id());

        return $this->streamPdf('laporan-rekap.pdf-komprehensif', [
            'f' => $f,
            'judul' => 'Laporan komprehensif MBG',
            'periode' => $f['dari'].' — '.$f['sampai'],
            'profil' => $profil,
            'logoDataUri' => PdfLogoProfil::dataUri($profil),
            'stokRows' => $stokRows,
            'limbahRows' => $limbahRows,
            'pengRows' => $pengRows,
            'neraca' => $neraca,
            'draft' => $request->boolean('draft'),
            'pencetak' => $request->user()?->name,
            'dicetak' => now()->translatedFormat('d F Y H:i'),
        ], 'landscape');
    }

    /**
     * @return array{0: int, 1: array<string, mixed>, 2: Collection<int, object>}
     */
    private function stokQueryFromSession(Request $request): array
    {
        $defaults = [
            'dari' => now()->startOfMonth()->toDateString(),
            'sampai' => now()->endOfMonth()->toDateString(),
            'barang_id' => '',
            'kategori_barang_id' => '',
        ];
        $f = $this->rekapFilters($request, 'stok', array_keys($defaults), $defaults);
        $profilId = ProfilMbgTenant::id();
        $barangId = $f['barang_id'] !== '' ? (int) $f['barang_id'] : null;
        $katId = $f['kategori_barang_id'] !== '' ? (int) $f['kategori_barang_id'] : null;
        $rows = LaporanRekapQueryService::stokBarangPeriode($profilId, (string) $f['dari'], (string) $f['sampai'], $barangId, $katId, PeriodeTenant::id());

        return [$profilId, $f, $rows];
    }

    /**
     * @return array{0: int, 1: array<string, mixed>, 2: Collection<int, array<string, mixed>>}
     */
    private function arusQueryFromSession(Request $request): array
    {
        $defaults = [
            'dari' => now()->startOfMonth()->toDateString(),
            'sampai' => now()->endOfMonth()->toDateString(),
            'barang_id' => $this->firstBarangIdForProfil(),
        ];
        $f = $this->rekapFilters($request, 'arus', array_keys($defaults), $defaults);
        if ((int) $f['barang_id'] <= 0) {
            $f['barang_id'] = $this->firstBarangIdForProfil();
        }
        $profilId = ProfilMbgTenant::id();
        $rows = LaporanRekapQueryService::arusStokDetail($profilId, (int) $f['barang_id'], (string) $f['dari'], (string) $f['sampai'], PeriodeTenant::id());

        return [$profilId, $f, $rows];
    }

    /**
     * @return array{0: int, 1: int, 2: int, 3: array<string, mixed>, 4: string|null}
     */
    private function keuanganQueryFromSession(Request $request): array
    {
        $defaults = [
            'bulan' => now()->month,
            'tahun' => now()->year,
        ];
        $f = $this->rekapFilters($request, 'keuangan', array_keys($defaults), $defaults);
        $profilId = ProfilMbgTenant::id();
        $bulan = max(1, min(12, (int) $f['bulan']));
        $tahun = max(2000, min(2100, (int) $f['tahun']));
        $data = NeracaKeuanganService::build($profilId, $bulan, $tahun, PeriodeTenant::id());
        $namaDapur = ProfilMbg::query()->whereKey($profilId)->value('nama_dapur');

        return [$profilId, $bulan, $tahun, $data, $namaDapur];
    }

    /**
     * @return array{0: int, 1: array<string, mixed>, 2: Collection<int, Penggajian>}
     */
    private function penggajianQueryFromSession(Request $request): array
    {
        $defaults = [
            'bulan' => now()->month,
            'tahun' => now()->year,
            'status' => '',
        ];
        $f = $this->rekapFilters($request, 'penggajian', array_keys($defaults), $defaults);
        $profilId = ProfilMbgTenant::id();
        $bulan = max(1, min(12, (int) $f['bulan']));
        $tahun = max(2000, min(2100, (int) $f['tahun']));
        $status = $f['status'] !== '' ? (string) $f['status'] : null;
        $rows = LaporanRekapQueryService::penggajianRekap($profilId, $bulan, $tahun, $status, PeriodeTenant::id());
        $f['bulan'] = $bulan;
        $f['tahun'] = $tahun;

        return [$profilId, $f, $rows];
    }

    /**
     * @return array{0: int, 1: array<string, mixed>, 2: Collection<int, object>}
     */
    private function limbahQueryFromSession(Request $request): array
    {
        $defaults = [
            'dari' => now()->startOfMonth()->toDateString(),
            'sampai' => now()->endOfMonth()->toDateString(),
            'kategori_limbah_id' => '',
        ];
        $f = $this->rekapFilters($request, 'limbah', array_keys($defaults), $defaults);
        $profilId = ProfilMbgTenant::id();
        $kat = $f['kategori_limbah_id'] !== '' ? (int) $f['kategori_limbah_id'] : null;
        $rows = LaporanRekapQueryService::limbahRekap($profilId, (string) $f['dari'], (string) $f['sampai'], $kat, PeriodeTenant::id());

        return [$profilId, $f, $rows];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function streamPdf(string $view, array $data, string $orientation): Response
    {
        $pdf = Pdf::loadView($view, $data)
            ->setOption('enable_php', true)
            ->setPaper('a4', $orientation);

        return $pdf->stream();
    }

    private function excelName(string $namaLaporan, int $profilId, string $periode, string $ext = 'xlsx'): string
    {
        $slug = Str::slug(ProfilMbg::query()->whereKey($profilId)->value('nama_dapur') ?? 'dapur', '_');
        $safePeriode = str_replace(['/', '\\'], '-', $periode);

        return $namaLaporan.'_'.$slug.'_'.$safePeriode.'.'.$ext;
    }

    private function firstBarangIdForProfil(): int
    {
        $pid = ProfilMbgTenant::id();
        $id = BarangMasuk::query()->where('profil_mbg_id', $pid)->orderByDesc('id')->value('barang_id');
        if ($id) {
            return (int) $id;
        }
        $id = BarangKeluar::query()->where('profil_mbg_id', $pid)->orderByDesc('id')->value('barang_id');
        if ($id) {
            return (int) $id;
        }
        $id = Barang::query()->where('status', StatusAktif::Aktif)->orderBy('nama_barang')->value('id');
        abort_unless($id, 422, 'Belum ada barang untuk laporan arus stok.');

        return (int) $id;
    }
}
