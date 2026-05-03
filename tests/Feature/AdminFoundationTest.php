<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminAuthorizationSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAuthorizationSeeder::class);
        $this->seed(SettingsSeeder::class);
    }

    public function test_guest_is_redirected_to_admin_login_from_dashboard(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_admin_can_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSeeText('إجراءات سريعة');
    }

    public function test_authenticated_admin_without_permission_is_blocked_from_permission_protected_route(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo('dashboard.view');

        $this->actingAs($user)
            ->get('/admin/settings')
            ->assertForbidden();
    }

    public function test_authenticated_admin_with_permission_can_access_permission_protected_route(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        Permission::findOrCreate('settings.view', 'web');
        $user->givePermissionTo('dashboard.view');
        $user->givePermissionTo('settings.view');

        $this->actingAs($user)
            ->get('/admin/settings')
            ->assertOk()
            ->assertSeeText('Brand');
    }
}
