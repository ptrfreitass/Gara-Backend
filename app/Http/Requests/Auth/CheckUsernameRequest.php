<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest para validação de verificação de username
 * 
 * @property string $username
 */
class CheckUsernameRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para verificação de username.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255'],
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
            'username.required' => 'O campo username é obrigatório.',
            'username.string' => 'O username deve ser uma string válida.',
            'username.max' => 'O username não pode ter mais de 255 caracteres.',
        ];
    }
}