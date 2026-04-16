<?php
// app/Models/FinanceImportSession.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceImportSession extends Model
{
    protected $fillable = [
        'user_id', 'bank_id', 'filename', 'status',
        'total_rows', 'confirmed_rows', 'skipped_rows',
    ];

    protected function casts(): array
    {
        return [
            'total_rows'     => 'integer',
            'confirmed_rows' => 'integer',
            'skipped_rows'   => 'integer',
        ];
    }

    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function bank(): BelongsTo     { return $this->belongsTo(Bank::class); }
    public function items(): HasMany      { return $this->hasMany(FinanceImportItem::class, 'session_id'); }

    public function pendingItems(): HasMany
    {
        return $this->items()->where('status', 'pending');
    }

    public function isCompleted(): bool   { return $this->status === 'completed'; }
    public function isReviewing(): bool   { return $this->status === 'reviewing'; }
}