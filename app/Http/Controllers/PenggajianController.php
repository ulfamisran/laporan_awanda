<?php

namespace App\Http\Controllers;

use App\Enums\StatusPenggajian;
use App\Exports\PenggajianExport;
use App\Http\Controllers\Concerns\ManagesPenggajianProfil;
use App\Models\Penggajian;
use App\Models\ProfilMbg;
use App\Models\Relawan;
use App\Support\PeriodeTenant;
use App\Support\ProfilMbgTenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PenggajianController extends Controller
{
    use ManagesPenggajianProfil;

    public function index(Request $request): View
    {
        $profilId = $this->profilMbgIdForPenggajianOrFirst($request);

        $bulan = $request->integer('bulan') ?: now()->month;
        $tahun = $request->integer('tahun') ?: now()->year;
        $statusFilter = $request->string('status')->toString();

        $query = Penggajian::query()
            ->with(['relawan.posisiRelawan', 'profilMbg'])
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', PeriodeTenant::id())
            ->periode($bulan, $tahun)
            ->orderBy('relawan_id');

        if ($this->isAdminDapurOnly($request)) {
            $query->where('status', StatusPenggajian::Draft);
        } elseif ($statusFilter !== '' && in_array($statusFilter, ['draft', 'approved', 'dibayar'], true)) {
            $query->where('status', $statusFilter);
        }

        $rows = $query->get();

        $totalNominal = (float) $rows->sum('total_gaji');
        $sudahDibayar = (float) $rows
            ->filter(fn (Penggajian $p) => $p->status === StatusPenggajian::Dibayar)
            ->sum('total_gaji');
        $belumDibayar = (float) $rows
            ->filter(fn (Penggajian $p) => $p->status !== StatusPenggajian::Dibayar)
            ->sum('total_gaji');

        $existingCount = Penggajian::query()
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', PeriodeTenant::id())
            ->periode($bulan, $tahun)
            ->count();

        return view('penggajian.index', [
            'profilId' => $profilId,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'statusFilter' => $statusFilter,
            'rows' => $rows,
            'totalRelawan' => $rows->count(),
            'totalNominal' => $totalNominal,
            'sudahDibayar' => $sudahDibayar,
            'belumDibayar' => $belumDibayar,
            'existingCount' => $existingCount,
        ]);
    }

    public function create(Request $request): View
    {
        $profilId = $this->profilMbgIdForPenggajianOrFirst($request);

        $bulan = $request->integer('bulan') ?: now()->month;
        $tahun = $request->integer('tahun') ?: now()->year;

        $previewRelawans = collect();
        $existingRelawanIds = collect();

        if ($request->boolean('preview')) {
            $request->validate([
                'bulan' => ['required', 'integer', 'min:1', 'max:12'],
                'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
            ]);

            $previewRelawans = Relawan::query()
                ->aktif()
                ->byDapur($profilId)
                ->with('posisiRelawan')
                ->orderBy('nama_lengkap')
                ->get();

            $existingRelawanIds = Penggajian::query()
                ->where('profil_mbg_id', $profilId)
                ->where('periode_id', PeriodeTenant::id())
                ->periode($bulan, $tahun)
                ->pluck('relawan_id');
        }

        return view('penggajian.create', compact(
            'profilId',
            'bulan',
            'tahun',
            'previewRelawans',
            'existingRelawanIds',
        ));
    }

    public function generateBulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'periode_bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'periode_tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $profilId = ProfilMbgTenant::id();
        $this->ensurePenggajianProfil($request, $profilId);
        $this->abortUnlessCanManageDraft($request);

        $bulan = (int) $data['periode_bulan'];
        $tahun = (int) $data['periode_tahun'];
        $userId = (int) $request->user()->getKey();

        $relawans = Relawan::query()
            ->aktif()
            ->byDapur($profilId)
            ->orderBy('id')
            ->get();

        $created = 0;
        foreach ($relawans as $rel) {
            $exists = Penggajian::query()
                ->where('relawan_id', $rel->getKey())
                ->where('profil_mbg_id', $profilId)
                ->where('periode_id', PeriodeTenant::id())
                ->periode($bulan, $tahun)
                ->exists();

            if ($exists) {
                continue;
            }

            Penggajian::query()->create([
                'relawan_id' => $rel->getKey(),
                'profil_mbg_id' => $profilId,
                'periode_id' => PeriodeTenant::id(),
                'periode_bulan' => $bulan,
                'periode_tahun' => $tahun,
                'gaji_pokok' => $rel->gaji_pokok,
                'tunjangan_transport' => 0,
                'tunjangan_makan' => 0,
                'tunjangan_lainnya' => 0,
                'potongan' => 0,
                'keterangan_potongan' => null,
                'tanggal_bayar' => null,
                'status' => StatusPenggajian::Draft,
                'catatan' => null,
                'created_by' => $userId,
                'approved_by' => null,
            ]);
            $created++;
        }

        return redirect()
            ->route('penggajian.index', [
                'bulan' => $bulan,
                'tahun' => $tahun,
            ])
            ->with('success', "Generate selesai: {$created} data penggajian baru (relawan tanpa duplikat periode dilewati).");
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'relawan_id' => ['required', 'integer', 'exists:relawans,id'],
            'periode_bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'periode_tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $profilId = ProfilMbgTenant::id();
        $this->ensurePenggajianProfil($request, $profilId);
        $this->abortUnlessCanManageDraft($request);

        $rel = Relawan::query()->findOrFail((int) $data['relawan_id']);
        if ((int) $rel->profil_mbg_id !== $profilId) {
            abort(422, 'Relawan tidak berada di cabang MBG ini.');
        }

        $bulan = (int) $data['periode_bulan'];
        $tahun = (int) $data['periode_tahun'];

        $exists = Penggajian::query()
            ->where('relawan_id', $rel->getKey())
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', PeriodeTenant::id())
            ->periode($bulan, $tahun)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Penggajian untuk relawan dan periode ini sudah ada.');
        }

        Penggajian::query()->create([
            'relawan_id' => $rel->getKey(),
            'profil_mbg_id' => $profilId,
            'periode_id' => PeriodeTenant::id(),
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
            'gaji_pokok' => $rel->gaji_pokok,
            'tunjangan_transport' => 0,
            'tunjangan_makan' => 0,
            'tunjangan_lainnya' => 0,
            'potongan' => 0,
            'keterangan_potongan' => null,
            'tanggal_bayar' => null,
            'status' => StatusPenggajian::Draft,
            'catatan' => null,
            'created_by' => (int) $request->user()->getKey(),
            'approved_by' => null,
        ]);

        return redirect()
            ->route('penggajian.index', ['bulan' => $bulan, 'tahun' => $tahun])
            ->with('success', 'Penggajian untuk satu relawan berhasil ditambahkan.');
    }

    public function show(Request $request, Penggajian $penggajian): View
    {
        $this->authorizePenggajianRecord($request, $penggajian);
        $penggajian->load(['relawan.posisiRelawan', 'profilMbg', 'creator', 'approver']);

        return view('penggajian.show', compact('penggajian'));
    }

    public function edit(Request $request, Penggajian $penggajian): View
    {
        $this->authorizePenggajianRecord($request, $penggajian);
        $this->abortUnlessDraft($penggajian);
        $this->abortUnlessCanManageDraft($request);

        $penggajian->load(['relawan.posisiRelawan', 'profilMbg']);

        return view('penggajian.edit', compact('penggajian'));
    }

    public function update(Request $request, Penggajian $penggajian): RedirectResponse
    {
        $this->authorizePenggajianRecord($request, $penggajian);
        $this->abortUnlessDraft($penggajian);
        $this->abortUnlessCanManageDraft($request);

        $validated = $request->validate([
            'tunjangan_transport' => ['required', 'numeric', 'min:0'],
            'tunjangan_makan' => ['required', 'numeric', 'min:0'],
            'tunjangan_lainnya' => ['required', 'numeric', 'min:0'],
            'potongan' => ['required', 'numeric', 'min:0'],
            'keterangan_potongan' => ['nullable', 'string', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        $penggajian->fill([
            'tunjangan_transport' => $validated['tunjangan_transport'],
            'tunjangan_makan' => $validated['tunjangan_makan'],
            'tunjangan_lainnya' => $validated['tunjangan_lainnya'],
            'potongan' => $validated['potongan'],
            'keterangan_potongan' => $validated['keterangan_potongan'] ?? null,
            'catatan' => $validated['catatan'] ?? null,
        ]);
        $penggajian->save();

        return redirect()
            ->route('penggajian.show', $penggajian)
            ->with('success', 'Komponen gaji diperbarui; total dihitung ulang otomatis.');
    }

    public function approve(Request $request, Penggajian $penggajian): RedirectResponse
    {
        $this->authorizePenggajianRecord($request, $penggajian);
        abort_unless($request->user()?->hasAnyRole(['admin_pusat', 'super_admin']), 403);

        if ($penggajian->status !== StatusPenggajian::Draft) {
            return back()->with('error', 'Hanya penggajian berstatus draft yang dapat disetujui.');
        }

        $penggajian->update([
            'status' => StatusPenggajian::Approved,
            'approved_by' => (int) $request->user()->getKey(),
        ]);

        return back()->with('success', 'Penggajian disetujui.');
    }

    public function bayar(Request $request, Penggajian $penggajian): RedirectResponse
    {
        $this->authorizePenggajianRecord($request, $penggajian);
        abort_unless($request->user()?->hasRole('super_admin'), 403);

        $data = $request->validate([
            'tanggal_bayar' => ['required', 'date'],
        ]);

        if ($penggajian->status !== StatusPenggajian::Approved) {
            return back()->with('error', 'Hanya penggajian berstatus disetujui yang dapat ditandai dibayar.');
        }

        $penggajian->update([
            'status' => StatusPenggajian::Dibayar,
            'tanggal_bayar' => $data['tanggal_bayar'],
        ]);

        return back()->with('success', 'Status pembayaran diperbarui.');
    }

    public function destroy(Request $request, Penggajian $penggajian): RedirectResponse
    {
        $this->authorizePenggajianRecord($request, $penggajian);
        $this->abortUnlessCanManageDraft($request);

        if ($penggajian->status !== StatusPenggajian::Draft) {
            return back()->with('error', 'Hanya penggajian draft yang dapat dihapus.');
        }

        $bulan = $penggajian->periode_bulan;
        $tahun = $penggajian->periode_tahun;

        $penggajian->delete();

        return redirect()
            ->route('penggajian.index', ['bulan' => $bulan, 'tahun' => $tahun])
            ->with('success', 'Data penggajian draft dihapus.');
    }

    public function cetakSlip(Request $request, Penggajian $penggajian): mixed
    {
        $this->authorizePenggajianRecord($request, $penggajian);
        $penggajian->load(['relawan.posisiRelawan', 'profilMbg']);

        $logoDataUri = $this->logoDataUriForProfil($penggajian->profilMbg);

        $pdf = Pdf::loadView('penggajian.slip-pdf', [
            'p' => $penggajian,
            'logoDataUri' => $logoDataUri,
        ])->setPaper('a4', 'portrait');

        $slug = $penggajian->relawan?->nik ?: 'id-'.$penggajian->getKey();

        return $pdf->stream('slip-gaji-'.$slug.'-'.$penggajian->periode_bulan.'-'.$penggajian->periode_tahun.'.pdf');
    }

    public function cetakRekap(Request $request): mixed
    {
        $profilId = $this->profilMbgIdForPenggajianOrFirst($request);
        $this->ensurePenggajianProfil($request, $profilId);

        $bulan = $request->integer('bulan') ?: now()->month;
        $tahun = $request->integer('tahun') ?: now()->year;

        $q = Penggajian::query()
            ->with(['relawan.posisiRelawan', 'profilMbg'])
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', PeriodeTenant::id())
            ->periode($bulan, $tahun)
            ->orderBy('relawan_id');

        if ($this->isAdminDapurOnly($request)) {
            $q->where('status', StatusPenggajian::Draft);
        }

        $rows = $q->get();

        $profil = $rows->first()?->profilMbg ?? ProfilMbg::query()->findOrFail($profilId);
        $logoDataUri = $this->logoDataUriForProfil($profil);

        $totalKeseluruhan = (float) $rows->sum('total_gaji');

        $periodeLabel = (new Penggajian)->forceFill([
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
        ])->periode_label;

        $pdf = Pdf::loadView('penggajian.rekap-pdf', [
            'rows' => $rows,
            'profil' => $profil,
            'periodeLabel' => $periodeLabel,
            'totalKeseluruhan' => $totalKeseluruhan,
            'logoDataUri' => $logoDataUri,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('rekap-penggajian-'.$profil->kode_dapur.'-'.$bulan.'-'.$tahun.'.pdf');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $profilId = $this->profilMbgIdForPenggajianOrFirst($request);
        $this->ensurePenggajianProfil($request, $profilId);

        $bulan = $request->integer('bulan') ?: now()->month;
        $tahun = $request->integer('tahun') ?: now()->year;
        $statusFilter = $request->string('status')->toString();

        $query = Penggajian::query()
            ->with(['relawan.posisiRelawan', 'profilMbg'])
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', PeriodeTenant::id())
            ->periode($bulan, $tahun)
            ->orderBy('relawan_id');

        if ($this->isAdminDapurOnly($request)) {
            $query->where('status', StatusPenggajian::Draft);
        } elseif ($statusFilter !== '' && in_array($statusFilter, ['draft', 'approved', 'dibayar'], true)) {
            $query->where('status', $statusFilter);
        }

        $rows = $query->get();
        $filename = 'penggajian-'.$profilId.'-'.$bulan.'-'.$tahun.'.xlsx';

        return Excel::download(new PenggajianExport($rows), $filename);
    }

    private function isAdminDapurOnly(Request $request): bool
    {
        $u = $request->user();
        if (! $u) {
            return false;
        }

        return $u->hasRole('admin') && ! $u->hasAnyRole(['super_admin', 'admin_pusat']);
    }

    private function abortUnlessCanManageDraft(Request $request): void
    {
        abort_unless(
            $request->user()?->hasAnyRole(['super_admin', 'admin_pusat', 'admin']),
            403
        );
    }

    private function authorizePenggajianRecord(Request $request, Penggajian $penggajian): void
    {
        $this->ensurePenggajianProfil($request, (int) $penggajian->profil_mbg_id);

        if ((int) $penggajian->periode_id !== PeriodeTenant::id()) {
            abort(404);
        }

        if ($this->isAdminDapurOnly($request) && $penggajian->status !== StatusPenggajian::Draft) {
            abort(403, 'Anda hanya dapat mengakses penggajian berstatus draft.');
        }
    }

    private function abortUnlessDraft(Penggajian $penggajian): void
    {
        abort_unless($penggajian->status === StatusPenggajian::Draft, 403, 'Hanya penggajian draft yang dapat diubah.');
    }

    private function logoDataUriForProfil(?ProfilMbg $profil): ?string
    {
        if (! $profil || ! $profil->logo) {
            return null;
        }

        $path = Storage::disk('public')->path('logo-mbg/'.$profil->logo);
        if (! is_file($path)) {
            return null;
        }

        $bin = @file_get_contents($path);
        if ($bin === false) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        return 'data:'.$mime.';base64,'.base64_encode($bin);
    }
}
