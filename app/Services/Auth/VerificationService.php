<?php
// app/Services/Auth/VerificationService.php

namespace App\Services\Auth;

use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Notifications\VerifyEmailCodeNotification;
use Illuminate\Validation\ValidationException;

class VerificationService
{
    // Cooldowns em segundos por número de reenvios
    private const COOLDOWNS = [0 => 60, 1 => 180, 2 => 300];
    private const CODE_TTL_MINUTES = 15;
    private const MAX_RESENDS = 3;

    public function sendCode(User $user): void
    {
        // Remove códigos anteriores
        EmailVerificationCode::where('user_id', $user->id)->delete();

        $code = $this->generateCode();

        EmailVerificationCode::create([
            'user_id'    => $user->id,
            'code'       => $code,
            'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
        ]);

        $user->notify(new VerifyEmailCodeNotification($code));
    }

    public function resendCode(User $user): array
    {
        $record = EmailVerificationCode::where('user_id', $user->id)->latest()->first();

        $resendCount = $record?->resend_count ?? 0;

        if ($resendCount >= self::MAX_RESENDS) {
            throw ValidationException::withMessages([
                'code' => ['Limite de reenvios atingido. Aguarde o código expirar.'],
            ]);
        }

        // Calcula cooldown
        $cooldown = self::COOLDOWNS[$resendCount] ?? 300;

        if ($record && $record->updated_at->diffInSeconds(now()) < $cooldown) {
            $remaining = $cooldown - $record->updated_at->diffInSeconds(now());
            throw ValidationException::withMessages([
                'code' => ["Aguarde {$remaining} segundos para reenviar."],
            ]);
        }

        // Gera novo código mantendo o resend_count
        EmailVerificationCode::where('user_id', $user->id)->delete();

        $code = $this->generateCode();

        EmailVerificationCode::create([
            'user_id'      => $user->id,
            'code'         => $code,
            'resend_count' => $resendCount + 1,
            'expires_at'   => now()->addMinutes(self::CODE_TTL_MINUTES),
        ]);

        $user->notify(new VerifyEmailCodeNotification($code));

        // Retorna próximo cooldown
        $nextResend = $resendCount + 1;
        $nextCooldown = self::COOLDOWNS[$nextResend] ?? null;

        return [
            'resend_count'    => $nextResend,
            'next_cooldown'   => $nextCooldown,
            'resends_left'    => self::MAX_RESENDS - $nextResend,
        ];
    }

    public function verifyCode(User $user, string $code): void
    {
        $record = EmailVerificationCode::where('user_id', $user->id)->latest()->first();

        if (!$record || $record->isExpired()) {
            throw ValidationException::withMessages([
                'code' => ['Código expirado. Solicite um novo.'],
            ]);
        }

        if ($record->isMaxAttemptsReached()) {
            throw ValidationException::withMessages([
                'code' => ['Muitas tentativas incorretas. Solicite um novo código.'],
            ]);
        }

        if (!hash_equals($record->code, strtoupper($code))) {
            $record->increment('attempts');
            $remaining = 5 - $record->attempts;

            throw ValidationException::withMessages([
                'code' => ["Código inválido. {$remaining} tentativa(s) restante(s)."],
            ]);
        }

        // Código correto — marca e-mail como verificado e limpa
        $user->markEmailAsVerified();
        $record->delete();
    }

    private function generateCode(): string
    {
        // 6 caracteres alfanuméricos maiúsculos, sem caracteres confusos (0, O, I, 1)
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        return substr(str_shuffle(str_repeat($chars, 6)), 0, 6);
    }
}