<?php
// app/Http/Controllers/Auth/PasswordController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\AuthService;

class PasswordController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    /**
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->validated('email'));

        return response()->json([
            'message' => 'Se este e-mail estiver cadastrado, você receberá as instruções em breve.',
        ]);
    }

    /**
     * POST /api/auth/reset-password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword($request->validated());

        return response()->json(['message' => 'Senha redefinida com sucesso.']);
    }
}    