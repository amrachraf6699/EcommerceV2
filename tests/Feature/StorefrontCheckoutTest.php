<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Services\AfsPaymentService;
use App\Support\FrontendCheckoutManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
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

        foreach ([
            ['payment', 'afs_environment', 'sandbox'],
            ['payment', 'afs_sandbox_entity_id', 'sandbox-entity'],
            ['payment', 'afs_sandbox_access_token', 'sandbox-token'],
            ['payment', 'afs_sandbox_base_url', 'https://afs.test'],
            ['payment', 'afs_live_entity_id', 'live-entity'],
            ['payment', 'afs_live_access_token', 'live-token'],
            ['payment', 'afs_live_base_url', 'https://afs.live'],
            ['payment', 'afs_brands', 'VISA MASTER'],
            ['shipping', 'shipping_gulf_cost', '4'],
            ['shipping', 'shipping_others_cost', '15'],
            ['shipping', 'enable_vat', '1'],
            ['shipping', 'vat_value', '10'],
        ] as [$group, $key, $value]) {
            Setting::query()->updateOrCreate(['key' => $key], compact('group', 'key', 'value') + [
                'label' => $key,
                'input_type' => str_contains($key, 'token') ? 'password' : 'text',
                'sort_order' => 1,
            ]);
        }

        $this->get(route('storefront.home', ['locale' => 'en']));
        $this->sessionId = app('session.store')->getId();
        $this->product = Product::query()->create([
            'name' => 'Runner Pro', 'slug' => 'runner-pro', 'short_description' => 'Runner',
            'description' => 'Runner', 'is_active' => true,
        ]);
        $this->variant = ProductVariant::query()->create([
            'product_id' => $this->product->id, 'size' => '42', 'color' => 'Black', 'price' => 100,
            'stock_quantity' => 5, 'is_default' => true, 'is_active' => true,
        ]);
    }

    public function test_checkout_requires_afs_configuration(): void
    {
        Setting::query()->where('group', 'payment')->delete();
        $this->seedCart();

        $this->checkoutGet(route('storefront.checkout.show', ['locale' => 'en']))
            ->assertOk()
            ->assertSee(__('storefront.checkout_maintenance', [], 'en'));
        $this->checkoutPost(route('storefront.checkout.store', ['locale' => 'en']), $this->payload())
            ->assertSessionHasErrors('cart');
    }

    public function test_checkout_creates_afs_widget_and_verifies_a_successful_payment_idempotently(): void
    {
        $this->seedCart();
        Http::fake(['https://afs.test/v1/checkouts' => Http::response(['id' => 'checkout_123'])]);

        $checkout = app(FrontendCheckoutManager::class)->beginAfsCheckout($this->managerRequest(), $this->payload());
        $order = Order::query()->firstOrFail();

        $this->assertSame('afs', $order->payment_provider);
        $this->assertSame('checkout_123', $order->payment_reference);
        $this->assertNull($order->payment_transaction_id);
        $this->assertSame(route('storefront.checkout.payment', ['locale' => 'en', 'order' => $order->order_number]), $checkout['payment_url']);
        Http::assertSent(fn ($request) => $request->url() === 'https://afs.test/v1/checkouts'
            && $request->hasHeader('Authorization', 'Bearer sandbox-token')
            && $request['entityId'] === 'sandbox-entity'
            && $request['merchantTransactionId'] === $order->order_number);

        $this->checkoutGet($checkout['payment_url'])->assertOk()
            ->assertSee('paymentWidgets', false)
            ->assertSee('checkout_123', false);

        $payment = [
            'id' => 'payment_123', 'amount' => (string) $order->grand_total, 'currency' => 'BHD',
            'merchantTransactionId' => $order->order_number, 'result' => ['code' => '000.100.110'],
        ];
        Http::fake(['https://afs.test/v1/checkouts/checkout_123/payment*' => Http::response($payment)]);

        $result = route('storefront.checkout.result', ['locale' => 'en', 'order' => $order->order_number, 'resourcePath' => '/v1/checkouts/checkout_123/payment']);
        $this->checkoutGet($result)->assertOk()->assertSee(__('storefront.checkout_success_title', [], 'en'));
        $this->checkoutGet($result)->assertOk();

        $this->assertSame('paid', $order->fresh()->payment_status->value);
        $this->assertSame('payment_123', $order->fresh()->payment_transaction_id);
        $this->assertSame(4, (int) $this->variant->fresh()->stock_quantity);
        $this->assertDatabaseMissing('carts', ['session_id' => $this->sessionId]);
    }

    public function test_checkout_rejects_a_resource_path_for_another_checkout(): void
    {
        $this->seedCart();
        Http::fake(['https://afs.test/v1/checkouts' => Http::response(['id' => 'checkout_123'])]);
        $checkout = app(FrontendCheckoutManager::class)->beginAfsCheckout($this->managerRequest(), $this->payload());

        $this->checkoutGet(route('storefront.checkout.result', [
            'locale' => 'en', 'order' => $checkout['order']->order_number,
            'resourcePath' => '/v1/checkouts/another_checkout/payment',
        ]))->assertNotFound();
        Http::assertSentCount(1);
    }

    public function test_a_non_successful_afs_result_keeps_the_cart(): void
    {
        $this->seedCart();
        Http::fake(['https://afs.test/v1/checkouts' => Http::response(['id' => 'checkout_failed'])]);
        $checkout = app(FrontendCheckoutManager::class)->beginAfsCheckout($this->managerRequest(), $this->payload());
        $order = $checkout['order'];
        Http::fake(['https://afs.test/v1/checkouts/checkout_failed/payment*' => Http::response([
            'id' => 'payment_failed', 'merchantTransactionId' => $order->order_number, 'result' => ['code' => '100.380.401'],
        ])]);

        $this->checkoutGet(route('storefront.checkout.result', [
            'locale' => 'en', 'order' => $order->order_number,
            'resourcePath' => '/v1/checkouts/checkout_failed/payment',
        ]))->assertOk()
            ->assertSee(__('storefront.checkout_failed_title', [], 'en'))
            ->assertSee(__('storefront.checkout_payment_authentication_failed', [], 'en'));

        $this->assertSame('failed', $order->fresh()->payment_status->value);
        $this->assertDatabaseHas('carts', ['session_id' => $this->sessionId]);
    }

    public function test_live_environment_uses_the_live_afs_endpoint(): void
    {
        Setting::query()->where('key', 'afs_environment')->update(['value' => 'live']);
        $service = app(AfsPaymentService::class);

        $this->assertTrue($service->isConfigured());
        $this->assertSame('live', $service->environment());
        $this->assertSame('https://afs.live/v1/paymentWidgets.js?checkoutId=checkout_live', $service->widgetUrl('checkout_live'));
    }

    public function test_checkout_requires_a_phone_and_a_valid_saudi_short_address(): void
    {
        $route = route('storefront.checkout.store', ['locale' => 'en']);

        $this->checkoutPost($route, array_merge($this->payload(), ['phone' => '']))
            ->assertSessionHasErrors('phone');

        $this->checkoutPost($route, array_merge($this->payload(), [
            'country' => 'Saudi Arabia',
            'short_address' => '',
        ]))->assertSessionHasErrors('short_address');

        $this->checkoutPost($route, array_merge($this->payload(), [
            'country' => 'Saudi Arabia',
            'short_address' => 'abcd1234',
        ]))->assertSessionHasErrors('short_address');

        $this->checkoutPost($route, array_merge($this->payload(), [
            'country' => 'Bahrain',
            'short_address' => 'invalid',
        ]))->assertSessionDoesntHaveErrors('short_address');
    }

    public function test_checkout_persists_the_country_code_and_saudi_short_address(): void
    {
        $this->seedCart();
        Http::fake(['https://afs.test/v1/checkouts' => Http::response(['id' => 'checkout_saudi_1'])]);

        app(FrontendCheckoutManager::class)->beginAfsCheckout($this->managerRequest(), array_merge($this->payload(), [
            'country' => 'Saudi Arabia',
            'phone' => '501234567',
            'short_address' => 'ABCD1234',
        ]));

        $this->assertDatabaseHas('orders', [
            'customer_phone' => '+966501234567',
            'shipping_short_address' => 'ABCD1234',
        ]);
    }

    private function seedCart(): void
    {
        $cart = Cart::query()->create([
            'session_id' => $this->sessionId, 'currency' => 'BHD', 'item_count' => 1,
            'subtotal' => 100, 'last_activity_at' => now(),
        ]);
        $cart->items()->create([
            'product_id' => $this->product->id, 'product_variant_id' => $this->variant->id,
            'product_name' => $this->product->name, 'variant_name' => $this->variant->display_name,
            'unit_price' => 100, 'quantity' => 1, 'line_total' => 100,
        ]);
    }

    /** @return array<string, string> */
    private function payload(): array
    {
        return [
            'first_name' => 'John', 'last_name' => 'Customer', 'email' => 'john@example.com', 'phone' => '12345678',
            'country' => 'Bahrain', 'state' => 'Capital', 'city' => 'Manama', 'address_line_1' => 'Street 1',
            'address_line_2' => 'Building 2', 'postal_code' => '100', 'customer_note' => 'Leave at the desk', 'coupon_code' => '',
        ];
    }

    private function checkoutGet(string $route)
    {
        return $this->withCookie(config('session.cookie'), Crypt::encryptString($this->sessionId))->get($route);
    }

    private function checkoutPost(string $route, array $payload)
    {
        return $this->withCookie(config('session.cookie'), Crypt::encryptString($this->sessionId))->post($route, $payload);
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
