<?php

namespace App\Http\Controllers;

use App\Enums\StatusAktif;
use App\Models\Barang;
use App\Models\Periode;
use App\Models\StokAwalBarang;
use App\Support\PeriodeTenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StokAwalBarangController extends Controller
{
    use Concerns\ManagesStokProfil;

    public function index(Request $request): View
    {
        $profilId = $this->profilMbgIdForStokOrFirst($request);
        $periodeId = PeriodeTenant::id();
        $periodeLabel = PeriodeTenant::model()->labelRingkas();

        $rows = Barang::query()
            ->with('kategoriBarang')
            ->where('barang.status', StatusAktif::Aktif)
            ->orderBy('barang.nama_barang')
            ->get();

        $stokMap = StokAwalBarang::query()
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->with('creator')
            ->get()
            ->keyBy('barang_id');

        return view('stok-awal.index', compact('rows', 'profilId', 'stokMap', 'periodeLabel'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $profilId = $this->profilMbgIdForStokOrFirst($request);
        $periodeId = PeriodeTenant::id();

        $barangId = $request->integer('barang_id');
        if ($barangId > 0) {
            $exists = StokAwalBarang::query()
                ->where('barang_id', $barangId)
                ->where('profil_mbg_id', $profilId)
                ->where('periode_id', $periodeId)
                ->first();
            if ($exists) {
                return redirect()
                    ->route('stok.awal.edit', $exists)
                    ->with('info', 'Stok awal untuk barang ini sudah ada; silakan ubah di form berikut.');
            }
        }

        $barangs = Barang::query()
            ->where('status', StatusAktif::Aktif)
            ->orderBy('nama_barang')
            ->get();

        $stokAwal = new StokAwalBarang([
            'profil_mbg_id' => $profilId,
            'periode_id' => $periodeId,
            'tanggal' => now()->toDateString(),
        ]);

        return view('stok-awal.form', [
            'mode' => 'create',
            'stokAwal' => $stokAwal,
            'barangs' => $barangs,
            'profilId' => $profilId,
            'selectedBarangId' => $barangId > 0 ? $barangId : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $profilId = $this->profilMbgIdFromStokForm($request);
        $periodeId = PeriodeTenant::id();

        $data = $request->validate([
            'barang_id' => [
                'required',
                'integer',
                Rule::exists('barang', 'id')->where(fn ($q) => $q->where('status', StatusAktif::Aktif)),
                Rule::unique('stok_awal_barang', 'barang_id')->where(fn ($q) => $q->where('profil_mbg_id', $profilId)->where('periode_id', $periodeId)),
            ],
            'tanggal' => ['required', 'date'],
            'jumlah' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'barang_id' => 'barang',
        ]);

        $data['profil_mbg_id'] = $profilId;
        $data['periode_id'] = $periodeId;
        $data['created_by'] = (int) $request->user()->getKey();

        StokAwalBarang::query()->create($data);

        return redirect()
            ->route('stok.awal.index')
            ->with('success', 'Stok awal berhasil disimpan.');
    }

    public function batch(Request $request): View
    {
        $profilId = $this->profilMbgIdForStokOrFirst($request);
        $periodeId = PeriodeTenant::id();

        $existingBarangIds = StokAwalBarang::query()
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->pluck('barang_id');

        $rows = Barang::query()
            ->with('kategoriBarang')
            ->where('barang.status', StatusAktif::Aktif)
            ->whereNotIn('barang.id', $existingBarangIds)
            ->orderBy('barang.nama_barang')
            ->get();

        return view('stok-awal.batch', compact('rows', 'profilId'));
    }

    public function batchStore(Request $request): RedirectResponse
    {
        $profilId = $this->profilMbgIdFromStokForm($request);
        $periodeId = PeriodeTenant::id();

        $existingForProfil = StokAwalBarang::query()
            ->where('profil_mbg_id', $profilId)
            ->where('periode_id', $periodeId)
            ->pluck('barang_id');

        $allowedBarangIds = Barang::query()
            ->where('status', StatusAktif::Aktif)
            ->whereNotIn('id', $existingForProfil)
            ->pluck('id')
            ->all();

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'rows' => ['required', 'array'],
            'rows.*.jumlah' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'rows.*.keterangan' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'tanggal' => 'tanggal',
        ]);

        foreach (array_keys($data['rows']) as $key) {
            if (! in_array((int) $key, $allowedBarangIds, true)) {
                throw ValidationException::withMessages([
                    'rows' => 'Terdapat barang yang tidak valid atau sudah memiliki stok awal. Muat ulang halaman lalu coba lagi.',
                ]);
            }
        }

        $userId = (int) $request->user()->getKey();
        $created = 0;

        DB::transaction(function () use ($data, $profilId, $periodeId, $userId, &$created): void {
            foreach ($data['rows'] as $barangId => $row) {
                $jumlah = $row['jumlah'] ?? null;
                if ($jumlah === null || $jumlah === '') {
                    continue;
                }

                StokAwalBarang::query()->create([
                    'barang_id' => (int) $barangId,
                    'profil_mbg_id' => $profilId,
                    'periode_id' => $periodeId,
                    'tanggal' => $data['tanggal'],
                    'jumlah' => $jumlah,
                    'keterangan' => $row['keterangan'] ?? null,
                    'created_by' => $userId,
                ]);
                $created++;
            }
        });

        if ($created === 0) {
            throw ValidationException::withMessages([
                'rows' => 'Isi kolom jumlah pada minimal satu barang untuk menyimpan stok awal.',
            ]);
        }

        return redirect()
            ->route('stok.awal.index')
            ->with('success', 'Stok awal untuk '.$created.' barang berhasil disimpan sekaligus.');
    }

    public function generateFromPeriodeSebelumnya(Request $request): RedirectResponse
    {
        $profilId = $this->profilMbgIdFromStokForm($request);
        $current = PeriodeTenant::model();
        if ((int) $current->profil_mbg_id !== $profilId) {
            abort(403);
        }

        $prev = Periode::query()
            ->where('profil_mbg_id', $profilId)
            ->whereDate('tanggal_akhir', '<', $current->tanggal_awal)
            ->orderByDesc('tanggal_akhir')
            ->orderByDesc('id')
            ->first();

        if (! $prev) {
            return redirect()
                ->route('stok.awal.index')
                ->with('warning', 'Tidak ada periode sebelumnya yang ditutup sebelum tanggal awal periode ini. Stok awal tidak diubah.');
        }

        $userId = (int) $request->user()->getKey();
        $tanggal = $current->tanggal_awal->toDateString();
        $prevId = (int) $prev->getKey();
        $curId = (int) $current->getKey();

        $barangs = Barang::query()->where('status', StatusAktif::Aktif)->orderBy('nama_barang')->get();

        DB::transaction(function () use ($barangs, $profilId, $curId, $prevId, $tanggal, $userId): void {
            foreach ($barangs as $b) {
                $stok = $b->getStokSaatIni($profilId, $prevId);
                $row = StokAwalBarang::query()->firstOrNew([
                    'barang_id' => (int) $b->getKey(),
                    'profil_mbg_id' => $profilId,
                    'periode_id' => $curId,
                ]);
                $row->tanggal = $tanggal;
                $row->jumlah = $stok;
                $row->keterangan = 'Generate dari stok akhir periode sebelumnya';
                if (! $row->exists) {
                    $row->created_by = $userId;
                }
                $row->save();
            }
        });

        return redirect()
            ->route('stok.awal.index')
            ->with('success', 'Stok awal diisi dari stok akhir periode sebelumnya. Silakan tinjau dan sesuaikan bila perlu.');
    }

    public function edit(Request $request, StokAwalBarang $awal): View
    {
        $this->ensureProfilPeriodeAccess($request, $awal);

        $profilId = $awal->profil_mbg_id;
        $awal->load('barang.kategoriBarang');

        return view('stok-awal.form', [
            'mode' => 'edit',
            'stokAwal' => $awal,
            'barangs' => collect([$awal->barang])->filter(),
            'profilId' => $profilId,
            'selectedBarangId' => $awal->barang_id,
        ]);
    }

    public function update(Request $request, StokAwalBarang $awal): RedirectResponse
    {
        $this->ensureProfilPeriodeAccess($request, $awal);

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'jumlah' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
        ]);

        $awal->update($data);

        return redirect()
            ->route('stok.awal.index')
            ->with('success', 'Stok awal berhasil diperbarui.');
    }

    public function destroy(Request $request, StokAwalBarang $awal): RedirectResponse
    {
        $this->ensureProfilPeriodeAccess($request, $awal);

        if (! $this->userCanDeleteStokRecord($awal)) {
            abort(403, 'Anda tidak dapat menghapus data ini.');
        }

        $awal->delete();

        return redirect()
            ->route('stok.awal.index')
            ->with('success', 'Stok awal berhasil dihapus.');
    }

    private function ensureProfilAccess(Request $request, int $profilMbgId): void
    {
        $scoped = $request->attributes->get('scoped_profil_mbg_id');
        if ($scoped !== null && $scoped !== '' && (int) $scoped !== (int) $profilMbgId) {
            abort(403, 'Data stok ini berada di luar dapur Anda.');
        }
    }

    private function ensureProfilPeriodeAccess(Request $request, StokAwalBarang $awal): void
    {
        $this->ensureProfilAccess($request, $awal->profil_mbg_id);
        if ((int) $awal->periode_id !== PeriodeTenant::id()) {
            abort(404);
        }
    }
}
