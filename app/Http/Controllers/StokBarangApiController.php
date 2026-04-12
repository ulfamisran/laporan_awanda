<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Support\PeriodeTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StokBarangApiController extends Controller
{
    use Concerns\ManagesStokProfil;

    /**
     * Info barang untuk form stok: satuan & stok saat ini per dapur.
     */
    public function show(Request $request, Barang $barang): JsonResponse
    {
        $profilId = $this->profilMbgIdFromStokForm($request);

        return response()->json([
            'id' => $barang->getKey(),
            'kode_barang' => $barang->kode_barang,
            'nama_barang' => $barang->nama_barang,
            'satuan' => $barang->satuan?->value,
            'satuan_label' => $barang->satuan?->label() ?? '',
            'stok_saat_ini' => $barang->getStokSaatIni($profilId, PeriodeTenant::id()),
        ]);
    }
}
