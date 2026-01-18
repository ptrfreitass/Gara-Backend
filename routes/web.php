<?php

use App\Http\Controllers\requests\auth\authController;
use App\Http\Controllers\verify\EmailVerificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::get('/', function () {
    return view('welcome');
});

// Public routes (Authentication)
Route::prefix('auth')->group(function () {
    Route::post('/register', [authController::class, 'register']);
    Route::post('/login', [authController::class, 'login']);
});

//-- Retorna request para o usuário atráve do sanctum
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//-- 
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

