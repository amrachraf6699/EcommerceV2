<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\FrontendCartManager;
use App\Support\FrontendCatalogPresenter;
use App\Support\LocalizedQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function categories(string $locale): JsonResponse
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount([
                'products as active_products_count' => fn ($query) => $query->where('is_active', true),
            ])
            ->orderBy('sort_order')
            ->orderByRaw(LocalizedQuery::expression('name'))
            ->get()
            ->map(function (Category $category): array {
                $category = FrontendCatalogPresenter::category($category);

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image_url' => $category->image_url,
                    'products_count' => (int) $category->active_products_count,
                ];
            })
            ->values();

        return response()->json([
            'prompt' => __('storefront.chatbot.prompts.categories', [], $locale),
            'categories' => $categories,
        ]);
    }

    public function categoryProducts(string $locale, Category $category): JsonResponse
    {
        $category = FrontendCatalogPresenter::category($category);
        $products = FrontendCatalogPresenter::products(
            $category->products()
                ->where('is_active', true)
                ->with(['categories', 'images', 'variants'])
                ->latest()
                ->limit(8)
                ->get()
        );

        if ($products->isEmpty()) {
            return response()->json([
                'prompt' => __('storefront.chatbot.prompts.no_products', ['category' => $category->name], $locale),
                'state' => 'empty',
                'actions' => [
                    [
                        'action' => 'load_fallback_products',
                        'label' => __('storefront.chatbot.actions.load_fallback_products', [], $locale),
                    ],
                    [
                        'action' => 'select_another_category',
                        'label' => __('storefront.chatbot.actions.select_another_category', [], $locale),
                    ],
                ],
                'products' => [],
            ]);
        }

        return response()->json([
            'prompt' => __('storefront.chatbot.prompts.products', ['category' => $category->name], $locale),
            'state' => 'products',
            'products' => $products->map(fn (Product $product): array => $this->productPayload($product))->values(),
        ]);
    }

    public function fallbackProducts(string $locale, Category $category): JsonResponse
    {
        $products = FrontendCatalogPresenter::products(
            Product::query()
                ->where('is_active', true)
                ->whereDoesntHave('categories', fn ($query) => $query->whereKey($category->id))
                ->with(['categories', 'images', 'variants'])
                ->orderByDesc('is_featured')
                ->latest()
                ->limit(8)
                ->get()
        );

        return response()->json([
            'prompt' => $products->isEmpty()
                ? __('storefront.chatbot.prompts.no_fallback_products', [], $locale)
                : __('storefront.chatbot.prompts.fallback_products', [], $locale),
            'state' => $products->isEmpty() ? 'empty' : 'products',
            'products' => $products->map(fn (Product $product): array => $this->productPayload($product))->values(),
            'actions' => $products->isEmpty() ? [[
                'action' => 'select_another_category',
                'label' => __('storefront.chatbot.actions.select_another_category', [], $locale),
            ]] : [],
        ]);
    }

    public function productVariants(string $locale, Product $product): JsonResponse
    {
        $product->loadMissing(['categories', 'images', 'variants']);
        $product = FrontendCatalogPresenter::product($product);

        $variants = $product->variants
            ->where('is_active', true)
            ->filter(fn ($variant) => (int) $variant->stock_quantity > 0)
            ->sortByDesc('is_default')
            ->values()
            ->map(fn ($variant): array => [
                'id' => $variant->id,
                'name' => $variant->display_name,
                'price_label' => storefront_format_money((float) $variant->price, 'BHD'),
                'stock_quantity' => (int) $variant->stock_quantity,
            ])
            ->values();

        if ($variants->isEmpty()) {
            return response()->json([
                'prompt' => __('storefront.chatbot.prompts.no_variants', ['product' => $product->name], $locale),
                'state' => 'empty',
                'actions' => [[
                    'action' => 'back_to_products',
                    'label' => __('storefront.chatbot.actions.back_to_products', [], $locale),
                ]],
                'variants' => [],
            ]);
        }

        return response()->json([
            'prompt' => __('storefront.chatbot.prompts.variants', ['product' => $product->name], $locale),
            'state' => 'variants',
            'product' => $this->productPayload($product),
            'variants' => $variants,
        ]);
    }

    public function storeCartItem(Request $request, string $locale, FrontendCartManager $cartManager): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $summary = $cartManager->addItem(
            $request,
            (int) $validated['product_id'],
            (int) $validated['product_variant_id'],
            (int) $validated['quantity'],
        );

        return response()->json([
            'message' => __('storefront.chatbot.success.added_to_cart', [], $locale),
            'cart' => $summary,
            'actions' => [
                [
                    'action' => 'checkout',
                    'label' => __('storefront.chatbot.actions.checkout', [], $locale),
                ],
                [
                    'action' => 'add_more_products',
                    'label' => __('storefront.chatbot.actions.add_more_products', [], $locale),
                ],
                [
                    'action' => 'close_chat',
                    'label' => __('storefront.chatbot.actions.close_chat', [], $locale),
                ],
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function productPayload(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image_url' => $product->primary_image_url,
            'price_label' => $product->display_price !== null
                ? storefront_format_money((float) $product->display_price, 'BHD')
                : __('storefront.common.price_unavailable'),
            'badge' => $product->display_badge,
        ];
    }
}
