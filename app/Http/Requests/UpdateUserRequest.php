<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends MbgFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->getKey();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => ['nullable', 'string', Password::min(8), 'confirmed'],
            'profil_mbg_id' => [
                'nullable',
                'integer',
                'exists:profil_mbg,id',
                Rule::requiredIf(fn () => $this->input('role') === 'admin'),
            ],
            'role' => ['required', 'string', Rule::in(['super_admin', 'admin_pusat', 'admin'])],
            'foto' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', 'string', Rule::in(['aktif', 'nonaktif'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'email',
            'password' => 'kata sandi',
            'profil_mbg_id' => 'profil MBG',
            'role' => 'peran',
            'foto' => 'foto',
            'status' => 'status',
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Peran yang dipilih tidak valid.',
            'status.in' => 'Status harus aktif atau nonaktif.',
        ];
    }
}
