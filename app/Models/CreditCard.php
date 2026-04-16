<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CreditCard extends Model
{
    protected $fillable = [
        'user_id',
        'bank_id',
        'finance_account_id',
        'name',
        'last_four_digits',
        'credit_limit',
        'available_credit',
        'closing_day',
        'due_day',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit'     => 'decimal:2',
            'available_credit' => 'decimal:2',
            'closing_day'      => 'integer',
            'due_day'          => 'integer',
            'is_active'        => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function financeAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(CreditCardInvoice::class);
    }

    public function currentInvoice(): HasOne
    {
        return $this->hasOne(CreditCardInvoice::class)
            ->where('status', 'open')
            ->latestOfMany();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }
}