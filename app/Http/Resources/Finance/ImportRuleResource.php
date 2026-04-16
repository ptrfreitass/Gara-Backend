<?php
// app/Http/Resources/Finance/ImportRuleResource.php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'keyword'                => $this->keyword,
            'type'                   => $this->type,
            'payment_method'         => $this->payment_method,
            'match_count'            => $this->match_count,
            'last_matched_at'        => $this->last_matched_at?->toISOString(),
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
            'created_at'             => $this->created_at->toISOString(),
        ];
    }
}