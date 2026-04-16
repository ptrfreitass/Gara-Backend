<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinanceAccountRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'bank_id'         => 'nullable|exists:banks,id',
            'name'            => 'required|string|max:100',
            'type'            => 'required|in:cash,checking,savings,investment,digital_wallet,other',
            'initial_balance' => 'required|numeric|min:0',
            'currency'        => 'nullable|string|size:3',
            'color'           => 'nullable|string|max:7',
            'icon'            => 'nullable|string|max:50',
        ];
    }
}