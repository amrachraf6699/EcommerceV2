<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\FrontendCatalogPresenter;
use Illuminate\Contracts\View\View;

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
}
