<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthController extends Controller
{
    //
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        event(new Registered($user));

        return response()->json([
            'message' => 'Usuário registrado com sucesso! Verifique seu e-mail.',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Login user and create token.
     **/
    public function login(Request $request)
    {
        // Validação inicial dos campos
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        // Identifica se o usuário digitou um e-mail ou um username
        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Tenta a autenticação com o campo dinâmico
        if (!Auth::guard('web')->attempt([$fieldType => $request->login, 'password' => $request->password])) {
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        $user = Auth::user();

        // Verificação de E-mail: Se não tiver data de verificação, barramos o acesso
        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'E-mail não verificado.',
                'email' => $user->email,
                'requires_verification' => true
            ], 403); // O Angular usará esse 403 para redirecionar para /verify
        }

        /** @var \App\Models\User $user */
        // Geração do Token (Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
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
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

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
    public function checkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $exists = User::where('email', $request->email)->exists();

        if ($exists) {
            return response()->json(['available' => false, 'message' => 'Este e-mail já está cadastrado.'], 409);
        }

        return response()->json(['available' => true, 'message' => 'E-mail disponível!'], 200);
    }

    /**
     * Verificar username
     */
    public function checkUsername(Request $request)
    {
        $request->validate(['username' => 'required|string']);

        $exists = User::where('username', $request->username)->exists();

        if ($exists) {
            return response()->json(['available' => false, 'message' => 'Este nome de usuário já está em uso.'], 409);
        }

        return response()->json(['available' => true, 'message' => 'Username disponível!'], 200);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
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

    public function resetPassword(Request $request)
    {
        $email = $request->query('email');
        $expires = $request->query('expires');
        $signature = $request->query('signature');

        if (!$email || !$expires || !$signature) {
            return response()->json(['message' => 'Parâmetros de segurança ausentes'], 403);
        }

        // 
        if (Carbon::now()->getTimestamp() > $expires) {
            return response()->json(['message' => 'O link de expirou.'], 403);
        }

        //
        $intendedUrl = URL::temporarySignedRoute(
            'password.reset.verify',
            Carbon::createFromTimestamp($expires),
            ['email' => $email]
        );

        //
        parse_str(parse_url($intendedUrl, PHP_URL_QUERY), $query);
        $validSignature = $query['signature'] ?? '';

        //
        if (!hash_equals($validSignature, $signature)) {
            return response()->json(['message' => 'Assinatura inválida. Sem permissão para alterar o e-mail.'], 403);
        }

        //
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ], [
            'password.confirmed' => 'As senhas não conincidem.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.'
        ]);

        //
        $user = User::where('email', $email)->first();
        
        //
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        // Atualiza a senha
        $user->password = Hash::make($request->password);

        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
        }

        $user->save();

        // Invalida todos os tokens/sessões anteriores
        $user->tokens()->delete();

        return response()->json(['message' => 'Senha alterada com sucesso!']);
    }


    // App/Http/Controllers/Api/Auth/AuthController.php

    public function resendCode(Request $request)
    {
        // 1. Valida se o e-mail foi enviado e se é um formato válido
        $request->validate([
            'email' => 'required|email'
        ]);

        // 2. Busca o usuário pelo e-mail
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
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6'
        ]);

        // Buscar o usuário
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        // Recuperar o código do Cache
        $storedCode = cache()->get('verification_code_' . $user->id);

        if (!$storedCode || $storedCode != $request->code) {
            return response()->json(['message' => 'Código inválido ou expirado.'], 422);
        }

        // Marcar como verificado e limpar o cache
        $user->markEmailasVerified();
        cache()->forget('verification_code_' . $user->id);

        // Logar automáticamente: gerar token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'E-mail verificado com sucesso!',
            'access' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 200);
    }
}
