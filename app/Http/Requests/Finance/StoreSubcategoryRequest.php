<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubcategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name'        => 'required|string|max:100',
            'color'       => 'nullable|string|max:20',

            // Garante que a categoria existe E pertence ao usuário autenticado
            'category_id' => [
                'required',
                "exists:finance_categories,id,user_id,{$userId}",
            ],
        ];
    }
}