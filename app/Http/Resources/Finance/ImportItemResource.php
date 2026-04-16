<?php
// app/Http/Resources/Finance/ImportItemResource.php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'original_description'   => $this->original_description,
            'original_amount'        => (float) $this->original_amount,
            'absolute_amount'        => $this->absolute_amount,
            'original_date'          => $this->original_date->format('Y-m-d'),
            'external_id'            => $this->external_id,
            'detected_type'          => $this->detected_type,

            // Campos editáveis
            'type'                   => $this->type,
            'description'            => $this->description,
            'payment_method'         => $this->payment_method,
            'status'                 => $this->status,

            // Relações
            'category'               => $this->whenLoaded('category', fn() => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'color'=> $this->category->color,
            ]),
            'subcategory'            => $this->whenLoaded('subcategory', fn() => [
                'id'   => $this->subcategory->id,
                'name' => $this->subcategory->name,
            ]),
            'finance_account'        => $this->whenLoaded('financeAccount', fn() => [
                'id'   => $this->financeAccount->id,
                'name' => $this->financeAccount->name,
            ]),
            'transfer_to_account'    => $this->whenLoaded('transferToAccount', fn() => [
                'id'   => $this->transferToAccount->id,
                'name' => $this->transferToAccount->name,
            ]),
            'matched_rule'           => $this->whenLoaded('matchedRule', fn() => [
                'id'      => $this->matchedRule->id,
                'keyword' => $this->matchedRule->keyword,
            ]),

            'transaction_id'         => $this->transaction_id,
        ];
    }
}