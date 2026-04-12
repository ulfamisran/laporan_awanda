<?php

namespace App\Http\Requests;

use App\Enums\SatuanBarang;
use App\Enums\StatusAktif;
use Illuminate\Validation\Rule;

class UpdateBarangRequest extends MbgFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['super_admin', 'admin_pusat', 'admin']) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $harga = $this->input('harga_satuan');
        if (is_string($harga)) {
            $digits = preg_replace('/[^\d]/', '', $harga);
            $this->merge(['harga_satuan' => $digits === '' ? 0 : (float) $digits]);
        }
    }

    public function rules(): array
    {
        return [
            'nama_barang' => ['required', 'string', 'max:255'],
            'kategori_barang_id' => ['required', 'integer', 'exists:kategori_barang,id'],
            'satuan' => ['required', Rule::enum(SatuanBarang::class)],
            'harga_satuan' => ['required', 'numeric', 'min:0'],
            'stok_minimum' => ['required', 'numeric', 'min:0'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::enum(StatusAktif::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_barang' => 'nama barang',
            'kategori_barang_id' => 'kategori barang',
            'satuan' => 'satuan',
            'harga_satuan' => 'harga satuan',
            'stok_minimum' => 'stok minimum',
            'deskripsi' => 'deskripsi',
            'foto' => 'foto',
            'status' => 'status',
        ];
    }
}
