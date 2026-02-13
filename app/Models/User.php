<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\VerifyEmailCode;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;

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
        'surname',
        'username',
        'email',
        'password',
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    public function sendEmailVerificationNotification(): void
    {
        Log::info('🔵 [EMAIL] Gerando código de verificação', [
            'user_id' => $this->id,
            'email' => $this->email
        ]);

        $code = (string) random_int(100000, 999999);

        Cache::put("verification_code_{$this->id}", $code, now()->addMinutes(15));

        Log::info('✅ [EMAIL] Código armazenado no cache', [
            'user_id' => $this->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(15)->toDateTimeString()
        ]);

        $this->notify(new VerifyEmailCode($code));

        Log::info('📧 [EMAIL] Notification enviada para fila', [
            'user_id' => $this->id,
            'notification' => VerifyEmailCode::class,
            'queue_connection' => config('queue.default')
        ]);
    }
}
