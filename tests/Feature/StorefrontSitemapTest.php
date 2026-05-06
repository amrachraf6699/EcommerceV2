<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontSitemapTest extends TestCase
{
    use RefreshDatabase;

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
    }

    public function test_sitemap_xml_contains_expected_public_localized_urls(): void
    {
        $activeCategory = Category::query()->create([
            'name' => 'Shoes',
            'slug' => 'shoes',
            'description' => 'Shoes',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Category::query()->create([
            'name' => 'Hidden',
            'slug' => 'hidden',
            'description' => 'Hidden',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $activeProduct = Product::query()->create([
            'name' => 'Runner Pro',
            'slug' => 'runner-pro',
            'short_description' => 'Runner Pro',
            'description' => 'Runner Pro',
            'is_active' => true,
            'is_featured' => true,
        ]);

        Product::query()->create([
            'name' => 'Hidden Product',
            'slug' => 'hidden-product',
            'short_description' => 'Hidden Product',
            'description' => 'Hidden Product',
            'is_active' => false,
            'is_featured' => false,
        ]);

        $activeProduct->categories()->attach($activeCategory);

        $page = Page::query()->create([
            'title' => 'About Us',
            'slug' => 'about-us',
            'content' => 'About content',
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $response->assertSee('/ar</loc>', false);
        $response->assertSee('/en</loc>', false);
        $response->assertSee('/ar/catalog</loc>', false);
        $response->assertSee('/en/categories</loc>', false);
        $response->assertSee('/ar/contact</loc>', false);
        $response->assertSee('/ar/categories/' . $activeCategory->slug . '</loc>', false);
        $response->assertSee('/en/products/' . $activeProduct->slug . '</loc>', false);
        $response->assertSee('/ar/p/' . $page->slug . '</loc>', false);

        $response->assertDontSee('/ar/track-order</loc>', false);
        $response->assertDontSee('/ar/cart</loc>', false);
        $response->assertDontSee('/products/hidden-product</loc>', false);
        $response->assertDontSee('/categories/hidden</loc>', false);
    }
}
