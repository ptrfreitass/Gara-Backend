<?php
// app/Http/Controllers/Finance/ImportController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\ConfirmImportSessionRequest;
use App\Http\Requests\Finance\UpdateImportItemRequest;
use App\Http\Requests\Finance\UploadImportRequest;
use App\Http\Resources\Finance\ImportItemResource;
use App\Http\Resources\Finance\ImportRuleResource;
use App\Http\Resources\Finance\ImportSessionResource;
use App\Services\Finance\ImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function __construct(protected ImportService $importService) {}

    // POST /api/finance/import/upload
    public function upload(UploadImportRequest $request): JsonResponse
    {
        $session = $this->importService->upload(
            $request->user(),
            $request->file('file'),
            $request->integer('bank_id')
        );

        return response()->json([
            'message' => 'Arquivo processado com sucesso.',
            'session' => new ImportSessionResource($session),
        ], 201);
    }

    // GET /api/finance/import/sessions
    public function sessions(Request $request): JsonResponse
    {
        $sessions = $this->importService->getSessions($request->user());

        return response()->json([
            'data' => ImportSessionResource::collection($sessions),
            'meta' => [
                'current_page' => $sessions->currentPage(),
                'last_page'    => $sessions->lastPage(),
                'total'        => $sessions->total(),
            ],
        ]);
    }

    // GET /api/finance/import/sessions/{id}
    public function show(Request $request, int $id): JsonResponse
    {
        $session = $this->importService->getSession($request->user(), $id);

        return response()->json([
            'session' => new ImportSessionResource($session),
        ]);
    }

    // PATCH /api/finance/import/items/{id}
    public function updateItem(UpdateImportItemRequest $request, int $id): JsonResponse
    {
        $item = $this->importService->updateItem(
            $request->user(),
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Item atualizado.',
            'item'    => new ImportItemResource($item),
        ]);
    }

    // POST /api/finance/import/sessions/{id}/confirm
    public function confirm(ConfirmImportSessionRequest $request, int $id): JsonResponse
    {
        $session = $this->importService->confirmSession($request->user(), $id);

        return response()->json([
            'message' => "Importação concluída. {$session->confirmed_rows} transações criadas.",
            'session' => new ImportSessionResource($session),
        ]);
    }

    // DELETE /api/finance/import/sessions/{id}
    public function cancel(Request $request, int $id): JsonResponse
    {
        $this->importService->cancelSession($request->user(), $id);

        return response()->json(['message' => 'Importação cancelada.']);
    }

    // GET /api/finance/import/rules
    public function rules(Request $request): JsonResponse
    {
        $rules = $this->importService->getRules($request->user());

        return response()->json([
            'data' => ImportRuleResource::collection($rules),
        ]);
    }

    // PATCH /api/finance/import/rules/{id}
    public function updateRule(Request $request, int $id): JsonResponse
    {
        $rule = $this->importService->updateRule(
            $request->user(),
            $id,
            $request->validate([
                'keyword'                => 'sometimes|string|max:255',
                'type'                   => 'sometimes|in:income,expense,transfer',
                'category_id'            => 'nullable|integer|exists:finance_categories,id',
                'subcategory_id'         => 'nullable|integer|exists:finance_subcategories,id',
                'finance_account_id'     => 'nullable|integer|exists:finance_accounts,id',
                'transfer_to_account_id' => 'nullable|integer|exists:finance_accounts,id',
                'payment_method'         => 'nullable|in:cash,pix,debit,credit,ted,boleto,transfer,other',
            ])
        );

        return response()->json([
            'message' => 'Regra atualizada.',
            'rule'    => new ImportRuleResource($rule),
        ]);
    }

    // DELETE /api/finance/import/rules/{id}
    public function deleteRule(Request $request, int $id): JsonResponse
    {
        $this->importService->deleteRule($request->user(), $id);

        return response()->json(['message' => 'Regra removida.']);
    }
}