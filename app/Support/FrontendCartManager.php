<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FrontendCartManager
{
    public function cartForSession(string $sessionId): ?Cart
    {
        return Cart::query()
            ->with([
                'items.product.images',
                'items.variant',
            ])
            ->where('session_id', $sessionId)
            ->first();
    }

    /**
     * @return array{items_count:int, subtotal:float, currency:string}
     */
    public function summaryForSession(string $sessionId): array
    {
        return FrontendTemplateData::cartSummary($this->cartForSession($sessionId));
    }

    /**
     * @return array{items_count:int, subtotal:float, currency:string}
     */
    public function addItem(Request $request, int $productId, int $variantId, int $quantity): array
    {
        $variant = ProductVariant::query()
            ->with('product')
            ->find($variantId);

        if (! $variant || $variant->product_id !== $productId) {
            throw ValidationException::withMessages([
                'product_variant_id' => __('storefront.cart_invalid_variant'),
            ]);
        }

        if (! $variant->product || ! $variant->product->is_active || ! $variant->is_active) {
            throw ValidationException::withMessages([
                'product_variant_id' => __('storefront.cart_variant_unavailable'),
            ]);
        }

        $stockQuantity = (int) $variant->stock_quantity;

        if ($stockQuantity <= 0) {
            throw ValidationException::withMessages([
                'product_variant_id' => __('storefront.product.out_of_stock'),
            ]);
        }

        $sessionId = $this->startSession($request);

        return DB::transaction(function () use ($productId, $quantity, $sessionId, $stockQuantity, $variant): array {
            $cart = Cart::query()->firstOrCreate(
                ['session_id' => $sessionId],
                [
                    'currency' => 'BHD',
                    'item_count' => 0,
                    'subtotal' => 0,
                ],
            );

            $item = $cart->items()
                ->where('product_variant_id', $variant->id)
                ->first();

            $existingQuantity = (int) ($item?->quantity ?? 0);
            $newQuantity = $existingQuantity + $quantity;

            if ($newQuantity > $stockQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => __('storefront.cart_quantity_exceeded', ['count' => $stockQuantity]),
                ]);
            }

            $unitPrice = (float) $variant->price;
            $variantName = $variant->display_name;

            $cart->items()->updateOrCreate(
                ['product_variant_id' => $variant->id],
                [
                    'product_id' => $productId,
                    'product_name' => $variant->product->name,
                    'variant_name' => $variantName,
                    'sku' => null,
                    'unit_price' => $unitPrice,
                    'quantity' => $newQuantity,
                    'line_total' => $unitPrice * $newQuantity,
                ],
            );

            return FrontendTemplateData::cartSummary($this->refreshCart($cart));
        });
    }

    /**
     * @return array{items_count:int, subtotal:float, currency:string}
     */
    public function updateItem(Request $request, int $itemId, int $quantity): array
    {
        $sessionId = $this->startSession($request);

        return DB::transaction(function () use ($itemId, $quantity, $sessionId): array {
            $item = $this->resolveCartItem($sessionId, $itemId);
            $variant = ProductVariant::query()->with('product')->find($item->product_variant_id);

            if (! $variant || ! $variant->product || ! $variant->product->is_active || ! $variant->is_active) {
                throw ValidationException::withMessages([
                    'quantity' => __('storefront.cart_variant_unavailable'),
                ]);
            }

            $stockQuantity = (int) $variant->stock_quantity;

            if ($stockQuantity <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => __('storefront.product.out_of_stock'),
                ]);
            }

            if ($quantity > $stockQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => __('storefront.cart_quantity_exceeded', ['count' => $stockQuantity]),
                ]);
            }

            $item->update([
                'quantity' => $quantity,
                'unit_price' => (float) $variant->price,
                'line_total' => (float) $variant->price * $quantity,
            ]);

            return FrontendTemplateData::cartSummary($this->refreshCart($item->cart));
        });
    }

    /**
     * @return array{items_count:int, subtotal:float, currency:string}
     */
    public function removeItem(Request $request, int $itemId): array
    {
        $sessionId = $this->startSession($request);

        return DB::transaction(function () use ($itemId, $sessionId): array {
            $item = $this->resolveCartItem($sessionId, $itemId);
            $cart = $item->cart;

            $item->delete();

            return FrontendTemplateData::cartSummary($this->refreshCart($cart));
        });
    }

    private function startSession(Request $request): string
    {
        $session = $request->session();

        if (! $session->isStarted()) {
            $session->start();
        }

        $session->put('storefront_cart_session', true);

        return $session->getId();
    }

    private function resolveCartItem(string $sessionId, int $itemId): CartItem
    {
        $item = CartItem::query()
            ->whereKey($itemId)
            ->whereHas('cart', fn ($query) => $query->where('session_id', $sessionId))
            ->with('cart')
            ->first();

        if (! $item) {
            throw ValidationException::withMessages([
                'cart' => __('storefront.cart_item_missing'),
            ]);
        }

        return $item;
    }

    private function refreshCart(Cart $cart): Cart
    {
        $freshCart = $cart->fresh('items');

        if (! $freshCart) {
            return $cart;
        }

        $freshCart->update([
            'item_count' => (int) $freshCart->items->sum('quantity'),
            'subtotal' => (float) $freshCart->items->sum('line_total'),
            'last_activity_at' => now(),
        ]);

        return $freshCart->fresh([
            'items.product.images',
            'items.variant',
        ]);
    }
}
