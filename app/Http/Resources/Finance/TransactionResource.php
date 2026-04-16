<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'amount'         => $this->amount,
            'description'    => $this->description,
            'date'           => $this->date->toDateString(),
            'type'           => $this->type,
            'payment_method' => $this->payment_method,
            'status'         => $this->status,
            'category'       => new CategoryResource($this->whenLoaded('category')),
            'subcategory'    => new SubcategoryResource($this->whenLoaded('subcategory')),
            'finance_account' => $this->whenLoaded('financeAccount', fn() => $this->financeAccount ? [
                'id'   => $this->financeAccount->id,
                'name' => $this->financeAccount->name,
                'type' => $this->financeAccount->type,
            ] : null),
            'credit_card'    => $this->whenLoaded('creditCard', fn() => $this->creditCard ? [
                'id'   => $this->creditCard->id,
                'name' => $this->creditCard->name,
            ] : null),
            'created_at'     => $this->created_at->toDateString(),
        ];
    }
}