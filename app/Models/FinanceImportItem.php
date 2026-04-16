<?php
// app/Models/FinanceImportItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceImportItem extends Model
{
    protected $fillable = [
        'session_id', 'user_id',
        'original_description', 'original_amount', 'original_date', 'external_id',
        'detected_type',
        'type', 'category_id', 'subcategory_id',
        'finance_account_id', 'transfer_to_account_id',
        'payment_method', 'description',
        'matched_rule_id', 'status', 'transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'original_amount' => 'decimal:2',
            'original_date'   => 'date',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(FinanceImportSession::class, 'session_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinanceCategory::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(FinanceSubcategory::class, 'subcategory_id');
    }

    public function financeAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function transferToAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'transfer_to_account_id');
    }

    public function matchedRule(): BelongsTo
    {
        return $this->belongsTo(FinanceImportRule::class, 'matched_rule_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class, 'transaction_id');
    }

    // Valor absoluto para exibição
    public function getAbsoluteAmountAttribute(): float
    {
        return abs((float) $this->original_amount);
    }
}