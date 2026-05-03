<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReminder;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductReminderController extends Controller
{
    public function store(Request $request, string $locale, Product $product): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'product_variant_id' => ['required', 'integer', Rule::exists('product_variants', 'id')],
            'email' => $customer
                ? ['nullable', 'email', 'max:255']
                : ['required', 'email', 'max:255'],
        ]);

        /** @var ProductVariant|null $variant */
        $variant = $product->variants()
            ->whereKey($validated['product_variant_id'])
            ->where('is_active', true)
            ->first();

        if (! $variant) {
            throw ValidationException::withMessages([
                'product_variant_id' => __('storefront.product.reminder_invalid_variant'),
            ]);
        }

        if ((int) $variant->stock_quantity > 0) {
            throw ValidationException::withMessages([
                'product_variant_id' => __('storefront.product.reminder_unavailable'),
            ]);
        }

        $email = $customer?->email ?: strtolower((string) $validated['email']);

        ProductReminder::query()->firstOrCreate(
            [
                'active_key' => ProductReminder::activeKey($variant->id, $customer?->id, $email),
            ],
            [
                'product_variant_id' => $variant->id,
                'customer_id' => $customer?->id,
                'email' => $customer ? null : $email,
                'locale' => $locale,
            ],
        );

        return response()->json([
            'message' => __('storefront.product.reminder_created'),
        ]);
    }
}
