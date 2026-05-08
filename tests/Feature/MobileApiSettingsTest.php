<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_storefront_safe_group_settings_and_file_urls(): void
    {
        Setting::query()->create([
            'group' => 'brand',
            'key' => 'name',
            'label' => 'Brand Name',
            'value' => 'SunFlower',
            'input_type' => 'text',
            'sort_order' => 1,
        ]);

        Setting::query()->create([
            'group' => 'brand',
            'key' => 'logo',
            'label' => 'Logo',
            'value' => 'settings/logo.png',
            'input_type' => 'file',
            'sort_order' => 2,
        ]);

        Setting::query()->create([
            'group' => 'brand',
            'key' => 'default_theme',
            'label' => 'Default theme',
            'value' => 'dark',
            'input_type' => 'select',
            'options' => ['dark', 'light'],
            'sort_order' => 3,
        ]);

        $this->getJson('/api/v1/settings/brand')
            ->assertOk()
            ->assertJsonPath('group', 'brand')
            ->assertJsonPath('settings.0.key', 'name')
            ->assertJsonPath('settings.0.value', 'SunFlower')
            ->assertJsonPath('settings.1.key', 'logo')
            ->assertJsonPath('settings.1.value', 'settings/logo.png')
            ->assertJsonPath('settings.1.asset_url', asset('storage/settings/logo.png'))
            ->assertJsonPath('settings.2.key', 'default_theme')
            ->assertJsonPath('settings.2.options.0', 'dark');
    }

    public function test_boolean_values_are_normalized_and_sensitive_groups_are_not_exposed(): void
    {
        Setting::query()->create([
            'group' => 'marketing',
            'key' => 'welcome_coupon_enabled',
            'label' => 'Welcome Coupon',
            'value' => '1',
            'input_type' => 'boolean',
            'sort_order' => 1,
        ]);

        Setting::query()->create([
            'group' => 'payment',
            'key' => 'tap_secret_key',
            'label' => 'Tap Secret',
            'value' => 'secret',
            'input_type' => 'password',
            'sort_order' => 1,
        ]);

        $this->getJson('/api/v1/settings/marketing')
            ->assertOk()
            ->assertJsonPath('settings.0.key', 'welcome_coupon_enabled')
            ->assertJsonPath('settings.0.value', true);

        $this->getJson('/api/v1/settings/payment')
            ->assertNotFound();
    }
}
