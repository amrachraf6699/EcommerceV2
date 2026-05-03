<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTrackOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->create([
            'group' => 'brand',
            'key' => 'name',
            'label' => 'Brand name',
            'value' => 'SunFlower',
            'input_type' => 'text',
            'sort_order' => 1,
        ]);
    }

    public function test_navbar_dropdown_shows_track_order_link_when_setting_is_enabled(): void
    {
        Setting::query()->create([
            'group' => 'marketing',
            'key' => 'track_order_enabled',
            'label' => 'Enable track order',
            'value' => '1',
            'input_type' => 'boolean',
            'sort_order' => 2,
        ]);

        $this->get(route('storefront.home'))
            ->assertOk()
            ->assertSee(route('storefront.track-order.show'), false);
    }

    public function test_guest_can_track_an_order_by_number_and_email(): void
    {
        Setting::query()->create([
            'group' => 'marketing',
            'key' => 'track_order_enabled',
            'label' => 'Enable track order',
            'value' => '1',
            'input_type' => 'boolean',
            'sort_order' => 2,
        ]);

        $order = Order::query()->create([
            'order_number' => 'SF-1001',
            'status' => 'processing',
            'payment_status' => 'paid',
            'fulfillment_status' => 'packed',
            'currency' => 'BHD',
            'customer_first_name' => 'Amr',
            'customer_last_name' => 'Ashraf',
            'customer_email' => 'customer@example.com',
            'subtotal' => 10,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 2,
            'grand_total' => 12,
            'shipping_country' => 'Egypt',
            'shipping_city' => 'Cairo',
            'shipping_address_line_1' => 'Nasr City',
            'placed_at' => now(),
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_name' => 'Mercurial',
            'variant_name' => '42',
            'sku' => 'MERC-42',
            'unit_price' => 10,
            'quantity' => 1,
            'line_total' => 10,
        ]);

        $this->post(route('storefront.track-order.store'), [
            'order_number' => 'SF-1001',
            'email' => 'customer@example.com',
        ])->assertOk()
            ->assertSeeText('SF-1001')
            ->assertSeeText('Mercurial')
            ->assertSeeText('processing');
    }

    public function test_track_order_page_returns_404_when_setting_is_disabled(): void
    {
        $this->get(route('storefront.track-order.show'))->assertNotFound();
    }
}
