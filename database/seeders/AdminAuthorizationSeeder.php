<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminAuthorizationSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'analytics.view',
            'admins.view',
            'admins.create',
            'admins.update',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'settings.view',
            'settings.update',
            'pages.view',
            'pages.create',
            'pages.update',
            'pages.delete',
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
            'customers.view',
            'customers.create',
            'customers.update',
            'customers.delete',
            'sliders.view',
            'sliders.create',
            'sliders.update',
            'sliders.delete',
            'clients.view',
            'clients.create',
            'clients.update',
            'clients.delete',
            'orders.view',
            'orders.update',
            'carts.view',
            'coupons.view',
            'coupons.create',
            'coupons.update',
            'coupons.delete',
            'welcome_coupons.view',
            'contact_messages.view',
            'contact_messages.update',
            'contact_messages.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('super-admin', 'web');
        $admin = Role::findOrCreate('admin', 'web');

        $superAdmin->syncPermissions(Permission::all());
        $admin->syncPermissions([
            'dashboard.view',
            'analytics.view',
            'products.view',
            'products.create',
            'products.update',
            'categories.view',
            'categories.create',
            'categories.update',
            'customers.view',
            'customers.create',
            'customers.update',
            'sliders.view',
            'sliders.create',
            'sliders.update',
            'clients.view',
            'clients.create',
            'clients.update',
            'orders.view',
            'orders.update',
            'settings.view',
            'pages.view',
            'pages.create',
            'pages.update',
            'carts.view',
            'coupons.view',
            'coupons.create',
            'coupons.update',
            'welcome_coupons.view',
            'contact_messages.view',
            'contact_messages.update',
        ]);
    }
}
