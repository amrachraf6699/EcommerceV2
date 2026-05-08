<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Http\Resources\Api\V1\ProductVariantResource;
use App\Models\Product;
use App\Support\FrontendCatalogPresenter;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function show(Product $product): JsonResponse
    {
        abort_unless($product->is_active, 404);

        $product->load(['categories', 'images', 'variants.images']);
        $product = FrontendCatalogPresenter::product($product);

        $relatedProductsQuery = Product::query()
            ->with(['categories', 'images', 'variants'])
            ->where('is_active', true)
            ->whereKeyNot($product->id);

        $relatedProducts = FrontendCatalogPresenter::products(
            (clone $relatedProductsQuery)
                ->whereHas('categories', fn ($query) => $query->whereIn('categories.id', $product->categories->pluck('id')))
                ->latest()
                ->limit(4)
                ->get()
        );

        if ($relatedProducts->isEmpty()) {
            $relatedProducts = FrontendCatalogPresenter::products(
                (clone $relatedProductsQuery)
                    ->inRandomOrder()
                    ->limit(4)
                    ->get()
            );
        }

        return response()->json([
            'product' => new ProductResource($product),
            'related_products' => ProductResource::collection($relatedProducts),
        ]);
    }

    public function variants(Product $product): JsonResponse
    {
        abort_unless($product->is_active, 404);
        $product->load(['variants.images']);

        return response()->json([
            'variants' => ProductVariantResource::collection($product->variants->where('is_active', true)->values()),
        ]);
    }
}
