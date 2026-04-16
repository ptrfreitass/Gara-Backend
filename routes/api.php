<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Finance\BankController;
use App\Http\Controllers\Finance\CreditCardController;
use App\Http\Controllers\Finance\FinanceAccountController;
use App\Http\Controllers\Finance\FinanceCategoryController;
use App\Http\Controllers\Finance\FinanceTransactionController;
use App\Http\Controllers\Finance\ImportController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is running',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::get('/health-check', function () {
    $status = [
        'laravel' => true,
        'database' => false,
        'redis' => false,
        'octane' => isset($_SERVER['OCTANE_DATABASE_SESSION_TTL']) || env('OCTANE_SERVER') === 'swoole',
    ];

    try { DB::connection()->getPdo(); $status['database'] = true; } catch (\Exception $e) {}
    try { Redis::ping();              $status['redis']    = true; } catch (\Exception $e) {}

    return response()->json($status);
});

Route::prefix('auth')->group(function () {
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login',    [AuthController::class, 'login']);
        Route::post('/verify-email',    [VerificationController::class, 'verify']);
         Route::post('/resend-verification', [VerificationController::class, 'resend']);
    });

    // Recuperação de senha
    Route::post('forgot-password', [PasswordController::class, 'forgotPassword']);
    Route::post('reset-password',  [PasswordController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me',      [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});


Route::middleware(['auth:sanctum'])->group(function () { 

    // Adicionar dentro do grupo auth:sanctum existente

        Route::prefix('finance/import')->group(function () {
            // Sessões
            Route::post('upload',                [ImportController::class, 'upload']);
            Route::get('sessions',               [ImportController::class, 'sessions']);
            Route::get('sessions/{id}',          [ImportController::class, 'show']);
            Route::post('sessions/{id}/confirm', [ImportController::class, 'confirm']);
            Route::delete('sessions/{id}',       [ImportController::class, 'cancel']);

            // Itens
            Route::patch('items/{id}',           [ImportController::class, 'updateItem']);

            // Regras
            Route::get('rules',                  [ImportController::class, 'rules']);
            Route::patch('rules/{id}',           [ImportController::class, 'updateRule']);
            Route::delete('rules/{id}',          [ImportController::class, 'deleteRule']);
        });  
          
    // ======= FINANÇAS ========
    Route::prefix('finance')->group(function () {
        // Saldo
        Route::get('/balance', [FinanceTransactionController::class, 'balance'])
            ->middleware('capability:finance.view');

        // Categorias
        Route::get('/categories', [FinanceCategoryController::class, 'index'])
            ->middleware('capability:finance.view');

        Route::post('/categories', [FinanceCategoryController::class, 'store'])
            ->middleware('capability:finance.create');

        Route::delete('/categories/{id}', [FinanceCategoryController::class, 'destroy'])
            ->middleware('capability:finance.delete');

        // Subcategorias
        Route::post('/subcategories', [FinanceCategoryController::class, 'storeSubcategory'])
            ->middleware('capability:finance.create');

        Route::delete('/subcategories/{id}', [FinanceCategoryController::class, 'destroySubcategory'])
            ->middleware('capability:finance.delete');

        // Transações
        Route::get('/transactions', [FinanceTransactionController::class, 'index'])
            ->middleware('capability:finance.view');

        Route::post('/transactions', [FinanceTransactionController::class, 'store'])
            ->middleware('capability:finance.create');

        Route::delete('/transactions/{id}', [FinanceTransactionController::class, 'destroy'])
            ->middleware('capability:finance.delete');
        // Bancos (referência — sem capability específica)
        Route::get('/banks', [BankController::class, 'index']);

        // Contas financeiras
        Route::get('/accounts',      [FinanceAccountController::class, 'index'])
            ->middleware('capability:finance.view');
        Route::post('/accounts',     [FinanceAccountController::class, 'store'])
            ->middleware('capability:finance.create');
        Route::put('/accounts/{id}', [FinanceAccountController::class, 'update'])
            ->middleware('capability:finance.create');
        Route::delete('/accounts/{id}', [FinanceAccountController::class, 'destroy'])
            ->middleware('capability:finance.delete');

        // Cartões de crédito
        Route::get('/credit-cards',      [CreditCardController::class, 'index'])
            ->middleware('capability:finance.view');
        Route::post('/credit-cards',     [CreditCardController::class, 'store'])
            ->middleware('capability:finance.create');
        Route::put('/credit-cards/{id}', [CreditCardController::class, 'update'])
            ->middleware('capability:finance.create');
        Route::delete('/credit-cards/{id}', [CreditCardController::class, 'destroy'])
            ->middleware('capability:finance.delete');  
    });
});