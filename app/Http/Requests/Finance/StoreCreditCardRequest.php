<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreditCardRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'bank_id'            => 'nullable|exists:banks,id',
            'finance_account_id' => 'nullable|exists:finance_accounts,id',
            'name'               => 'required|string|max:100',
            'last_four_digits'   => 'nullable|string|size:4',
            'credit_limit'       => 'required|numeric|min:0',
            'closing_day'        => 'required|integer|min:1|max:31',
            'due_day'            => 'required|integer|min:1|max:31',
            'color'              => 'nullable|string|max:7',
        ];
    }
}