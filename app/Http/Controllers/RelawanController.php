<?php

namespace App\Http\Controllers;

use App\Exports\RelawanExport;
use App\Http\Requests\StoreRelawanRequest;
use App\Http\Requests\UpdateRelawanRequest;
use App\Models\PosisiRelawan;
use App\Models\Relawan;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class RelawanController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeRelawanModule();

        $posisis = PosisiRelawan::query()->orderBy('nama_posisi')->get();

        return view('relawan.index', compact('posisis'));
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorizeRelawanModule();

        $query = $this->filteredRelawanQuery($request)
            ->select('relawans.*')
            ->with(['posisiRelawan', 'profilMbg'])
            ->orderByDesc('relawans.id');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('foto_thumb', function (Relawan $relawan) {
                $url = $relawan->foto_url;
                if (! $url) {
                    return '<span class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-[11px] font-bold text-white" style="background:#4a9b7a;">R</span>';
                }

                return '<img src="'.e($url).'" alt="" class="h-10 w-10 rounded-lg border object-cover" style="border-color:#d4e8f4;">';
            })
            ->addColumn('posisi_label', fn (Relawan $r) => e($r->posisiRelawan?->nama_posisi ?? '—'))
            ->addColumn('dapur_label', fn (Relawan $r) => e($r->profilMbg?->nama_dapur ?? '—'))
            ->addColumn('gaji_label', fn (Relawan $r) => formatRupiah($r->gaji_pokok))
            ->addColumn('gaji_harian_label', fn (Relawan $r) => formatRupiah($r->gaji_per_hari))
            ->addColumn('status_badge', function (Relawan $r) {
                return match ($r->status) {
                    'aktif' => '<span class="rounded-full px-3 py-1 text-[11px] font-semibold" style="background:#d4f0e8;color:#2d7a60;">Aktif</span>',
                    'cuti' => '<span class="rounded-full px-3 py-1 text-[11px] font-semibold" style="background:#fff3cd;color:#856404;">Cuti</span>',
                    default => '<span class="rounded-full px-3 py-1 text-[11px] font-semibold" style="background:#fde8e8;color:#c0392b;">Nonaktif</span>',
                };
            })
            ->addColumn('aksi', function (Relawan $relawan) {
                $detail = '<a href="'.e(route('master.relawan.show', $relawan)).'" class="text-xs font-semibold" style="color:#1a4a6b;">Profil</a>';
                $edit = '<a href="'.e(route('master.relawan.edit', $relawan)).'" class="ml-3 text-xs font-semibold" style="color:#4a9b7a;">Ubah</a>';
                $hapus = '<form method="POST" action="'.e(route('master.relawan.destroy', $relawan)).'" class="ml-3 inline form-hapus-relawan">'
                    .csrf_field()
                    .method_field('DELETE')
                    .'<button type="submit" class="text-xs font-semibold" style="color:#c0392b;">Hapus</button>'
                    .'</form>';

                return '<div class="flex flex-wrap items-center justify-end gap-y-1">'.$detail.$edit.$hapus.'</div>';
            })
            ->rawColumns(['foto_thumb', 'status_badge', 'aksi'])
            ->toJson();
    }

    public function create(): View
    {
        $this->authorizeRelawanWrite();

        $relawan = new Relawan;
        $posisis = PosisiRelawan::query()->orderBy('nama_posisi')->get();

        return view('relawan.create', compact('relawan', 'posisis'));
    }

    public function store(StoreRelawanRequest $request): RedirectResponse
    {
        $this->authorizeRelawanWrite();

        $data = $request->validated();
        unset($data['foto'], $data['crop_x'], $data['crop_y'], $data['crop_w'], $data['crop_h']);

        if (! $request->user()?->hasRole('super_admin')) {
            $data['gaji_pokok'] = 0;
            $data['gaji_per_hari'] = 0;
        }

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->simpanFotoRelawan($request->file('foto'), $request);
        }

        Relawan::query()->create($data);

        return redirect()
            ->route('master.relawan.index')
            ->with('success', 'Relawan berhasil ditambahkan.');
    }

    public function show(Relawan $relawan): View
    {
        $this->authorizeRelawanModule();
        $this->ensureRelawanInScope($relawan);

        $relawan->load(['posisiRelawan', 'profilMbg']);
        $riwayatGaji = $relawan->penggajian()
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->limit(6)
            ->get();

        return view('relawan.show', compact('relawan', 'riwayatGaji'));
    }

    public function edit(Relawan $relawan): View
    {
        $this->authorizeRelawanWrite();
        $this->ensureRelawanInScope($relawan);

        $posisis = PosisiRelawan::query()->orderBy('nama_posisi')->get();

        return view('relawan.edit', compact('relawan', 'posisis'));
    }

    public function update(UpdateRelawanRequest $request, Relawan $relawan): RedirectResponse
    {
        $this->authorizeRelawanWrite();
        $this->ensureRelawanInScope($relawan);

        $data = $request->validated();
        unset($data['foto'], $data['crop_x'], $data['crop_y'], $data['crop_w'], $data['crop_h']);

        if (! $request->user()?->hasRole('super_admin')) {
            unset($data['gaji_pokok'], $data['gaji_per_hari']);
        }

        if ($request->hasFile('foto')) {
            if ($relawan->foto) {
                Storage::disk('public')->delete('foto-relawan/'.$relawan->foto);
            }
            $data['foto'] = $this->simpanFotoRelawan($request->file('foto'), $request);
        }

        $relawan->update($data);

        return redirect()
            ->route('master.relawan.index')
            ->with('success', 'Data relawan berhasil diperbarui.');
    }

    public function destroy(Relawan $relawan): RedirectResponse
    {
        $this->authorizeRelawanWrite();
        $this->ensureRelawanInScope($relawan);

        $relawan->delete();

        return redirect()
            ->route('master.relawan.index')
            ->with('success', 'Relawan berhasil dihapus (soft delete).');
    }

    public function exportExcel(Request $request)
    {
        $this->authorizeRelawanModule();

        $query = $this->filteredRelawanQuery($request)
            ->with(['posisiRelawan', 'profilMbg'])
            ->orderBy('relawans.nama_lengkap');

        $filename = 'relawan_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new RelawanExport($query), $filename);
    }

    public function cetakKartu(Relawan $relawan)
    {
        $this->authorizeRelawanModule();
        $this->ensureRelawanInScope($relawan);

        $relawan->load(['posisiRelawan', 'profilMbg']);

        $fotoDataUri = null;
        if ($relawan->foto && Storage::disk('public')->exists('foto-relawan/'.$relawan->foto)) {
            $bin = Storage::disk('public')->get('foto-relawan/'.$relawan->foto);
            $ext = strtolower(pathinfo($relawan->foto, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };
            $fotoDataUri = 'data:'.$mime.';base64,'.base64_encode($bin);
        }

        $qr = (new Builder)->build(
            writer: new PngWriter,
            data: 'MBG-REL|ID:'.$relawan->getKey().'|NIK:'.$relawan->nik,
            size: 160,
            margin: 2,
        );

        $pdf = Pdf::loadView('relawan.kartu-pdf', [
            'relawan' => $relawan,
            'fotoDataUri' => $fotoDataUri,
            'qrDataUri' => $qr->getDataUri(),
        ])->setPaper([0, 0, 320, 204], 'landscape');

        return $pdf->stream('kartu-relawan-'.$relawan->nik.'.pdf');
    }

    private function filteredRelawanQuery(Request $request): EloquentBuilder
    {
        $q = Relawan::query();

        $scoped = $request->attributes->get('scoped_profil_mbg_id');
        if ($scoped) {
            $q->where('relawans.profil_mbg_id', $scoped);
        }

        if ($request->filled('posisi_relawan_id')) {
            $q->where('relawans.posisi_relawan_id', $request->integer('posisi_relawan_id'));
        }

        if ($request->filled('status') && in_array($request->string('status')->toString(), ['aktif', 'nonaktif', 'cuti'], true)) {
            $q->where('relawans.status', $request->string('status'));
        }

        return $q;
    }

    private function simpanFotoRelawan(UploadedFile $file, Request $request): string
    {
        $manager = app(ImageManager::class);
        $image = $manager->read($file->getRealPath());

        if (
            $request->filled('crop_w')
            && $request->filled('crop_h')
            && $request->filled('crop_x')
            && $request->filled('crop_y')
        ) {
            $image->crop(
                max(1, (int) $request->input('crop_w')),
                max(1, (int) $request->input('crop_h')),
                max(0, (int) $request->input('crop_x')),
                max(0, (int) $request->input('crop_y')),
            );
        }

        $filename = uniqid('rv_', true).'.jpg';
        Storage::disk('public')->put(
            'foto-relawan/'.$filename,
            (string) $image->toJpeg(85)
        );

        return $filename;
    }

    private function authorizeRelawanModule(): void
    {
        if (! auth()->user()?->hasAnyRole(['super_admin', 'admin_pusat', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses ke data relawan.');
        }
    }

    private function authorizeRelawanWrite(): void
    {
        $this->authorizeRelawanModule();
    }

    private function ensureRelawanInScope(Relawan $relawan): void
    {
        $scoped = request()->attributes->get('scoped_profil_mbg_id');
        if ($scoped && (int) $relawan->profil_mbg_id !== (int) $scoped) {
            abort(403, 'Relawan ini berada di luar dapur Anda.');
        }
    }
}
