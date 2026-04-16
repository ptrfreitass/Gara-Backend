<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreCategoryRequest;
use App\Http\Requests\Finance\StoreSubcategoryRequest;
use App\Http\Resources\Finance\CategoryResource;
use App\Http\Resources\Finance\SubcategoryResource;
use App\Services\Finance\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceCategoryController extends Controller
{
    public function __construct(protected FinanceService $service) {}

    public function index(Request $request): JsonResponse
    {
        $categories = $this->service->getCategories($request->user());
        return response()->json(CategoryResource::collection($categories));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->service->createCategory($request->user(), $request->validated());
        return response()->json(new CategoryResource($category), 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->deleteCategory($request->user(), $id);
        return response()->json(['message' => 'Categoria removida.']);
    }

    // Subcategorias (aninhadas na categoria)

    public function storeSubcategory(StoreSubcategoryRequest $request): JsonResponse
    {
        $sub = $this->service->createSubcategory($request->user(), $request->validated());
        return response()->json(new SubcategoryResource($sub), 201);
    }

    public function destroySubcategory(Request $request, int $id): JsonResponse
    {
        $this->service->deleteSubcategory($request->user(), $id);
        return response()->json(['message' => 'Subcategoria removida.']);
    }
}