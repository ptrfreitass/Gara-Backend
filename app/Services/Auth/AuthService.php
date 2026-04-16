<?php
// app/Services/Auth/AuthService.php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Auth\VerificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly VerificationService $verificationService,
    ) {}

    public function register(array $data): User
    {
        $user = User::create([
            'name'      => $data['name'],
            'username'  => $data['username'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'plan_type' => \App\Enums\PlanType::Free,
        ]);

        $this->verificationService->sendCode($user);

        return $user;
    }

    /**
     * Autentica por e-mail ou username via sessão stateful (Sanctum SPA).
     * Não retorna token. A sessão é gerenciada pelo cookie de sessão.
     *
     * @throws ValidationException
     */
    public function login(string $identifier, string $password, bool $remember = false): bool
    {
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (!Auth::attempt([$field => $identifier, 'password' => $password], $remember)) {
            throw ValidationException::withMessages([
                'identifier' => ['Credenciais inválidas.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasVerifiedEmail()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'identifier' => ['E-mail não verificado. Verifique sua caixa de entrada.'],
            ])->status(403);
        }

        return true;
    }

    public function forgotPassword(string $email): void
    {
        Password::sendResetLink(['email' => $email]);
    }

    /**
     * @throws ValidationException
     */
    public function resetPassword(array $data): void
    {
        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'token' => ['Token inválido ou expirado.'],
            ]);
        }
    }
}