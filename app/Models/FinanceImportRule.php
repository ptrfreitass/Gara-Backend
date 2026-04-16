<?php
// app/Models/FinanceImportRule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceImportRule extends Model
{
    protected $fillable = [
        'user_id', 'keyword',
        'type', 'category_id', 'subcategory_id',
        'finance_account_id', 'transfer_to_account_id',
        'payment_method', 'match_count', 'last_matched_at',
    ];

    protected function casts(): array
    {
        return [
            'match_count'     => 'integer',
            'last_matched_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function category(): BelongsTo { return $this->belongsTo(FinanceCategory::class, 'category_id'); }
    public function subcategory(): BelongsTo { return $this->belongsTo(FinanceSubcategory::class, 'subcategory_id'); }
    public function financeAccount(): BelongsTo { return $this->belongsTo(FinanceAccount::class, 'finance_account_id'); }
    public function transferToAccount(): BelongsTo { return $this->belongsTo(FinanceAccount::class, 'transfer_to_account_id'); }

    // Verifica se a keyword bate na descrição (case-insensitive)
    public function matches(string $description): bool
    {
        return str_contains(
            mb_strtolower($description),
            mb_strtolower($this->keyword)
        );
    }
}