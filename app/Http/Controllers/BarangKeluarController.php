<?php

namespace App\Http\Controllers;

use App\Enums\BarangKeluarTujuan;
use App\Enums\StatusAktif;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Support\KodeTransaksiStok;
use App\Support\PeriodeTenant;
use App\Support\ProfilMbgTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class BarangKeluarController extends Controller
{
    use Concerns\ManagesStokProfil;

    public function index(Request $request): View
    {
        $profilIdDefault = $this->profilMbgIdForStokOrFirst($request);
        $periodeId = PeriodeTenant::id();

        return view('barang-keluar.index', compact('profilIdDefault', 'periodeId'));
    }

    public function data(Request $request): JsonResponse
    {
        $query = BarangKeluar::query()
            ->with(['barang.kategoriBarang', 'profilMbg', 'creator'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $query->where('profil_mbg_id', ProfilMbgTenant::id());
        $query->where('periode_id', PeriodeTenant::id());

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('tanggal', fn (BarangKeluar $r) => $r->tanggal?->format('d/m/Y') ?? '')
            ->addColumn('barang_cell', fn (BarangKeluar $r) => e($r->barang?->kode_barang.' — '.$r->barang?->nama_barang))
            ->addColumn('jumlah_cell', fn (BarangKeluar $r) => '<span class="font-mono">'.e(number_format((float) $r->jumlah, 2, ',', '.')).' '.e($r->satuan).'</span>')
            ->addColumn('tujuan_label', fn (BarangKeluar $r) => e($r->tujuan_penggunaan?->label() ?? '—'))
            ->addColumn('creator_name', fn (BarangKeluar $r) => e($r->creator?->name ?? '—'))
            ->addColumn('aksi', function (BarangKeluar $r) {
                $show = '<a href="'.e(route('stok.keluar.show', $r)).'" class="text-xs font-semibold" style="color:#1a4a6b;">Detail</a>';
                $edit = '<a href="'.e(route('stok.keluar.edit', $r)).'" class="ml-3 text-xs font-semibold" style="color:#4a9b7a;">Ubah</a>';
                $hapus = '';
                if ($this->userCanDeleteStokRecord($r)) {
                    $hapus = '<form method="POST" action="'.e(route('stok.keluar.destroy', $r)).'" class="ml-3 inline form-hapus-stok">'
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
        $profilId = $this->profilMbgIdForStokOrFirst($request);
        $periodeId = PeriodeTenant::id();
        $barangs = Barang::query()->where('status', StatusAktif::Aktif)->orderBy('nama_barang')->get();
        $previewKode = $this->previewNextKode();

        return view('barang-keluar.create', compact('profilId', 'periodeId', 'barangs', 'previewKode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $profilId = $this->profilMbgIdFromStokForm($request);

        $data = $request->validate([
            'barang_id' => ['required', 'integer', 'exists:barang,id'],
            'tanggal' => ['required', 'date'],
            'jumlah' => ['required', 'numeric', 'min:0.01', 'max:9999999999999.99'],
            'satuan' => ['required', 'string', 'max:32'],
            'tujuan_penggunaan' => ['required', 'string', Rule::in(array_map(static fn (BarangKeluarTujuan $e) => $e->value, BarangKeluarTujuan::cases()))],
            'keterangan' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'barang_id' => 'barang',
        ]);

        $this->assertStokCukup((int) $data['barang_id'], $profilId, (float) $data['jumlah'], null);

        $data['profil_mbg_id'] = $profilId;
        $data['periode_id'] = PeriodeTenant::id();
        $data['created_by'] = (int) $request->user()->getKey();
        $data['kode_transaksi'] = KodeTransaksiStok::generate('BK', 'barang_keluar');

        BarangKeluar::query()->create($data);

        return redirect()
            ->route('stok.keluar.index')
            ->with('success', 'Transaksi barang keluar berhasil disimpan.');
    }

    public function show(Request $request, BarangKeluar $keluar): View
    {
        $this->ensureProfilPeriodeRow($request, $keluar);
        $keluar->load(['barang.kategoriBarang', 'profilMbg', 'creator']);

        return view('barang-keluar.show', compact('keluar'));
    }

    public function edit(Request $request, BarangKeluar $keluar): View
    {
        $this->ensureProfilPeriodeRow($request, $keluar);
        $profilId = $keluar->profil_mbg_id;
        $periodeId = PeriodeTenant::id();
        $keluar->load('barang');
        $previewKode = $keluar->kode_transaksi;

        return view('barang-keluar.edit', compact('keluar', 'profilId', 'periodeId', 'previewKode'));
    }

    public function update(Request $request, BarangKeluar $keluar): RedirectResponse
    {
        $this->ensureProfilPeriodeRow($request, $keluar);

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'jumlah' => ['required', 'numeric', 'min:0.01', 'max:9999999999999.99'],
            'satuan' => ['required', 'string', 'max:32'],
            'tujuan_penggunaan' => ['required', 'string', Rule::in(array_map(static fn (BarangKeluarTujuan $e) => $e->value, BarangKeluarTujuan::cases()))],
            'keterangan' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->assertStokCukup(
            (int) $keluar->barang_id,
            (int) $keluar->profil_mbg_id,
            (float) $data['jumlah'],
            $keluar
        );

        $keluar->update($data);

        return redirect()
            ->route('stok.keluar.index')
            ->with('success', 'Transaksi barang keluar diperbarui.');
    }

    public function destroy(Request $request, BarangKeluar $keluar): RedirectResponse
    {
        $this->ensureProfilPeriodeRow($request, $keluar);

        if (! $this->userCanDeleteStokRecord($keluar)) {
            abort(403, 'Anda tidak dapat menghapus transaksi ini.');
        }

        $keluar->delete();

        return redirect()
            ->route('stok.keluar.index')
            ->with('success', 'Transaksi barang keluar dihapus.');
    }

    private function assertStokCukup(int $barangId, int $profilId, float $jumlahKeluar, ?BarangKeluar $existing): void
    {
        DB::transaction(function () use ($barangId, $profilId, $jumlahKeluar, $existing): void {
            $barang = Barang::query()->whereKey($barangId)->lockForUpdate()->firstOrFail();
            $stok = $barang->getStokSaatIni($profilId, PeriodeTenant::id());
            if ($existing) {
                $stok += (float) $existing->jumlah;
            }
            if ($stok + 1e-9 < $jumlahKeluar) {
                abort(422, 'Stok tidak mencukupi. Stok tersedia: '.number_format($stok, 2, ',', '.'));
            }
        });
    }

    private function previewNextKode(): string
    {
        $prefix = 'BK-'.now()->format('Ymd').'-';
        $last = BarangKeluar::query()
            ->where('kode_transaksi', 'like', $prefix.'%')
            ->orderByDesc('kode_transaksi')
            ->value('kode_transaksi');
        $next = 1;
        if ($last && preg_match('/-(\d{3})$/', (string) $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function ensureProfilRow(Request $request, int $profilMbgId): void
    {
        $scoped = $request->attributes->get('scoped_profil_mbg_id');
        if ($scoped !== null && $scoped !== '' && (int) $scoped !== (int) $profilMbgId) {
            abort(403, 'Transaksi ini berada di luar dapur Anda.');
        }
    }

    private function ensureProfilPeriodeRow(Request $request, BarangKeluar $keluar): void
    {
        $this->ensureProfilRow($request, $keluar->profil_mbg_id);
        if ((int) $keluar->periode_id !== PeriodeTenant::id()) {
            abort(404);
        }
    }
}
