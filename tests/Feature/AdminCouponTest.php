<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Database\Seeders\AdminAuthorizationSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCouponTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAuthorizationSeeder::class);
        $this->seed(SettingsSeeder::class);
    }

    public function test_admin_with_permission_can_view_coupon_listing(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo('dashboard.view', 'coupons.view');

        Coupon::query()->create([
            'code' => 'LIST10',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.coupons.index'))
            ->assertOk()
            ->assertSeeText('LIST10');
    }

    public function test_admin_can_create_coupon(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo('dashboard.view', 'coupons.view', 'coupons.create');

        $this->actingAs($user)
            ->post(route('admin.coupons.store'), [
                'code' => 'spring20',
                'discount_type' => 'percent',
                'discount_value' => 20,
                'is_active' => '1',
                'starts_at' => now()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'min_order_subtotal' => 50,
                'usage_limit' => 100,
                'usage_limit_per_customer' => 2,
                'allowed_countries' => ['Bahrain', 'Saudi Arabia'],
            ])
            ->assertRedirect(route('admin.coupons.index'));

        $this->assertDatabaseHas('coupons', [
            'code' => 'SPRING20',
            'discount_type' => 'percent',
        ]);
    }
}
