<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class StorefrontChatbotTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    private Category $emptyCategory;

    private Product $featuredProduct;

    private Product $plainProduct;

    private ProductVariant $featuredVariant;

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
            'key' => 'chatbot_enabled',
            'label' => 'Enable chatbot',
            'value' => '1',
            'input_type' => 'boolean',
            'sort_order' => 2,
        ]);

        $this->category = Category::query()->create([
            'name' => 'Shoes',
            'slug' => 'shoes',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->emptyCategory = Category::query()->create([
            'name' => 'Empty',
            'slug' => 'empty',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->featuredProduct = Product::query()->create([
            'name' => 'Runner Pro',
            'slug' => 'runner-pro',
            'is_active' => true,
            'is_featured' => true,
        ]);
        $this->featuredProduct->categories()->attach($this->category);

        $this->plainProduct = Product::query()->create([
            'name' => 'Street Pro',
            'slug' => 'street-pro',
            'is_active' => true,
            'is_featured' => false,
        ]);

        $this->featuredVariant = ProductVariant::query()->create([
            'product_id' => $this->featuredProduct->id,
            'name' => '42',
            'sku' => 'RUNNER-42',
            'price' => 100,
            'stock_quantity' => 4,
            'is_default' => true,
            'is_active' => true,
        ]);

        ProductVariant::query()->create([
            'product_id' => $this->plainProduct->id,
            'name' => '43',
            'sku' => 'STREET-43',
            'price' => 120,
            'stock_quantity' => 3,
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    private function chatPost(string $route, array $payload)
    {
        return $this->withoutMiddleware(VerifyCsrfToken::class)->withHeaders([
            'Accept' => 'application/json',
        ])->withCookie(config('session.cookie'), Crypt::encryptString('chatbot-session'))
            ->post($route, $payload);
    }

    public function test_chatbot_trigger_is_shown_only_when_enabled(): void
    {
        $this->get(route('storefront.home', ['locale' => 'en']))
            ->assertOk()
            ->assertSee('id="chatbotWidget"', false);

        Setting::query()
            ->where('key', 'chatbot_enabled')
            ->firstOrFail()
            ->update(['value' => '0']);

        $this->get(route('storefront.home', ['locale' => 'en']))
            ->assertOk()
            ->assertDontSee('id="chatbotWidget"', false);
    }

    public function test_chatbot_category_and_products_endpoints_return_structured_payloads(): void
    {
        $this->getJson(route('storefront.chatbot.categories.index', ['locale' => 'en']))
            ->assertOk()
            ->assertJsonPath('categories.0.slug', 'shoes')
            ->assertJsonPath('categories.0.products_count', 1);

        $this->getJson(route('storefront.chatbot.categories.products.index', ['locale' => 'en', 'category' => $this->category->slug]))
            ->assertOk()
            ->assertJsonPath('state', 'products')
            ->assertJsonPath('products.0.slug', 'runner-pro');
    }

    public function test_empty_category_and_fallback_endpoints_support_chatbot_branching(): void
    {
        $this->getJson(route('storefront.chatbot.categories.products.index', ['locale' => 'en', 'category' => $this->emptyCategory->slug]))
            ->assertOk()
            ->assertJsonPath('state', 'empty')
            ->assertJsonPath('actions.0.action', 'load_fallback_products')
            ->assertJsonPath('actions.1.action', 'select_another_category');

        $this->getJson(route('storefront.chatbot.categories.fallback-products.index', ['locale' => 'en', 'category' => $this->emptyCategory->slug]))
            ->assertOk()
            ->assertJsonPath('state', 'products')
            ->assertJsonPath('products.0.slug', 'runner-pro');
    }

    public function test_chatbot_variants_endpoint_filters_to_purchasable_variants(): void
    {
        ProductVariant::query()->create([
            'product_id' => $this->featuredProduct->id,
            'name' => '44',
            'sku' => 'RUNNER-44',
            'price' => 100,
            'stock_quantity' => 0,
            'is_default' => false,
            'is_active' => true,
        ]);

        $this->getJson(route('storefront.chatbot.products.variants.index', ['locale' => 'en', 'product' => $this->featuredProduct->slug]))
            ->assertOk()
            ->assertJsonPath('state', 'variants')
            ->assertJsonCount(1, 'variants')
            ->assertJsonPath('variants.0.id', $this->featuredVariant->id);
    }

    public function test_chatbot_add_to_cart_endpoint_reuses_cart_logic_and_returns_follow_up_actions(): void
    {
        $this->chatPost(route('storefront.chatbot.cart-items.store', ['locale' => 'en']), [
            'product_id' => $this->featuredProduct->id,
            'product_variant_id' => $this->featuredVariant->id,
            'quantity' => 2,
        ])->assertOk()
            ->assertJsonPath('cart.items_count', 2)
            ->assertJsonPath('cart.subtotal', 200)
            ->assertJsonPath('actions.0.action', 'checkout')
            ->assertJsonPath('actions.1.action', 'add_more_products')
            ->assertJsonPath('actions.2.action', 'close_chat');
    }
}
