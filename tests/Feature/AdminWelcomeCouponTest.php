<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WelcomeCoupon;
use Database\Seeders\AdminAuthorizationSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWelcomeCouponTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAuthorizationSeeder::class);
        $this->seed(SettingsSeeder::class);
    }

    public function test_admin_with_permission_can_view_welcome_coupon_listing(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo('dashboard.view', 'welcome_coupons.view');

        WelcomeCoupon::query()->create([
            'email' => 'coupon@example.com',
            'code' => 'WELCOME-ADMIN1',
            'discount_type' => 'percent',
            'discount_value' => 15,
            'locale' => 'en',
            'sent_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.welcome-coupons.index'))
            ->assertOk()
            ->assertSeeText('WELCOME-ADMIN1')
            ->assertSeeText('coupon@example.com');
    }

    public function test_admin_without_permission_cannot_view_welcome_coupon_listing(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo('dashboard.view');

        $this->actingAs($user)
            ->get(route('admin.welcome-coupons.index'))
            ->assertForbidden();
    }

    public function test_welcome_coupon_listing_filters_used_and_unused_coupons(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo('dashboard.view', 'welcome_coupons.view');

        WelcomeCoupon::query()->create([
            'email' => 'unused@example.com',
            'code' => 'WELCOME-UNUSED',
            'discount_type' => 'amount',
            'discount_value' => 10,
            'locale' => 'en',
            'sent_at' => now(),
        ]);

        WelcomeCoupon::query()->create([
            'email' => 'used@example.com',
            'code' => 'WELCOME-USED',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'locale' => 'en',
            'sent_at' => now(),
            'used_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.welcome-coupons.index', ['status' => 'used']))
            ->assertOk()
            ->assertSeeText('WELCOME-USED')
            ->assertDontSeeText('WELCOME-UNUSED');
    }
}
