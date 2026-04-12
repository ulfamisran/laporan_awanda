<?php

namespace App\Http\Controllers;

use App\Support\SaldoDana;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KeuanganSaldoApiController extends Controller
{
    use Concerns\ManagesKeuanganProfil;

    public function show(Request $request): JsonResponse
    {
        $profilId = $this->profilMbgIdForKeuanganOrFirst($request);
        $saldo = SaldoDana::getSaldoDana($profilId);

        return response()->json([
            'profil_mbg_id' => $profilId,
            'saldo_saat_ini' => $saldo,
            'saldo_format' => formatRupiah($saldo),
        ]);
    }
}
