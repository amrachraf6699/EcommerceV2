<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\WelcomeCoupon;
use App\Services\CheckoutPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->create([
            'group' => 'shipping',
            'key' => 'shipping_gulf_cost',
            'label' => 'Gulf shipping cost',
            'value' => '6',
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
    }

    public function test_gulf_shipping_uses_current_settings_value(): void
    {
        $cart = Cart::query()->create([
            'session_id' => 'test-session',
            'currency' => 'BHD',
            'item_count' => 2,
            'subtotal' => 100,
        ]);

        $summary = app(CheckoutPricingService::class)->calculate($cart, [
            'country' => 'Bahrain',
        ]);

        $this->assertSame('gulf', $summary['shipping_zone']);
        $this->assertSame(6.0, $summary['shipping_total']);
        $this->assertSame(10.6, $summary['tax_total']);
        $this->assertSame(116.6, $summary['grand_total']);
    }

    public function test_europe_america_three_plus_uses_bulk_rate_from_settings(): void
    {
        $cart = Cart::query()->create([
            'session_id' => 'test-session-2',
            'currency' => 'BHD',
            'item_count' => 3,
            'subtotal' => 300,
        ]);

        $summary = app(CheckoutPricingService::class)->calculate($cart, [
            'country' => 'United States',
        ]);

        $this->assertSame('europe_america', $summary['shipping_zone']);
        $this->assertSame(30.0, $summary['shipping_total']);
        $this->assertSame(33.0, $summary['tax_total']);
        $this->assertSame(363.0, $summary['grand_total']);
    }

    public function test_updating_settings_only_affects_future_calculations(): void
    {
        $cart = Cart::query()->create([
            'session_id' => 'test-session-3',
            'currency' => 'BHD',
            'item_count' => 1,
            'subtotal' => 100,
        ]);

        $service = app(CheckoutPricingService::class);

        $firstSummary = $service->calculate($cart, [
            'country' => 'United States',
        ]);

        Setting::query()
            ->where('group', 'shipping')
            ->where('key', 'shipping_europe_america_1_2_cost')
            ->update(['value' => '20']);

        $updatedService = app(CheckoutPricingService::class);

        $secondSummary = $updatedService->calculate($cart, [
            'country' => 'United States',
        ]);

        $this->assertSame(15.0, $firstSummary['shipping_total']);
        $this->assertSame(20.0, $secondSummary['shipping_total']);
    }

    public function test_detected_egypt_is_treated_like_gulf(): void
    {
        $cart = Cart::query()->create([
            'session_id' => 'test-session-4',
            'currency' => 'BHD',
            'item_count' => 1,
            'subtotal' => 100,
        ]);

        $summary = app(CheckoutPricingService::class)->calculate($cart, [
            'detected_country_code' => 'EG',
        ]);

        $this->assertSame('Egypt', $summary['country']);
        $this->assertSame('gulf', $summary['shipping_zone']);
        $this->assertSame(6.0, $summary['shipping_total']);
        $this->assertNull($summary['error']);
    }

    public function test_other_countries_fall_back_to_europe_america_pricing(): void
    {
        $cart = Cart::query()->create([
            'session_id' => 'test-session-5',
            'currency' => 'BHD',
            'item_count' => 2,
            'subtotal' => 100,
        ]);

        $summary = app(CheckoutPricingService::class)->calculate($cart, [
            'country' => 'Japan',
        ]);

        $this->assertSame('europe_america', $summary['shipping_zone']);
        $this->assertSame(30.0, $summary['shipping_total']);
        $this->assertSame(13.0, $summary['tax_total']);
        $this->assertSame(143.0, $summary['grand_total']);
        $this->assertNull($summary['error']);
    }

    public function test_valid_percent_coupon_reduces_subtotal_before_vat(): void
    {
        $cart = Cart::query()->create([
            'session_id' => 'test-session-6',
            'currency' => 'BHD',
            'item_count' => 1,
            'subtotal' => 100,
        ]);

        $customer = Customer::factory()->create([
            'email' => 'coupon@example.com',
        ]);

        WelcomeCoupon::query()->create([
            'customer_id' => $customer->id,
            'email' => 'coupon@example.com',
            'code' => 'WELCOME-PERCENT',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'locale' => 'en',
        ]);

        $summary = app(CheckoutPricingService::class)->calculate($cart, [
            'country' => 'Bahrain',
            'customer' => $customer,
            'email' => $customer->email,
            'coupon_code' => 'welcome-percent',
        ]);

        $this->assertTrue($summary['coupon_applied']);
        $this->assertSame(20.0, $summary['discount_total']);
        $this->assertSame(80.0, $summary['subtotal_after_discount']);
        $this->assertSame(8.6, $summary['tax_total']);
        $this->assertSame(94.6, $summary['grand_total']);
    }

    public function test_valid_fixed_coupon_is_capped_at_subtotal(): void
    {
        $cart = Cart::query()->create([
            'session_id' => 'test-session-7',
            'currency' => 'BHD',
            'item_count' => 1,
            'subtotal' => 25,
        ]);

        WelcomeCoupon::query()->create([
            'email' => 'guest@example.com',
            'code' => 'WELCOME-AMOUNT',
            'discount_type' => 'amount',
            'discount_value' => 40,
            'locale' => 'en',
        ]);

        $summary = app(CheckoutPricingService::class)->calculate($cart, [
            'country' => 'Bahrain',
            'email' => 'guest@example.com',
            'coupon_code' => 'WELCOME-AMOUNT',
        ]);

        $this->assertSame(25.0, $summary['discount_total']);
        $this->assertSame(0.0, $summary['subtotal_after_discount']);
        $this->assertSame(0.6, $summary['tax_total']);
        $this->assertSame(6.6, $summary['grand_total']);
    }

    public function test_valid_standard_coupon_reduces_subtotal_before_vat(): void
    {
        $cart = Cart::query()->create([
            'session_id' => 'test-session-8',
            'currency' => 'BHD',
            'item_count' => 1,
            'subtotal' => 120,
        ]);

        Coupon::query()->create([
            'code' => 'SAVE15',
            'discount_type' => 'percent',
            'discount_value' => 15,
            'is_active' => true,
            'min_order_subtotal' => 100,
            'allowed_countries' => ['Bahrain'],
        ]);

        $summary = app(CheckoutPricingService::class)->calculate($cart, [
            'country' => 'Bahrain',
            'email' => 'guest@example.com',
            'coupon_code' => 'save15',
        ]);

        $this->assertTrue($summary['coupon_applied']);
        $this->assertSame('SAVE15', $summary['coupon_code']);
        $this->assertSame(18.0, $summary['discount_total']);
        $this->assertSame(102.0, $summary['subtotal_after_discount']);
        $this->assertSame(10.8, $summary['tax_total']);
        $this->assertSame(118.8, $summary['grand_total']);
    }

    public function test_standard_coupon_takes_precedence_over_welcome_coupon_with_same_code(): void
    {
        $cart = Cart::query()->create([
            'session_id' => 'test-session-9',
            'currency' => 'BHD',
            'item_count' => 1,
            'subtotal' => 100,
        ]);

        Coupon::query()->create([
            'code' => 'SAVE20',
            'discount_type' => 'amount',
            'discount_value' => 12,
            'is_active' => true,
        ]);

        WelcomeCoupon::query()->create([
            'email' => 'guest@example.com',
            'code' => 'SAVE20',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'locale' => 'en',
        ]);

        $summary = app(CheckoutPricingService::class)->calculate($cart, [
            'country' => 'Bahrain',
            'email' => 'guest@example.com',
            'coupon_code' => 'SAVE20',
        ]);

        $this->assertTrue($summary['coupon_applied']);
        $this->assertSame(12.0, $summary['discount_total']);
        $this->assertSame(88.0, $summary['subtotal_after_discount']);
    }
}
