<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobileApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_log_in_and_fetch_profile_via_api(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Sara Ali',
            'email' => 'sara@example.com',
            'phone' => '12345678',
            'country' => 'Bahrain',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertCreated()
            ->assertJsonPath('customer.email', 'sara@example.com')
            ->assertJsonStructure(['token', 'customer' => ['id', 'email']]);

        $customer = Customer::query()->where('email', 'sara@example.com')->firstOrFail();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'sara@example.com',
            'password' => 'Password123!',
        ])->assertOk()
            ->assertJsonPath('customer.id', $customer->id);

        $token = $customer->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('customer.email', 'sara@example.com');
    }

    public function test_inactive_customer_cannot_log_in(): void
    {
        Customer::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => false,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'Password123!',
        ])->assertForbidden()
            ->assertJsonPath('message', __('storefront.auth.inactive_account'));
    }
}
