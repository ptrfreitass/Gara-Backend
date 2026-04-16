<?php
// app/Http/Requests/Auth/LoginRequest.php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string'],
            'password'   => ['required', 'string'],
            'remember'   => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.required' => 'Informe seu e-mail ou usuário.',
            'password.required'   => 'A senha é obrigatória.',
        ];
    }
}