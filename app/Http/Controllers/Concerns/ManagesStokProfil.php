<?php

namespace App\Http\Controllers\Concerns;

use App\Support\ProfilMbgTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait ManagesStokProfil
{
    protected function profilMbgIdForStok(Request $request): int
    {
        return ProfilMbgTenant::id();
    }

    protected function profilMbgIdForStokOrFirst(Request $request): int
    {
        return ProfilMbgTenant::id();
    }

    protected function profilMbgIdFromStokForm(Request $request): int
    {
        return ProfilMbgTenant::id();
    }

    protected function userCanDeleteStokRecord(?Model $record): bool
    {
        if (! $record || ! $record->getKey()) {
            return false;
        }

        $u = auth()->user();
        if (! $u) {
            return false;
        }

        if ($u->hasRole('super_admin')) {
            return true;
        }

        return (int) $record->created_by === (int) $u->getKey()
            && $record->created_at
            && $record->created_at->greaterThan(now()->subHours(24));
    }
}
