<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\WelcomeCoupon;
use App\Support\FrontendCheckoutManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StorefrontCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private ProductVariant $variant;

    private string $sessionId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);

        config()->set('services.tap.base_url', 'https://tap.test');

        Setting::query()->create([
            'group' => 'payment',
            'key' => 'tap_secret_key',
            'label' => 'Tap secret key',
            'value' => 'tap-secret',
            'input_type' => 'password',
            'sort_order' => 1,
        ]);

        Setting::query()->create([
            'group' => 'payment',
            'key' => 'tap_public_key',
            'label' => 'Tap public key',
            'value' => 'tap-public',
            'input_type' => 'password',
            'sort_order' => 2,
        ]);

        Setting::query()->create([
            'group' => 'shipping',
            'key' => 'shipping_gulf_cost',
            'label' => 'Gulf shipping cost',
            'value' => '4',
            'input_type' => 'number',
            'sort_order' => 1,
        ]);

        Setting::query()->create([
            'group' => 'shipping',
            'key' => 'shipping_europe_america_1_2_cost',
            'label' => 'Europe and America shipping (1-2)',
            'value' => '15',
            'input_type' => 'number',
            'sort_order' => 2,
        ]);

        Setting::query()->create([
            'group' => 'shipping',
            'key' => 'shipping_europe_america_3_plus_cost',
            'label' => 'Europe and America shipping (3+)',
            'value' => '10',
            'input_type' => 'number',
            'sort_order' => 3,
        ]);

        Setting::query()->create([
            'group' => 'shipping',
            'key' => 'enable_vat',
            'label' => 'Enable VAT',
            'value' => '1',
            'input_type' => 'checkbox',
            'sort_order' => 1,
        ]);

        Setting::query()->create([
            'group' => 'shipping',
            'key' => 'vat_value',
            'label' => 'VAT value',
            'value' => '10',
            'input_type' => 'number',
            'sort_order' => 2,
        ]);

        $this->get(route('storefront.home', ['locale' => 'en']));
        $this->sessionId = app('session.store')->getId();

        $this->product = Product::query()->create([
            'name' => 'Runner Pro',
            'slug' => 'runner-pro',
            'short_description' => 'Performance runner',
            'description' => 'Full product description',
            'is_active' => true,
            'is_featured' => false,
        ]);

        $this->variant = ProductVariant::query()->create([
            'product_id' => $this->product->id,
            'name' => '42',
            'sku' => 'RUNNER-42',
            'price' => 100,
            'stock_quantity' => 5,
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    public function test_guest_can_open_checkout_without_login(): void
    {
        $this->seedCart();

        $this->checkoutGet(route('storefront.checkout.show', ['locale' => 'en']))
            ->assertOk()
            ->assertSee(__('storefront.checkout_title', [], 'en'))
            ->assertSee('name="first_name"', false);
    }

    public function test_signed_in_customer_uses_same_checkout_page_with_prefilled_data(): void
    {
        $this->seedCart();

        $customer = Customer::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'country' => 'Bahrain',
        ]);

        CustomerAddress::query()->create([
            'customer_id' => $customer->id,
            'label' => 'Home',
            'recipient_name' => 'Jane Doe',
            'phone' => '12345678',
            'country' => 'Bahrain',
            'state' => 'Capital',
            'city' => 'Manama',
            'address_line_1' => 'Road 1',
            'postal_code' => '100',
            'is_default_shipping' => true,
            'is_default_billing' => true,
        ]);

        $this->actingAs($customer, 'customer');

        $this->checkoutGet(route('storefront.checkout.show', ['locale' => 'en']))
            ->assertOk()
            ->assertSee('value="Jane"', false)
            ->assertSee('value="Doe"', false)
            ->assertSee('value="jane@example.com"', false)
            ->assertSee('value="Road 1"', false);
    }

    public function test_empty_cart_cannot_enter_payment_flow(): void
    {
        $this->checkoutPost(route('storefront.checkout.store', ['locale' => 'en']), $this->checkoutPayload())
            ->assertSessionHasErrors('cart');
    }

    public function test_checkout_is_blocked_when_tap_settings_are_missing(): void
    {
        Setting::query()->where('group', 'payment')->delete();
        $this->seedCart();

        $this->checkoutGet(route('storefront.checkout.show', ['locale' => 'en']))
            ->assertOk()
            ->assertSee(__('storefront.checkout_maintenance', [], 'en'));

        $this->checkoutPost(route('storefront.checkout.store', ['locale' => 'en']), $this->checkoutPayload())
            ->assertSessionHasErrors([
                'cart' => __('storefront.checkout_maintenance', [], 'en'),
            ]);
    }

    public function test_checkout_rejects_inactive_or_out_of_stock_cart_items_before_tap_session_creation(): void
    {
        $this->seedCart();
        $this->variant->update(['stock_quantity' => 0]);

        Http::fake();

        $this->checkoutPost(route('storefront.checkout.store', ['locale' => 'en']), $this->checkoutPayload())
            ->assertSessionHasErrors('cart');

        Http::assertNothingSent();
    }

    public function test_checkout_creates_pending_order_and_redirects_to_tap(): void
    {
        $this->seedCart(2);

        Http::fake([
            'https://tap.test/v2/charges' => Http::response([
                'id' => 'chg_123',
                'status' => 'INITIATED',
                'reference' => [
                    'transaction' => 'ORD-REF-1',
                    'order' => 'ORD-REF-1',
                ],
                'transaction' => [
                    'url' => 'https://tap.test/pay/chg_123',
                ],
            ], 200),
        ]);

        $checkout = app(FrontendCheckoutManager::class)->beginTapCheckout(
            $this->managerRequest(),
            $this->checkoutPayload(),
        );

        $this->assertSame('https://tap.test/pay/chg_123', $checkout['redirect_url']);

        $order = Order::query()->firstOrFail();

        $this->assertSame('pending', $order->status);
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertSame('tap', $order->payment_provider);
        $this->assertSame('chg_123', $order->payment_transaction_id);
        $this->assertSame('200.00', $order->subtotal);
        $this->assertSame('4.00', $order->shipping_total);
        $this->assertSame('20.40', $order->tax_total);
        $this->assertSame('224.40', $order->grand_total);
        $this->assertDatabaseCount('order_items', 1);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'https://tap.test/v2/charges'
                && (float) ($data['amount'] ?? 0) === 224.4;
        });
    }

    public function test_checkout_applies_welcome_coupon_to_summary_order_and_payment_amount(): void
    {
        $this->seedCart(2);

        WelcomeCoupon::query()->create([
            'email' => 'john@example.com',
            'code' => 'WELCOME-20OFF',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'locale' => 'en',
        ]);

        Http::fake([
            'https://tap.test/v2/charges' => Http::response([
                'id' => 'chg_coupon',
                'status' => 'INITIATED',
                'reference' => [
                    'transaction' => 'ORD-REF-COUPON',
                    'order' => 'ORD-REF-COUPON',
                ],
                'transaction' => [
                    'url' => 'https://tap.test/pay/chg_coupon',
                ],
            ], 200),
        ]);

        $payload = $this->checkoutPayload();
        $payload['coupon_code'] = 'welcome-20off';

        $checkout = app(FrontendCheckoutManager::class)->beginTapCheckout(
            $this->managerRequest(),
            $payload,
        );

        $this->assertSame('https://tap.test/pay/chg_coupon', $checkout['redirect_url']);

        $order = Order::query()->firstOrFail();
        $coupon = WelcomeCoupon::query()->firstOrFail();

        $this->assertSame('200.00', $order->subtotal);
        $this->assertSame('40.00', $order->discount_total);
        $this->assertSame('4.00', $order->shipping_total);
        $this->assertSame('16.40', $order->tax_total);
        $this->assertSame('180.40', $order->grand_total);
        $this->assertSame($order->id, $coupon->order_id);
        $this->assertNull($coupon->used_at);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return (float) ($data['amount'] ?? 0) === 180.4;
        });
    }

    public function test_checkout_summary_endpoint_returns_shipping_and_vat_for_selected_country(): void
    {
        $this->seedCart(2);

        $request = Request::create('/en/checkout/summary?country=United%20States', 'GET');
        $session = app('session.store');
        $session->setId($this->sessionId);
        $session->start();
        $request->setLaravelSession($session);

        $response = app(CheckoutController::class)->summary($request);
        $payload = $response->getData(true);

        $this->assertSame('europe_america', $payload['summary']['shipping_zone']);
        $this->assertEquals(30.0, $payload['summary']['shipping_total']);
        $this->assertEquals(23.0, $payload['summary']['tax_total']);
        $this->assertEquals(253.0, $payload['summary']['grand_total']);
    }

    public function test_checkout_summary_endpoint_returns_coupon_discount_when_email_matches(): void
    {
        $this->seedCart(2);

        WelcomeCoupon::query()->create([
            'email' => 'john@example.com',
            'code' => 'WELCOME-SUMMARY',
            'discount_type' => 'amount',
            'discount_value' => 25,
            'locale' => 'en',
        ]);

        $request = Request::create('/en/checkout/summary?country=Bahrain&email=john@example.com&coupon_code=WELCOME-SUMMARY', 'GET');
        $session = app('session.store');
        $session->setId($this->sessionId);
        $session->start();
        $request->setLaravelSession($session);

        $response = app(CheckoutController::class)->summary($request);
        $payload = $response->getData(true);

        $this->assertTrue($payload['summary']['coupon_applied']);
        $this->assertSame('WELCOME-SUMMARY', $payload['summary']['coupon_code']);
        $this->assertEquals(25.0, $payload['summary']['discount_total']);
        $this->assertEquals(17.9, $payload['summary']['tax_total']);
        $this->assertEquals(196.9, $payload['summary']['grand_total']);
    }

    public function test_checkout_treats_north_africa_country_like_gulf(): void
    {
        $this->seedCart();

        $payload = $this->checkoutPayload();
        $payload['country'] = 'Egypt';

        Http::fake([
            'https://tap.test/v2/charges' => Http::response([
                'id' => 'chg_egypt',
                'status' => 'INITIATED',
                'reference' => [
                    'transaction' => 'ORD-REF-EG',
                    'order' => 'ORD-REF-EG',
                ],
                'transaction' => [
                    'url' => 'https://tap.test/pay/chg_egypt',
                ],
            ], 200),
        ]);

        app(FrontendCheckoutManager::class)->beginTapCheckout(
            $this->managerRequest(),
            $payload,
        );

        $order = Order::query()->firstOrFail();

        $this->assertSame('4.00', $order->shipping_total);
        $this->assertSame('10.40', $order->tax_total);
        $this->assertSame('114.40', $order->grand_total);
    }

    public function test_tap_success_marks_order_paid_clears_cart_and_logs_customer_in(): void
    {
        $this->seedCart(2);

        Http::fake([
            'https://tap.test/v2/charges' => Http::response([
                'id' => 'chg_success',
                'status' => 'INITIATED',
                'reference' => [
                    'transaction' => 'ORD-REF-2',
                    'order' => 'ORD-REF-2',
                ],
                'transaction' => [
                    'url' => 'https://tap.test/pay/chg_success',
                ],
            ], 200),
            'https://tap.test/v2/charges/chg_success' => Http::response([
                'id' => 'chg_success',
                'status' => 'CAPTURED',
                'reference' => [
                    'transaction' => 'ORD-REF-2',
                    'order' => Order::query()->first()?->order_number,
                ],
                'metadata' => [
                    'order_id' => '1',
                ],
            ], 200),
        ]);

        app(FrontendCheckoutManager::class)->beginTapCheckout(
            $this->managerRequest(),
            $this->checkoutPayload(),
        );

        $order = Order::query()->firstOrFail();

        Http::fake([
            'https://tap.test/v2/charges/chg_success' => Http::response([
                'id' => 'chg_success',
                'status' => 'CAPTURED',
                'reference' => [
                    'transaction' => 'ORD-REF-2',
                    'order' => $order->order_number,
                ],
                'metadata' => [
                    'order_id' => (string) $order->id,
                ],
            ], 200),
        ]);

        $this->checkoutGet(route('storefront.checkout.result', [
            'locale' => 'en',
            'order' => $order->order_number,
            'tap_id' => 'chg_success',
        ]))->assertOk()
            ->assertSee(__('storefront.checkout_success_title', [], 'en'));

        $order = $order->fresh();

        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('processing', $order->status);
        $this->assertNotNull($order->placed_at);
        $this->assertDatabaseMissing('carts', ['session_id' => $this->sessionId]);
        $this->assertSame(3, (int) $this->variant->fresh()->stock_quantity);
        $this->assertAuthenticated('customer');
        $this->assertSame($order->customer_id, auth('customer')->id());

        $this->checkoutGet(route('storefront.cart.summary', ['locale' => 'en']))
            ->assertOk()
            ->assertJsonPath('cart.items_count', 0)
            ->assertJsonPath('cart.subtotal', 0);
    }

    public function test_failed_or_canceled_payment_keeps_order_and_cart(): void
    {
        $this->seedCart();

        Http::fake([
            'https://tap.test/v2/charges' => Http::response([
                'id' => 'chg_fail',
                'status' => 'INITIATED',
                'reference' => [
                    'transaction' => 'ORD-REF-3',
                    'order' => 'ORD-REF-3',
                ],
                'transaction' => [
                    'url' => 'https://tap.test/pay/chg_fail',
                ],
            ], 200),
        ]);

        app(FrontendCheckoutManager::class)->beginTapCheckout(
            $this->managerRequest(),
            $this->checkoutPayload(),
        );

        $order = Order::query()->firstOrFail();

        Http::fake([
            'https://tap.test/v2/charges/chg_fail' => Http::response([
                'id' => 'chg_fail',
                'status' => 'FAILED',
                'reference' => [
                    'transaction' => 'ORD-REF-3',
                    'order' => $order->order_number,
                ],
                'metadata' => [
                    'order_id' => (string) $order->id,
                ],
            ], 200),
        ]);

        $this->checkoutGet(route('storefront.checkout.result', [
            'locale' => 'en',
            'order' => $order->order_number,
            'tap_id' => 'chg_fail',
        ]))->assertOk()
            ->assertSee(__('storefront.checkout_failed_title', [], 'en'));

        $this->assertSame('failed', $order->fresh()->payment_status);
        $this->assertDatabaseHas('carts', ['session_id' => $this->sessionId]);
    }

    public function test_tap_callback_is_idempotent(): void
    {
        $this->seedCart();

        Http::fake([
            'https://tap.test/v2/charges' => Http::response([
                'id' => 'chg_callback',
                'status' => 'INITIATED',
                'reference' => [
                    'transaction' => 'ORD-REF-4',
                    'order' => 'ORD-REF-4',
                ],
                'transaction' => [
                    'url' => 'https://tap.test/pay/chg_callback',
                ],
            ], 200),
        ]);

        app(FrontendCheckoutManager::class)->beginTapCheckout(
            $this->managerRequest(),
            $this->checkoutPayload(),
        );

        $order = Order::query()->firstOrFail();

        Http::fake([
            'https://tap.test/v2/charges/chg_callback' => Http::response([
                'id' => 'chg_callback',
                'status' => 'CAPTURED',
                'reference' => [
                    'transaction' => 'ORD-REF-4',
                    'order' => $order->order_number,
                ],
                'metadata' => [
                    'order_id' => (string) $order->id,
                ],
            ], 200),
        ]);

        $this->post(route('storefront.checkout.tap.callback', ['locale' => 'en']), ['id' => 'chg_callback'])
            ->assertOk();

        $this->post(route('storefront.checkout.tap.callback', ['locale' => 'en']), ['id' => 'chg_callback'])
            ->assertOk();

        $this->assertSame(4, (int) $this->variant->fresh()->stock_quantity);
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_paid_checkout_marks_welcome_coupon_used(): void
    {
        $this->seedCart();

        WelcomeCoupon::query()->create([
            'email' => 'john@example.com',
            'code' => 'WELCOME-PAID',
            'discount_type' => 'amount',
            'discount_value' => 10,
            'locale' => 'en',
        ]);

        Http::fake([
            'https://tap.test/v2/charges' => Http::response([
                'id' => 'chg_coupon_paid',
                'status' => 'INITIATED',
                'reference' => [
                    'transaction' => 'ORD-REF-PAID',
                    'order' => 'ORD-REF-PAID',
                ],
                'transaction' => [
                    'url' => 'https://tap.test/pay/chg_coupon_paid',
                ],
            ], 200),
        ]);

        $payload = $this->checkoutPayload();
        $payload['coupon_code'] = 'WELCOME-PAID';

        app(FrontendCheckoutManager::class)->beginTapCheckout(
            $this->managerRequest(),
            $payload,
        );

        $order = Order::query()->firstOrFail();

        Http::fake([
            'https://tap.test/v2/charges/chg_coupon_paid' => Http::response([
                'id' => 'chg_coupon_paid',
                'status' => 'CAPTURED',
                'reference' => [
                    'transaction' => 'ORD-REF-PAID',
                    'order' => $order->order_number,
                ],
                'metadata' => [
                    'order_id' => (string) $order->id,
                ],
            ], 200),
        ]);

        $this->checkoutGet(route('storefront.checkout.result', [
            'locale' => 'en',
            'order' => $order->order_number,
            'tap_id' => 'chg_coupon_paid',
        ]))->assertOk();

        $coupon = WelcomeCoupon::query()->firstOrFail();

        $this->assertSame($order->id, $coupon->order_id);
        $this->assertNotNull($coupon->used_at);
    }

    public function test_paid_checkout_marks_standard_coupon_used_and_stores_snapshot(): void
    {
        $this->seedCart();

        $coupon = Coupon::query()->create([
            'code' => 'SAVE10',
            'discount_type' => 'amount',
            'discount_value' => 10,
            'is_active' => true,
            'allowed_countries' => ['Bahrain'],
        ]);

        Http::fake([
            'https://tap.test/v2/charges' => Http::response([
                'id' => 'chg_standard_paid',
                'status' => 'INITIATED',
                'reference' => [
                    'transaction' => 'ORD-REF-STANDARD',
                    'order' => 'ORD-REF-STANDARD',
                ],
                'transaction' => [
                    'url' => 'https://tap.test/pay/chg_standard_paid',
                ],
            ], 200),
        ]);

        $payload = $this->checkoutPayload();
        $payload['coupon_code'] = 'save10';

        app(FrontendCheckoutManager::class)->beginTapCheckout(
            $this->managerRequest(),
            $payload,
        );

        $order = Order::query()->firstOrFail();

        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame('SAVE10', $order->coupon_code);
        $this->assertSame('standard', $order->coupon_type);
        $this->assertSame('10.00', $order->coupon_value);
        $this->assertSame('10.00', $order->discount_total);

        Http::fake([
            'https://tap.test/v2/charges/chg_standard_paid' => Http::response([
                'id' => 'chg_standard_paid',
                'status' => 'CAPTURED',
                'reference' => [
                    'transaction' => 'ORD-REF-STANDARD',
                    'order' => $order->order_number,
                ],
                'metadata' => [
                    'order_id' => (string) $order->id,
                ],
            ], 200),
        ]);

        $this->checkoutGet(route('storefront.checkout.result', [
            'locale' => 'en',
            'order' => $order->order_number,
            'tap_id' => 'chg_standard_paid',
        ]))->assertOk();

        $this->assertDatabaseHas('coupon_redemptions', [
            'coupon_id' => $coupon->id,
            'order_id' => $order->id,
            'customer_email' => 'john@example.com',
        ]);
        $this->assertSame(1, CouponRedemption::query()->count());
    }

    private function seedCart(int $quantity = 1): void
    {
        $cart = Cart::query()->create([
            'session_id' => $this->sessionId,
            'currency' => 'BHD',
            'item_count' => $quantity,
            'subtotal' => $quantity * 100,
            'last_activity_at' => now(),
        ]);

        $cart->items()->create([
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'product_name' => $this->product->name,
            'variant_name' => $this->variant->name,
            'sku' => $this->variant->sku,
            'unit_price' => 100,
            'quantity' => $quantity,
            'line_total' => $quantity * 100,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function checkoutPayload(): array
    {
        return [
            'first_name' => 'John',
            'last_name' => 'Customer',
            'email' => 'john@example.com',
            'phone' => '12345678',
            'country' => 'Bahrain',
            'state' => 'Capital',
            'city' => 'Manama',
            'address_line_1' => 'Street 1',
            'address_line_2' => 'Building 2',
            'postal_code' => '100',
            'customer_note' => 'Leave at the desk',
            'coupon_code' => '',
        ];
    }

    private function checkoutGet(string $route)
    {
        return $this->withCookie(config('session.cookie'), Crypt::encryptString($this->sessionId))
            ->get($route);
    }

    private function checkoutPost(string $route, array $payload)
    {
        return $this->withCookie(config('session.cookie'), Crypt::encryptString($this->sessionId))
            ->post($route, $payload);
    }

    private function managerRequest(): Request
    {
        $request = Request::create('/en/checkout', 'POST');
        $session = app('session.store');
        $session->setId($this->sessionId);
        $session->start();
        $request->setLaravelSession($session);

        return $request;
    }
}
