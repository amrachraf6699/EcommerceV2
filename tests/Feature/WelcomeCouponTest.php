<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use App\Models\WelcomeCoupon;
use App\Notifications\WelcomeCouponIssuedNotification;
use App\Services\WelcomeCouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WelcomeCouponTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->create([
            'group' => 'marketing',
            'key' => 'welcome_coupon_enabled',
            'label' => 'Enable welcome coupon',
            'value' => '1',
            'input_type' => 'boolean',
            'sort_order' => 1,
        ]);

        Setting::query()->create([
            'group' => 'marketing',
            'key' => 'welcome_coupon_discount_mode',
            'label' => 'Discount mode',
            'value' => 'fixed_percent',
            'input_type' => 'select',
            'sort_order' => 2,
            'options' => ['fixed_percent', 'fixed_amount', 'random_percent', 'random_amount'],
        ]);

        Setting::query()->create([
            'group' => 'marketing',
            'key' => 'welcome_coupon_value',
            'label' => 'Discount value',
            'value' => '15',
            'input_type' => 'number',
            'sort_order' => 3,
        ]);
    }

    public function test_guest_can_request_welcome_coupon_by_email(): void
    {
        Notification::fake();

        $this->postJson(route('storefront.welcome-coupon.store', ['locale' => 'en']), [
            'email' => 'guest@example.com',
        ])->assertOk();

        $coupon = WelcomeCoupon::query()->firstOrFail();

        $this->assertSame('guest@example.com', $coupon->email);
        $this->assertSame('percent', $coupon->discount_type);
        $this->assertEquals(15.0, (float) $coupon->discount_value);

        Notification::assertSentOnDemand(WelcomeCouponIssuedNotification::class, function ($notification, $channels, $notifiable): bool {
            return ($notifiable->routes['mail'] ?? null) === 'guest@example.com';
        });
    }

    public function test_existing_customer_gets_welcome_coupon_on_their_notifiable_account(): void
    {
        Notification::fake();

        $customer = Customer::query()->create([
            'name' => 'Mona',
            'email' => 'mona@example.com',
            'password' => 'Password123!',
            'is_active' => true,
        ]);

        $this->postJson(route('storefront.welcome-coupon.store', ['locale' => 'en']), [
            'email' => 'mona@example.com',
        ])->assertOk();

        $coupon = WelcomeCoupon::query()->firstOrFail();

        $this->assertSame($customer->id, $coupon->customer_id);
        Notification::assertSentTo($customer, WelcomeCouponIssuedNotification::class);
    }

    public function test_used_welcome_coupon_cannot_be_issued_again(): void
    {
        WelcomeCoupon::query()->create([
            'email' => 'guest@example.com',
            'code' => 'WELCOME-USED1',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'locale' => 'en',
            'used_at' => now(),
        ]);

        $this->postJson(route('storefront.welcome-coupon.store', ['locale' => 'en']), [
            'email' => 'guest@example.com',
        ])->assertStatus(422);
    }

    public function test_coupon_validation_is_bound_to_the_matching_customer_account(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Mona',
            'email' => 'mona@example.com',
            'password' => 'Password123!',
            'is_active' => true,
        ]);

        $otherCustomer = Customer::query()->create([
            'name' => 'Omar',
            'email' => 'omar@example.com',
            'password' => 'Password123!',
            'is_active' => true,
        ]);

        $coupon = WelcomeCoupon::query()->create([
            'email' => 'mona@example.com',
            'code' => 'WELCOME-ABCD1234',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'locale' => 'en',
        ]);

        $service = app(WelcomeCouponService::class);

        $this->assertNotNull($service->findRedeemableForCustomer($customer, $coupon->code));
        $this->assertNull($service->findRedeemableForCustomer($otherCustomer, $coupon->code));
        $this->assertSame($customer->id, $coupon->fresh()->customer_id);
    }

    public function test_guest_can_redeem_coupon_when_checkout_email_matches_owner_email(): void
    {
        $coupon = WelcomeCoupon::query()->create([
            'email' => 'guest@example.com',
            'code' => 'WELCOME-GUEST01',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'locale' => 'en',
        ]);

        $service = app(WelcomeCouponService::class);

        $this->assertNotNull($service->findRedeemableForCheckout(null, 'guest@example.com', $coupon->code));
        $this->assertNull($service->findRedeemableForCheckout(null, 'other@example.com', $coupon->code));
    }

    public function test_marking_coupon_as_used_links_it_to_the_order(): void
    {
        $coupon = WelcomeCoupon::query()->create([
            'email' => 'guest@example.com',
            'code' => 'WELCOME-LINK01',
            'discount_type' => 'amount',
            'discount_value' => 10,
            'locale' => 'en',
        ]);

        $order = Order::query()->create([
            'order_number' => 'ORD-TEST-1',
            'status' => 'processing',
            'payment_status' => 'paid',
            'fulfillment_status' => 'unfulfilled',
            'currency' => 'BHD',
            'customer_first_name' => 'Guest',
            'customer_last_name' => 'Customer',
            'customer_email' => 'guest@example.com',
            'billing_country' => 'Bahrain',
            'billing_city' => 'Manama',
            'billing_address_line_1' => 'Street 1',
            'shipping_country' => 'Bahrain',
            'shipping_city' => 'Manama',
            'shipping_address_line_1' => 'Street 1',
            'subtotal' => 100,
            'discount_total' => 10,
            'tax_total' => 0,
            'shipping_total' => 0,
            'grand_total' => 90,
        ]);

        app(WelcomeCouponService::class)->markAsUsed($coupon, $order);

        $coupon = $coupon->fresh();

        $this->assertNotNull($coupon->used_at);
        $this->assertSame($order->id, $coupon->order_id);
    }
}
