<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CheckEmailRequest;
use App\Http\Requests\Auth\CheckUsernameRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendCodeRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyCodeRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthController extends Controller
{
    /**
     * Registra um novo usuário.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        Log::info('🔵 [REGISTER] Iniciando registro de usuário', [
            'email' => $request->email,
            'username' => $request->username
        ]);

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Log::info('✅ [REGISTER] Usuário criado com sucesso', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        event(new Registered($user));

        Log::info('📧 [REGISTER] Evento Registered disparado', [
            'user_id' => $user->id
        ]);

        return response()->json([
            'message' => 'Usuário registrado com sucesso! Verifique seu e-mail.',
            'user' => new UserResource($user)
        ], 201);
    }

    /**
     * Login user and create token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (!Auth::guard('web')->attempt([$fieldType => $request->login, 'password' => $request->password], true)) {
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        $user = Auth::user();

        if (!$user->email_verified_at) {
            Auth::guard('web')->logout();
            return response()->json([
                'message' => 'E-mail não verificado.',
                'email' => $user->email,
                'requires_verification' => true
            ], 403);
        }

        $request->session()->regenerate();

        return response()->json([
            'user' => [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email
            ]
        ], 200);
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Deslogado com sucesso!',
        ], 200);
    }

    /**
     * Get authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * Verificar email
     */
    public function checkEmail(CheckEmailRequest $request): JsonResponse
    {
        $exists = User::where('email', $request->email)->exists();

        if ($exists) {
            return response()->json(['available' => false, 'message' => 'Este e-mail já está cadastrado.'], 409);
        }

        return response()->json(['available' => true, 'message' => 'E-mail disponível!'], 200);
    }

    /**
     * Verificar username
     */
    public function checkUsername(CheckUsernameRequest $request): JsonResponse
    {
        $exists = User::where('username', $request->username)->exists();

        if ($exists) {
            return response()->json(['available' => false, 'message' => 'Este nome de usuário já está em uso.'], 409);
        }

        return response()->json(['available' => true, 'message' => 'Username disponível!'], 200);
    }


    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            // 1. Gera a URL assinada para a rota da API
            $temporaryFullUrl = URL::temporarySignedRoute(
                'password.reset.verify', // Nome da rota no api.php
                Carbon::now()->addMinutes(15),
                ['email' => $user->email]
            );

            // 2. Transforma a URL da API na URL do Angular
            // Ex: De http://api.com/api/reset-password?sig=...
            // Para: http://localhost:4200/recovery?sig=...
            $frontendUrl = "http://localhost:8000/recovery"; // URL do seu Angular
            $urlParts = parse_url($temporaryFullUrl);
            $finalUrl = $frontendUrl . '?' . $urlParts['query'];

            // 3. Envia o e-mail
            $user->notify(new ResetPasswordNotification($finalUrl));

            if (config('app.env') === 'local') {
                return response()->json(['dev_link' => $finalUrl]);
            }
        }

        return response()->json(['message' => 'Link enviado com sucesso.']);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $email = $request->query('email');
        $expires = $request->query('expires');
        $signature = $request->query('signature');

        if (!$email || !$expires || !$signature) {
            return response()->json(['message' => 'Parâmetros de segurança ausentes'], 403);
        }

        if (Carbon::now()->getTimestamp() > $expires) {
            return response()->json(['message' => 'O link de expirou.'], 403);
        }

        $intendedUrl = URL::temporarySignedRoute(
            'password.reset.verify',
            Carbon::createFromTimestamp($expires),
            ['email' => $email]
        );

        parse_str(parse_url($intendedUrl, PHP_URL_QUERY), $query);
        $validSignature = $query['signature'] ?? '';

        if (!hash_equals($validSignature, $signature)) {
            return response()->json(['message' => 'Assinatura inválida. Sem permissão para alterar o e-mail.'], 403);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        $user->password = Hash::make($request->password);

        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
        }

        $user->save();

        return response()->json(['message' => 'Senha alterada com sucesso!']);
    }


    // App/Http/Controllers/Api/Auth/AuthController.php

    /**
     * Reenvia o código de verificação.
     */
    public function resendCode(ResendCodeRequest $request): JsonResponse
    {
        // Busca o usuário pelo e-mail
        $user = User::where('email', $request->email)->first();

        // 3. Segurança: Se o usuário não existe, não confirmamos para evitar "email harvesting"
        // Ou retornamos erro 404 se preferir ser direto.
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        // 4. Verifica se o usuário já está verificado
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Este e-mail já foi verificado.'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Um novo código de verificação foi enviado para o seu e-mail.'
        ], 200);
    }
    /**
     * Verificação do código de usuário
     */
    public function verifyCode(VerifyCodeRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        $storedCode = cache()->get('verification_code_' . $user->id);

        if (!$storedCode || $storedCode != $request->code) {
            return response()->json(['message' => 'Código inválido ou expirado.'], 422);
        }

        $user->markEmailasVerified();
        cache()->forget('verification_code_' . $user->id);

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'E-mail verificado com sucesso!',
            'user' => new UserResource($user)
        ], 200);
    }
}