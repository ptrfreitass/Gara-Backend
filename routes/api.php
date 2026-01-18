<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ROTAS PÚBLICAS 
// Registro do usuário
Route::post('/register', [AuthController::class, 'register']);
// Login do usuário
Route::post('/login', [AuthController::class, 'login']);
// Verificando email
Route::post('/check-email', [AuthController::class, 'checkEmail']);
// Verificando nome de usuário
Route::post('/check-username', [AuthController::class, 'checkUsername']);
// Verificação de código por email
Route::post('/verify-email', [AuthController::class, 'verifyCode']);
// Reenvio do código de verificação
Route::post('/resend-code', [AuthController::class, 'resendCode']);
// Rota para envio do link
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
// Nova senha
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset.verify');

// ROTAS PROTEGIDAS 
Route::middleware('auth:sanctum')->group(function () {
    // Deslogar usuário
    Route::post('/logout', [AuthController::class, 'logout']);



});