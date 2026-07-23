<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Lang;

class FrontendCatalogPresenter
{
    /**
     * @param EloquentCollection<int, Product> $products
     * @return EloquentCollection<int, Product>
     */
    public static function products(EloquentCollection $products): EloquentCollection
    {
        return $products->map(fn (Product $product) => self::product($product));
    }

    public static function product(Product $product): Product
    {
        $activeVariants = $product->variants->where('is_active', true);
        $isSoldOut = $product->variants->isNotEmpty() && (int) $product->variants->sum('stock_quantity') <= 0;
        $variant = $product->variants
            ->where('is_active', true)
            ->sortByDesc(fn (ProductVariant $variant) => $variant->is_default)
            ->sortBy('price')
            ->first();

        $image = $product->images
            ->sortByDesc('is_primary')
            ->sortBy('sort_order')
            ->first();

        $product->setAttribute('default_variant', $variant);
        $product->setAttribute('primary_image_url', $image ? asset('storage/' . $image->path) : null);
        $product->setAttribute('display_price', $variant?->price);
        $product->setAttribute('display_compare_price', $variant?->compare_at_price);
        $product->setAttribute('display_stock_quantity', (int) $activeVariants->sum('stock_quantity'));
        $product->setAttribute('display_is_sold_out', $isSoldOut);
        $product->setAttribute('display_label', filled($product->label)
            ? $product->label
            : ($product->categories->pluck('name')->first() ?: (string) setting('brand.name', config('app.name'))));
        $product->setAttribute(
            'display_badge',
            $isSoldOut
                ? Lang::get('storefront.badges.sold_out')
                : ($product->is_featured
                    ? Lang::get('storefront.badges.featured')
                    : ($product->created_at?->gt(now()->subDays(14)) ? Lang::get('storefront.badges.new') : null))
        );

        return $product;
    }

    public static function category(Category $category): Category
    {
        $imageUrl = $category->image ? asset('storage/' . $category->image) : null;

        if (! $imageUrl && $category->relationLoaded('products')) {
            $firstProduct = $category->products->first();
            $firstImage = $firstProduct?->images->sortByDesc('is_primary')->sortBy('sort_order')->first();
            $imageUrl = $firstImage ? asset('storage/' . $firstImage->path) : null;
        }

        $category->setAttribute('image_url', $imageUrl);

        return $category;
    }
}
