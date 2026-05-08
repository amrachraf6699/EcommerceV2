<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileApiCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_owned_cart_supports_add_update_remove_and_isolation(): void
    {
        $customer = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        $product = Product::query()->create([
            'name' => 'Runner Pro',
            'slug' => 'runner-pro',
            'short_description' => 'Performance runner',
            'description' => 'Full product description',
            'is_active' => true,
            'is_featured' => false,
        ]);
        $variant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'size' => '42',
            'color' => 'Black',
            'price' => 100,
            'stock_quantity' => 5,
            'is_default' => true,
            'is_active' => true,
        ]);

        Sanctum::actingAs($customer);

        $addResponse = $this
            ->postJson('/api/v1/cart/items', [
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'quantity' => 2,
            ])->assertCreated()
            ->assertJsonPath('cart.item_count', 2)
            ->assertJsonPath('cart.subtotal', 200);

        $itemId = $addResponse->json('cart.items.0.id');

        $this
            ->patchJson('/api/v1/cart/items/'.$itemId, [
                'quantity' => 3,
            ])->assertOk()
            ->assertJsonPath('cart.item_count', 3)
            ->assertJsonPath('cart.subtotal', 300);

        Sanctum::actingAs($otherCustomer);

        $this
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('customer.id', $otherCustomer->id);

        $this
            ->patchJson('/api/v1/cart/items/'.$itemId, [
                'quantity' => 1,
            ])->assertStatus(422);

        Sanctum::actingAs($customer);

        $this
            ->deleteJson('/api/v1/cart/items/'.$itemId)
            ->assertOk()
            ->assertJsonPath('cart.item_count', 0)
            ->assertJsonPath('cart.subtotal', 0);
    }
}
