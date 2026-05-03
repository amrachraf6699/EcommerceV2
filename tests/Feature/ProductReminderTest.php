<?php

namespace Tests\Feature;

use App\Jobs\SendProductRestockReminders;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductReminder;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\ProductVariantRestockedNotification;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProductReminderTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAuthorizationSeeder::class);

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
            'stock_quantity' => 0,
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    public function test_guest_can_create_reminder_for_out_of_stock_variant(): void
    {
        $this->postJson(route('storefront.products.reminders.store', [
            'locale' => 'en',
            'product' => $this->product->slug,
        ]), [
            'product_variant_id' => $this->variant->id,
            'email' => 'guest@example.com',
        ])->assertOk()
            ->assertJson([
                'message' => __('storefront.product.reminder_created', [], 'en'),
            ]);

        $this->assertDatabaseHas('product_reminders', [
            'product_variant_id' => $this->variant->id,
            'email' => 'guest@example.com',
            'locale' => 'en',
        ]);
    }

    public function test_logged_in_customer_can_create_reminder_without_email(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Mona',
            'email' => 'mona@example.com',
            'password' => 'Password123!',
            'is_active' => true,
        ]);

        $this->actingAs($customer, 'customer')->postJson(route('storefront.products.reminders.store', [
            'locale' => 'en',
            'product' => $this->product->slug,
        ]), [
            'product_variant_id' => $this->variant->id,
        ])->assertOk();

        $this->assertDatabaseHas('product_reminders', [
            'product_variant_id' => $this->variant->id,
            'customer_id' => $customer->id,
            'email' => null,
        ]);
    }

    public function test_duplicate_reminder_submission_does_not_create_multiple_active_rows(): void
    {
        $payload = [
            'product_variant_id' => $this->variant->id,
            'email' => 'guest@example.com',
        ];

        $route = route('storefront.products.reminders.store', [
            'locale' => 'en',
            'product' => $this->product->slug,
        ]);

        $this->postJson($route, $payload)->assertOk();
        $this->postJson($route, $payload)->assertOk();

        $this->assertSame(1, ProductReminder::query()->count());
    }

    public function test_reminder_creation_fails_for_invalid_or_in_stock_variant(): void
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
            'stock_quantity' => 0,
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->postJson(route('storefront.products.reminders.store', [
            'locale' => 'en',
            'product' => $this->product->slug,
        ]), [
            'product_variant_id' => $otherVariant->id,
            'email' => 'guest@example.com',
        ])->assertStatus(422);

        $this->variant->update(['stock_quantity' => 3]);

        $this->postJson(route('storefront.products.reminders.store', [
            'locale' => 'en',
            'product' => $this->product->slug,
        ]), [
            'product_variant_id' => $this->variant->id,
            'email' => 'guest@example.com',
        ])->assertStatus(422);
    }

    public function test_updating_variant_stock_from_zero_to_positive_dispatches_reminder_job(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('super-admin');

        $this->actingAs($admin)->put(route('admin.products.variants.update', [
            'product' => $this->product,
            'variant' => $this->variant,
        ]), [
            'name' => $this->variant->name,
            'sku' => $this->variant->sku,
            'price' => $this->variant->price,
            'compare_at_price' => $this->variant->compare_at_price,
            'cost_price' => $this->variant->cost_price,
            'stock_quantity' => 5,
            'is_default' => '1',
            'is_active' => '1',
        ])->assertRedirect();

        Queue::assertPushed(SendProductRestockReminders::class, function (SendProductRestockReminders $job): bool {
            return $job->variantId === $this->variant->id;
        });
    }

    public function test_updating_variant_without_restock_transition_does_not_dispatch_reminder_job(): void
    {
        Queue::fake();

        $this->variant->update(['stock_quantity' => 5]);

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('super-admin');

        $this->actingAs($admin)->put(route('admin.products.variants.update', [
            'product' => $this->product,
            'variant' => $this->variant,
        ]), [
            'name' => $this->variant->name,
            'sku' => $this->variant->sku,
            'price' => $this->variant->price,
            'compare_at_price' => $this->variant->compare_at_price,
            'cost_price' => $this->variant->cost_price,
            'stock_quantity' => 8,
            'is_default' => '1',
            'is_active' => '1',
        ])->assertRedirect();

        Queue::assertNothingPushed();
    }

    public function test_restock_job_notifies_pending_reminders_and_marks_them_sent(): void
    {
        Notification::fake();

        $customer = Customer::query()->create([
            'name' => 'Mona',
            'email' => 'mona@example.com',
            'password' => 'Password123!',
            'is_active' => true,
        ]);

        $customerReminder = ProductReminder::query()->create([
            'product_variant_id' => $this->variant->id,
            'customer_id' => $customer->id,
            'locale' => 'en',
            'active_key' => ProductReminder::activeKey($this->variant->id, $customer->id, null),
        ]);

        $guestReminder = ProductReminder::query()->create([
            'product_variant_id' => $this->variant->id,
            'email' => 'guest@example.com',
            'locale' => 'en',
            'active_key' => ProductReminder::activeKey($this->variant->id, null, 'guest@example.com'),
        ]);

        $this->variant->update(['stock_quantity' => 4]);

        (new SendProductRestockReminders($this->variant->id))->handle();

        Notification::assertSentTo($customer, ProductVariantRestockedNotification::class);
        Notification::assertSentOnDemand(ProductVariantRestockedNotification::class, function ($notification, $channels, $notifiable): bool {
            return ($notifiable->routes['mail'] ?? null) === 'guest@example.com';
        });

        $this->assertNotNull($customerReminder->fresh()->notified_at);
        $this->assertNull($customerReminder->fresh()->active_key);
        $this->assertNotNull($guestReminder->fresh()->notified_at);
        $this->assertNull($guestReminder->fresh()->active_key);
    }

    public function test_restock_job_skips_already_notified_reminders(): void
    {
        Notification::fake();

        ProductReminder::query()->create([
            'product_variant_id' => $this->variant->id,
            'email' => 'guest@example.com',
            'locale' => 'en',
            'active_key' => null,
            'notified_at' => now(),
        ]);

        $this->variant->update(['stock_quantity' => 2]);

        (new SendProductRestockReminders($this->variant->id))->handle();

        Notification::assertNothingSent();
    }
}
