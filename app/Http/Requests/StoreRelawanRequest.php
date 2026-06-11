<?php

namespace App\Http\Requests;

use App\Support\ProfilMbgTenant;
use Illuminate\Validation\Rule;

class StoreRelawanRequest extends MbgFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['super_admin', 'admin_pusat', 'admin']) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $u = $this->user();
        if ($u && $u->hasRole('admin') && ! $u->hasAnyRole(['super_admin', 'admin_pusat'])) {
            $this->merge(['profil_mbg_id' => $u->profil_mbg_id]);
        } elseif ($u && $u->hasAnyRole(['super_admin', 'admin_pusat'])) {
            $this->merge(['profil_mbg_id' => ProfilMbgTenant::id()]);
        }

        foreach (['gaji_pokok', 'gaji_per_hari'] as $field) {
            $raw = $this->input($field);
            if (is_string($raw)) {
                $digits = preg_replace('/\D+/', '', $raw);
                $this->merge([$field => $digits === '' ? '0' : $digits]);
            }
        }
    }

    public function rules(): array
    {
        $u = $this->user();

        $profilRules = [
            'required',
            'integer',
            'exists:profil_mbg,id',
        ];

        if ($u && $u->hasRole('admin') && ! $u->hasAnyRole(['super_admin', 'admin_pusat'])) {
            $profilRules[] = Rule::in([(int) $u->profil_mbg_id]);
        }

        return [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'posisi_relawan_id' => ['required', 'integer', 'exists:posisi_relawan,id'],
            'profil_mbg_id' => $profilRules,
            'jenis_kelamin' => ['required', 'string', Rule::in(['L', 'P'])],
            'gaji_pokok' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'gaji_per_hari' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'status' => ['required', 'string', Rule::in(['aktif', 'nonaktif', 'cuti'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_lengkap' => 'nama lengkap',
            'posisi_relawan_id' => 'posisi',
            'profil_mbg_id' => 'dapur MBG',
            'jenis_kelamin' => 'jenis kelamin',
            'gaji_pokok' => 'gaji pokok',
            'gaji_per_hari' => 'gaji per hari',
        ];
    }
}
