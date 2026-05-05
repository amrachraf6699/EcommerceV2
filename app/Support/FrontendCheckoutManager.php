<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\CheckoutPricingService;
use App\Services\CouponService;
use App\Services\CurrencyDetectionService;
use App\Services\OrderNotificationService;
use App\Services\TapPaymentService;
use App\Services\WelcomeCouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FrontendCheckoutManager
{
    public function __construct(
        private readonly TapPaymentService $tapPaymentService,
        private readonly CheckoutPricingService $checkoutPricingService,
        private readonly CouponService $couponService,
        private readonly CurrencyDetectionService $currencyDetectionService,
        private readonly StorefrontCountryCatalog $countryCatalog,
        private readonly WelcomeCouponService $welcomeCouponService,
        private readonly OrderNotificationService $orderNotificationService,
    ) {
    }

    public function cartForSession(string $sessionId): ?Cart
    {
        return Cart::query()
            ->with('items')
            ->where('session_id', $sessionId)
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function checkoutFormData(Request $request): array
    {
        /** @var Customer|null $customer */
        $customer = Auth::guard('customer')->user();
        $address = $customer?->addresses()
            ->orderByDesc('is_default_shipping')
            ->latest()
            ->first();

        $draftOrder = Order::query()
            ->where('session_id', $request->session()->getId())
            ->whereNull('placed_at')
            ->latest()
            ->first();

        [$firstName, $lastName] = $this->splitName($customer?->name ?: trim(($draftOrder?->customer_first_name ?? '').' '.($draftOrder?->customer_last_name ?? '')));

        $detectedCountryName = $this->detectedCountryName($request);

        return [
            'first_name' => old('first_name', $draftOrder?->customer_first_name ?: $firstName),
            'last_name' => old('last_name', $draftOrder?->customer_last_name ?: $lastName),
            'email' => old('email', $customer?->email ?: $draftOrder?->customer_email),
            'phone' => old('phone', $draftOrder?->customer_phone ?: $customer?->phone ?: $address?->phone),
            'country' => old('country', $draftOrder?->shipping_country ?: $customer?->country ?: $address?->country ?: $detectedCountryName),
            'state' => old('state', $draftOrder?->shipping_state ?: $address?->state),
            'city' => old('city', $draftOrder?->shipping_city ?: $address?->city),
            'address_line_1' => old('address_line_1', $draftOrder?->shipping_address_line_1 ?: $address?->address_line_1),
            'address_line_2' => old('address_line_2', $draftOrder?->shipping_address_line_2 ?: $address?->address_line_2),
            'postal_code' => old('postal_code', $draftOrder?->shipping_postal_code ?: $address?->postal_code),
            'customer_note' => old('customer_note', $draftOrder?->customer_note),
            'coupon_code' => old('coupon_code', $draftOrder?->coupon_code),
        ];
    }

    /**
     * @return array{
     *     country:?string,
     *     shipping_zone:?string,
     *     shipping_rate_source:?string,
     *     shipping_total:float,
     *     tax_total:float,
     *     subtotal:float,
     *     grand_total:float,
     *     coupon_code:?string,
     *     coupon_applied:bool,
     *     coupon_error:?string,
     *     error:?string
     * }
     */
    public function checkoutSummary(Request $request, ?string $country = null, ?string $email = null, ?string $couponCode = null): array
    {
        $sessionId = $this->ensureSession($request);
        $cart = $this->cartForSession($sessionId);
        $detected = $this->currencyDetectionService->detect($request);
        $customer = $this->pricingCustomer($email);

        return $this->checkoutPricingService->calculate($cart, [
            'country' => $country,
            'detected_country_code' => $detected['country_code'] ?? null,
            'customer' => $customer,
            'email' => $email ?: $customer?->email,
            'coupon_code' => $couponCode,
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{order:Order,redirect_url:string}
     */
    public function beginTapCheckout(Request $request, array $validated): array
    {
        if (! $this->tapPaymentService->isConfigured()) {
            throw ValidationException::withMessages([
                'cart' => __('storefront.checkout_maintenance'),
            ]);
        }

        $sessionId = $this->ensureSession($request);
        $cart = $this->cartForSession($sessionId);

        if (! $cart || $cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => __('storefront.checkout_empty_cart'),
            ]);
        }

        $customer = $this->resolveCustomer($validated);

        $order = DB::transaction(function () use ($cart, $customer, $request, $sessionId, $validated): Order {
            $lockedCart = Cart::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($cart->id);

            if ($lockedCart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => __('storefront.checkout_empty_cart'),
                ]);
            }

            $variants = ProductVariant::query()
                ->with('product')
                ->whereIn('id', $lockedCart->items->pluck('product_variant_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($lockedCart->items as $item) {
                /** @var ProductVariant|null $variant */
                $variant = $variants->get($item->product_variant_id);

                if (! $variant || ! $variant->product || ! $variant->product->is_active || ! $variant->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => __('storefront.cart_variant_unavailable'),
                    ]);
                }

                if ((int) $variant->stock_quantity < (int) $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => __('storefront.cart_quantity_exceeded', ['count' => (int) $variant->stock_quantity]),
                    ]);
                }
            }

            $pendingOrder = Order::query()
                ->where('session_id', $sessionId)
                ->whereNull('placed_at')
                ->latest()
                ->first();

            $order = $pendingOrder ?? new Order([
                'order_number' => $this->generateOrderNumber(),
            ]);

            $pricing = $this->checkoutSummary(
                $request,
                (string) $validated['country'],
                (string) ($customer?->email ?: $validated['email']),
                (string) ($validated['coupon_code'] ?? ''),
            );

            if ($pricing['error']) {
                throw ValidationException::withMessages([
                    'country' => $pricing['error'],
                ]);
            }

            if ($pricing['coupon_error']) {
                throw ValidationException::withMessages([
                    'coupon_code' => $pricing['coupon_error'],
                ]);
            }

            $appliedCoupon = $this->checkoutPricingService->requireRedeemableCoupon(
                $customer,
                (string) ($customer?->email ?: $validated['email']),
                (string) $validated['country'],
                (string) ($validated['coupon_code'] ?? ''),
                (float) $pricing['subtotal_before_discount'],
            );

            $order->fill([
                'session_id' => $sessionId,
                'customer_id' => $customer?->id,
                'coupon_id' => ($appliedCoupon && $appliedCoupon['type'] === 'standard')
                    ? $appliedCoupon['coupon']->id
                    : null,
                'coupon_code' => $appliedCoupon['coupon']->code ?? null,
                'coupon_type' => $appliedCoupon['type'] ?? null,
                'coupon_value' => $appliedCoupon['coupon']->discount_value ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_provider' => 'tap',
                'fulfillment_status' => 'unfulfilled',
                'currency' => $lockedCart->currency ?: 'BHD',
                'customer_first_name' => (string) $validated['first_name'],
                'customer_last_name' => (string) $validated['last_name'],
                'customer_email' => (string) ($customer?->email ?: $validated['email']),
                'customer_phone' => $validated['phone'] ?? null,
                'billing_country' => $validated['country'],
                'billing_state' => $validated['state'] ?? null,
                'billing_city' => $validated['city'],
                'billing_address_line_1' => $validated['address_line_1'],
                'billing_address_line_2' => $validated['address_line_2'] ?? null,
                'billing_postal_code' => $validated['postal_code'] ?? null,
                'shipping_same_as_billing' => true,
                'shipping_country' => $validated['country'],
                'shipping_state' => $validated['state'] ?? null,
                'shipping_city' => $validated['city'],
                'shipping_address_line_1' => $validated['address_line_1'],
                'shipping_address_line_2' => $validated['address_line_2'] ?? null,
                'shipping_postal_code' => $validated['postal_code'] ?? null,
                'customer_note' => $validated['customer_note'] ?? null,
                'subtotal' => $pricing['subtotal'],
                'discount_total' => $pricing['discount_total'],
                'tax_total' => $pricing['tax_total'],
                'shipping_total' => $pricing['shipping_total'],
                'grand_total' => $pricing['grand_total'],
                'placed_at' => null,
            ]);
            $order->save();

            if ($order->welcomeCoupon && (! $appliedCoupon || $appliedCoupon['type'] !== 'welcome' || $order->welcomeCoupon->id !== $appliedCoupon['coupon']->id)) {
                $order->welcomeCoupon->forceFill(['order_id' => null])->save();
            }

            if ($appliedCoupon && $appliedCoupon['type'] === 'welcome') {
                $appliedCoupon['coupon']->forceFill([
                    'customer_id' => $customer->id,
                    'order_id' => $order->id,
                ])->save();
            }

            $order->items()->delete();
            $order->items()->createMany($lockedCart->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'product_name' => $item->product_name,
                'variant_name' => $item->variant_name,
                'sku' => $item->sku,
                'unit_price' => $item->unit_price,
                'quantity' => $item->quantity,
                'line_total' => $item->line_total,
            ])->all());

            return $order->fresh('items');
        });

        $charge = $this->tapPaymentService->createHostedCharge(
            $order,
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $order->customer_email,
            ],
            route('storefront.checkout.result', ['locale' => app()->getLocale(), 'order' => $order->order_number]),
            route('storefront.checkout.tap.callback', ['locale' => app()->getLocale()]),
        );

        $order->forceFill([
            'payment_reference' => (string) (data_get($charge, 'reference.transaction')
                ?: data_get($charge, 'reference.order')
                ?: $order->order_number),
            'payment_transaction_id' => data_get($charge, 'id'),
            'payment_redirect_url' => data_get($charge, 'transaction.url'),
        ])->save();

        $request->session()->put('storefront_checkout_order_id', $order->id);
        $request->session()->put('storefront_checkout_customer_id', $customer?->id);

        return [
            'order' => $order->fresh(),
            'redirect_url' => (string) data_get($charge, 'transaction.url'),
        ];
    }

    /**
     * @param  array<string, mixed>  $charge
     */
    public function syncOrderFromTapCharge(array $charge, ?Request $request = null): Order
    {
        $order = $this->resolveOrderFromCharge($charge);
        $status = strtoupper((string) data_get($charge, 'status'));

        return DB::transaction(function () use ($charge, $order, $request, $status): Order {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::query()
                ->with(['items', 'coupon', 'welcomeCoupon'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            $lockedOrder->forceFill([
                'payment_provider' => 'tap',
                'payment_reference' => (string) (data_get($charge, 'reference.transaction')
                    ?: data_get($charge, 'reference.order')
                    ?: $lockedOrder->payment_reference
                    ?: $lockedOrder->order_number),
                'payment_transaction_id' => data_get($charge, 'id') ?: $lockedOrder->payment_transaction_id,
            ]);

            if ($status === 'CAPTURED') {
                if ($lockedOrder->payment_status !== 'paid') {
                    $this->decrementStockForOrder($lockedOrder);

                    $lockedOrder->fill([
                        'status' => 'processing',
                        'payment_status' => 'paid',
                        'placed_at' => $lockedOrder->placed_at ?: now(),
                    ]);

                    Cart::query()->where('session_id', $lockedOrder->session_id)->delete();

                    if ($lockedOrder->coupon_type === 'standard' && $lockedOrder->coupon) {
                        $this->couponService->markAsUsed($lockedOrder->coupon, $lockedOrder);
                    }

                    if ($lockedOrder->coupon_type === 'welcome' && $lockedOrder->coupon_code) {
                        $welcomeCoupon = $this->welcomeCouponService->findRedeemableForCheckout(
                            $lockedOrder->customer,
                            $lockedOrder->customer_email,
                            $lockedOrder->coupon_code,
                        );

                        if ($welcomeCoupon) {
                            $this->welcomeCouponService->markAsUsed($welcomeCoupon, $lockedOrder);
                        }
                    }

                    $this->orderNotificationService->notifyPlaced($lockedOrder->fresh('customer'), app()->getLocale());
                }

                $lockedOrder->save();

                if ($request && $lockedOrder->customer && $lockedOrder->customer->is_active && ! Auth::guard('customer')->check()) {
                    Auth::guard('customer')->login($lockedOrder->customer);
                    $request->session()->regenerate();
                }

                if ($request) {
                    $request->session()->forget('storefront_checkout_order_id');
                }

                return $lockedOrder->fresh('items');
            }

            if (in_array($status, ['CANCELLED', 'DECLINED', 'FAILED', 'VOID'], true)) {
                $lockedOrder->fill([
                    'payment_status' => $status === 'CANCELLED' ? 'canceled' : 'failed',
                    'status' => 'pending',
                ])->save();

                return $lockedOrder->fresh('items');
            }

            $lockedOrder->fill([
                'payment_status' => 'pending',
            ])->save();

            return $lockedOrder->fresh('items');
        });
    }

    public function findOrderForResult(Request $request): ?Order
    {
        $orderNumber = $request->query('order');

        if (is_string($orderNumber) && $orderNumber !== '') {
            return Order::query()
                ->with('items')
                ->where('order_number', $orderNumber)
                ->first();
        }

        $orderId = $request->session()->get('storefront_checkout_order_id');

        if ($orderId) {
            return Order::query()->with('items')->find($orderId);
        }

        return null;
    }

    public function cancelOrder(Order $order): Order
    {
        if ($order->payment_status === 'paid') {
            return $order;
        }

        $order->update([
            'payment_status' => 'canceled',
            'status' => 'pending',
        ]);

        return $order->fresh('items');
    }

    private function ensureSession(Request $request): string
    {
        if (! $request->session()->isStarted()) {
            $request->session()->start();
        }

        return $request->session()->getId();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveCustomer(array $validated): Customer
    {
        /** @var Customer|null $authenticatedCustomer */
        $authenticatedCustomer = Auth::guard('customer')->user();

        if ($authenticatedCustomer) {
            $authenticatedCustomer->update([
                'name' => trim($validated['first_name'].' '.$validated['last_name']),
                'phone' => $validated['phone'] ?? $authenticatedCustomer->phone,
                'country' => $validated['country'] ?? $authenticatedCustomer->country,
            ]);

            return $authenticatedCustomer;
        }

        $customer = Customer::query()->firstOrNew([
            'email' => (string) $validated['email'],
        ]);

        if ($customer->exists && ! $customer->is_active) {
            throw ValidationException::withMessages([
                'email' => __('storefront.auth.inactive_account'),
            ]);
        }

        $customer->fill([
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'phone' => $validated['phone'] ?? null,
            'country' => $validated['country'],
            'is_active' => true,
        ]);

        if (! $customer->exists) {
            $customer->forceFill([
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ]);
        }

        $customer->save();

        return $customer;
    }

    private function resolveOrderFromCharge(array $charge): Order
    {
        $orderId = data_get($charge, 'metadata.order_id');
        $orderNumber = (string) (data_get($charge, 'reference.order')
            ?: data_get($charge, 'reference.transaction')
            ?: '');
        $tapId = (string) data_get($charge, 'id');

        $query = Order::query()->with('items');

        if ($orderId) {
            $order = (clone $query)->find($orderId);

            if ($order) {
                return $order;
            }
        }

        if ($orderNumber !== '') {
            $order = (clone $query)->where('order_number', $orderNumber)->first();

            if ($order) {
                return $order;
            }
        }

        if ($tapId !== '') {
            $order = (clone $query)->where('payment_transaction_id', $tapId)->first();

            if ($order) {
                return $order;
            }
        }

        throw ValidationException::withMessages([
            'payment' => __('storefront.checkout_payment_not_found'),
        ]);
    }

    private function decrementStockForOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            if (! $item->product_variant_id) {
                continue;
            }

            /** @var ProductVariant|null $variant */
            $variant = ProductVariant::query()
                ->lockForUpdate()
                ->find($item->product_variant_id);

            if (! $variant) {
                continue;
            }

            $variant->decrement('stock_quantity', (int) $item->quantity);
        }
    }

    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * @return array{0:string,1:string}
     */
    private function splitName(?string $name): array
    {
        $name = trim((string) $name);

        if ($name === '') {
            return ['', ''];
        }

        $parts = preg_split('/\s+/', $name, 2) ?: [];

        return [
            $parts[0] ?? '',
            $parts[1] ?? '',
        ];
    }

    private function detectedCountryName(Request $request): ?string
    {
        $detected = $this->currencyDetectionService->detect($request);

        return $this->countryCatalog->countryNameFromDetectedCode($detected['country_code'] ?? null);
    }

    private function pricingCustomer(?string $email = null): ?Customer
    {
        /** @var Customer|null $authenticatedCustomer */
        $authenticatedCustomer = Auth::guard('customer')->user();

        if ($authenticatedCustomer) {
            return $authenticatedCustomer;
        }

        $email = trim((string) $email);

        if ($email === '') {
            return null;
        }

        return Customer::query()
            ->whereRaw('LOWER(email) = ?', [Str::lower($email)])
            ->first();
    }
}
