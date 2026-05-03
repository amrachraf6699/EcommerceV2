<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\FrontendCatalogPresenter;
use App\Support\LocalizedQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderByRaw(LocalizedQuery::expression('name'))
            ->get()
            ->map(fn (Category $category): Category => FrontendCatalogPresenter::category($category));

        $featuredCategories = $categories->take(6);

        return view('frontend.categories.index', [
            'categories' => $categories,
            'featuredCategories' => $featuredCategories,
        ]);
    }

    public function show(string $locale, Category $category): View
    {
        $category = FrontendCatalogPresenter::category($category);
        $products = $category->products()
            ->where('is_active', true)
            ->with(['categories', 'images', 'variants'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $products->setCollection(FrontendCatalogPresenter::products($products->getCollection()));

        $alternativeCategory = Category::query()
            ->where('is_active', true)
            ->whereKeyNot($category->id)
            ->whereHas('products', fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->orderByRaw(LocalizedQuery::expression('name'))
            ->first();

        return view('frontend.categories.show', [
            'category' => $category,
            'products' => $products,
            'alternativeCategory' => $alternativeCategory ? FrontendCatalogPresenter::category($alternativeCategory) : null,
        ]);
    }

    public function fallbackProducts(string $locale, Category $category, Request $request): JsonResponse
    {
        $products = Product::query()
            ->where('is_active', true)
            ->whereDoesntHave('categories', fn ($query) => $query->whereKey($category->id))
            ->with(['categories', 'images', 'variants'])
            ->latest()
            ->limit(8)
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'html' => null,
                'message' => __('storefront.category_show.no_products_hint'),
            ], 404);
        }

        $products = FrontendCatalogPresenter::products($products);

        return response()->json([
            'html' => view('frontend.partials.product-collection', [
                'products' => $products,
                'showOverlay' => false,
            ])->render(),
            'title' => __('storefront.category_show.loaded_products_title'),
        ]);
    }
}
