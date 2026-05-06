<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\FrontendCatalogPresenter;
use App\Support\LocalizedQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $activeVariantsQuery = ProductVariant::query()
            ->select('product_variants.*')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true)
            ->whereNull('product_variants.deleted_at')
            ->where('product_variants.is_active', true);

        $selectedSizes = collect($request->input('sizes', []))
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->values()
            ->all();

        $selectedColors = collect($request->input('colors', []))
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->values()
            ->all();

        $query = Product::query()
            ->with(['categories', 'images', 'variants'])
            ->where('products.is_active', true);

        if ($request->filled('search')) {
            $search = (string) $request->string('search');

            $query->where(fn (Builder $builder) => $builder
                ->whereRaw(LocalizedQuery::expression('products.name') . ' LIKE ?', ["%{$search}%"])
                ->orWhere('products.slug', 'like', "%{$search}%")
                ->orWhereRaw(LocalizedQuery::expression('products.short_description') . ' LIKE ?', ["%{$search}%"]));
        }

        if ($request->filled('category')) {
            $category = (string) $request->string('category');
            $query->whereHas('categories', fn (Builder $builder) => $builder->where('slug', $category));
        }

        if ($selectedSizes !== [] || $selectedColors !== []) {
            $query->whereHas('variants', function (Builder $builder) use ($selectedSizes, $selectedColors): void {
                $builder->where('is_active', true);

                if ($selectedSizes !== []) {
                    $builder->whereIn('size', $selectedSizes);
                }

                if ($selectedColors !== []) {
                    $builder->whereIn('color', $selectedColors);
                }
            });
        }

        if ($request->filled('min_price')) {
            $minPrice = (float) $request->input('min_price');

            $query->whereHas('variants', fn (Builder $builder) => $builder
                ->where('is_active', true)
                ->where('price', '>=', $minPrice));
        }

        if ($request->filled('max_price')) {
            $maxPrice = (float) $request->input('max_price');

            $query->whereHas('variants', fn (Builder $builder) => $builder
                ->where('is_active', true)
                ->where('price', '<=', $maxPrice));
        }

        match ((string) $request->string('sort', 'featured')) {
            'price-low' => $query->leftJoin('product_variants as default_variants', function ($join): void {
                $join->on('default_variants.product_id', '=', 'products.id')
                    ->whereNull('default_variants.deleted_at')
                    ->where('default_variants.is_active', true)
                    ->where('default_variants.is_default', true);
            })->orderBy('default_variants.price')->select('products.*'),
            'price-high' => $query->leftJoin('product_variants as default_variants', function ($join): void {
                $join->on('default_variants.product_id', '=', 'products.id')
                    ->whereNull('default_variants.deleted_at')
                    ->where('default_variants.is_active', true)
                    ->where('default_variants.is_default', true);
            })->orderByDesc('default_variants.price')->select('products.*'),
            'newest' => $query->latest('products.created_at'),
            default => $query->orderByDesc('products.is_featured')->latest('products.created_at'),
        };

        $products = $query->paginate(12)->withQueryString();
        $products->setCollection(FrontendCatalogPresenter::products($products->getCollection()));

        return view('frontend.catalog.index', [
            'products' => $products,
            'filterCategories' => Category::query()
                ->where('is_active', true)
                ->withCount('products')
                ->orderBy('sort_order')
                ->orderByRaw(LocalizedQuery::expression('name'))
                ->get(),
            'sizeOptions' => (clone $activeVariantsQuery)
                ->select('product_variants.size')
                ->whereNotNull('product_variants.size')
                ->where('product_variants.size', '!=', '')
                ->distinct()
                ->orderBy('product_variants.size')
                ->pluck('product_variants.size'),
            'colorOptions' => (clone $activeVariantsQuery)
                ->select('product_variants.color')
                ->whereNotNull('product_variants.color')
                ->where('product_variants.color', '!=', '')
                ->distinct()
                ->orderBy('product_variants.color')
                ->pluck('product_variants.color'),
            'priceRange' => [
                'min' => (float) ((clone $activeVariantsQuery)->min('product_variants.price') ?? 0),
                'max' => (float) ((clone $activeVariantsQuery)->max('product_variants.price') ?? 0),
            ],
            'selectedCategory' => $request->string('category')->toString(),
            'selectedSort' => $request->string('sort', 'featured')->toString(),
            'searchTerm' => $request->string('search')->toString(),
            'selectedMinPrice' => $request->string('min_price')->toString(),
            'selectedMaxPrice' => $request->string('max_price')->toString(),
            'selectedSizes' => $selectedSizes,
            'selectedColors' => $selectedColors,
        ]);
    }
}
