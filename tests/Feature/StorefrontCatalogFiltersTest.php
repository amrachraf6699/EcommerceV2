<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontCatalogFiltersTest extends TestCase
{
    use RefreshDatabase;

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
    }

    public function test_catalog_filters_products_by_variant_name_and_price_range(): void
    {
        $category = Category::query()->create([
            'name' => 'Sneakers',
            'slug' => 'sneakers',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $matchingProduct = Product::query()->create([
            'name' => 'Runner One',
            'slug' => 'runner-one',
            'is_active' => true,
            'is_featured' => true,
        ]);
        $matchingProduct->categories()->attach($category);

        ProductVariant::query()->create([
            'product_id' => $matchingProduct->id,
            'name' => '42',
            'sku' => 'RUN-42',
            'price' => 120,
            'stock_quantity' => 3,
            'is_default' => true,
            'is_active' => true,
        ]);

        $otherProduct = Product::query()->create([
            'name' => 'Runner Two',
            'slug' => 'runner-two',
            'is_active' => true,
            'is_featured' => false,
        ]);
        $otherProduct->categories()->attach($category);

        ProductVariant::query()->create([
            'product_id' => $otherProduct->id,
            'name' => '44',
            'sku' => 'RUN-44',
            'price' => 220,
            'stock_quantity' => 5,
            'is_default' => true,
            'is_active' => true,
        ]);

        $response = $this->get(route('storefront.catalog', [
            'locale' => 'en',
            'category' => $category->slug,
            'sizes' => ['42'],
            'min_price' => 100,
            'max_price' => 150,
        ]));

        $response->assertOk();
        $response->assertDontSeeText('Runner Two');
        $response->assertSeeText('120.00');
        $response->assertDontSeeText('220.00');
        $response->assertSeeText('42');
        $response->assertSeeText('Price Range');
    }

    public function test_catalog_page_lists_distinct_variant_names_as_size_filters(): void
    {
        $product = Product::query()->create([
            'name' => 'Trainer',
            'slug' => 'trainer',
            'is_active' => true,
            'is_featured' => true,
        ]);

        ProductVariant::query()->create([
            'product_id' => $product->id,
            'name' => '41',
            'sku' => 'TR-41',
            'price' => 90,
            'stock_quantity' => 4,
            'is_default' => true,
            'is_active' => true,
        ]);

        ProductVariant::query()->create([
            'product_id' => $product->id,
            'name' => '41',
            'sku' => 'TR-41-B',
            'price' => 95,
            'stock_quantity' => 2,
            'is_default' => false,
            'is_active' => true,
        ]);

        ProductVariant::query()->create([
            'product_id' => $product->id,
            'name' => '43',
            'sku' => 'TR-43',
            'price' => 100,
            'stock_quantity' => 2,
            'is_default' => false,
            'is_active' => true,
        ]);

        $response = $this->get(route('storefront.catalog', ['locale' => 'en']));

        $response->assertOk();
        $response->assertSee('value="41"', false);
        $response->assertSee('value="43"', false);
    }
}
