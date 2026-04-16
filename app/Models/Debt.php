<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debt extends Model
{
    protected $fillable = [
        'user_id',
        'finance_account_id',
        'credit_card_id',
        'creditor_name',
        'description',
        'type',
        'payment_method',
        'original_amount',
        'remaining_amount',
        'interest_rate',
        'total_installments',
        'paid_installments',
        'start_date',
        'due_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'original_amount'   => 'decimal:2',
            'remaining_amount'  => 'decimal:2',
            'interest_rate'     => 'decimal:4',
            'start_date'        => 'date',
            'due_date'          => 'date',
            'total_installments' => 'integer',
            'paid_installments'  => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function financeAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class);
    }

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }
}