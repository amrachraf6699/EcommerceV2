<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCatalogOrdersCartsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAuthorizationSeeder::class);

        $this->superAdmin = User::factory()->create(['is_active' => true]);
        $this->superAdmin->assignRole('super-admin');
    }

    public function test_product_create_update_and_filtering_work(): void
    {
        Storage::fake('public');

        $category = Category::create(['name' => 'Serums', 'slug' => 'serums']);

        $this->actingAs($this->superAdmin)->post(route('admin.products.store'), [
            'name' => 'Glow Serum',
            'slug' => 'glow-serum',
            'label' => 'Best seller',
            'short_description' => 'Short',
            'description' => 'Long',
            'meta_title' => 'Glow SEO',
            'meta_description' => 'SEO text',
            'categories' => [$category->id],
            'is_active' => '1',
            'is_featured' => '1',
            'variants' => [
                [
                    'name' => '30ml',
                    'price' => 100,
                    'stock_quantity' => 5,
                    'is_default' => '1',
                    'is_active' => '1',
                ],
                [
                    'name' => '60ml',
                    'price' => 150,
                    'stock_quantity' => 8,
                    'is_active' => '1',
                ],
            ],
            'images' => [
                UploadedFile::fake()->image('glow-front.png'),
                UploadedFile::fake()->image('glow-back.png'),
            ],
            'image_alt_text' => 'Glow serum',
            'image_sort_order' => 10,
            'image_variant_index' => 0,
            'images_primary' => '1',
        ])->assertRedirect();

        $product = Product::firstOrFail();
        $this->assertSame('Best seller', $product->label);
        $this->assertTrue($product->categories->contains($category));
        $this->assertCount(2, $product->variants);
        $this->assertCount(2, $product->images);
        $this->assertSame(1, $product->images()->where('is_primary', true)->count());
        $this->assertSame([10, 11], $product->images()->orderBy('sort_order')->pluck('sort_order')->all());

        foreach ($product->images as $image) {
            $this->assertSame('Glow serum', $image->alt_text);
            $this->assertSame($product->variants()->orderBy('id')->first()->id, $image->product_variant_id);
            Storage::disk('public')->assertExists($image->path);
        }

        $this->actingAs($this->superAdmin)->put(route('admin.products.update', $product), [
            'name' => 'Glow Serum Updated',
            'slug' => 'glow-serum-updated',
            'label' => 'Updated label',
            'categories' => [$category->id],
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertSame('Updated label', $product->fresh()->label);

        $this->actingAs($this->superAdmin)->get(route('admin.products.index', ['category' => $category->id]))
            ->assertOk()
            ->assertSeeText('Glow Serum Updated');
    }

    public function test_variant_rules_and_image_uploads_work(): void
    {
        Storage::fake('public');

        $product = Product::create(['name' => 'Glow Serum', 'slug' => 'glow-serum']);
        $otherProduct = Product::create(['name' => 'Other Serum', 'slug' => 'other-serum']);

        $this->actingAs($this->superAdmin)->post(route('admin.products.variants.store', $product), [
            'name' => '30ml',
            'price' => 100,
            'stock_quantity' => 5,
            'is_default' => '1',
            'is_active' => '1',
        ])->assertRedirect();

        $variantA = ProductVariant::firstOrFail();

        $this->actingAs($this->superAdmin)->post(route('admin.products.variants.store', $product), [
            'name' => '60ml',
            'price' => 150,
            'stock_quantity' => 8,
            'is_default' => '1',
            'is_active' => '1',
        ])->assertRedirect();

        $variantA->refresh();
        $this->assertFalse($variantA->is_default);

        $foreignVariant = ProductVariant::create([
            'product_id' => $otherProduct->id,
            'sku' => 'OTHER-1',
            'price' => 50,
            'stock_quantity' => 3,
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin)->post(route('admin.products.images.store', $product), [
            'alt_text' => 'Missing image',
        ])->assertSessionHasErrors('images');

        $this->actingAs($this->superAdmin)->post(route('admin.products.images.store', $product), [
            'images' => [UploadedFile::fake()->image('invalid.png')],
            'product_variant_id' => $foreignVariant->id,
        ])->assertSessionHasErrors('product_variant_id');

        $variantB = ProductVariant::query()->whereKeyNot($variantA->id)->firstOrFail();

        $this->actingAs($this->superAdmin)->post(route('admin.products.images.store', $product), [
            'images' => [
                UploadedFile::fake()->image('serum-front.png'),
                UploadedFile::fake()->image('serum-back.png'),
            ],
            'alt_text' => 'Glow serum',
            'is_primary' => '1',
            'sort_order' => 5,
            'product_variant_id' => $variantB->id,
        ])->assertRedirect();

        $images = ProductImage::orderBy('sort_order')->get();

        $this->assertCount(2, $images);
        $this->assertSame([5, 6], $images->pluck('sort_order')->all());
        $this->assertSame(1, ProductImage::where('is_primary', true)->count());
        $this->assertTrue($images->first()->is_primary);
        $this->assertFalse($images->last()->is_primary);

        foreach ($images as $image) {
            $this->assertSame('Glow serum', $image->alt_text);
            $this->assertSame($variantB->id, $image->product_variant_id);
            Storage::disk('public')->assertExists($image->path);
        }

        $this->actingAs($this->superAdmin)->put(route('admin.products.images.update', [$product, $images->first()]), [
            'alt_text' => 'Still invalid',
            'product_variant_id' => $foreignVariant->id,
        ])->assertSessionHasErrors('product_variant_id');

        $this->actingAs($this->superAdmin)->delete(route('admin.products.variants.destroy', [$product, $variantB]))
            ->assertRedirect();

        $variantA->refresh();
        $this->assertTrue($variantA->is_default);

        foreach ($images->fresh() as $image) {
            $this->assertNull($image->product_variant_id);
        }
    }

    public function test_product_delete_cleans_up_product_images(): void
    {
        Storage::fake('public');

        $product = Product::create(['name' => 'Delete Me', 'slug' => 'delete-me']);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'DEL-1',
            'price' => 10,
            'stock_quantity' => 1,
            'is_default' => true,
            'is_active' => true,
        ]);

        $image = $product->images()->create([
            'path' => UploadedFile::fake()->image('delete-me.png')->store('products', 'public'),
            'alt_text' => 'Delete me',
            'sort_order' => 0,
            'product_variant_id' => $variant->id,
            'is_primary' => true,
        ]);

        Storage::disk('public')->assertExists($image->path);

        $this->actingAs($this->superAdmin)->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertSoftDeleted('product_variants', ['id' => $variant->id]);
        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($image->path);
    }

    public function test_order_listing_show_and_status_updates_work_with_snapshots(): void
    {
        $product = Product::create(['name' => 'Glow Serum', 'slug' => 'glow-serum']);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SERUM-30',
            'price' => 100,
            'stock_quantity' => 5,
            'is_default' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-1',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'fulfillment_status' => 'unfulfilled',
            'currency' => 'USD',
            'customer_first_name' => 'Sara',
            'customer_last_name' => 'Ali',
            'customer_email' => 'sara@example.com',
            'grand_total' => 120,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Glow Serum',
            'variant_name' => '30ml',
            'sku' => 'SERUM-30',
            'unit_price' => 100,
            'quantity' => 1,
            'line_total' => 100,
        ]);

        $product->delete();

        $orderItem = $order->items()->with(['product', 'variant'])->firstOrFail();
        $this->assertNotNull($orderItem->product);
        $this->assertTrue($orderItem->product->trashed());
        $this->assertNotNull($orderItem->variant);
        $this->assertTrue($orderItem->variant->trashed());

        $this->actingAs($this->superAdmin)->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSeeText('ORD-1');

        $this->actingAs($this->superAdmin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSeeText('Glow Serum');

        $this->actingAs($this->superAdmin)->put(route('admin.orders.update', $order), [
            'status' => 'processing',
            'payment_status' => 'paid',
            'fulfillment_status' => 'packed',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'processing', 'payment_status' => 'paid']);
    }

    public function test_order_snapshot_survives_hard_deleted_catalog_rows(): void
    {
        $product = Product::create(['name' => 'Archive Serum', 'slug' => 'archive-serum']);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'ARCHIVE-1',
            'price' => 50,
            'stock_quantity' => 2,
            'is_default' => true,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-HARD-1',
            'status' => 'paid',
            'payment_status' => 'paid',
            'fulfillment_status' => 'fulfilled',
            'currency' => 'USD',
            'customer_first_name' => 'Nora',
            'customer_last_name' => 'Saleh',
            'customer_email' => 'nora@example.com',
            'grand_total' => 50,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Archive Serum',
            'variant_name' => 'Default',
            'sku' => 'ARCHIVE-1',
            'unit_price' => 50,
            'quantity' => 1,
            'line_total' => 50,
        ]);

        $variant->forceDelete();
        $product->forceDelete();

        $orderItem->refresh();
        $this->assertNull($orderItem->product_id);
        $this->assertNull($orderItem->product_variant_id);

        $this->actingAs($this->superAdmin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSeeText('Archive Serum')
            ->assertSeeText('السجل المرتبط محذوف من الكتالوج');
    }

    public function test_cart_listing_and_detail_support_active_and_expired_filters(): void
    {
        $product = Product::create(['name' => 'Glow Serum', 'slug' => 'glow-serum']);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SERUM-30',
            'price' => 100,
            'stock_quantity' => 5,
            'is_default' => true,
        ]);

        $activeCart = Cart::create([
            'session_id' => 'active-session',
            'item_count' => 1,
            'subtotal' => 100,
            'last_activity_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        $expiredCart = Cart::create([
            'session_id' => 'expired-session',
            'item_count' => 1,
            'subtotal' => 100,
            'last_activity_at' => now()->subDay(),
            'expires_at' => now()->subHour(),
        ]);

        $activeCart->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Glow Serum',
            'variant_name' => '30ml',
            'sku' => 'SERUM-30',
            'unit_price' => 100,
            'quantity' => 1,
            'line_total' => 100,
        ]);

        $this->actingAs($this->superAdmin)->get(route('admin.carts.index', ['status' => 'expired']))
            ->assertOk()
            ->assertSeeText('expired-session')
            ->assertDontSeeText('active-session');

        $this->actingAs($this->superAdmin)->get(route('admin.carts.show', $activeCart))
            ->assertOk()
            ->assertSeeText('Glow Serum');

        $this->assertNotNull($expiredCart);
    }
}
