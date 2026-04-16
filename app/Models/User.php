<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'plan_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'plan_type' => 'free',
    ];

    protected $casts = [
        'plan_type' => \App\Enums\PlanType::class,
    ];
    
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'plan_type'         => PlanType::class, // Importante para o Laravel converter a string do banco no Enum
        ];
    }

    // -------------------------
    // Relações
    // -------------------------
    
    public function emailVerificationCodes(): HasMany
    {
        return $this->hasMany(EmailVerificationCode::class, 'user_id', 'id');
    }

    public function financeCategories(): HasMany
    {
        return $this->hasMany(FinanceCategory::class);
    }

    public function financeTransactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class);
    }

    public function financeBalance(): HasOne
    {
        return $this->hasOne(FinanceBalance::class);
    }

    /// -------------------------
    // Capabilities / Plano
    // -------------------------

    /**
     * Busca e cacheia as capabilities do plano do usuário.
     * Fonte única de verdade — usada internamente.
     */
    private function getCapabilities(): array
    {
        return cache()->remember(
            "user_capabilities_{$this->id}",
            now()->addMinutes(30),
            fn() => PlanCapability::where('plan_type', $this->plan_type->value)
                ->join('capabilities', 'plan_capabilities.capability_id', '=', 'capabilities.id')
                ->pluck('capabilities.name')
                ->toArray()
        );
    }

    /**
     * Retorna todas as capabilities do usuário (usado no UserResource).
     */
    public function capabilities(): array
    {
        return $this->getCapabilities();
    }

    /**
     * Verifica se o usuário tem uma capability específica (usado no Middleware).
     */
    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->getCapabilities());
    }

    /**
     * Limpa o cache de capabilities (chamar após mudança de plano).
     */
    public function clearCapabilitiesCache(): void
    {
        cache()->forget("user_capabilities_{$this->id}");
    }
}
