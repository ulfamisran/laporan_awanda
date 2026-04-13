<?php

namespace App\Http\Requests;

use App\Support\ProfilMbgTenant;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UpdateRelawanRequest extends MbgFormRequest
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

        foreach (['tanggal_lahir', 'tanggal_bergabung'] as $field) {
            $raw = $this->input($field);
            if (is_string($raw) && $raw !== '') {
                try {
                    $this->merge([$field => Carbon::createFromFormat('d/m/Y', $raw)->format('Y-m-d')]);
                } catch (\Throwable) {
                    //
                }
            }
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
        $relawanId = $this->route('relawan')?->getKey();

        $profilRules = [
            'required',
            'integer',
            'exists:profil_mbg,id',
        ];

        if ($u && $u->hasRole('admin') && ! $u->hasAnyRole(['super_admin', 'admin_pusat'])) {
            $profilRules[] = Rule::in([(int) $u->profil_mbg_id]);
        }

        $gajiRules = $u?->hasRole('super_admin')
            ? ['required', 'numeric', 'min:0', 'max:9999999999999.99']
            : ['prohibited'];

        return [
            'nik' => ['required', 'digits:16', Rule::unique('relawans', 'nik')->ignore($relawanId)],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'posisi_relawan_id' => ['required', 'integer', 'exists:posisi_relawan,id'],
            'profil_mbg_id' => $profilRules,
            'jenis_kelamin' => ['required', 'string', Rule::in(['L', 'P'])],
            'no_hp' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255'],
            'alamat' => ['required', 'string', 'max:5000'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'tanggal_bergabung' => ['required', 'date'],
            'foto' => ['nullable', 'image', 'max:4096'],
            'gaji_pokok' => $gajiRules,
            'gaji_per_hari' => $gajiRules,
            'status' => ['required', 'string', Rule::in(['aktif', 'nonaktif', 'cuti'])],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'crop_x' => ['nullable', 'integer', 'min:0'],
            'crop_y' => ['nullable', 'integer', 'min:0'],
            'crop_w' => ['nullable', 'integer', 'min:1'],
            'crop_h' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nik' => 'NIK',
            'nama_lengkap' => 'nama lengkap',
            'posisi_relawan_id' => 'posisi',
            'profil_mbg_id' => 'dapur MBG',
            'jenis_kelamin' => 'jenis kelamin',
            'no_hp' => 'nomor HP',
            'tanggal_lahir' => 'tanggal lahir',
            'tanggal_bergabung' => 'tanggal bergabung',
            'gaji_pokok' => 'gaji pokok',
            'gaji_per_hari' => 'gaji per hari',
        ];
    }
}
