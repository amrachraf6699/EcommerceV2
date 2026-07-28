<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class AdminVariantColorTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAuthorizationSeeder::class);
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole('super-admin');
    }

    public function test_admin_variant_colors_are_normalized_to_uppercase_six_digit_hex(): void
    {
        $product = Product::query()->create(['name' => 'Runner', 'slug' => 'runner']);

        $this->actingAs($this->admin)
            ->postJson(route('admin.products.variants.store', $product), [
                'size' => '42',
                'color' => '#aBc',
                'price' => 100,
                'stock_quantity' => 5,
                'is_active' => true,
            ])
            ->assertOk();

        $variant = ProductVariant::query()->sole();

        $this->assertSame('#AABBCC', $variant->color);

        $this->actingAs($this->admin)
            ->putJson(route('admin.products.variants.update', [$product, $variant]), [
                'size' => '42',
                'color' => '12ab34',
                'price' => 100,
                'stock_quantity' => 5,
                'is_active' => true,
            ])
            ->assertOk();

        $this->assertSame('#12AB34', $variant->fresh()->color);
    }

    public function test_admin_variant_colors_reject_non_hex_values(): void
    {
        $product = Product::query()->create(['name' => 'Runner', 'slug' => 'runner']);

        $this->actingAs($this->admin)
            ->postJson(route('admin.products.variants.store', $product), [
                'size' => '42',
                'color' => 'blue',
                'price' => 100,
                'stock_quantity' => 5,
                'is_active' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('color');
    }
}
