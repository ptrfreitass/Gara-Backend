<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreFinanceAccountRequest;
use App\Http\Resources\Finance\FinanceAccountResource;
use App\Models\FinanceAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $accounts = FinanceAccount::where('user_id', $request->user()->id)
            ->with('bank')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json(FinanceAccountResource::collection($accounts));
    }

    public function store(StoreFinanceAccountRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id']         = $request->user()->id;
        $data['current_balance'] = $data['initial_balance'];

        $account = FinanceAccount::create($data);
        $account->load('bank');

        return response()->json(new FinanceAccountResource($account), 201);
    }

    public function update(StoreFinanceAccountRequest $request, int $id): JsonResponse
    {
        $account = FinanceAccount::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $account->update($request->validated());
        $account->load('bank');

        return response()->json(new FinanceAccountResource($account));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $account = FinanceAccount::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Soft delete — desativa ao invés de deletar
        $account->update(['is_active' => false]);

        return response()->json(['message' => 'Conta desativada.']);
    }
}