<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerCartManager
{
    public function cartForCustomer(Customer $customer): ?Cart
    {
        return Cart::query()
            ->with([
                'items.product.images',
                'items.variant',
            ])
            ->where('customer_id', $customer->id)
            ->first();
    }

    public function resolveCartForCustomer(Customer $customer): Cart
    {
        return Cart::query()->firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'currency' => 'BHD',
                'item_count' => 0,
                'subtotal' => 0,
            ],
        );
    }

    /**
     * @return array{items_count:int, subtotal:float, currency:string}
     */
    public function summaryForCustomer(Customer $customer): array
    {
        return FrontendTemplateData::cartSummary($this->cartForCustomer($customer));
    }

    public function addItem(Customer $customer, int $productId, int $variantId, int $quantity): Cart
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

        return DB::transaction(function () use ($customer, $productId, $quantity, $stockQuantity, $variant): Cart {
            $cart = $this->resolveCartForCustomer($customer);

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

            $cart->items()->updateOrCreate(
                ['product_variant_id' => $variant->id],
                [
                    'product_id' => $productId,
                    'product_name' => $variant->product->name,
                    'variant_name' => $variant->display_name,
                    'sku' => null,
                    'unit_price' => $unitPrice,
                    'quantity' => $newQuantity,
                    'line_total' => $unitPrice * $newQuantity,
                ],
            );

            return $this->refreshCart($cart);
        });
    }

    public function updateItem(Customer $customer, int $itemId, int $quantity): Cart
    {
        return DB::transaction(function () use ($customer, $itemId, $quantity): Cart {
            $item = $this->resolveCartItem($customer, $itemId);
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

            return $this->refreshCart($item->cart);
        });
    }

    public function removeItem(Customer $customer, int $itemId): Cart
    {
        return DB::transaction(function () use ($customer, $itemId): Cart {
            $item = $this->resolveCartItem($customer, $itemId);
            $cart = $item->cart;

            $item->delete();

            return $this->refreshCart($cart);
        });
    }

    private function resolveCartItem(Customer $customer, int $itemId): CartItem
    {
        $item = CartItem::query()
            ->whereKey($itemId)
            ->whereHas('cart', fn ($query) => $query->where('customer_id', $customer->id))
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
            'customer',
            'items.product.images',
            'items.variant',
        ]);
    }
}
