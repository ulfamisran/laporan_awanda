<?php

namespace App\Http\Controllers\Concerns;

use App\Support\ProfilMbgTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait ManagesLimbahProfil
{
    protected function profilMbgIdFilterForLimbah(Request $request): int
    {
        return ProfilMbgTenant::id();
    }

    protected function profilMbgIdForLimbahOrFirst(Request $request): int
    {
        return ProfilMbgTenant::id();
    }

    protected function profilMbgIdFromLimbahForm(Request $request): int
    {
        return ProfilMbgTenant::id();
    }

    /**
     * @return Collection<int, never>
     */
    protected function profilListForLimbahFilter(Request $request): Collection
    {
        return collect();
    }

    protected function ensureLimbahProfil(Request $request, int $profilMbgId): void
    {
        if ((int) $profilMbgId !== ProfilMbgTenant::id()) {
            abort(403, 'Data ini tidak sesuai profil cabang MBG.');
        }
    }

    protected function applyLimbahProfilFilter(Builder $query, Request $request): void
    {
        $query->where('profil_mbg_id', ProfilMbgTenant::id());
    }
}
