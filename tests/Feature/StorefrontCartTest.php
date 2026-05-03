<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\FrontendCartManager;
use App\Support\FrontendTemplateData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StorefrontCartTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private ProductVariant $variant;

    private string $sessionId = 'storefront-cart-test';

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::query()->create([
            'name' => 'Runner Pro',
            'slug' => 'runner-pro',
            'short_description' => 'Performance runner',
            'description' => 'Full product description',
            'is_active' => true,
            'is_featured' => false,
        ]);

        $this->variant = ProductVariant::query()->create([
            'product_id' => $this->product->id,
            'name' => '42',
            'sku' => 'RUNNER-42',
            'price' => 100,
            'stock_quantity' => 4,
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    private function postCart(string $route, array $payload)
    {
        return $this->withHeaders([
            'Accept' => 'application/json',
        ])->withCookie(config('session.cookie'), Crypt::encryptString($this->sessionId))
            ->post($route, $payload);
    }

    private function getCart(string $route)
    {
        return $this->withHeaders([
            'Accept' => 'application/json',
        ])->withCookie(config('session.cookie'), Crypt::encryptString($this->sessionId))
            ->get($route);
    }

    private function cartManagerRequest(): Request
    {
        $request = Request::create('/en/cart/items', 'POST');
        $session = app('session.store');
        $session->start();
        $request->setLaravelSession($session);

        return $request;
    }

    public function test_adding_a_valid_variant_creates_a_session_cart_and_item(): void
    {
        $this->postCart(route('storefront.cart.items.store', ['locale' => 'en']), [
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ])->assertOk()
            ->assertJsonPath('message', __('storefront.product.added_to_cart', [], 'en'))
            ->assertJsonPath('cart.items_count', 2)
            ->assertJsonPath('cart.subtotal', 200)
            ->assertJsonPath('cart.currency', 'BHD');

        $cart = Cart::query()->firstOrFail();

        $this->assertSame(2, $cart->item_count);
        $this->assertSame('200.00', $cart->subtotal);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
            'line_total' => '200.00',
        ]);
    }

    public function test_adding_the_same_variant_twice_increments_quantity_instead_of_creating_duplicate_rows(): void
    {
        $request = $this->cartManagerRequest();
        $manager = app(FrontendCartManager::class);

        $manager->addItem($request, $this->product->id, $this->variant->id, 1);
        $summary = $manager->addItem($request, $this->product->id, $this->variant->id, 2);

        $this->assertSame(3, $summary['items_count']);
        $this->assertSame(300.0, $summary['subtotal']);

        $this->assertSame(1, CartItem::query()->count());
        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $this->variant->id,
            'quantity' => 3,
            'line_total' => '300.00',
        ]);
    }

    public function test_inactive_variant_is_rejected(): void
    {
        $this->variant->update(['is_active' => false]);

        $this->postCart(route('storefront.cart.items.store', ['locale' => 'en']), [
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
        ])->assertStatus(422)
            ->assertJsonValidationErrors('product_variant_id');

        $this->assertDatabaseCount('carts', 0);
    }

    public function test_out_of_stock_variant_is_rejected(): void
    {
        $this->variant->update(['stock_quantity' => 0]);

        $this->postCart(route('storefront.cart.items.store', ['locale' => 'en']), [
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
        ])->assertStatus(422)
            ->assertJsonValidationErrors('product_variant_id');
    }

    public function test_request_exceeding_available_stock_is_rejected(): void
    {
        $request = $this->cartManagerRequest();
        $manager = app(FrontendCartManager::class);

        $manager->addItem($request, $this->product->id, $this->variant->id, 3);

        try {
            $manager->addItem($request, $this->product->id, $this->variant->id, 2);
            $this->fail('Expected a validation exception for exceeding stock.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quantity', $exception->errors());
        }

        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $this->variant->id,
            'quantity' => 3,
        ]);
    }

    public function test_variant_product_mismatch_is_rejected(): void
    {
        $otherProduct = Product::query()->create([
            'name' => 'Street Pro',
            'slug' => 'street-pro',
            'is_active' => true,
        ]);

        $otherVariant = ProductVariant::query()->create([
            'product_id' => $otherProduct->id,
            'name' => '43',
            'sku' => 'STREET-43',
            'price' => 120,
            'stock_quantity' => 5,
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->postCart(route('storefront.cart.items.store', ['locale' => 'en']), [
            'product_id' => $this->product->id,
            'product_variant_id' => $otherVariant->id,
            'quantity' => 1,
        ])->assertStatus(422)
            ->assertJsonValidationErrors('product_variant_id');
    }

    public function test_cart_summary_is_reflected_in_shared_frontend_data_and_summary_endpoint(): void
    {
        $request = $this->cartManagerRequest();
        $manager = app(FrontendCartManager::class);
        $manager->addItem($request, $this->product->id, $this->variant->id, 3);

        $sessionId = $request->session()->getId();
        $summary = $manager->summaryForSession($sessionId);
        $shared = FrontendTemplateData::shared($sessionId);

        $this->assertSame(3, $summary['items_count']);
        $this->assertSame(300.0, $summary['subtotal']);
        $this->assertSame('BHD', $summary['currency']);
        $this->assertSame(3, $shared['frontendCartSummary']['items_count']);
        $this->assertSame(300.0, $shared['frontendCartSummary']['subtotal']);
    }

    public function test_product_page_renders_disabled_purchase_controls_for_initial_out_of_stock_variant(): void
    {
        $this->variant->update(['stock_quantity' => 0]);

        $this->get(route('storefront.products.show', ['locale' => 'en', 'product' => $this->product->slug]))
            ->assertOk()
            ->assertSee('id="qtyIncreaseButton" type="button" onclick="changeQty(1)" disabled', false)
            ->assertSee('id="productAddToCartButton" type="button" onclick="addToCart()" disabled', false)
            ->assertSee('id="stickyAddToCartButton" style="width:auto;padding:12px 24px;font-size:14px" type="button" onclick="addToCart()" disabled', false);
    }
}
