<?php

namespace App\Http\Middleware;

use App\Support\ProfilMbgTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Membatasi akses berdasarkan peran Spatie.
     *
     * Satu instalasi = satu profil cabang MBG. Atribut `scoped_profil_mbg_id`
     * selalu berisi ID profil tersebut agar kueri transaksi konsisten.
     *
     * @param  string  ...$roles  Daftar nama peran yang diizinkan (mis. super_admin,admin)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Autentikasi diperlukan.');
        }

        $allowed = collect($roles)->map(fn (string $r) => trim($r))->filter()->values()->all();

        if ($allowed === []) {
            abort(403, 'Peran tidak dikonfigurasi untuk rute ini.');
        }

        if (! $user->hasAnyRole($allowed)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $request->attributes->set('scoped_profil_mbg_id', ProfilMbgTenant::id());

        return $next($request);
    }
}
