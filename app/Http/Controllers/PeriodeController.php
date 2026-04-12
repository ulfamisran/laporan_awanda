<?php

namespace App\Http\Controllers;

use App\Enums\StatusAktif;
use App\Models\Periode;
use App\Support\PeriodeTenant;
use App\Support\ProfilMbgTenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PeriodeController extends Controller
{
    public function index(): View
    {
        $profilId = ProfilMbgTenant::id();
        $items = Periode::query()
            ->where('profil_mbg_id', $profilId)
            ->orderByDesc('tanggal_awal')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('periode.index', compact('items'));
    }

    public function create(): View
    {
        return view('periode.form', [
            'mode' => 'create',
            'periode' => new Periode([
                'profil_mbg_id' => ProfilMbgTenant::id(),
                'status' => StatusAktif::Aktif,
                'tanggal_awal' => now()->startOfMonth()->toDateString(),
                'tanggal_akhir' => now()->endOfMonth()->toDateString(),
                'tanggal_pelaporan' => now()->endOfMonth()->toDateString(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $profilId = ProfilMbgTenant::id();
        $data = $this->validated($request, null, $profilId);
        $data['profil_mbg_id'] = $profilId;

        DB::transaction(function () use ($data, $profilId): void {
            if (($data['status'] ?? '') === StatusAktif::Aktif->value) {
                Periode::query()->where('profil_mbg_id', $profilId)->update(['status' => StatusAktif::Nonaktif->value]);
            }
            Periode::query()->create($data);
        });

        return redirect()
            ->route('periode.index')
            ->with('success', 'Periode berhasil ditambahkan.');
    }

    public function edit(Periode $periode): View
    {
        $this->ensureProfil($periode);

        return view('periode.form', [
            'mode' => 'edit',
            'periode' => $periode,
        ]);
    }

    public function update(Request $request, Periode $periode): RedirectResponse
    {
        $this->ensureProfil($periode);
        $profilId = ProfilMbgTenant::id();
        $data = $this->validated($request, $periode->getKey(), $profilId);

        DB::transaction(function () use ($data, $periode, $profilId): void {
            if (($data['status'] ?? '') === StatusAktif::Aktif->value) {
                Periode::query()
                    ->where('profil_mbg_id', $profilId)
                    ->whereKeyNot($periode->getKey())
                    ->update(['status' => StatusAktif::Nonaktif->value]);
            }
            $periode->update($data);
        });

        return redirect()
            ->route('periode.index')
            ->with('success', 'Periode berhasil diperbarui.');
    }

    public function destroy(Periode $periode): RedirectResponse
    {
        $this->ensureProfil($periode);

        try {
            $periode->delete();
        } catch (\Throwable) {
            return redirect()
                ->route('periode.index')
                ->with('error', 'Periode tidak dapat dihapus karena masih dipakai oleh data transaksi.');
        }

        return redirect()
            ->route('periode.index')
            ->with('success', 'Periode berhasil dihapus.');
    }

    public function pilih(Request $request): RedirectResponse
    {
        $profilId = ProfilMbgTenant::id();
        $data = $request->validate([
            'periode_id' => [
                'required',
                'integer',
                Rule::exists('periode', 'id')->where(fn ($q) => $q->where('profil_mbg_id', $profilId)),
            ],
        ], [], ['periode_id' => 'periode']);

        session(['periode_id' => (int) $data['periode_id']]);
        PeriodeTenant::forgetCached();

        return redirect()->back()->with('success', 'Periode laporan telah diganti.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $ignoreId, int $profilId): array
    {
        $data = $request->validate([
            'nama' => ['nullable', 'string', 'max:191'],
            'tanggal_awal' => ['required', 'date'],
            'tanggal_akhir' => ['required', 'date', 'after_or_equal:tanggal_awal'],
            'tanggal_pelaporan' => ['required', 'date'],
            'status' => ['required', Rule::enum(StatusAktif::class)],
        ], [], [
            'tanggal_awal' => 'tanggal awal periode',
            'tanggal_akhir' => 'tanggal akhir periode',
            'tanggal_pelaporan' => 'tanggal pelaporan',
            'status' => 'status periode',
        ]);

        $awal = $data['tanggal_awal'];
        $akhir = $data['tanggal_akhir'];

        $overlap = Periode::query()
            ->where('profil_mbg_id', $profilId)
            ->when($ignoreId !== null, fn ($q) => $q->whereKeyNot($ignoreId))
            ->whereDate('tanggal_awal', '<=', $akhir)
            ->whereDate('tanggal_akhir', '>=', $awal)
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'tanggal_awal' => 'Rentang tanggal periode bentrok dengan periode lain.',
            ]);
        }

        $data['status'] = $data['status'] instanceof StatusAktif ? $data['status']->value : (string) $data['status'];

        return $data;
    }

    private function ensureProfil(Periode $periode): void
    {
        if ((int) $periode->profil_mbg_id !== ProfilMbgTenant::id()) {
            abort(403);
        }
    }
}
