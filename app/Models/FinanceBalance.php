<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceBalance extends Model
{
    protected $fillable = [
        'user_id',
        'total_income',
        'total_expense',
        'balance',
    ];

    protected function casts(): array
    {
        return [
            'total_income'  => 'decimal:2',
            'total_expense' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    // -------------------------
    // Relações
    // -------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}