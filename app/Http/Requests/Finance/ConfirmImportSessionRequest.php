<?php
// app/Http/Requests/Finance/ConfirmImportSessionRequest.php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmImportSessionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // Confirmação não precisa de body — a sessão já tem os itens
        return [];
    }
}