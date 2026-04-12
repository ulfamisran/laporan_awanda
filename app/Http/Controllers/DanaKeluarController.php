<?php

namespace App\Http\Controllers;

use App\Models\AkunDana;
use App\Models\DanaKeluar;
use App\Support\KodeTransaksiKeuangan;
use App\Support\PeriodeTenant;
use App\Support\SaldoDana;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DanaKeluarController extends Controller
{
    use Concerns\ManagesKeuanganProfil;

    public function index(Request $request): View
    {
        $profilIdDefault = $this->profilMbgIdForKeuanganOrFirst($request);

        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $totalBulanIni = (float) DanaKeluar::query()
            ->where('profil_mbg_id', $profilIdDefault)
            ->where('periode_id', PeriodeTenant::id())
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->sum('jumlah');

        return view('dana-keluar.index', compact('profilIdDefault', 'totalBulanIni'));
    }

    public function data(Request $request): JsonResponse
    {
        $query = DanaKeluar::query()
            ->with(['akunJenisDana', 'akunKas', 'creator'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $query->where('profil_mbg_id', $this->profilMbgIdForKeuanganOrFirst($request));
        $query->where('periode_id', PeriodeTenant::id());

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('tanggal', fn (DanaKeluar $r) => $r->tanggal?->format('d/m/Y') ?? '')
            ->addColumn('jenis_dana_cell', function (DanaKeluar $r) {
                $a = $r->akunJenisDana;

                return e($a ? ($a->kode.' — '.$a->nama) : '—');
            })
            ->addColumn('kas_cell', function (DanaKeluar $r) {
                $a = $r->akunKas;

                return e($a ? ($a->kode.' — '.$a->nama) : '—');
            })
            ->editColumn('nomor_bukti', fn (DanaKeluar $r) => e((string) ($r->nomor_bukti ?? '')))
            ->editColumn('uraian_transaksi', fn (DanaKeluar $r) => e(Str::limit((string) ($r->uraian_transaksi ?? ''), 120)))
            ->addColumn('jumlah_cell', fn (DanaKeluar $r) => '<span class="font-mono">'.e(formatRupiah($r->jumlah)).'</span>')
            ->addColumn('aksi', function (DanaKeluar $r) {
                $show = '<a href="'.e(route('keuangan.keluar.show', $r)).'" class="text-xs font-semibold" style="color:#1a4a6b;">Detail</a>';
                $edit = '<a href="'.e(route('keuangan.keluar.edit', $r)).'" class="ml-3 text-xs font-semibold" style="color:#4a9b7a;">Ubah</a>';
                $hapus = '';
                if ($this->userCanDeleteKeuangan($r)) {
                    $hapus = '<form method="POST" action="'.e(route('keuangan.keluar.destroy', $r)).'" class="ml-3 inline form-hapus-keuangan">'
                        .csrf_field()
                        .method_field('DELETE')
                        .'<button type="submit" class="text-xs font-semibold" style="color:#c0392b;">Hapus</button>'
                        .'</form>';
                }

                return '<div class="flex flex-wrap items-center justify-end">'.$show.$edit.$hapus.'</div>';
            })
            ->rawColumns(['jumlah_cell', 'aksi'])
            ->toJson();
    }

    public function create(Request $request): View
    {
        $profilId = $this->profilMbgIdForKeuanganOrFirst($request);
        $akunJenisDana = AkunDana::daftarAkunBukuPembantuJenisDana();
        $akunKas = AkunDana::daftarAkunBukuPembantuKas();
        $previewKode = $this->previewNextKodeKeluar();
        $saldoSaatIni = SaldoDana::getSaldoDana($profilId);

        return view('dana-keluar.create', compact('profilId', 'akunJenisDana', 'akunKas', 'previewKode', 'saldoSaatIni'));
    }

    public function store(Request $request): RedirectResponse
    {
        $profilId = $this->profilMbgIdFromKeuanganForm($request);
        $data = $this->validatedKeluar($request);

        $this->assertSaldoCukupKeluar($profilId, (float) $data['jumlah'], null);

        $data['profil_mbg_id'] = $profilId;
        $data['periode_id'] = PeriodeTenant::id();
        $data['created_by'] = (int) $request->user()->getKey();
        $data['kode_transaksi'] = KodeTransaksiKeuangan::generate('DK', 'dana_keluar');
        $data['gambar_nota'] = $this->simpanNotaBatch($request);

        DanaKeluar::query()->create($data);

        return redirect()
            ->route('keuangan.keluar.index')
            ->with('success', 'Dana keluar berhasil disimpan.');
    }

    public function show(Request $request, DanaKeluar $keluar): View
    {
        $this->ensureKeuanganProfil($request, (int) $keluar->profil_mbg_id);
        $this->ensureKeuanganPeriode($keluar);
        $keluar->load(['akunJenisDana', 'akunKas', 'creator']);

        return view('dana-keluar.show', compact('keluar'));
    }

    public function buktiPdf(Request $request, DanaKeluar $keluar)
    {
        $this->ensureKeuanganProfil($request, (int) $keluar->profil_mbg_id);
        $this->ensureKeuanganPeriode($keluar);
        $keluar->load(['akunJenisDana', 'akunKas', 'creator']);

        $pdf = Pdf::loadView('keuangan.bukti-transaksi-pdf', ['trx' => $keluar, 'jenis' => 'keluar'])
            ->setPaper('a4', 'portrait');

        return $pdf->stream('bukti-'.$keluar->kode_transaksi.'.pdf');
    }

    public function edit(Request $request, DanaKeluar $keluar): View
    {
        $this->ensureKeuanganProfil($request, (int) $keluar->profil_mbg_id);
        $this->ensureKeuanganPeriode($keluar);
        $profilId = (int) $keluar->profil_mbg_id;
        $akunJenisDana = AkunDana::daftarAkunBukuPembantuJenisDana();
        $akunKas = AkunDana::daftarAkunBukuPembantuKas();
        $previewKode = $keluar->kode_transaksi;
        $saldoSaatIni = SaldoDana::getSaldoDana($profilId);

        return view('dana-keluar.edit', compact('keluar', 'profilId', 'akunJenisDana', 'akunKas', 'previewKode', 'saldoSaatIni'));
    }

    public function update(Request $request, DanaKeluar $keluar): RedirectResponse
    {
        $this->ensureKeuanganProfil($request, (int) $keluar->profil_mbg_id);
        $this->ensureKeuanganPeriode($keluar);

        $data = $this->validatedKeluar($request, isUpdate: true);

        $this->assertSaldoCukupKeluar((int) $keluar->profil_mbg_id, (float) $data['jumlah'], $keluar);

        $existing = $keluar->gambar_nota ?? [];
        $newFiles = $this->simpanNotaBatch($request);
        if ($newFiles !== []) {
            $data['gambar_nota'] = array_values(array_merge($existing, $newFiles));
        } else {
            $data['gambar_nota'] = $existing;
        }

        $keluar->update($data);

        return redirect()
            ->route('keuangan.keluar.index')
            ->with('success', 'Dana keluar diperbarui.');
    }

    public function destroy(Request $request, DanaKeluar $keluar): RedirectResponse
    {
        $this->ensureKeuanganProfil($request, (int) $keluar->profil_mbg_id);
        $this->ensureKeuanganPeriode($keluar);
        if (! $this->userCanDeleteKeuangan($keluar)) {
            abort(403);
        }

        foreach ($keluar->gambar_nota ?? [] as $rel) {
            if (is_string($rel) && Storage::disk('public')->exists($rel)) {
                Storage::disk('public')->delete($rel);
            }
        }
        $keluar->delete();

        return redirect()
            ->route('keuangan.keluar.index')
            ->with('success', 'Transaksi dana keluar dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedKeluar(Request $request, bool $isUpdate = false): array
    {
        $allowedJenis = AkunDana::idsAkunBukuPembantuJenisDana();
        $allowedKas = AkunDana::idsAkunBukuPembantuKas();

        $rules = [
            'akun_jenis_dana_id' => ['required', 'integer', Rule::in($allowedJenis)],
            'akun_kas_id' => ['required', 'integer', Rule::in($allowedKas)],
            'nomor_bukti' => ['required', 'string', 'max:64'],
            'tanggal' => ['required', 'date'],
            'jumlah_angka' => ['required', 'numeric', 'min:0.01', 'max:9999999999999.99'],
            'keperluan' => ['required', 'string', 'max:255'],
            'uraian_transaksi' => ['required', 'string', 'max:10000'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'gambar_nota' => ['nullable', 'array', 'max:10'],
            'gambar_nota.*' => ['file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
        if (! $isUpdate) {
            $rules['profil_mbg_id'] = ['nullable', 'integer', 'exists:profil_mbg,id'];
        }

        $data = $request->validate($rules);
        $data['jumlah'] = (float) $data['jumlah_angka'];
        unset($data['jumlah_angka'], $data['gambar_nota']);

        return $data;
    }

    /**
     * @return list<string>
     */
    private function simpanNotaBatch(Request $request): array
    {
        $paths = [];
        foreach ($request->file('gambar_nota', []) ?: [] as $file) {
            if ($file && $file->isValid()) {
                $paths[] = $file->store('nota-dana', 'public');
            }
        }

        return $paths;
    }

    private function ensureKeuanganPeriode(DanaKeluar $keluar): void
    {
        if ((int) $keluar->periode_id !== PeriodeTenant::id()) {
            abort(404);
        }
    }

    private function assertSaldoCukupKeluar(int $profilId, float $jumlahKeluar, ?DanaKeluar $existing): void
    {
        DB::transaction(function () use ($profilId, $jumlahKeluar, $existing): void {
            $saldo = SaldoDana::getSaldoDana($profilId);
            if ($existing) {
                $saldo += (float) $existing->jumlah;
            }
            if ($saldo + 1e-9 < $jumlahKeluar) {
                abort(422, 'Saldo dana tidak mencukupi. Saldo tersedia: '.formatRupiah($saldo));
            }
        });
    }

    private function previewNextKodeKeluar(): string
    {
        $prefix = 'DK-'.now()->format('Ymd').'-';
        $last = DanaKeluar::query()
            ->where('kode_transaksi', 'like', $prefix.'%')
            ->orderByDesc('kode_transaksi')
            ->value('kode_transaksi');
        $next = 1;
        if ($last && preg_match('/-(\d{3})$/', (string) $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
