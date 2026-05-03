<?php

namespace Tests\Feature;

use App\Models\Setting;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_seeder_keeps_existing_values_and_adds_new_recaptcha_keys(): void
    {
        Setting::query()->create([
            'group' => 'security',
            'key' => 'recaptcha_site_key',
            'label' => 'Existing site key',
            'value' => 'keep-this-value',
            'input_type' => 'text',
            'sort_order' => 1,
        ]);

        $this->seed(SettingsSeeder::class);

        $this->assertDatabaseHas('settings', [
            'key' => 'recaptcha_site_key',
            'value' => 'keep-this-value',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'recaptcha_secret_key',
            'group' => 'security',
            'input_type' => 'password',
        ]);

        $this->assertSame(1, Setting::query()->where('key', 'recaptcha_site_key')->count());
    }
}
