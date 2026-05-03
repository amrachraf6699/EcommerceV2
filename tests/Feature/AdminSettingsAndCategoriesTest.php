<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\AdminAuthorizationSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSettingsAndCategoriesTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAuthorizationSeeder::class);
        $this->seed(SettingsSeeder::class);

        $this->superAdmin = User::factory()->create(['is_active' => true]);
        $this->superAdmin->assignRole('super-admin');
    }

    public function test_settings_page_renders_grouped_settings(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.settings.index'))
            ->assertOk();
    }

    public function test_settings_text_boolean_select_and_file_values_can_be_saved(): void
    {
        Storage::fake('public');

        $this->actingAs($this->superAdmin)->put(route('admin.settings.update'), [
            'group' => 'brand',
            'name' => 'SunFlower',
            'address' => 'Cairo',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertRedirect();

        $this->actingAs($this->superAdmin)->put(route('admin.settings.update'), [
            'group' => 'marketing',
            'welcome_coupon_enabled' => '1',
            'welcome_coupon_discount_mode' => 'random_percent',
            'welcome_coupon_min_value' => '10',
            'welcome_coupon_max_value' => '20',
            'track_order_enabled' => '0',
        ])->assertRedirect();

        $this->actingAs($this->superAdmin)->put(route('admin.settings.update'), [
            'group' => 'shipping',
            'shipping_type' => 'fixed',
            'shipping_inside_country' => '50',
            'shipping_outside_country' => '120',
            'enable_vat' => '1',
            'vat_value' => '15',
        ])->assertRedirect();

        $this->assertDatabaseHas('settings', ['key' => 'name', 'value' => 'SunFlower']);
        $this->assertDatabaseHas('settings', ['key' => 'address', 'value' => 'Cairo']);
        $this->assertDatabaseHas('settings', ['key' => 'welcome_coupon_enabled', 'value' => '1']);
        $this->assertDatabaseHas('settings', ['key' => 'welcome_coupon_discount_mode', 'value' => 'random_percent']);
        $this->assertDatabaseHas('settings', ['key' => 'welcome_coupon_min_value', 'value' => '10']);
        $this->assertDatabaseHas('settings', ['key' => 'welcome_coupon_max_value', 'value' => '20']);
        $this->assertDatabaseHas('settings', ['key' => 'track_order_enabled', 'value' => '0']);
        $this->assertDatabaseHas('settings', ['key' => 'shipping_type', 'value' => 'fixed']);

        $logoSetting = Setting::where('key', 'logo')->firstOrFail();
        Storage::disk('public')->assertExists($logoSetting->value);
    }

    public function test_category_crud_and_unique_slug_validation_work(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->superAdmin)->post(route('admin.categories.store'), [
            'name' => 'Skin Care',
            'slug' => 'skin-care',
            'description' => 'Main category',
            'image' => UploadedFile::fake()->image('skin-care.png'),
            'is_active' => '1',
            'sort_order' => 1,
        ]);

        $category = Category::firstOrFail();
        $response->assertRedirect(route('admin.categories.index'));
        Storage::disk('public')->assertExists($category->image);

        Category::create(['name' => 'Body', 'slug' => 'body']);

        $this->actingAs($this->superAdmin)->post(route('admin.categories.store'), [
            'name' => 'Duplicate',
            'slug' => 'body',
        ])->assertSessionHasErrors('slug');
    }
}
