<?php

namespace App\Http\Middleware;

use App\Enums\StatusAktif;
use App\Models\Periode;
use App\Support\PeriodeTenant;
use App\Support\ProfilMbgTenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

final class ResolvePeriode
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();
        $profilId = ProfilMbgTenant::id();

        $hideToolbar = $this->hideToolbar($routeName);
        $skipRequire = $this->skipRequire($routeName);

        $count = (int) Periode::query()->where('profil_mbg_id', $profilId)->count();

        if (! $skipRequire && $count === 0) {
            if ($routeName !== 'periode.index') {
                return redirect()
                    ->route('periode.index')
                    ->with('warning', 'Buat minimal satu periode laporan sebelum menggunakan modul operasional.');
            }
        }

        if ($count > 0) {
            $this->ensureSessionPeriode($profilId);
        }

        if (! $hideToolbar && $count > 0) {
            $currentId = (int) session('periode_id', 0);
            $current = $currentId > 0
                ? Periode::query()->whereKey($currentId)->where('profil_mbg_id', $profilId)->first()
                : null;
            $options = Periode::query()
                ->where('profil_mbg_id', $profilId)
                ->orderByDesc('tanggal_awal')
                ->orderByDesc('id')
                ->get();
            View::share('periodeToolbar', [
                'visible' => true,
                'current' => $current,
                'options' => $options,
            ]);
        } else {
            View::share('periodeToolbar', [
                'visible' => false,
                'current' => null,
                'options' => collect(),
            ]);
        }

        PeriodeTenant::forgetCached();

        return $next($request);
    }

    private function ensureSessionPeriode(int $profilId): void
    {
        $sid = (int) session('periode_id', 0);
        $exists = $sid > 0 && Periode::query()->whereKey($sid)->where('profil_mbg_id', $profilId)->exists();
        if ($exists) {
            return;
        }

        $aktif = Periode::query()
            ->where('profil_mbg_id', $profilId)
            ->where('status', StatusAktif::Aktif)
            ->orderByDesc('tanggal_awal')
            ->value('id');
        if ($aktif) {
            session(['periode_id' => (int) $aktif]);

            return;
        }

        $fallback = Periode::query()
            ->where('profil_mbg_id', $profilId)
            ->orderByDesc('tanggal_awal')
            ->value('id');
        if ($fallback) {
            session(['periode_id' => (int) $fallback]);
        }
    }

    private function hideToolbar(?string $name): bool
    {
        if ($name === null) {
            return true;
        }

        return str_starts_with($name, 'master.');
    }

    private function skipRequire(?string $name): bool
    {
        if ($name === null) {
            return true;
        }
        if (str_starts_with($name, 'master.')) {
            return true;
        }
        if (str_starts_with($name, 'periode.')) {
            return true;
        }
        if (str_starts_with($name, 'keuangan.stok-dana-awal.')) {
            return true;
        }
        if (str_starts_with($name, 'pengaturan.')) {
            return true;
        }

        return false;
    }
}
