<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CustomerResetPasswordNotification;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_customer_auth_pages(): void
    {
        $this->get(route('storefront.auth.login', ['locale' => 'en']))->assertOk();
        $this->get(route('storefront.auth.register', ['locale' => 'en']))->assertOk();
        $this->get(route('storefront.auth.password.request', ['locale' => 'en']))->assertOk();
    }

    public function test_customer_can_register_and_is_redirected_to_profile(): void
    {
        $response = $this->post(route('storefront.auth.register.store', ['locale' => 'en']), [
            'name' => 'Sara Ali',
            'email' => 'sara@example.com',
            'phone' => '123456',
            'country' => 'Egypt',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('storefront.profile.edit', ['locale' => 'en']));
        $this->assertAuthenticated('customer');
        $this->assertDatabaseHas('customers', ['email' => 'sara@example.com']);
    }

    public function test_forgot_password_redirects_home_with_generic_message_and_uses_customer_notification(): void
    {
        Notification::fake();

        $customer = Customer::factory()->create([
            'email' => 'reset@example.com',
        ]);

        $this->post(route('storefront.auth.password.email', ['locale' => 'en']), [
            'email' => $customer->email,
        ])->assertRedirect(route('storefront.home', ['locale' => 'en']));

        $this->followRedirects($this->post(route('storefront.auth.password.email', ['locale' => 'en']), [
            'email' => $customer->email,
        ]))->assertSee(__('storefront.auth.reset_submitted', [], 'en'));

        Notification::assertSentTo($customer, CustomerResetPasswordNotification::class);
    }

    public function test_customer_can_log_in_and_log_out(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'mona@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $this->post(route('storefront.auth.login.store', ['locale' => 'en']), [
            'email' => $customer->email,
            'password' => 'Password123!',
        ])->assertRedirect(route('storefront.profile.edit', ['locale' => 'en']));

        $this->assertAuthenticated('customer');

        $this->actingAs($customer, 'customer')
            ->post(route('storefront.auth.logout', ['locale' => 'en']))
            ->assertRedirect(route('storefront.home', ['locale' => 'en']));

        $this->assertGuest('customer');
    }

    public function test_authenticated_customer_can_manage_profile_orders_and_addresses(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Mona Ali',
            'email' => 'mona@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $order = Order::query()->create([
            'order_number' => 'ORD-2001',
            'customer_id' => $customer->id,
            'status' => 'processing',
            'payment_status' => 'paid',
            'fulfillment_status' => 'unfulfilled',
            'currency' => 'BHD',
            'customer_first_name' => 'Mona',
            'customer_last_name' => 'Ali',
            'customer_email' => $customer->email,
            'shipping_address_line_1' => 'Street 1',
            'shipping_city' => 'Cairo',
            'shipping_country' => 'Egypt',
            'subtotal' => 100,
            'discount_total' => 0,
            'tax_total' => 5,
            'shipping_total' => 10,
            'grand_total' => 115,
            'placed_at' => now(),
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_name' => 'Runner Pro',
            'variant_name' => '42',
            'sku' => 'RUNNER-42',
            'unit_price' => 100,
            'quantity' => 1,
            'line_total' => 100,
        ]);

        $this->actingAs($customer, 'customer')
            ->get(route('storefront.profile.edit', ['locale' => 'en']))
            ->assertOk()
            ->assertSee('Mona Ali');

        $this->actingAs($customer, 'customer')
            ->put(route('storefront.profile.update', ['locale' => 'en']), [
                'name' => 'Mona Hassan',
                'email' => 'mona@example.com',
                'phone' => '987654',
                'country' => 'Bahrain',
            ])->assertRedirect();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Mona Hassan',
            'country' => 'Bahrain',
        ]);

        $this->actingAs($customer, 'customer')
            ->get(route('storefront.orders.index', ['locale' => 'en']))
            ->assertOk()
            ->assertSee('ORD-2001');

        $this->actingAs($customer, 'customer')
            ->get(route('storefront.orders.show', ['locale' => 'en', 'order' => $order->order_number]))
            ->assertOk()
            ->assertSee('Runner Pro');

        $this->actingAs($customer, 'customer')
            ->post(route('storefront.addresses.store', ['locale' => 'en']), [
                'label' => 'Home',
                'recipient_name' => 'Mona Hassan',
                'phone' => '987654',
                'country' => 'Bahrain',
                'state' => 'Capital',
                'city' => 'Manama',
                'address_line_1' => 'Flat 12',
                'postal_code' => '1001',
                'is_default_shipping' => '1',
            ])->assertRedirect();

        $this->assertDatabaseHas('customer_addresses', [
            'customer_id' => $customer->id,
            'label' => 'Home',
            'is_default_shipping' => true,
        ]);
    }

    public function test_customer_order_pages_keep_snapshot_when_catalog_record_is_deleted(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Mona Ali',
            'email' => 'mona@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $product = Product::create(['name' => 'Runner Pro', 'slug' => 'runner-pro']);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '42',
            'sku' => 'RUNNER-42',
            'price' => 100,
            'stock_quantity' => 3,
            'is_default' => true,
            'is_active' => true,
        ]);

        $order = Order::query()->create([
            'order_number' => 'ORD-DEL-1',
            'customer_id' => $customer->id,
            'status' => 'processing',
            'payment_status' => 'paid',
            'fulfillment_status' => 'unfulfilled',
            'currency' => 'BHD',
            'customer_first_name' => 'Mona',
            'customer_last_name' => 'Ali',
            'customer_email' => $customer->email,
            'shipping_address_line_1' => 'Street 1',
            'shipping_city' => 'Cairo',
            'shipping_country' => 'Egypt',
            'subtotal' => 100,
            'discount_total' => 0,
            'tax_total' => 5,
            'shipping_total' => 10,
            'grand_total' => 115,
            'placed_at' => now(),
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Runner Pro',
            'variant_name' => '42',
            'sku' => 'RUNNER-42',
            'unit_price' => 100,
            'quantity' => 1,
            'line_total' => 100,
        ]);

        $product->delete();

        $this->actingAs($customer, 'customer')
            ->get(route('storefront.orders.show', ['locale' => 'en', 'order' => $order->order_number]))
            ->assertOk()
            ->assertSee('Runner Pro');

        $variant->forceDelete();
        $product->forceDelete();

        $this->actingAs($customer, 'customer')
            ->get(route('storefront.orders.show', ['locale' => 'en', 'order' => $order->order_number]))
            ->assertOk()
            ->assertSee('Runner Pro')
            ->assertSee('Deleted catalog record. Order snapshot preserved.');
    }
}
