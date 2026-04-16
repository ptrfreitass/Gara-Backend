<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Capability extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    // -------------------------
    // Relações
    // -------------------------

    public function planCapabilities(): HasMany
    {
        return $this->hasMany(PlanCapability::class);
    }
}