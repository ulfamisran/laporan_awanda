<?php

namespace App\Http\Controllers;

use App\Models\AkunDana;
use App\Models\DanaKeluar;
use App\Models\DanaMasuk;
use App\Models\StokDanaAwal;
use App\Models\StokDanaAwalAkun;
use App\Support\SaldoDana;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class StokDanaAwalController extends Controller
{
    use Concerns\ManagesKeuanganProfil;

    public function index(Request $request): View
    {
        $profilId = $this->profilMbgIdForKeuanganOrFirst($request);

        $stok = StokDanaAwal::query()
            ->where('profil_mbg_id', $profilId)
            ->with(['creator', 'profilMbg', 'lines'])
            ->first();

        $akunRows = $this->flattenAkunRows();
        $saldoByAkunId = $stok
            ? $stok->lines->mapWithKeys(fn (StokDanaAwalAkun $l) => [(int) $l->akun_dana_id => (float) $l->saldo_awal])->all()
            : [];

        $aktivitasTerbaru = $this->aktivitasProfil($profilId);
        $saldoGlobal = SaldoDana::getSaldoDana($profilId);

        $allAkun = AkunDana::query()->orderBy('urutan')->orderBy('kode')->get();
        $saldoAkhirById = [];
        if ($allAkun->isNotEmpty()) {
            foreach ($allAkun as $a) {
                $saldoAkhirById[$a->id] = $this->saldoAkhirUntukAkun($a, $allAkun, $saldoByAkunId, $saldoGlobal);
            }
        }

        return view('stok-dana-awal.index', compact(
            'profilId',
            'stok',
            'akunRows',
            'saldoByAkunId',
            'aktivitasTerbaru',
            'saldoGlobal',
            'allAkun',
            'saldoAkhirById',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $profilId = $this->profilMbgIdFromKeuanganForm($request);
        $this->mergeSaldoRupiahFromRequest($request);

        if (AkunDana::query()->where('is_grup', false)->doesntExist()) {
            return redirect()
                ->route('keuangan.stok-dana-awal.index')
                ->with('warning', 'Master akun dana belum disiapkan.');
        }

        if (StokDanaAwal::query()->where('profil_mbg_id', $profilId)->exists()) {
            return redirect()
                ->route('keuangan.stok-dana-awal.index')
                ->with('warning', 'Stok dana awal sudah ada. Gunakan simpan perubahan pada tabel.');
        }

        $leafIds = $this->leafAkunIds();
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'saldo' => ['required', 'array'],
            'saldo.*' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
        ]);

        foreach (array_keys($data['saldo']) as $key) {
            if (! in_array((int) $key, $leafIds, true)) {
                return redirect()
                    ->route('keuangan.stok-dana-awal.index')
                    ->withInput()
                    ->withErrors(['saldo' => 'Terdapat akun yang tidak valid.']);
            }
        }

        $stok = StokDanaAwal::query()->create([
            'profil_mbg_id' => $profilId,
            'tanggal' => $data['tanggal'],
            'keterangan' => $data['keterangan'] ?? null,
            'created_by' => (int) $request->user()->getKey(),
        ]);

        $this->syncSaldoLines($stok, $data['saldo'], $leafIds);

        return redirect()
            ->route('keuangan.stok-dana-awal.index')
            ->with('success', 'Stok dana awal berhasil disimpan.');
    }

    public function update(Request $request, StokDanaAwal $stok_dana_awal): RedirectResponse
    {
        $this->ensureKeuanganProfil($request, (int) $stok_dana_awal->profil_mbg_id);
        $this->mergeSaldoRupiahFromRequest($request);

        if (AkunDana::query()->where('is_grup', false)->doesntExist()) {
            return redirect()
                ->route('keuangan.stok-dana-awal.index')
                ->with('warning', 'Master akun dana belum disiapkan.');
        }

        $leafIds = $this->leafAkunIds();
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'saldo' => ['required', 'array'],
            'saldo.*' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
        ]);

        foreach (array_keys($data['saldo']) as $key) {
            if (! in_array((int) $key, $leafIds, true)) {
                return redirect()
                    ->route('keuangan.stok-dana-awal.index')
                    ->withInput()
                    ->withErrors(['saldo' => 'Terdapat akun yang tidak valid.']);
            }
        }

        $stok_dana_awal->update([
            'tanggal' => $data['tanggal'],
            'keterangan' => $data['keterangan'] ?? null,
        ]);

        $this->syncSaldoLines($stok_dana_awal, $data['saldo'], $leafIds);

        return redirect()
            ->route('keuangan.stok-dana-awal.index')
            ->with('success', 'Stok dana awal diperbarui.');
    }

    /**
     * @return Collection<int, array{akun: AkunDana, depth: int}>
     */
    private function flattenAkunRows(): Collection
    {
        $all = AkunDana::query()->orderBy('urutan')->orderBy('kode')->get();

        return $this->flattenAkunRecursive($all, null, 0);
    }

    /**
     * @return Collection<int, array{akun: AkunDana, depth: int}>
     */
    private function flattenAkunRecursive(Collection $all, ?int $parentId, int $depth): Collection
    {
        $slice = $parentId === null
            ? $all->whereNull('parent_id')->values()
            : $all->where('parent_id', $parentId)->values();

        $out = collect();
        foreach ($slice as $akun) {
            $out->push(['akun' => $akun, 'depth' => $depth]);
            $out = $out->concat($this->flattenAkunRecursive($all, (int) $akun->id, $depth + 1));
        }

        return $out;
    }

    /**
     * @return list<int>
     */
    private function leafAkunIds(): array
    {
        return AkunDana::query()->where('is_grup', false)->orderBy('urutan')->pluck('id')->all();
    }

    /**
     * @param  array<string, mixed>  $saldoInput
     * @param  list<int>  $leafIds
     */
    private function syncSaldoLines(StokDanaAwal $stok, array $saldoInput, array $leafIds): void
    {
        $stok->lines()->delete();

        foreach ($leafIds as $aid) {
            $raw = $saldoInput[(string) $aid] ?? $saldoInput[$aid] ?? null;
            if ($raw === null || $raw === '') {
                continue;
            }
            $v = round((float) $raw, 2);
            if (abs($v) < 1e-9) {
                continue;
            }
            StokDanaAwalAkun::query()->create([
                'stok_dana_awal_id' => $stok->getKey(),
                'akun_dana_id' => $aid,
                'saldo_awal' => $v,
            ]);
        }
    }

    /**
     * @param  array<int, float>  $nilaiLeaf
     */
    private function saldoAkhirUntukAkun(AkunDana $akun, Collection $allAkun, array $nilaiLeaf, float $saldoGlobal): float
    {
        if ($akun->kode === '1000') {
            return $saldoGlobal;
        }
        if (! $akun->is_grup) {
            return (float) ($nilaiLeaf[$akun->id] ?? 0);
        }

        return (float) $allAkun
            ->where('parent_id', $akun->id)
            ->sum(fn (AkunDana $c) => $this->saldoAkhirUntukAkun($c, $allAkun, $nilaiLeaf, $saldoGlobal));
    }

    /**
     * @param  array<int, float>  $nilaiPerAkun  saldo awal per id akun (hanya daun)
     */
    public static function agregatSaldoAwal(AkunDana $akun, Collection $allAkun, array $nilaiPerAkun): float
    {
        if (! $akun->is_grup) {
            return (float) ($nilaiPerAkun[$akun->id] ?? 0);
        }

        return (float) $allAkun
            ->where('parent_id', $akun->id)
            ->sum(fn (AkunDana $c) => self::agregatSaldoAwal($c, $allAkun, $nilaiPerAkun));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function mergeSaldoRupiahFromRequest(Request $request): void
    {
        $saldo = $request->input('saldo', []);
        if (! is_array($saldo)) {
            return;
        }
        $out = [];
        foreach ($saldo as $k => $v) {
            $digits = preg_replace('/\D+/', '', (string) $v);
            $out[$k] = $digits === '' ? null : $digits;
        }
        $request->merge(['saldo' => $out]);
    }

    private function aktivitasProfil(int $profilId): Collection
    {
        return DanaMasuk::query()
            ->where('profil_mbg_id', $profilId)
            ->with(['akunJenisDana', 'akunKas'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn (DanaMasuk $m) => [
                'jenis' => 'masuk',
                'tanggal' => $m->tanggal,
                'kode' => $m->kode_transaksi,
                'label' => $m->ringkasanBukuPembantu(),
                'uraian' => (string) ($m->uraian_transaksi ?? ''),
                'jumlah' => (float) $m->jumlah,
            ])
            ->concat(
                DanaKeluar::query()
                    ->where('profil_mbg_id', $profilId)
                    ->with(['akunJenisDana', 'akunKas'])
                    ->orderByDesc('tanggal')
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get()
                    ->map(fn (DanaKeluar $k) => [
                        'jenis' => 'keluar',
                        'tanggal' => $k->tanggal,
                        'kode' => $k->kode_transaksi,
                        'label' => $k->ringkasanBukuPembantu(),
                        'uraian' => (string) ($k->uraian_transaksi ?? ''),
                        'jumlah' => (float) $k->jumlah,
                    ])
            )
            ->sortByDesc(fn (array $r) => $r['tanggal']->timestamp)
            ->values()
            ->take(10);
    }
}
