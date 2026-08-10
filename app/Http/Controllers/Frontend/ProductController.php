<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\FrontendCatalogPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(string $locale, Product $product): View
    {
        $product->load(['categories', 'images', 'variants']);
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

        return view('frontend.products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function variantOptions(Request $request, string $locale, Product $product): JsonResponse
    {
        abort_unless($product->is_active, 404);

        $filters = $request->validate([
            'color' => ['nullable', 'string', 'max:255'],
            'size' => ['nullable', 'string', 'max:255'],
        ]);

        $color = filled($filters['color'] ?? null) ? $filters['color'] : null;
        $size = filled($filters['size'] ?? null) ? $filters['size'] : null;
        $variants = $product->variants()->orderBy('id')->get();

        $sizes = ($color === null ? $variants : $variants->where('color', $color))
            ->pluck('size')
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values();

        $colorKeys = ($size === null ? $variants : $variants->where('size', $size))
            ->map(fn ($variant) => md5((string) $variant->color))
            ->unique()
            ->values();

        $selectedVariant = $color !== null && $size !== null
            ? $variants->first(fn ($variant) => $variant->color === $color && $variant->size === $size)
            : null;

        return response()->json([
            'sizes' => $sizes,
            'color_keys' => $colorKeys,
            'selected_variant' => $selectedVariant ? [
                'id' => $selectedVariant->id,
                'size' => $selectedVariant->size,
                'color_key' => md5((string) $selectedVariant->color),
                'stock_quantity' => (int) $selectedVariant->stock_quantity,
                'is_active' => (bool) $selectedVariant->is_active,
                'has_box' => (bool) $selectedVariant->has_box,
                'ground_type' => $selectedVariant->ground_type?->label(),
            ] : null,
        ]);
    }
}
