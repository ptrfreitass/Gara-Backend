<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditCardInvoice extends Model
{
    protected $fillable = [
        'credit_card_id',
        'reference_month',
        'reference_year',
        'opening_date',
        'closing_date',
        'due_date',
        'total_amount',
        'paid_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'opening_date' => 'date',
            'closing_date' => 'date',
            'due_date'     => 'date',
            'total_amount' => 'decimal:2',
            'paid_amount'  => 'decimal:2',
        ];
    }

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class);
    }
}