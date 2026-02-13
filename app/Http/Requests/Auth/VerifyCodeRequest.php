<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest para validação de Verificação de Código
 * 
 * @property string $email
 * @property string $code
 */
class VerifyCodeRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para verificação de código.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:30'],
            'code' => ['required', 'string', 'size:6'],
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
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.string' => 'O e-mail deve ser uma string válida.',
            'email.email' => 'O e-mail deve ser um endereço válido.',
            'email.max' => 'O e-mail não pode ter mais de 255 caracteres.',
            
            'code.required' => 'O código de verificação é obrigatório.',
            'code.string' => 'O código deve ser uma string válida.',
            'code.size' => 'O código deve ter exatamente 6 caracteres.',
        ];
    }
}