<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreCreditCardRequest;
use App\Http\Resources\Finance\CreditCardResource;
use App\Models\CreditCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreditCardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cards = CreditCard::where('user_id', $request->user()->id)
            ->with(['bank', 'currentInvoice'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json(CreditCardResource::collection($cards));
    }

    public function store(StoreCreditCardRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id']          = $request->user()->id;
        $data['available_credit'] = $data['credit_limit'];

        $card = CreditCard::create($data);
        $card->load(['bank', 'currentInvoice']);

        return response()->json(new CreditCardResource($card), 201);
    }

    public function update(StoreCreditCardRequest $request, int $id): JsonResponse
    {
        $card = CreditCard::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $card->update($request->validated());
        $card->load(['bank', 'currentInvoice']);

        return response()->json(new CreditCardResource($card));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $card = CreditCard::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $card->update(['is_active' => false]);

        return response()->json(['message' => 'Cartão desativado.']);
    }
}