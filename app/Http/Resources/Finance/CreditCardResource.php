<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'last_four_digits' => $this->last_four_digits,
            'credit_limit'     => $this->credit_limit,
            'available_credit' => $this->available_credit,
            'used_credit'      => (float)$this->credit_limit - (float)$this->available_credit,
            'closing_day'      => $this->closing_day,
            'due_day'          => $this->due_day,
            'color'            => $this->color,
            'is_active'        => $this->is_active,
            'bank'             => $this->whenLoaded('bank', fn() => [
                'id'   => $this->bank->id,
                'name' => $this->bank->name,
                'code' => $this->bank->code,
            ]),
            'current_invoice'  => $this->whenLoaded('currentInvoice', fn() => $this->currentInvoice ? [
                'id'              => $this->currentInvoice->id,
                'reference_month' => $this->currentInvoice->reference_month,
                'reference_year'  => $this->currentInvoice->reference_year,
                'total_amount'    => $this->currentInvoice->total_amount,
                'due_date'        => $this->currentInvoice->due_date->toDateString(),
                'status'          => $this->currentInvoice->status,
            ] : null),
            'created_at' => $this->created_at->toDateString(),
        ];
    }
}