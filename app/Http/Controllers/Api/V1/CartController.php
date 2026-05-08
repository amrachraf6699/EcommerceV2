<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CartResource;
use App\Support\CustomerCartManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CustomerCartManager $cartManager)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $cart = $this->cartManager->resolveCartForCustomer($request->user());
        $cart = $cart->fresh(['items.product.categories', 'items.product.images', 'items.product.variants', 'items.variant']);

        return response()->json([
            'cart' => new CartResource($cart),
        ]);
    }

    public function storeItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->cartManager->addItem(
            $request->user(),
            (int) $validated['product_id'],
            (int) $validated['product_variant_id'],
            (int) $validated['quantity'],
        );

        return response()->json([
            'message' => __('storefront.product.added_to_cart'),
            'cart' => new CartResource($cart),
        ], 201);
    }

    public function updateItem(Request $request, int $item): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->cartManager->updateItem($request->user(), $item, (int) $validated['quantity']);

        return response()->json([
            'message' => __('storefront.cart_updated'),
            'cart' => new CartResource($cart),
        ]);
    }

    public function destroyItem(Request $request, int $item): JsonResponse
    {
        $cart = $this->cartManager->removeItem($request->user(), $item);

        return response()->json([
            'message' => __('storefront.cart_item_removed'),
            'cart' => new CartResource($cart),
        ]);
    }
}
