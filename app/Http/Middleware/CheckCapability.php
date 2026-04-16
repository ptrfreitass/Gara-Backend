<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCapability
{
    public function handle(Request $request, Closure $next, string $capability): Response
    {
        if (!$request->user() || !$request->user()->hasCapability($capability)) {
            return response()->json([
                'error' => 'Acesso negado',
                'message' => "Seu plano atual não possui a permissão: {$capability}"
            ], 403);
        }

        return $next($request);
    }
}