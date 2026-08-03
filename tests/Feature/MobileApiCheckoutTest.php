<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MobileApiCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            ['payment', 'afs_environment', 'sandbox'],
            ['payment', 'afs_sandbox_entity_id', 'sandbox-entity'],
            ['payment', 'afs_sandbox_access_token', 'sandbox-token'],
            ['payment', 'afs_sandbox_base_url', 'https://afs.test'],
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
    }

    public function test_mobile_checkout_returns_an_afs_hosted_widget_url(): void
    {
        $customer = Customer::factory()->create(['email' => 'john@example.com', 'country' => 'Bahrain']);
        $product = Product::query()->create([
            'name' => 'Runner Pro', 'slug' => 'runner-pro', 'short_description' => 'Runner',
            'description' => 'Runner', 'is_active' => true,
        ]);
        $variant = ProductVariant::query()->create([
            'product_id' => $product->id, 'size' => '42', 'color' => 'Black', 'price' => 100,
            'stock_quantity' => 5, 'is_default' => true, 'is_active' => true,
        ]);
        $cart = Cart::query()->create(['customer_id' => $customer->id, 'currency' => 'BHD', 'item_count' => 1, 'subtotal' => 100, 'last_activity_at' => now()]);
        $cart->items()->create([
            'product_id' => $product->id, 'product_variant_id' => $variant->id, 'product_name' => $product->name,
            'variant_name' => $variant->display_name, 'unit_price' => 100, 'quantity' => 1, 'line_total' => 100,
        ]);

        Http::fake(['https://afs.test/v1/checkouts' => Http::response(['id' => 'checkout_mobile_1'])]);
        $token = $customer->createToken('mobile')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/checkout', $this->payload());

        $response->assertCreated()
            ->assertJsonPath('payment.payment_provider', 'afs')
            ->assertJsonPath('payment.payment_mode', 'hosted_widget')
            ->assertJsonPath('payment.checkout_id', 'checkout_mobile_1')
            ->assertJsonPath('payment.payment_brands.0', 'VISA')
            ->assertJsonPath('payment.payment_brands.1', 'MASTER')
            ->assertJsonPath('order.customer.email', 'john@example.com');

        Http::assertSent(fn ($request) => $request->url() === 'https://afs.test/v1/checkouts'
            && $request->hasHeader('Authorization', 'Bearer sandbox-token')
            && $request['entityId'] === 'sandbox-entity'
            && $request['paymentType'] === 'DB');
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
}
