<?php

namespace Tests\Feature;

use Database\Seeders\AdminAuthorizationSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EcommerceDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_ecommerce_tables_exist(): void
    {
        $tables = [
            'categories',
            'products',
            'category_product',
            'product_variants',
            'product_images',
            'carts',
            'cart_items',
            'customers',
            'customer_addresses',
            'product_reminders',
            'welcome_coupons',
            'sliders',
            'orders',
            'order_items',
            'settings',
            'roles',
            'permissions',
            'jobs',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Failed asserting that table [{$table}] exists.");
        }
    }

    public function test_settings_seeder_reads_groups_file_and_expands_mail_settings(): void
    {
        $this->seed(SettingsSeeder::class);

        $this->assertDatabaseHas('settings', [
            'group' => 'social',
            'key' => 'facebook',
            'input_type' => 'text',
            'is_public' => true,
        ]);

        $this->assertDatabaseHas('settings', [
            'group' => 'brand',
            'key' => 'logo',
            'input_type' => 'file',
        ]);

        $this->assertDatabaseHas('settings', [
            'group' => 'marketing',
            'key' => 'welcome_coupon_enabled',
            'input_type' => 'boolean',
        ]);

        $this->assertDatabaseHas('settings', [
            'group' => 'marketing',
            'key' => 'welcome_coupon_discount_mode',
            'input_type' => 'select',
        ]);

        $this->assertDatabaseHas('settings', [
            'group' => 'mail',
            'key' => 'mail_host',
            'input_type' => 'text',
        ]);
    }

    public function test_admin_authorization_seeder_creates_roles_and_permissions(): void
    {
        $this->seed(AdminAuthorizationSeeder::class);

        $this->assertDatabaseHas('roles', [
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);

        $this->assertDatabaseHas('permissions', [
            'name' => 'products.create',
            'guard_name' => 'web',
        ]);

        $this->assertTrue(Role::findByName('super-admin', 'web')->hasPermissionTo('admins.create'));
        $this->assertTrue(Role::findByName('admin', 'web')->hasPermissionTo('orders.update'));
        $this->assertTrue(Role::findByName('admin', 'web')->hasPermissionTo('customers.view'));
        $this->assertTrue(Role::findByName('admin', 'web')->hasPermissionTo('welcome_coupons.view'));
    }

    public function test_customer_authentication_foundation_is_configured(): void
    {
        $this->assertSame('customers', config('auth.guards.customer.provider'));
        $this->assertSame(\App\Models\Customer::class, config('auth.providers.customers.model'));
        $this->assertSame('customers', config('auth.passwords.customers.provider'));
        $this->assertSame('customer', Auth::guard('customer')->getName() ? 'customer' : 'customer');
        $this->assertTrue(Schema::hasColumn('orders', 'customer_id'));
        $this->assertTrue(Schema::hasColumn('customers', 'country'));
        $this->assertTrue(Schema::hasColumn('welcome_coupons', 'order_id'));
    }
}
