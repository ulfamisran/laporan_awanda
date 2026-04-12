<?php

namespace App\Http\Requests;

use App\Support\ProfilMbgTenant;
use Illuminate\Validation\Rule;

class UpdateProfilMbgRequest extends MbgFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $nullableStrings = ['id_sppg', 'nama_yayasan', 'ketua_yayasan', 'nomor_rekening_va', 'tempat_pelaporan'];
        foreach ($nullableStrings as $key) {
            if ($this->has($key) && $this->input($key) === '') {
                $this->merge([$key => null]);
            }
        }
        if ($this->has('tahun_anggaran') && ($this->input('tahun_anggaran') === '' || $this->input('tahun_anggaran') === null)) {
            $this->merge(['tahun_anggaran' => null]);
        }
    }

    public function rules(): array
    {
        $id = ProfilMbgTenant::id();

        return [
            'nama_dapur' => ['required', 'string', 'max:255'],
            'kode_dapur' => [
                'required',
                'string',
                'max:50',
                Rule::unique('profil_mbg', 'kode_dapur')->ignore($id),
            ],
            'id_sppg' => [
                'nullable',
                'string',
                'max:64',
                Rule::unique('profil_mbg', 'id_sppg')->ignore($id),
            ],
            'alamat' => ['nullable', 'string'],
            'kota' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'no_telp' => ['nullable', 'string', 'max:50'],
            'penanggung_jawab' => ['nullable', 'string', 'max:255'],
            'nama_akuntansi' => ['nullable', 'string', 'max:255'],
            'nama_ahli_gizi' => ['nullable', 'string', 'max:255'],
            'nama_yayasan' => ['nullable', 'string', 'max:255'],
            'ketua_yayasan' => ['nullable', 'string', 'max:255'],
            'nomor_rekening_va' => ['nullable', 'string', 'max:128'],
            'tahun_anggaran' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'tempat_pelaporan' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', 'string', Rule::in(['aktif', 'nonaktif'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_dapur' => 'nama SPPG',
            'kode_dapur' => 'kode dapur',
            'id_sppg' => 'ID SPPG',
            'alamat' => 'alamat',
            'kota' => 'kota',
            'provinsi' => 'provinsi',
            'no_telp' => 'nomor telepon',
            'penanggung_jawab' => 'nama kepala SPPG',
            'nama_akuntansi' => 'nama akuntan SPPG',
            'nama_ahli_gizi' => 'ahli gizi',
            'nama_yayasan' => 'nama yayasan',
            'ketua_yayasan' => 'ketua yayasan / yang mewakili',
            'nomor_rekening_va' => 'nomor rekening / VA',
            'tahun_anggaran' => 'tahun anggaran',
            'tempat_pelaporan' => 'tempat pelaporan',
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
