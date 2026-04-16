<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\JsonResponse;

class BankController extends Controller
{   

    // Rota pública dentro do contexto autenticado — apenas listagem
    public function index(): JsonResponse
    {
        $banks = Bank::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'logo_url']);
        
        return response()->json($banks);
    }
}