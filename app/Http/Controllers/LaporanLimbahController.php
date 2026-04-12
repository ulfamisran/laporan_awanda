<?php

namespace App\Http\Controllers;

use App\Enums\JenisPenangananLimbah;
use App\Enums\SatuanLimbah;
use App\Exports\LaporanLimbahHarianListExport;
use App\Exports\LaporanLimbahRekapExport;
use App\Http\Controllers\Concerns\ManagesLimbahProfil;
use App\Models\KategoriLimbah;
use App\Models\LaporanLimbah;
use App\Models\LaporanLimbahHarian;
use App\Models\ProfilMbg;
use App\Support\KodeLaporanLimbah;
use App\Support\PeriodeTenant;
use App\Support\ProfilMbgTenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class LaporanLimbahController extends Controller
{
    use ManagesLimbahProfil;

    public function index(Request $request): View
    {
        $profilIdDefault = $this->profilMbgIdForLimbahOrFirst($request);

        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $summaryQuery = LaporanLimbah::query();
        $this->applyLimbahProfilFilter($summaryQuery, $request);
        $this->applyPeriodeLaporan($summaryQuery);
        $summaryQuery->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()]);
        if ($request->filled('kategori_limbah_id')) {
            $summaryQuery->where('kategori_limbah_id', $request->integer('kategori_limbah_id'));
        }
        if ($request->filled('jenis_penanganan')) {
            $summaryQuery->where('jenis_penanganan', $request->string('jenis_penanganan')->toString());
        }

        $summaryRows = $summaryQuery->get();
        $totalVolumeKg = (float) $summaryRows->sum(fn (LaporanLimbah $r) => $r->volumeKgEstimate());
        $volumeDaur = (float) $summaryRows
            ->filter(fn (LaporanLimbah $r) => $r->jenis_penanganan === JenisPenangananLimbah::DidaurUlang)
            ->sum(fn (LaporanLimbah $r) => $r->volumeKgEstimate());
        $volumeDijual = (float) $summaryRows
            ->filter(fn (LaporanLimbah $r) => $r->jenis_penanganan === JenisPenangananLimbah::Dijual)
            ->sum(fn (LaporanLimbah $r) => $r->volumeKgEstimate());
        $pendapatanDijual = (float) $summaryRows
            ->filter(fn (LaporanLimbah $r) => $r->jenis_penanganan === JenisPenangananLimbah::Dijual)
            ->sum(fn (LaporanLimbah $r) => (float) ($r->harga_jual ?? 0));

        $chartBar = $this->chartVolumePerKategoriPerBulan($request);

        $kategoris = KategoriLimbah::query()->orderBy('nama_kategori')->get();

        return view('laporan-limbah.index', compact(
            'profilIdDefault',
            'totalVolumeKg',
            'volumeDaur',
            'volumeDijual',
            'pendapatanDijual',
            'chartBar',
            'kategoris',
        ));
    }

    public function data(Request $request): JsonResponse
    {
        $kategoris = KategoriLimbah::query()->orderBy('nama_kategori')->get();

        $query = LaporanLimbahHarian::query()
            ->with(['details.kategoriLimbah'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $this->applyLimbahProfilFilter($query, $request);
        $query->where('periode_id', PeriodeTenant::id());

        if ($request->filled('kategori_limbah_id')) {
            $kid = $request->integer('kategori_limbah_id');
            $query->whereHas('details', fn (Builder $d) => $d->where('kategori_limbah_id', $kid));
        }
        if ($request->filled('jenis_penanganan')) {
            $jen = $request->string('jenis_penanganan')->toString();
            $query->whereHas('details', fn (Builder $d) => $d->where('jenis_penanganan', $jen));
        }
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal', '>=', $request->string('tanggal_dari')->toString());
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal', '<=', $request->string('tanggal_sampai')->toString());
        }

        $dt = DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('tanggal', fn (LaporanLimbahHarian $h) => $h->tanggal?->format('d/m/Y') ?? '')
            ->editColumn('menu_makanan', fn (LaporanLimbahHarian $h) => Str::limit((string) $h->menu_makanan, 120));

        foreach ($kategoris as $k) {
            $kid = (int) $k->id;
            $dt->addColumn('kat_'.$kid, function (LaporanLimbahHarian $h) use ($kid) {
                $d = $h->details->firstWhere('kategori_limbah_id', $kid);
                if (! $d) {
                    return '<span class="inst-td-muted text-xs">—</span>';
                }
                $s = $d->satuan instanceof SatuanLimbah ? $d->satuan->label() : (string) $d->satuan;
                $jml = '<span class="font-mono text-xs">'.e(number_format((float) $d->jumlah, 2, ',', '.')).' '.e($s).'</span>';
                if (! $d->gambar_url) {
                    return '<div class="text-center">'.$jml.'</div>';
                }

                return '<div class="flex flex-col items-center gap-1">'
                    .'<img src="'.e($d->gambar_url).'" alt="" class="h-10 w-10 rounded object-cover" style="border:1px solid #d4e8f4">'
                    .$jml
                    .'</div>';
            });
        }

        $rawCols = ['aksi'];
        foreach ($kategoris as $k) {
            $rawCols[] = 'kat_'.$k->id;
        }

        return $dt
            ->addColumn('aksi', function (LaporanLimbahHarian $h) {
                $show = '<a href="'.e(route('laporan-limbah.harian.show', $h)).'" class="text-xs font-semibold" style="color:#1a4a6b;">Detail</a>';
                $edit = '<a href="'.e(route('laporan-limbah.harian.edit', $h)).'" class="ml-3 text-xs font-semibold" style="color:#4a9b7a;">Ubah</a>';
                $hapus = '<form method="POST" action="'.e(route('laporan-limbah.harian.destroy', $h)).'" class="ml-3 inline" onsubmit="return confirm(\'Hapus laporan harian ini?\');">'
                    .csrf_field()
                    .method_field('DELETE')
                    .'<button type="submit" class="text-xs font-semibold" style="color:#c0392b;">Hapus</button>'
                    .'</form>';

                return '<div class="flex flex-wrap items-center justify-end">'.$show.$edit.$hapus.'</div>';
            })
            ->rawColumns($rawCols)
            ->toJson();
    }

    public function create(Request $request): View
    {
        $profilId = $this->profilMbgIdForLimbahOrFirst($request);
        $kategoris = KategoriLimbah::query()->orderBy('nama_kategori')->get();

        return view('laporan-limbah.create', compact('profilId', 'kategoris'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->mergeProfilForAdminDapur($request);
        $profilId = $this->profilMbgIdFromLimbahForm($request);
        $this->ensureLimbahProfil($request, $profilId);

        $kategoris = KategoriLimbah::query()->orderBy('nama_kategori')->get();
        if ($kategoris->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'Belum ada kategori limbah di master.')
                ->withInput();
        }

        $validated = $this->validatedDailyBatch($request, $kategoris, false, null);
        $tanggalStr = $this->parseTanggal($validated['tanggal'])->toDateString();

        $dup = LaporanLimbahHarian::query()
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', PeriodeTenant::id())
            ->whereDate('tanggal', $tanggalStr)
            ->exists();
        if ($dup) {
            return redirect()
                ->back()
                ->withErrors(['tanggal' => 'Laporan untuk tanggal ini sudah ada. Ubah lewat daftar atau pilih tanggal lain.'])
                ->withInput();
        }

        $periodeId = PeriodeTenant::id();

        $harian = DB::transaction(function () use ($request, $profilId, $periodeId, $tanggalStr, $validated, $kategoris): LaporanLimbahHarian {
            $h = LaporanLimbahHarian::query()->create([
                'profil_mbg_id' => $profilId,
                'periode_id' => $periodeId,
                'tanggal' => $tanggalStr,
                'menu_makanan' => $validated['menu_makanan'],
                'created_by' => (int) $request->user()->getKey(),
            ]);

            foreach ($kategoris as $k) {
                $kid = (int) $k->id;
                $row = $validated['kategori'][$kid];
                $file = $request->file("kategori.$kid.gambar");
                $gambar = $this->simpanGambarLimbah($file);
                LaporanLimbah::query()->create([
                    'harian_id' => $h->id,
                    'kode_laporan' => KodeLaporanLimbah::generate(),
                    'kategori_limbah_id' => $kid,
                    'profil_mbg_id' => $profilId,
                    'periode_id' => $periodeId,
                    'tanggal' => $tanggalStr,
                    'jumlah' => $row['jumlah'],
                    'satuan' => $row['satuan'],
                    'jenis_penanganan' => $row['jenis_penanganan'],
                    'harga_jual' => $row['harga_jual'] ?? null,
                    'keterangan' => $row['keterangan'] ?? null,
                    'gambar' => $gambar,
                    'created_by' => (int) $request->user()->getKey(),
                ]);
            }

            return $h;
        });

        return redirect()
            ->route('laporan-limbah.harian.show', $harian)
            ->with('success', 'Laporan limbah harian berhasil disimpan.');
    }

    public function show(Request $request, LaporanLimbahHarian $harian): View
    {
        $this->ensureLimbahProfil($request, (int) $harian->profil_mbg_id);
        $this->ensureLimbahHarianPeriode($harian);
        $harian->load(['details.kategoriLimbah', 'profilMbg', 'creator']);

        return view('laporan-limbah.show', ['harian' => $harian]);
    }

    public function edit(Request $request, LaporanLimbahHarian $harian): View
    {
        $this->ensureLimbahProfil($request, (int) $harian->profil_mbg_id);
        $this->ensureLimbahHarianPeriode($harian);
        $profilId = (int) $harian->profil_mbg_id;
        $kategoris = KategoriLimbah::query()->orderBy('nama_kategori')->get();
        $harian->load('details');

        return view('laporan-limbah.edit', [
            'harian' => $harian,
            'profilId' => $profilId,
            'kategoris' => $kategoris,
        ]);
    }

    public function update(Request $request, LaporanLimbahHarian $harian): RedirectResponse
    {
        $this->ensureLimbahProfil($request, (int) $harian->profil_mbg_id);
        $this->ensureLimbahHarianPeriode($harian);
        $this->mergeProfilForAdminDapur($request);
        $profilId = $this->profilMbgIdFromLimbahForm($request);
        $this->ensureLimbahProfil($request, $profilId);

        $kategoris = KategoriLimbah::query()->orderBy('nama_kategori')->get();
        if ($kategoris->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'Belum ada kategori limbah di master.')
                ->withInput();
        }

        $harian->load('details');
        $validated = $this->validatedDailyBatch($request, $kategoris, true, $harian);
        $tanggalStr = $this->parseTanggal($validated['tanggal'])->toDateString();

        $dup = LaporanLimbahHarian::query()
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', PeriodeTenant::id())
            ->whereDate('tanggal', $tanggalStr)
            ->where('id', '!=', $harian->id)
            ->exists();
        if ($dup) {
            return redirect()
                ->back()
                ->withErrors(['tanggal' => 'Tanggal bentrok dengan laporan harian lain.'])
                ->withInput();
        }

        $periodeId = PeriodeTenant::id();

        DB::transaction(function () use ($request, $harian, $profilId, $periodeId, $tanggalStr, $validated, $kategoris): void {
            $harian->update([
                'tanggal' => $tanggalStr,
                'menu_makanan' => $validated['menu_makanan'],
                'profil_mbg_id' => $profilId,
            ]);

            $byKat = $harian->details->keyBy('kategori_limbah_id');

            foreach ($kategoris as $k) {
                $kid = (int) $k->id;
                $row = $validated['kategori'][$kid];
                $existing = $byKat->get($kid);
                $file = $request->file("kategori.$kid.gambar");
                $gambar = ($file && $file->isValid())
                    ? $this->simpanGambarLimbah($file)
                    : ($existing?->gambar);
                if ($file && $file->isValid() && $existing?->gambar) {
                    $this->hapusGambarLimbah($existing->gambar);
                }

                $payload = [
                    'profil_mbg_id' => $profilId,
                    'periode_id' => $periodeId,
                    'tanggal' => $tanggalStr,
                    'jumlah' => $row['jumlah'],
                    'satuan' => $row['satuan'],
                    'jenis_penanganan' => $row['jenis_penanganan'],
                    'harga_jual' => $row['harga_jual'] ?? null,
                    'keterangan' => $row['keterangan'] ?? null,
                    'gambar' => $gambar,
                ];

                if ($existing) {
                    $existing->update($payload);
                } else {
                    $payload['harian_id'] = $harian->id;
                    $payload['kode_laporan'] = KodeLaporanLimbah::generate();
                    $payload['kategori_limbah_id'] = $kid;
                    $payload['created_by'] = (int) $request->user()->getKey();
                    LaporanLimbah::query()->create($payload);
                }
            }
        });

        return redirect()
            ->route('laporan-limbah.harian.show', $harian)
            ->with('success', 'Laporan limbah harian diperbarui.');
    }

    public function destroy(Request $request, LaporanLimbahHarian $harian): RedirectResponse
    {
        $this->ensureLimbahProfil($request, (int) $harian->profil_mbg_id);
        $this->ensureLimbahHarianPeriode($harian);
        $harian->load('details');
        foreach ($harian->details as $d) {
            $this->hapusGambarLimbah($d->gambar);
        }
        $harian->delete();

        return redirect()
            ->route('laporan-limbah.index')
            ->with('success', 'Laporan limbah harian dihapus.');
    }

    public function rekapitulasi(Request $request): View
    {
        $profilIdDefault = $this->profilMbgIdForLimbahOrFirst($request);
        $kategoris = KategoriLimbah::query()->orderBy('nama_kategori')->get();

        $dari = $request->input('dari') ?: now()->startOfMonth()->toDateString();
        $sampai = $request->input('sampai') ?: now()->endOfMonth()->toDateString();

        $rows = $this->buildRekapRows($request, $dari, $sampai);
        $pie = $this->piePenanganan($request, $dari, $sampai);

        return view('laporan-limbah.rekapitulasi', compact(
            'profilIdDefault',
            'kategoris',
            'dari',
            'sampai',
            'rows',
            'pie',
        ));
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        if ($request->boolean('rekap')) {
            $dari = $request->input('dari') ?: now()->startOfMonth()->toDateString();
            $sampai = $request->input('sampai') ?: now()->endOfMonth()->toDateString();
            $rows = $this->buildRekapRows($request, $dari, $sampai);

            return Excel::download(new LaporanLimbahRekapExport($rows), 'rekap-limbah.xlsx');
        }

        $rows = $this->filteredHarianQuery($request)
            ->with('details')
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        return Excel::download(new LaporanLimbahHarianListExport($rows), 'laporan-limbah-harian.xlsx');
    }

    public function exportPdf(Request $request)
    {
        if ($request->boolean('rekap')) {
            $dari = $request->input('dari') ?: now()->startOfMonth()->toDateString();
            $sampai = $request->input('sampai') ?: now()->endOfMonth()->toDateString();
            $rows = $this->buildRekapRows($request, $dari, $sampai);
            $pie = $this->piePenanganan($request, $dari, $sampai);

            $pdf = Pdf::loadView('laporan-limbah.rekap-pdf', [
                'rows' => $rows,
                'pie' => $pie,
                'dari' => $dari,
                'sampai' => $sampai,
            ])->setPaper('a4', 'landscape');

            return $pdf->stream('rekap-limbah.pdf');
        }

        $kategoris = KategoriLimbah::query()->orderBy('nama_kategori')->get();
        $harians = $this->filteredHarianQuery($request)
            ->with(['details.kategoriLimbah'])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $pdfRows = $harians->map(function (LaporanLimbahHarian $h) use ($kategoris) {
            $byKat = $h->details->keyBy('kategori_limbah_id');
            $cells = [];
            foreach ($kategoris as $k) {
                $d = $byKat->get($k->id);
                $sat = $d && $d->satuan instanceof SatuanLimbah ? $d->satuan->label() : ($d ? (string) $d->satuan : '');
                $cells[] = (object) [
                    'gambar_data_uri' => $d ? $this->limbahGambarDataUriForPdf($d->gambar) : null,
                    'jumlah_satuan' => $d
                        ? number_format((float) $d->jumlah, 2, ',', '.').' '.$sat
                        : '—',
                ];
            }

            return (object) [
                'tanggal_fmt' => $h->tanggal?->format('d/m/Y') ?? '—',
                'menu' => $h->menu_makanan,
                'cells' => $cells,
            ];
        });

        $profil = ProfilMbg::query()->find(ProfilMbgTenant::id());
        $logoDataUri = $this->profilLogoDataUriForPdf($profil);

        $pdf = Pdf::loadView('laporan-limbah.list-pdf', [
            'kategoris' => $kategoris,
            'pdfRows' => $pdfRows,
            'profil' => $profil,
            'logoDataUri' => $logoDataUri,
            'periodeAktif' => PeriodeTenant::model()->labelRingkas(),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('laporan-limbah.pdf');
    }

    private function filteredHarianQuery(Request $request): Builder
    {
        $query = LaporanLimbahHarian::query();
        $this->applyLimbahProfilFilter($query, $request);
        $query->where('periode_id', PeriodeTenant::id());
        if ($request->filled('kategori_limbah_id')) {
            $kid = $request->integer('kategori_limbah_id');
            $query->whereHas('details', fn (Builder $d) => $d->where('kategori_limbah_id', $kid));
        }
        if ($request->filled('jenis_penanganan')) {
            $jen = $request->string('jenis_penanganan')->toString();
            $query->whereHas('details', fn (Builder $d) => $d->where('jenis_penanganan', $jen));
        }
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal', '>=', $request->string('tanggal_dari')->toString());
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal', '<=', $request->string('tanggal_sampai')->toString());
        }

        return $query;
    }

    /**
     * @return Collection<int, object>
     */
    private function buildRekapRows(Request $request, string $dari, string $sampai): Collection
    {
        $q = LaporanLimbah::query()->with('kategoriLimbah');
        $this->applyLimbahProfilFilter($q, $request);
        $this->applyPeriodeLaporan($q);
        if ($request->filled('kategori_limbah_id')) {
            $q->where('kategori_limbah_id', $request->integer('kategori_limbah_id'));
        }
        $q->periode($dari, $sampai);

        return $q->get()
            ->groupBy('kategori_limbah_id')
            ->map(function (Collection $items) {
                $nama = $items->first()?->kategoriLimbah?->nama_kategori ?? '—';

                $vol = fn (JenisPenangananLimbah $j) => (float) $items
                    ->filter(fn (LaporanLimbah $r) => $r->jenis_penanganan === $j)
                    ->sum(fn (LaporanLimbah $r) => $r->volumeKgEstimate());

                $pendapatan = (float) $items
                    ->filter(fn (LaporanLimbah $r) => $r->jenis_penanganan === JenisPenangananLimbah::Dijual)
                    ->sum(fn (LaporanLimbah $r) => (float) ($r->harga_jual ?? 0));

                return (object) [
                    'nama_kategori' => $nama,
                    'total_volume_kg' => (float) $items->sum(fn (LaporanLimbah $r) => $r->volumeKgEstimate()),
                    'vol_dibuang' => $vol(JenisPenangananLimbah::Dibuang),
                    'vol_didaur_ulang' => $vol(JenisPenangananLimbah::DidaurUlang),
                    'vol_dijual' => $vol(JenisPenangananLimbah::Dijual),
                    'vol_dikembalikan' => $vol(JenisPenangananLimbah::Dikembalikan),
                    'vol_lainnya' => $vol(JenisPenangananLimbah::Lainnya),
                    'pendapatan' => $pendapatan,
                ];
            })
            ->values();
    }

    /**
     * @return list<array{label: string, value: float}>
     */
    private function piePenanganan(Request $request, string $dari, string $sampai): array
    {
        $q = LaporanLimbah::query();
        $this->applyLimbahProfilFilter($q, $request);
        $this->applyPeriodeLaporan($q);
        if ($request->filled('kategori_limbah_id')) {
            $q->where('kategori_limbah_id', $request->integer('kategori_limbah_id'));
        }
        $q->periode($dari, $sampai);
        $rows = $q->get();

        $out = [];
        foreach (JenisPenangananLimbah::cases() as $case) {
            $v = (float) $rows
                ->filter(fn (LaporanLimbah $r) => $r->jenis_penanganan === $case)
                ->sum(fn (LaporanLimbah $r) => $r->volumeKgEstimate());
            $out[] = ['label' => $case->label(), 'value' => $v];
        }

        return $out;
    }

    /**
     * @return array{labels: list<string>, datasets: list<array{label: string, data: list<float>, backgroundColor: string}>}
     */
    private function chartVolumePerKategoriPerBulan(Request $request): array
    {
        $from = now()->subMonths(11)->startOfMonth();
        $to = now()->endOfMonth();

        $q = LaporanLimbah::query()->with('kategoriLimbah');
        $this->applyLimbahProfilFilter($q, $request);
        $this->applyPeriodeLaporan($q);
        $q->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()]);
        $rows = $q->get();

        $labels = [];
        $keys = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->copy()->subMonths($i)->startOfMonth();
            $keys[] = $d->format('Y-m');
            $labels[] = $d->translatedFormat('M Y');
        }

        $kategoriIds = $rows->pluck('kategori_limbah_id')->unique()->filter()->values();
        $namaKat = [];
        foreach ($kategoriIds as $kid) {
            $namaKat[$kid] = $rows->firstWhere('kategori_limbah_id', $kid)?->kategoriLimbah?->nama_kategori ?? '#'.$kid;
        }

        $palette = ['#1a4a6b', '#4a9b7a', '#7fa8c9', '#2d7a60', '#c0392b', '#8b5cf6', '#f59e0b', '#0ea5e9', '#64748b', '#ec4899'];
        $datasets = [];
        $idx = 0;
        foreach ($namaKat as $kid => $nama) {
            $data = [];
            foreach ($keys as $ym) {
                $sum = (float) $rows
                    ->filter(fn (LaporanLimbah $r) => (int) $r->kategori_limbah_id === (int) $kid
                        && $r->tanggal && $r->tanggal->format('Y-m') === $ym)
                    ->sum(fn (LaporanLimbah $r) => $r->volumeKgEstimate());
                $data[] = round($sum, 2);
            }
            $datasets[] = [
                'label' => $nama,
                'data' => $data,
                'backgroundColor' => $palette[$idx % count($palette)],
            ];
            $idx++;
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    private function mergeProfilForAdminDapur(Request $request): void
    {
        $request->merge(['profil_mbg_id' => ProfilMbgTenant::id()]);
    }

    private function applyPeriodeLaporan(Builder $query): void
    {
        $query->where('periode_id', PeriodeTenant::id());
    }

    private function ensureLimbahHarianPeriode(LaporanLimbahHarian $harian): void
    {
        if ((int) $harian->periode_id !== PeriodeTenant::id()) {
            abort(404);
        }
    }

    /**
     * @param  Collection<int, KategoriLimbah>  $kategoris
     * @return array{tanggal: string, menu_makanan: string, kategori: array<int, array<string, mixed>>}
     */
    private function validatedDailyBatch(Request $request, Collection $kategoris, bool $isUpdate, ?LaporanLimbahHarian $existingHarian): array
    {
        $satIn = implode(',', array_column(SatuanLimbah::cases(), 'value'));
        $jenIn = implode(',', array_column(JenisPenangananLimbah::cases(), 'value'));

        $rules = [
            'tanggal' => ['required', 'string'],
            'menu_makanan' => ['required', 'string', 'max:1000'],
        ];

        foreach ($kategoris as $k) {
            $id = (int) $k->id;
            $rules["kategori.$id.jumlah"] = ['required', 'numeric', 'min:0.01', 'max:99999999.99'];
            $rules["kategori.$id.satuan"] = ['required', 'string', 'in:'.$satIn];
            $rules["kategori.$id.jenis_penanganan"] = ['required', 'string', 'in:'.$jenIn];
            $rules["kategori.$id.harga_jual"] = ['nullable', 'numeric', 'min:0'];
            $rules["kategori.$id.keterangan"] = ['nullable', 'string', 'max:5000'];
            $rules["kategori.$id.gambar"] = $isUpdate
                ? ['nullable', 'file', 'image', 'max:5120', 'mimes:jpeg,jpg,png,webp']
                : ['required', 'file', 'image', 'max:5120', 'mimes:jpeg,jpg,png,webp'];
        }

        $validated = $request->validate($rules, [], [
            'menu_makanan' => 'menu makanan',
        ]);

        $existingHarian?->loadMissing('details');
        $byKat = $existingHarian?->details->keyBy('kategori_limbah_id');

        foreach ($kategoris as $k) {
            $id = (int) $k->id;
            $file = $request->file("kategori.$id.gambar");
            if ($isUpdate && (! $file || ! $file->isValid())) {
                $ex = $byKat?->get($id);
                if (! $ex?->gambar) {
                    throw ValidationException::withMessages([
                        "kategori.$id.gambar" => 'Foto limbah wajib diisi untuk kategori ini.',
                    ]);
                }
            }

            if (($validated['kategori'][$id]['jenis_penanganan'] ?? '') === JenisPenangananLimbah::Dijual->value) {
                $extra = $request->validate([
                    "kategori.$id.harga_jual" => ['required', 'numeric', 'min:0'],
                ]);
                $validated['kategori'][$id]['harga_jual'] = data_get($extra, "kategori.$id.harga_jual");
            } else {
                $validated['kategori'][$id]['harga_jual'] = null;
            }
        }

        $validated['tanggal'] = $this->parseTanggal($validated['tanggal'])->toDateString();

        return $validated;
    }

    private function parseTanggal(string $raw): Carbon
    {
        try {
            return Carbon::createFromFormat('d/m/Y', $raw)->startOfDay();
        } catch (\Throwable) {
            return Carbon::parse($raw)->startOfDay();
        }
    }

    private function simpanGambarLimbah(?UploadedFile $file): ?string
    {
        if (! $file || ! $file->isValid()) {
            return null;
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = uniqid('limbah_', true).'.'.$ext;
        $file->storeAs('foto-limbah', $name, 'public');

        return $name;
    }

    private function profilLogoDataUriForPdf(?ProfilMbg $profil): ?string
    {
        if ($profil === null || $profil->logo === null || $profil->logo === '') {
            return null;
        }

        $relative = 'logo-mbg/'.$profil->logo;
        $disk = Storage::disk('public');
        if (! $disk->exists($relative)) {
            return null;
        }

        $path = $disk->path($relative);
        if (! is_readable($path)) {
            return null;
        }

        $binary = @file_get_contents($path);
        if ($binary === false || $binary === '') {
            return null;
        }

        $mime = @mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }

    private function limbahGambarDataUriForPdf(?string $nama): ?string
    {
        if ($nama === null || $nama === '') {
            return null;
        }

        $relative = 'foto-limbah/'.$nama;
        $disk = Storage::disk('public');
        if (! $disk->exists($relative)) {
            return null;
        }

        $path = $disk->path($relative);
        if (! is_readable($path)) {
            return null;
        }

        $binary = @file_get_contents($path);
        if ($binary === false || $binary === '') {
            return null;
        }

        $mime = @mime_content_type($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }

    private function hapusGambarLimbah(?string $nama): void
    {
        if (! $nama) {
            return;
        }
        Storage::disk('public')->delete('foto-limbah/'.$nama);
    }
}
