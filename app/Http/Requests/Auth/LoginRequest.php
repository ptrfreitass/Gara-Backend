<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest para validação de Login
 * 
 * @property string $login
 * @property string $password
 */
class LoginRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para o login.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Mensagens customizadas de validação.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required' => 'O campo login é obrigatório.',
            'login.string' => 'O login deve ser uma string válida.',
            'password.required' => 'O campo senha é obrigatório.',
            'password.string' => 'A senha deve ser uma string válida.',
        ];
    }
}