<?php

namespace App\Http\Controllers\Concerns;

use App\Support\ProfilMbgTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait ManagesKeuanganProfil
{
    protected function profilMbgIdForKeuangan(Request $request): int
    {
        return ProfilMbgTenant::id();
    }

    protected function profilMbgIdForKeuanganOrFirst(Request $request): int
    {
        return ProfilMbgTenant::id();
    }

    protected function profilMbgIdFromKeuanganForm(Request $request): int
    {
        return ProfilMbgTenant::id();
    }

    /**
     * @return Collection<int, never>
     */
    protected function profilListForKeuanganFilter(Request $request): Collection
    {
        return collect();
    }

    protected function ensureKeuanganProfil(Request $request, int $profilMbgId): void
    {
        if ((int) $profilMbgId !== ProfilMbgTenant::id()) {
            abort(403, 'Data ini tidak sesuai profil cabang MBG.');
        }
    }

    protected function userCanDeleteKeuangan(?Model $record): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }
}
