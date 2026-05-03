<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAuthorizationSeeder::class);
    }

    public function test_valid_admin_credentials_can_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('super-admin');

        $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('super-admin');

        $this->from(route('admin.login'))
            ->post('/admin/login', [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_non_admin_user_cannot_access_admin_routes_even_if_authenticated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_authenticated_admin_can_log_out(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->post('/admin/logout')
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_authenticated_admin_can_update_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->patch('/admin/profile', [
                'name' => 'Updated Admin',
                'email' => 'updated@example.com',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Admin',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_authenticated_admin_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);
        $user->assignRole('admin');

        $this->actingAs($user)
            ->put('/admin/profile/password', [
                'current_password' => 'old-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }
}
