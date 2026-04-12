<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

abstract class MbgFormRequest extends FormRequest
{
    protected function failedAuthorization(): void
    {
        throw new AuthorizationException('Anda tidak memiliki izin untuk tindakan ini.');
    }
}
