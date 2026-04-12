<?php

namespace App\Http\Controllers\Concerns;

use App\Support\ProfilMbgTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait ManagesPenggajianProfil
{
    protected function profilMbgIdForPenggajian(Request $request): int
    {
        return ProfilMbgTenant::id();
    }

    protected function profilMbgIdForPenggajianOrFirst(Request $request): int
    {
        return ProfilMbgTenant::id();
    }

    protected function profilMbgIdFromPenggajianForm(Request $request): int
    {
        return ProfilMbgTenant::id();
    }

    /**
     * @return Collection<int, never>
     */
    protected function profilListForPenggajianFilter(Request $request): Collection
    {
        return collect();
    }

    protected function ensurePenggajianProfil(Request $request, int $profilMbgId): void
    {
        if ((int) $profilMbgId !== ProfilMbgTenant::id()) {
            abort(403, 'Data ini tidak sesuai profil cabang MBG.');
        }
    }
}
