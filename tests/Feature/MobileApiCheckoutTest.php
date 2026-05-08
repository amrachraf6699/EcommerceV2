<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MobileApiCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.tap.base_url', 'https://tap.test');

        foreach ([
            ['payment', 'tap_secret_key', 'tap-secret'],
            ['payment', 'tap_public_key', 'tap-public'],
            ['shipping', 'shipping_gulf_cost', '4'],
            ['shipping', 'shipping_others_cost', '15'],
            ['shipping', 'enable_vat', '1'],
            ['shipping', 'vat_value', '10'],
        ] as [$group, $key, $value]) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'group' => $group,
                    'label' => $key,
                    'value' => $value,
                    'input_type' => 'text',
                    'sort_order' => 1,
                ]
            );
        }

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
            'size' => '42',
            'color' => 'Black',
            'price' => 100,
            'stock_quantity' => 5,
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    public function test_mobile_checkout_creates_order_and_can_sync_payment_status(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'john@example.com',
            'country' => 'Bahrain',
        ]);

        Cart::query()->create([
            'customer_id' => $customer->id,
            'currency' => 'BHD',
            'item_count' => 2,
            'subtotal' => 200,
            'last_activity_at' => now(),
        ])->items()->create([
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'product_name' => $this->product->name,
            'variant_name' => $this->variant->display_name,
            'sku' => null,
            'unit_price' => 100,
            'quantity' => 2,
            'line_total' => 200,
        ]);

        Http::fake([
            'https://tap.test/v2/charges' => Http::response([
                'id' => 'chg_mobile_1',
                'status' => 'INITIATED',
                'reference' => [
                    'transaction' => 'ORD-MOBILE-1',
                    'order' => 'ORD-MOBILE-1',
                ],
                'transaction' => [
                    'url' => 'https://tap.test/pay/chg_mobile_1',
                ],
            ], 200),
        ]);

        $token = $customer->createToken('mobile')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/checkout', [
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
                'shipping_box_type' => 'without_box',
                'payment_mode' => 'native_sdk',
            ])->assertCreated()
            ->assertJsonPath('payment.payment_provider', 'tap')
            ->assertJsonPath('payment.payment_mode', 'native_sdk')
            ->assertJsonPath('payment.tap_charge_id', 'chg_mobile_1')
            ->assertJsonPath('order.customer.email', 'john@example.com');

        $order = Order::query()->firstOrFail();

        Http::fake([
            'https://tap.test/v2/charges/chg_mobile_1' => Http::response([
                'id' => 'chg_mobile_1',
                'status' => 'CAPTURED',
                'reference' => [
                    'transaction' => 'ORD-MOBILE-1',
                    'order' => $order->order_number,
                ],
                'metadata' => [
                    'order_id' => (string) $order->id,
                ],
            ], 200),
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/checkout/orders/'.$order->order_number.'/payment-status')
            ->assertOk()
            ->assertJsonPath('order.payment_status', 'paid')
            ->assertJsonPath('order.status', 'processing');

        $this->assertDatabaseMissing('carts', ['customer_id' => $customer->id]);
        $this->assertSame(3, (int) $this->variant->fresh()->stock_quantity);
    }
}
