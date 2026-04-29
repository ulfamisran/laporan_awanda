<?php

namespace App\Http\Controllers;

use App\Enums\StatusPenggajian;
use App\Exports\PenggajianExport;
use App\Http\Controllers\Concerns\ManagesPenggajianProfil;
use App\Models\AkunDana;
use App\Models\DanaKeluar;
use App\Models\Penggajian;
use App\Models\ProfilMbg;
use App\Models\Relawan;
use App\Support\KodeTransaksiKeuangan;
use App\Support\PeriodeTenant;
use App\Support\ProfilMbgTenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        $statusFilter = $request->string('status')->toString();

        $query = Penggajian::query()
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', PeriodeTenant::id())
            ->orderByDesc('periode_mulai')
            ->orderByDesc('periode_selesai')
            ->orderBy('metode_penggajian');

        if ($this->isAdminDapurOnly($request)) {
            $query->where('status', StatusPenggajian::Draft);
        } elseif ($statusFilter !== '' && in_array($statusFilter, ['draft', 'approved', 'dibayar'], true)) {
            $query->where('status', $statusFilter);
        }

        $rows = $query->get();
        $batches = $rows
            ->groupBy(fn (Penggajian $row) => implode('|', [
                optional($row->periode_mulai)->toDateString(),
                optional($row->periode_selesai)->toDateString(),
                $row->metode_penggajian ?: 'gaji_pokok',
            ]))
            ->map(function ($group) {
                $first = $group->first();
                $mulai = optional($first->periode_mulai)->toDateString();
                $selesai = optional($first->periode_selesai)->toDateString();
                $bulan = (int) ($first->periode_bulan ?? 0);
                $tahun = (int) ($first->periode_tahun ?? 0);
                if (($mulai === null || $mulai === '' || $selesai === null || $selesai === '') && $bulan > 0 && $tahun > 0) {
                    $anchor = Carbon::create($tahun, $bulan, 1);
                    $mulai = $mulai ?: $anchor->copy()->startOfMonth()->toDateString();
                    $selesai = $selesai ?: $anchor->copy()->endOfMonth()->toDateString();
                }
                $statuses = $group
                    ->map(fn (Penggajian $row) => (string) ($row->status?->value ?? $row->status))
                    ->unique()
                    ->values();
                $status = $statuses->count() === 1 ? (string) $statuses->first() : 'campuran';

                return [
                    'periode_mulai' => $mulai,
                    'periode_selesai' => $selesai,
                    'periode_label' => $first->periode_label,
                    'metode_penggajian' => $first->metode_penggajian ?: 'gaji_pokok',
                    'status' => $status,
                    'total_karyawan' => $group->count(),
                    'total_pembayaran' => (float) $group->sum('total_gaji'),
                ];
            })
            ->sortByDesc(fn (array $batch) => ($batch['periode_mulai'] ?? '').'|'.($batch['metode_penggajian'] ?? ''))
            ->values();

        return view('penggajian.index', [
            'profilId' => $profilId,
            'statusFilter' => $statusFilter,
            'batches' => $batches,
        ]);
    }

    public function batchDetail(Request $request): View
    {
        $profilId = $this->profilMbgIdForPenggajianOrFirst($request);
        $mulai = $request->string('mulai')->toString();
        $selesai = $request->string('selesai')->toString();
        $metode = $this->normalizeMetodePenggajian($request->string('metode')->toString());
        abort_if($mulai === '' || $selesai === '', 404);

        $rows = Penggajian::query()
            ->with(['relawan.posisiRelawan', 'profilMbg'])
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', PeriodeTenant::id())
            ->whereDate('periode_mulai', $mulai)
            ->whereDate('periode_selesai', $selesai)
            ->where('metode_penggajian', $metode)
            ->orderBy('relawan_id')
            ->get();

        $this->ensurePenggajianProfil($request, $profilId);

        return view('penggajian.batch-detail', [
            'rows' => $rows,
            'mulai' => $mulai,
            'selesai' => $selesai,
            'metode' => $metode,
            'periodeLabel' => $rows->first()?->periode_label ?? $mulai.' - '.$selesai,
            'totalPembayaran' => (float) $rows->sum('total_gaji'),
        ]);
    }

    public function batchStatus(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mulai' => ['required', 'date'],
            'selesai' => ['required', 'date', 'after_or_equal:mulai'],
            'metode' => ['required', 'string', 'in:gaji_pokok,kehadiran'],
            'aksi' => ['required', 'string', 'in:approve,bayar'],
            'tanggal_bayar' => ['nullable', 'date'],
        ]);

        $profilId = ProfilMbgTenant::id();
        $this->ensurePenggajianProfil($request, $profilId);

        $query = Penggajian::query()
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', PeriodeTenant::id())
            ->whereDate('periode_mulai', $data['mulai'])
            ->whereDate('periode_selesai', $data['selesai'])
            ->where('metode_penggajian', $data['metode']);

        if ($data['aksi'] === 'approve') {
            abort_unless($request->user()?->hasAnyRole(['admin_pusat', 'super_admin']), 403);
            $updated = (clone $query)
                ->where('status', StatusPenggajian::Draft)
                ->update([
                    'status' => StatusPenggajian::Approved->value,
                    'approved_by' => (int) $request->user()->getKey(),
                ]);

            return back()->with('success', "Status penggajian batch berhasil disetujui ({$updated} relawan).");
        }

        abort_unless($request->user()?->hasRole('super_admin'), 403);
        $tanggalBayar = $data['tanggal_bayar'] ?? now()->toDateString();
        $updated = 0;
        $totalDibayar = 0.0;
        DB::transaction(function () use ($query, $tanggalBayar, &$updated, &$totalDibayar, $request, $data): void {
            $approvedRows = (clone $query)
                ->where('status', StatusPenggajian::Approved)
                ->get(['id', 'total_gaji', 'periode_id', 'profil_mbg_id']);

            $updated = $approvedRows->count();
            $totalDibayar = (float) $approvedRows->sum('total_gaji');
            if ($updated === 0) {
                return;
            }

            Penggajian::query()
                ->whereIn('id', $approvedRows->pluck('id'))
                ->update([
                    'status' => StatusPenggajian::Dibayar->value,
                    'tanggal_bayar' => $tanggalBayar,
                ]);

            $first = $approvedRows->first();
            $periodeLabel = (new Penggajian)->forceFill([
                'periode_mulai' => $data['mulai'],
                'periode_selesai' => $data['selesai'],
            ])->periode_label;
            $metodeLabel = $data['metode'] === 'kehadiran' ? 'kehadiran' : 'gaji pokok';

            $this->catatDanaKeluarPenggajian(
                (int) $first->profil_mbg_id,
                (int) $first->periode_id,
                $tanggalBayar,
                $totalDibayar,
                'Pembayaran batch penggajian '.$periodeLabel.' (metode '.$metodeLabel.')',
                (int) $request->user()->getKey()
            );
        });

        return back()->with('success', "Status penggajian batch ditandai dibayar ({$updated} relawan).");
    }

    public function cetakKwitansiBatch(Request $request): mixed
    {
        $profilId = $this->profilMbgIdForPenggajianOrFirst($request);
        $this->ensurePenggajianProfil($request, $profilId);

        $data = $request->validate([
            'mulai' => ['required', 'date'],
            'selesai' => ['required', 'date', 'after_or_equal:mulai'],
            'metode' => ['required', 'string', 'in:gaji_pokok,kehadiran'],
        ]);

        $rows = Penggajian::query()
            ->with(['relawan.posisiRelawan', 'profilMbg'])
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', PeriodeTenant::id())
            ->whereDate('periode_mulai', $data['mulai'])
            ->whereDate('periode_selesai', $data['selesai'])
            ->where('metode_penggajian', $data['metode'])
            ->orderBy('relawan_id')
            ->get();

        $profil = $rows->first()?->profilMbg ?? ProfilMbg::query()->findOrFail($profilId);
        $logoDataUri = $this->logoDataUriForProfil($profil);

        $pdf = Pdf::loadView('penggajian.kwitansi-batch-pdf', [
            'rows' => $rows,
            'logoDataUri' => $logoDataUri,
            'profil' => $profil,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('kwitansi-batch-'.$data['mulai'].'-'.$data['selesai'].'-'.$data['metode'].'.pdf');
    }

    public function create(Request $request): View
    {
        $profilId = $this->profilMbgIdForPenggajianOrFirst($request);

        $mulai = $request->date('mulai')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $selesai = $request->date('selesai')?->toDateString() ?? now()->endOfMonth()->toDateString();
        $metode = $this->normalizeMetodePenggajian($request->string('metode_penggajian')->toString());
        $statusCreate = $this->normalizeStatusCreate($request->string('status_create')->toString());
        $tanggalBayarCreate = $request->date('tanggal_bayar_create')?->toDateString() ?? now()->toDateString();
        $defaultJumlahHadir = $this->hitungHariPeriode($mulai, $selesai);

        $previewRelawans = collect();
        $existingRelawanIds = collect();

        if ($request->boolean('preview')) {
            $request->validate([
                'mulai' => ['required', 'date'],
                'selesai' => ['required', 'date', 'after_or_equal:mulai'],
            ]);
            $mulaiCarbon = Carbon::parse($mulai);

            $previewRelawans = Relawan::query()
                ->aktif()
                ->byDapur($profilId)
                ->with('posisiRelawan')
                ->orderBy('nama_lengkap')
                ->get();

            $existingRelawanIds = Penggajian::query()
                ->where('profil_mbg_id', $profilId)
                ->where('periode_id', PeriodeTenant::id())
                ->where('periode_bulan', (int) $mulaiCarbon->month)
                ->where('periode_tahun', (int) $mulaiCarbon->year)
                ->pluck('relawan_id');
        }

        return view('penggajian.create', compact(
            'profilId',
            'mulai',
            'selesai',
            'metode',
            'statusCreate',
            'tanggalBayarCreate',
            'defaultJumlahHadir',
            'previewRelawans',
            'existingRelawanIds',
        ));
    }

    public function generateBulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'periode_mulai' => ['required', 'date'],
            'periode_selesai' => ['required', 'date', 'after_or_equal:periode_mulai'],
            'metode_penggajian' => ['required', 'string', 'in:gaji_pokok,kehadiran'],
            'status_create' => ['nullable', 'string', 'in:draft,approved,dibayar'],
            'tanggal_bayar_create' => ['nullable', 'date'],
            'jumlah_hadir' => ['nullable', 'array'],
            'jumlah_hadir.*' => ['nullable', 'integer', 'min:0', 'max:31'],
        ]);

        $profilId = ProfilMbgTenant::id();
        $this->ensurePenggajianProfil($request, $profilId);
        $this->abortUnlessCanManageDraft($request);

        $mulai = Carbon::parse($data['periode_mulai']);
        $selesai = Carbon::parse($data['periode_selesai']);
        $metode = $this->normalizeMetodePenggajian((string) $data['metode_penggajian']);
        $statusCreate = $this->normalizeStatusCreate((string) ($data['status_create'] ?? 'draft'));
        $tanggalBayarCreate = $statusCreate === 'dibayar'
            ? Carbon::parse($data['tanggal_bayar_create'] ?? now()->toDateString())->toDateString()
            : null;
        if ($statusCreate === 'dibayar') {
            abort_unless($request->user()?->hasRole('super_admin'), 403);
        }
        $defaultJumlahHadir = $this->hitungHariPeriode($mulai->toDateString(), $selesai->toDateString());
        $userId = (int) $request->user()->getKey();

        $relawans = Relawan::query()
            ->aktif()
            ->byDapur($profilId)
            ->orderBy('id')
            ->get();

        $created = 0;
        $totalDibayar = 0.0;
        foreach ($relawans as $rel) {
            $jumlahHadir = (int) ($data['jumlah_hadir'][$rel->getKey()] ?? $defaultJumlahHadir);
            $exists = Penggajian::query()
                ->where('relawan_id', $rel->getKey())
                ->where('profil_mbg_id', $profilId)
                ->where('periode_id', PeriodeTenant::id())
                ->where('periode_bulan', (int) $mulai->month)
                ->where('periode_tahun', (int) $mulai->year)
                ->exists();

            if ($exists) {
                continue;
            }

            Penggajian::query()->create([
                'relawan_id' => $rel->getKey(),
                'profil_mbg_id' => $profilId,
                'periode_id' => PeriodeTenant::id(),
                'periode_bulan' => (int) $mulai->month,
                'periode_tahun' => (int) $mulai->year,
                'periode_mulai' => $mulai->toDateString(),
                'periode_selesai' => $selesai->toDateString(),
                'metode_penggajian' => $metode,
                'jumlah_hadir' => $jumlahHadir,
                'gaji_pokok' => $this->hitungGajiPokokPeriode($rel, $metode, $jumlahHadir),
                'tunjangan_transport' => 0,
                'tunjangan_makan' => 0,
                'tunjangan_lainnya' => 0,
                'potongan' => 0,
                'keterangan_potongan' => null,
                'tanggal_bayar' => $tanggalBayarCreate,
                'status' => $statusCreate,
                'catatan' => null,
                'created_by' => $userId,
                'approved_by' => $statusCreate !== 'draft' ? $userId : null,
            ]);
            if ($statusCreate === 'dibayar') {
                $totalDibayar += $this->hitungGajiPokokPeriode($rel, $metode, $jumlahHadir);
            }
            $created++;
        }

        if ($statusCreate === 'dibayar' && $created > 0 && $totalDibayar > 0) {
            $periodeLabel = (new Penggajian)->forceFill([
                'periode_mulai' => $mulai->toDateString(),
                'periode_selesai' => $selesai->toDateString(),
            ])->periode_label;
            $metodeLabel = $metode === 'kehadiran' ? 'kehadiran' : 'gaji pokok';
            $this->catatDanaKeluarPenggajian(
                $profilId,
                PeriodeTenant::id(),
                (string) $tanggalBayarCreate,
                $totalDibayar,
                'Pembayaran batch penggajian '.$periodeLabel.' (metode '.$metodeLabel.')',
                $userId
            );
        }

        return redirect()
            ->route('penggajian.index', [
                'mulai' => $mulai->toDateString(),
                'selesai' => $selesai->toDateString(),
            ])
            ->with('success', "Generate selesai: {$created} data penggajian baru (relawan tanpa duplikat periode dilewati).");
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'relawan_id' => ['required', 'integer', 'exists:relawans,id'],
            'periode_mulai' => ['required', 'date'],
            'periode_selesai' => ['required', 'date', 'after_or_equal:periode_mulai'],
            'metode_penggajian' => ['required', 'string', 'in:gaji_pokok,kehadiran'],
            'status_create' => ['nullable', 'string', 'in:draft,approved,dibayar'],
            'tanggal_bayar_create' => ['nullable', 'date'],
            'jumlah_hadir' => ['nullable', 'integer', 'min:0', 'max:31'],
        ]);

        $profilId = ProfilMbgTenant::id();
        $this->ensurePenggajianProfil($request, $profilId);
        $this->abortUnlessCanManageDraft($request);

        $rel = Relawan::query()->findOrFail((int) $data['relawan_id']);
        if ((int) $rel->profil_mbg_id !== $profilId) {
            abort(422, 'Relawan tidak berada di cabang MBG ini.');
        }

        $mulai = Carbon::parse($data['periode_mulai']);
        $selesai = Carbon::parse($data['periode_selesai']);
        $metode = $this->normalizeMetodePenggajian((string) $data['metode_penggajian']);
        $statusCreate = $this->normalizeStatusCreate((string) ($data['status_create'] ?? 'draft'));
        $tanggalBayarCreate = $statusCreate === 'dibayar'
            ? Carbon::parse($data['tanggal_bayar_create'] ?? now()->toDateString())->toDateString()
            : null;
        if ($statusCreate === 'dibayar') {
            abort_unless($request->user()?->hasRole('super_admin'), 403);
        }
        $jumlahHadir = $metode === 'kehadiran'
            ? (int) $data['jumlah_hadir']
            : $this->hitungHariPeriode($mulai->toDateString(), $selesai->toDateString());

        $exists = Penggajian::query()
            ->where('relawan_id', $rel->getKey())
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', PeriodeTenant::id())
            ->where('periode_bulan', (int) $mulai->month)
            ->where('periode_tahun', (int) $mulai->year)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Penggajian untuk relawan dan periode ini sudah ada.');
        }

        $created = Penggajian::query()->create([
            'relawan_id' => $rel->getKey(),
            'profil_mbg_id' => $profilId,
            'periode_id' => PeriodeTenant::id(),
            'periode_bulan' => (int) $mulai->month,
            'periode_tahun' => (int) $mulai->year,
            'periode_mulai' => $mulai->toDateString(),
            'periode_selesai' => $selesai->toDateString(),
            'metode_penggajian' => $metode,
            'jumlah_hadir' => $jumlahHadir,
            'gaji_pokok' => $this->hitungGajiPokokPeriode($rel, $metode, $jumlahHadir),
            'tunjangan_transport' => 0,
            'tunjangan_makan' => 0,
            'tunjangan_lainnya' => 0,
            'potongan' => 0,
            'keterangan_potongan' => null,
            'tanggal_bayar' => $tanggalBayarCreate,
            'status' => $statusCreate,
            'catatan' => null,
            'created_by' => (int) $request->user()->getKey(),
            'approved_by' => $statusCreate !== 'draft' ? (int) $request->user()->getKey() : null,
        ]);

        if ($statusCreate === 'dibayar') {
            $this->catatDanaKeluarPenggajian(
                $profilId,
                PeriodeTenant::id(),
                (string) $tanggalBayarCreate,
                (float) $created->total_gaji,
                'Pembayaran penggajian relawan '.$created->periode_label,
                (int) $request->user()->getKey()
            );
        }

        return redirect()
            ->route('penggajian.index', ['mulai' => $mulai->toDateString(), 'selesai' => $selesai->toDateString()])
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
            'metode_penggajian' => ['required', 'string', 'in:gaji_pokok,kehadiran'],
            'jumlah_hadir' => ['nullable', 'integer', 'min:0', 'max:31'],
            'tunjangan_transport' => ['required', 'numeric', 'min:0'],
            'tunjangan_makan' => ['required', 'numeric', 'min:0'],
            'tunjangan_lainnya' => ['required', 'numeric', 'min:0'],
            'potongan' => ['required', 'numeric', 'min:0'],
            'keterangan_potongan' => ['nullable', 'string', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        $metode = $this->normalizeMetodePenggajian((string) $validated['metode_penggajian']);
        $jumlahHadir = $metode === 'kehadiran'
            ? (int) $validated['jumlah_hadir']
            : $this->hitungHariPeriode(
                optional($penggajian->periode_mulai)->toDateString() ?? now()->toDateString(),
                optional($penggajian->periode_selesai)->toDateString() ?? now()->toDateString()
            );
        $penggajian->fill([
            'metode_penggajian' => $metode,
            'jumlah_hadir' => $jumlahHadir,
            'gaji_pokok' => $this->hitungGajiPokokPeriode($penggajian->relawan, $metode, $jumlahHadir),
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

        DB::transaction(function () use ($penggajian, $data, $request): void {
            $penggajian->update([
                'status' => StatusPenggajian::Dibayar,
                'tanggal_bayar' => $data['tanggal_bayar'],
            ]);

            $this->catatDanaKeluarPenggajian(
                (int) $penggajian->profil_mbg_id,
                (int) $penggajian->periode_id,
                (string) $data['tanggal_bayar'],
                (float) $penggajian->total_gaji,
                'Pembayaran penggajian relawan '.$penggajian->periode_label,
                (int) $request->user()->getKey()
            );
        });

        return back()->with('success', 'Status pembayaran diperbarui.');
    }

    public function destroy(Request $request, Penggajian $penggajian): RedirectResponse
    {
        $this->authorizePenggajianRecord($request, $penggajian);
        $this->abortUnlessCanManageDraft($request);

        if ($penggajian->status !== StatusPenggajian::Draft) {
            return back()->with('error', 'Hanya penggajian draft yang dapat dihapus.');
        }

        $mulai = optional($penggajian->periode_mulai)->toDateString();
        $selesai = optional($penggajian->periode_selesai)->toDateString();

        $penggajian->delete();

        return redirect()
            ->route('penggajian.index', ['mulai' => $mulai, 'selesai' => $selesai])
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

        return $pdf->stream('slip-gaji-'.$slug.'-'.optional($penggajian->periode_mulai)->format('Ymd').'.pdf');
    }

    public function cetakRekap(Request $request): mixed
    {
        $profilId = $this->profilMbgIdForPenggajianOrFirst($request);
        $this->ensurePenggajianProfil($request, $profilId);

        $mulai = $request->date('mulai')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $selesai = $request->date('selesai')?->toDateString() ?? now()->endOfMonth()->toDateString();

        $q = Penggajian::query()
            ->with(['relawan.posisiRelawan', 'profilMbg'])
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', PeriodeTenant::id())
            ->whereDate('periode_mulai', $mulai)
            ->whereDate('periode_selesai', $selesai)
            ->orderBy('relawan_id');

        if ($this->isAdminDapurOnly($request)) {
            $q->where('status', StatusPenggajian::Draft);
        }

        $rows = $q->get();

        $profil = $rows->first()?->profilMbg ?? ProfilMbg::query()->findOrFail($profilId);
        $logoDataUri = $this->logoDataUriForProfil($profil);

        $totalKeseluruhan = (float) $rows->sum('total_gaji');

        $periodeLabel = (new Penggajian)->forceFill([
            'periode_mulai' => $mulai,
            'periode_selesai' => $selesai,
        ])->periode_label;

        $pdf = Pdf::loadView('penggajian.rekap-pdf', [
            'rows' => $rows,
            'profil' => $profil,
            'periodeLabel' => $periodeLabel,
            'totalKeseluruhan' => $totalKeseluruhan,
            'logoDataUri' => $logoDataUri,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('rekap-penggajian-'.$profil->kode_dapur.'-'.$mulai.'-'.$selesai.'.pdf');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $profilId = $this->profilMbgIdForPenggajianOrFirst($request);
        $this->ensurePenggajianProfil($request, $profilId);

        $mulai = $request->date('mulai')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $selesai = $request->date('selesai')?->toDateString() ?? now()->endOfMonth()->toDateString();
        $statusFilter = $request->string('status')->toString();

        $query = Penggajian::query()
            ->with(['relawan.posisiRelawan', 'profilMbg'])
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', PeriodeTenant::id())
            ->whereDate('periode_mulai', $mulai)
            ->whereDate('periode_selesai', $selesai)
            ->orderBy('relawan_id');

        if ($this->isAdminDapurOnly($request)) {
            $query->where('status', StatusPenggajian::Draft);
        } elseif ($statusFilter !== '' && in_array($statusFilter, ['draft', 'approved', 'dibayar'], true)) {
            $query->where('status', $statusFilter);
        }

        $rows = $query->get();
        $filename = 'penggajian-'.$profilId.'-'.$mulai.'-'.$selesai.'.xlsx';

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

    private function hitungGajiPokokPeriode(?Relawan $relawan, string $metode, int $jumlahHadir): float
    {
        if (! $relawan) {
            return 0;
        }

        if ($metode === 'kehadiran') {
            return round((float) $relawan->gaji_per_hari * max(0, $jumlahHadir), 2);
        }

        return round((float) $relawan->gaji_pokok, 2);
    }

    private function normalizeMetodePenggajian(string $metode): string
    {
        return in_array($metode, ['gaji_pokok', 'kehadiran'], true) ? $metode : 'gaji_pokok';
    }

    private function normalizeStatusCreate(string $status): string
    {
        return in_array($status, ['draft', 'approved', 'dibayar'], true) ? $status : 'draft';
    }

    private function hitungHariPeriode(string $mulai, string $selesai): int
    {
        $start = Carbon::parse($mulai)->startOfDay();
        $end = Carbon::parse($selesai)->startOfDay();
        if ($end->lt($start)) {
            return 0;
        }

        return $start->diffInDays($end) + 1;
    }

    private function catatDanaKeluarPenggajian(
        int $profilId,
        int $periodeId,
        string $tanggal,
        float $jumlah,
        string $uraian,
        int $createdBy,
    ): void {
        if ($jumlah <= 0) {
            return;
        }

        $akunJenisDanaId = (int) (AkunDana::query()
            ->where('kode', '2130')
            ->where('is_grup', false)
            ->value('id') ?? 0);
        if ($akunJenisDanaId === 0) {
            abort(422, 'Akun 2130 Biaya Operasional belum tersedia.');
        }

        $akunKasId = (int) (AkunDana::query()
            ->where('kode', '1102')
            ->where('is_grup', false)
            ->value('id') ?? 0);
        if ($akunKasId === 0) {
            abort(422, 'Akun kas 1102 (Kas di Bank) belum tersedia.');
        }

        DanaKeluar::query()->create([
            'kode_transaksi' => KodeTransaksiKeuangan::generate('DK', 'dana_keluar'),
            'akun_jenis_dana_id' => $akunJenisDanaId,
            'akun_kas_id' => $akunKasId,
            'profil_mbg_id' => $profilId,
            'periode_id' => $periodeId,
            'tanggal' => $tanggal,
            'jumlah' => round($jumlah, 2),
            'nomor_bukti' => 'PGJ-'.now()->format('YmdHis'),
            'keperluan' => 'Pembayaran penggajian relawan',
            'keterangan' => null,
            'uraian_transaksi' => $uraian,
            'gambar_nota' => [],
            'created_by' => $createdBy,
        ]);
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
