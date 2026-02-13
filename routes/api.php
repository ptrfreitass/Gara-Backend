<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;


// Teste
Route::get('/health', fn () => response()->json(['ok' => true]));

Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'me']);

// ROTAS PÚBLICAS
// Registro do usuário
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1');
// Login do usuário
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');
// Verificando email
Route::post('/check-email', [AuthController::class, 'checkEmail'])
    ->middleware('throttle:10,1');
// Verificando nome de usuário
Route::post('/check-username', [AuthController::class, 'checkUsername'])
    ->middleware('throttle:10,1');
// Verificação de código por email
Route::post('/verify-email', [AuthController::class, 'verifyCode'])
    ->middleware('throttle:5,1');
// Reenvio do código de verificação
Route::post('/resend-code', [AuthController::class, 'resendCode'])
    ->middleware('throttle:3,1');
// Rota para envio do link
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
    ->middleware('throttle:3,1');
// Nova senha
Route::post('/reset-password', [AuthController::class, 'resetPassword'])
    ->name('password.reset.verify')
    ->middleware('throttle:5,1');

//Route::post('/importar-nota', [NotaFiscal::class, 'store']);

// ROTAS PROTEGIDAS
Route::middleware('auth:sanctum')->group(function () {
    // Deslogar usuário
    Route::post('/logout', [AuthController::class, 'logout']);
});