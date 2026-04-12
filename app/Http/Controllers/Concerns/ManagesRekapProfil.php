<?php

namespace App\Http\Controllers\Concerns;

use App\Support\ProfilMbgTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait ManagesRekapProfil
{
    protected function profilIdForRekapOrFirst(Request $request): int
    {
        return ProfilMbgTenant::id();
    }

    /**
     * @return Collection<int, never>
     */
    protected function profilListRekap(Request $request): Collection
    {
        return collect();
    }

    protected function ensureRekapProfil(Request $request, int $profilMbgId): void
    {
        if ((int) $profilMbgId !== ProfilMbgTenant::id()) {
            abort(403, 'Data ini tidak sesuai profil cabang MBG.');
        }
    }
}
