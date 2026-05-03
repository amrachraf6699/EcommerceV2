<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StorefrontPricingContextTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        Setting::query()->create([
            'group' => 'brand',
            'key' => 'name',
            'label' => 'Brand name',
            'value' => 'SunFlower',
            'input_type' => 'text',
            'sort_order' => 1,
        ]);

        $category = Category::query()->create([
            'name' => 'Running',
            'slug' => 'running',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->product = Product::query()->create([
            'name' => 'Runner Pro',
            'slug' => 'runner-pro',
            'short_description' => 'Performance runner',
            'description' => 'Full product description',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $this->product->categories()->attach($category);

        ProductVariant::query()->create([
            'product_id' => $this->product->id,
            'name' => '42',
            'sku' => 'RUNNER-42',
            'price' => 489,
            'compare_at_price' => 599,
            'stock_quantity' => 4,
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    public function test_pricing_context_endpoint_returns_enabled_conversion_context(): void
    {
        Http::fake([
            'http://ip-api.com/json/8.8.8.8?fields=status,countryCode' => Http::response([
                'status' => 'success',
                'countryCode' => 'US',
            ], 200),
            'https://api.frankfurter.dev/*' => Http::response([
                'base' => 'BHD',
                'quote' => 'USD',
                'date' => '2026-04-29',
                'rate' => 2.65,
            ], 200),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->getJson(route('storefront.pricing.context', ['locale' => 'en']))
            ->assertOk()
            ->assertJsonPath('pricing.base_currency', 'BHD')
            ->assertJsonPath('pricing.detected_country_code', 'US')
            ->assertJsonPath('pricing.detected_currency', 'USD')
            ->assertJsonPath('pricing.rate', 2.65)
            ->assertJsonPath('pricing.rate_date', '2026-04-29')
            ->assertJsonPath('pricing.enabled', true);
    }

    public function test_pricing_context_endpoint_is_disabled_for_bahrain_currency(): void
    {
        Http::fake([
            'http://ip-api.com/json/8.8.8.8?fields=status,countryCode' => Http::response([
                'status' => 'success',
                'countryCode' => 'BH',
            ], 200),
            'https://ipwho.is/8.8.8.8' => Http::response([
                'success' => true,
                'country_code' => 'BH',
            ], 200),
            'https://ipapi.co/8.8.8.8/json/' => Http::response([
                'country_code' => 'BH',
            ], 200),
        ]);

        Cache::flush();

        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->getJson(route('storefront.pricing.context', ['locale' => 'en']))
            ->assertOk()
            ->assertJsonPath('pricing.base_currency', 'BHD')
            ->assertJsonPath('pricing.detected_country_code', 'BH')
            ->assertJsonPath('pricing.detected_currency', 'BHD')
            ->assertJsonPath('pricing.rate', null)
            ->assertJsonPath('pricing.enabled', false);
    }

    public function test_product_page_renders_price_placeholders_for_client_side_conversion(): void
    {
        Http::fake([
            'http://ip-api.com/json/8.8.8.8?fields=status,countryCode' => Http::response([
                'status' => 'success',
                'countryCode' => 'US',
            ], 200),
            'https://api.frankfurter.dev/*' => Http::response([
                'base' => 'BHD',
                'quote' => 'USD',
                'date' => '2026-04-29',
                'rate' => 2.65,
            ], 200),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->get(route('storefront.products.show', ['locale' => 'en', 'product' => $this->product->slug]))
            ->assertOk()
            ->assertSee('data-price-root', false)
            ->assertSee('data-bhd-amount="489.00"', false)
            ->assertSee('data-bhd-primary', false)
            ->assertDontSee('data-local-price', false);
    }
}
