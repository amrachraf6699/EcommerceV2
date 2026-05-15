<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontHomeShopBySizeTest extends TestCase
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

    public function test_home_shop_by_size_shows_each_unique_size_individually(): void
    {
        $product = Product::query()->create([
            'name' => 'Trainer',
            'slug' => 'trainer',
            'is_active' => true,
            'is_featured' => true,
        ]);

        foreach (['40', '41', '42', '43', '44', '45', '46', '42'] as $index => $size) {
            ProductVariant::query()->create([
                'product_id' => $product->id,
                'size' => $size,
                'color' => 'Black',
                'price' => 100 + $index,
                'stock_quantity' => 2,
                'is_default' => $index === 0,
                'is_active' => true,
            ]);
        }

        $response = $this->get(route('storefront.home', ['locale' => 'en']));

        $response->assertOk();
        $this->assertSame(7, substr_count($response->getContent(), 'class="shop-by-size-card reveal"'));
        $response->assertSeeInOrder([
            '<strong>40</strong>',
            '<strong>41</strong>',
            '<strong>42</strong>',
            '<strong>43</strong>',
            '<strong>44</strong>',
            '<strong>45</strong>',
            '<strong>46</strong>',
        ], false);
    }
}
