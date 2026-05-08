<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Category;
use App\Support\FrontendCatalogPresenter;
use App\Support\LocalizedQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderByRaw(LocalizedQuery::expression('name'))
            ->get()
            ->map(fn (Category $category): Category => FrontendCatalogPresenter::category($category));

        return response()->json([
            'categories' => CategoryResource::collection($categories),
        ]);
    }

    public function show(Request $request, Category $category): JsonResponse
    {
        abort_unless($category->is_active, 404);

        $category = FrontendCatalogPresenter::category($category);
        $products = $category->products()
            ->where('is_active', true)
            ->with(['categories', 'images', 'variants'])
            ->latest()
            ->paginate((int) $request->integer('per_page', 12))
            ->withQueryString();

        $products->setCollection(FrontendCatalogPresenter::products($products->getCollection()));

        return response()->json([
            'category' => new CategoryResource($category),
            'products' => ProductResource::collection($products),
        ]);
    }
}
