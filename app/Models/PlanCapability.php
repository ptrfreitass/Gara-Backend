<?php

namespace App\Models;

use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanCapability extends Model
{
    protected $table = 'plan_capabilities'; // força o nome correto
    
    protected $fillable = [
        'plan_type',
        'capability_id',
    ];

    protected function casts(): array
    {
        return [
            'plan_type' => PlanType::class,
        ];
    }

    // -------------------------
    // Relações
    // -------------------------

    public function capability(): BelongsTo
    {
        return $this->belongsTo(Capability::class);
    }
}