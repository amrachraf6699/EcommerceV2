<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Slider;
use App\Support\FrontendCatalogPresenter;
use App\Support\LocalizedQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featuredCategories = Category::query()
            ->where('is_active', true)
            ->withCount('products')
            ->with([
                'products' => fn ($query) => $query
                    ->where('is_active', true)
                    ->with(['images', 'variants', 'categories'])
                    ->latest()
                    ->limit(4),
            ])
            ->orderBy('sort_order')
            ->orderByRaw(LocalizedQuery::expression('name'))
            ->limit(6)
            ->get()
            ->map(function (Category $category): Category {
                $category->setRelation('products', FrontendCatalogPresenter::products($category->products));

                return FrontendCatalogPresenter::category($category);
            });

        $homeProductOptions = $this->homeProductOptions();
        $featuredProducts = $this->resolveHomeProducts('featured');

        $newArrivalProducts = FrontendCatalogPresenter::products(
            Product::query()
                ->with(['categories', 'images', 'variants'])
                ->where('is_active', true)
                ->latest()
                ->limit(8)
                ->get()
        );

        return view('frontend.home', [
            'heroSliders' => Slider::query()
                ->where('is_active', true)
                ->latest()
                ->limit(3)
                ->get(),
            'featuredCategories' => $featuredCategories,
            'homeProductOptions' => $homeProductOptions,
            'featuredProducts' => $featuredProducts,
            'newArrivalProducts' => $newArrivalProducts,
            'shopBySizeGroups' => $this->shopBySizeGroups(),
            'clients' => Client::query()
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    public function productsFeed(Request $request, string $locale): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:featured,new,category'],
            'category_id' => ['nullable', 'integer'],
        ]);

        $category = null;

        if ($validated['type'] === 'category') {
            $category = Category::query()
                ->where('is_active', true)
                ->whereKey($validated['category_id'])
                ->firstOrFail();
        }

        $products = $this->resolveHomeProducts($validated['type'], $category);

        return response()->json([
            'html' => view('frontend.partials.product-collection', [
                'products' => $products,
            ])->render(),
            'empty' => $products->isEmpty(),
            'empty_title' => __('storefront.home.no_featured_title', [], $locale),
        ]);
    }

    private function homeProductOptions()
    {
        return collect([
            [
                'type' => 'featured',
                'label' => __('storefront.home.featured_products'),
            ],
            [
                'type' => 'new',
                'label' => __('storefront.home.new_arrivals'),
            ],
        ]);
    }

    private function resolveHomeProducts(string $type, ?Category $category = null)
    {
        $query = Product::query()
            ->with(['categories', 'images', 'variants'])
            ->where('is_active', true);

        if ($type === 'featured') {
            $featuredProducts = FrontendCatalogPresenter::products(
                (clone $query)
                    ->where('is_featured', true)
                    ->latest()
                    ->limit(8)
                    ->get()
            );

            if ($featuredProducts->isNotEmpty()) {
                return $featuredProducts;
            }

            return FrontendCatalogPresenter::products(
                (clone $query)
                    ->inRandomOrder()
                    ->limit(8)
                    ->get()
            );
        }

        if ($type === 'new') {
            return FrontendCatalogPresenter::products(
                (clone $query)
                    ->latest()
                    ->limit(8)
                    ->get()
            );
        }

        return FrontendCatalogPresenter::products(
            $category->products()
                ->with(['categories', 'images', 'variants'])
                ->where('is_active', true)
                ->latest()
                ->limit(8)
                ->get()
        );
    }

    private function shopBySizeGroups()
    {
        $sizes = ProductVariant::query()
            ->select('product_variants.size')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true)
            ->whereNull('product_variants.deleted_at')
            ->where('product_variants.is_active', true)
            ->whereNotNull('product_variants.size')
            ->where('product_variants.size', '!=', '')
            ->distinct()
            ->pluck('product_variants.size')
            ->map(fn ($size) => trim((string) $size))
            ->filter()
            ->unique()
            ->sort(fn (string $first, string $second) => strnatcasecmp($first, $second))
            ->values();

        if ($sizes->isEmpty()) {
            return collect();
        }

        return $sizes->map(function (string $size): array {
            $sizes = [$size];

            return [
                'sizes' => $sizes,
                'from' => $size,
                'to' => $size,
                'url' => route('storefront.catalog', [
                    'locale' => app()->getLocale(),
                    'sizes' => $sizes,
                ]),
            ];
        });
    }
}
