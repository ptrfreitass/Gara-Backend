<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreTransactionRequest;
use App\Http\Resources\Finance\BalanceResource;
use App\Http\Resources\Finance\TransactionResource;
use App\Services\Finance\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceTransactionController extends Controller
{
    public function __construct(protected FinanceService $service) {}

    public function index(Request $request): JsonResponse
    {
        $transactions = $this->service->getTransactions(
            $request->user(),
            $request->only(['type', 'category_id', 'start_date', 'end_date'])
        );

        return response()->json(
            TransactionResource::collection($transactions)->response()->getData(true)
        );
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $transaction = $this->service->createTransaction(
            $request->user(),
            $request->validated()
        );

        return response()->json(new TransactionResource($transaction), 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->deleteTransaction($request->user(), $id);
        return response()->json(['message' => 'Transação removida.']);
    }

    public function balance(Request $request): JsonResponse
    {
        $balance = $this->service->getBalance($request->user());
        return response()->json(new BalanceResource($balance));
    }
}