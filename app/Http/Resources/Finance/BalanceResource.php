<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BalanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'balance'    => $this->balance,
            'total_income' => $this->total_income,
            'total_expense' => $this->total_expense,
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
