<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\Slider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->create([
            'group' => 'brand',
            'key' => 'name',
            'label' => 'Brand name',
            'value' => 'SunFlower',
            'input_type' => 'text',
            'sort_order' => 1,
        ]);

        Setting::query()->create([
            'group' => 'marketing',
            'key' => 'welcome_coupon_enabled',
            'label' => 'Enable welcome coupon',
            'value' => '1',
            'input_type' => 'boolean',
            'sort_order' => 2,
        ]);

        $this->category = Category::query()->create([
            'name' => 'Sport',
            'slug' => 'sport',
            'description' => 'Selected sport category',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->product = Product::query()->create([
            'name' => 'Air Max 270',
            'slug' => 'air-max-270',
            'short_description' => 'Real product inside the storefront',
            'description' => 'Full product description',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $this->product->categories()->attach($this->category);

        ProductVariant::query()->create([
            'product_id' => $this->product->id,
            'name' => '42',
            'sku' => 'AM270-42',
            'price' => 489,
            'compare_at_price' => 599,
            'stock_quantity' => 4,
            'is_default' => true,
            'is_active' => true,
        ]);

        Slider::query()->create([
            'title' => 'Hero slide',
            'subtitle' => 'Real storefront content',
            'image' => 'sliders/hero.jpg',
            'link' => 'https://example.com',
            'text_color' => '#ffffff',
            'button_background_color' => '#ffffff',
            'button_text_color' => '#000000',
            'horizontal_align' => 'center',
            'vertical_align' => 'center',
            'is_active' => true,
        ]);
    }

    public function test_settings_helpers_support_dot_lookup_group_lookup_and_boolean_casting(): void
    {
        $this->assertSame('SunFlower', setting('brand.name'));
        $this->assertCount(1, setting_group('marketing'));
        $this->assertTrue(setting_bool('marketing.welcome_coupon_enabled'));
        $this->assertSame('fallback', setting('marketing.missing', 'fallback'));
    }

    public function test_homepage_renders_template_based_storefront_and_welcome_coupon_popup(): void
    {
        $response = $this->get(route('storefront.home'));

        $response->assertOk();
        $response->assertSeeText('Hero slide');
        $response->assertSeeText('Air Max 270');
        $response->assertSeeText('SunFlower');
        $response->assertSee('id="welcomeCouponPopup"', false);
        $response->assertDontSeeText('Homepage System');
    }

    public function test_homepage_hides_welcome_coupon_popup_when_setting_is_disabled(): void
    {
        Setting::query()->where('group', 'marketing')->where('key', 'welcome_coupon_enabled')->update(['value' => '0']);

        $this->get(route('storefront.home'))
            ->assertOk()
            ->assertDontSee('id="welcomeCouponPopup"', false);
    }

    public function test_catalog_category_and_product_routes_render_successfully(): void
    {
        $this->get(route('storefront.catalog'))
            ->assertOk()
            ->assertSeeText('Air Max 270');

        $this->get(route('storefront.categories.show', ['category' => $this->category->slug]))
            ->assertOk()
            ->assertSeeText('Sport');

        $this->get(route('storefront.products.show', ['product' => $this->product->slug]))
            ->assertOk()
            ->assertSeeText('Air Max 270')
            ->assertSeeText('489.00');
    }

    public function test_empty_category_page_shows_fallback_actions_and_ajax_endpoint_returns_products(): void
    {
        $emptyCategory = Category::query()->create([
            'name' => 'Empty',
            'slug' => 'empty',
            'description' => 'No products here',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->get(route('storefront.categories.show', ['locale' => 'en', 'category' => $emptyCategory->slug]))
            ->assertOk()
            ->assertSeeText('No products are available in this category yet.')
            ->assertSeeText('Browse another category')
            ->assertSeeText('Load me some products');

        $this->getJson(route('storefront.categories.fallback-products', ['locale' => 'en', 'category' => $emptyCategory->slug]))
            ->assertOk()
            ->assertJsonPath('title', 'Suggested products for you')
            ->assertJsonStructure(['html', 'title']);
    }
}
