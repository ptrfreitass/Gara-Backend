<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceCategory extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'color',
    ];

    // -------------------------
    // Relações
    // -------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(FinanceSubcategory::class, 'category_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class, 'category_id');
    }
}