<?php
// app/Http/Requests/Finance/UpdateImportItemRequest.php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImportItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type'                   => 'sometimes|in:income,expense,transfer',
            'category_id'            => 'nullable|integer|exists:finance_categories,id',
            'subcategory_id'         => 'nullable|integer|exists:finance_subcategories,id',
            'finance_account_id'     => 'nullable|integer|exists:finance_accounts,id',
            'transfer_to_account_id' => 'nullable|integer|exists:finance_accounts,id',
            'payment_method'         => 'nullable|in:cash,pix,debit,credit,ted,boleto,transfer,other',
            'description'            => 'nullable|string|max:255',
            'status'                 => 'sometimes|in:pending,skipped',

            // "Lembrar"
            'remember'               => 'sometimes|boolean',
            'keyword'                => 'required_if:remember,true|nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'keyword.required_if' => 'Informe a palavra-chave para salvar a regra.',
        ];
    }
}