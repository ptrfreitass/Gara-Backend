<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'type'            => $this->type,
            'initial_balance' => $this->initial_balance,
            'current_balance' => $this->current_balance,
            'currency'        => $this->currency,
            'color'           => $this->color,
            'icon'            => $this->icon,
            'is_active'       => $this->is_active,
            'bank'            => $this->whenLoaded('bank', fn() => [
                'id'   => $this->bank->id,
                'name' => $this->bank->name,
                'code' => $this->bank->code,
            ]),
            'created_at' => $this->created_at->toDateString(),
        ];
    }
}