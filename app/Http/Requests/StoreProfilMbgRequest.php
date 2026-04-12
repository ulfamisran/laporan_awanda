<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreProfilMbgRequest extends MbgFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama_dapur' => ['required', 'string', 'max:255'],
            'kode_dapur' => ['required', 'string', 'max:50', 'unique:profil_mbg,kode_dapur'],
            'alamat' => ['nullable', 'string'],
            'kota' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'no_telp' => ['nullable', 'string', 'max:50'],
            'penanggung_jawab' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', 'string', Rule::in(['aktif', 'nonaktif'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_dapur' => 'nama dapur',
            'kode_dapur' => 'kode dapur',
            'alamat' => 'alamat',
            'kota' => 'kota',
            'provinsi' => 'provinsi',
            'no_telp' => 'nomor telepon',
            'penanggung_jawab' => 'penanggung jawab',
            'logo' => 'logo',
            'status' => 'status',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status harus aktif atau nonaktif.',
        ];
    }
}
