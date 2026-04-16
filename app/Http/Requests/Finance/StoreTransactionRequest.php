<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category_id'        => 'required|exists:finance_categories,id',
            'subcategory_id'     => 'nullable|exists:finance_subcategories,id',
            'finance_account_id' => 'nullable|exists:finance_accounts,id',
            'credit_card_id'     => 'nullable|exists:credit_cards,id',
            'amount'             => 'required|numeric|min:0.01',
            'description'        => 'nullable|string|max:255',
            'date'               => 'required|date',
            'type'               => 'required|in:income,expense',
            'payment_method'     => 'nullable|in:cash,pix,debit,credit,ted,boleto,other',
            'status'             => 'nullable|in:pending,completed,cancelled',
        ];
    }
}