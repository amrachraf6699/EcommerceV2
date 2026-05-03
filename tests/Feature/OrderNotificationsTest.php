<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\AdminNewOrderNotification;
use App\Notifications\CustomerOrderMilestoneNotification;
use App\Notifications\CustomerOrderPlacedNotification;
use App\Services\OrderNotificationService;
use App\Support\FrontendCheckoutManager;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private ProductVariant $variant;

    private string $sessionId;

    private User $orderAdmin;

    private User $inactiveOrderAdmin;

    private User $unrelatedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->seed(AdminAuthorizationSeeder::class);

        config()->set('services.tap.base_url', 'https://tap.test');

        $this->seedPaymentAndShippingSettings();
        $this->seedNotificationSettings();

        $this->orderAdmin = User::factory()->create(['is_active' => true]);
        $this->orderAdmin->assignRole('admin');

        $this->inactiveOrderAdmin = User::factory()->create(['is_active' => false]);
        $this->inactiveOrderAdmin->assignRole('admin');

        $this->unrelatedUser = User::factory()->create(['is_active' => true]);

        $this->get(route('storefront.home', ['locale' => 'en']));
        $this->sessionId = app('session.store')->getId();

        $this->product = Product::query()->create([
            'name' => 'Runner Pro',
            'slug' => 'runner-pro',
            'short_description' => 'Performance runner',
            'description' => 'Full product description',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::query()->create([
            'product_id' => $this->product->id,
            'name' => '42',
            'sku' => 'RUNNER-42',
            'price' => 100,
            'stock_quantity' => 5,
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    public function test_paid_checkout_sends_customer_placed_notification_and_admin_database_notification(): void
    {
        Notification::fake();
        $this->seedCart(2);

        Http::fake([
            'https://tap.test/v2/charges' => Http::response([
                'id' => 'chg_notify',
                'status' => 'INITIATED',
                'reference' => [
                    'transaction' => 'ORD-REF-NOTIFY',
                    'order' => 'ORD-REF-NOTIFY',
                ],
                'transaction' => [
                    'url' => 'https://tap.test/pay/chg_notify',
                ],
            ], 200),
        ]);

        app(FrontendCheckoutManager::class)->beginTapCheckout(
            $this->managerRequest(),
            $this->checkoutPayload(),
        );

        $order = Order::query()->firstOrFail();

        Http::fake([
            'https://tap.test/v2/charges/chg_notify' => Http::response([
                'id' => 'chg_notify',
                'status' => 'CAPTURED',
                'reference' => [
                    'transaction' => 'ORD-REF-NOTIFY',
                    'order' => $order->order_number,
                ],
                'metadata' => [
                    'order_id' => (string) $order->id,
                ],
            ], 200),
        ]);

        app(FrontendCheckoutManager::class)->syncOrderFromTapCharge(
            app(\App\Services\TapPaymentService::class)->fetchCharge('chg_notify'),
            $this->managerRequest()
        );

        $order = $order->fresh('customer');
        $customer = $order->customer;

        $this->assertNotNull($customer);

        Notification::assertSentTo($customer, CustomerOrderPlacedNotification::class);
        Notification::assertSentTo($this->orderAdmin, AdminNewOrderNotification::class);
        Notification::assertNotSentTo($this->inactiveOrderAdmin, AdminNewOrderNotification::class);
        Notification::assertNotSentTo($this->unrelatedUser, AdminNewOrderNotification::class);
    }

    public function test_admin_order_updates_send_customer_milestone_notifications_only_on_supported_transitions(): void
    {
        Notification::fake();

        $customer = Customer::factory()->create([
            'email' => 'customer@example.com',
            'is_active' => true,
        ]);

        $order = Order::query()->create([
            'order_number' => 'ORD-MILESTONE-1',
            'customer_id' => $customer->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'fulfillment_status' => 'unfulfilled',
            'currency' => 'BHD',
            'customer_first_name' => 'Amr',
            'customer_last_name' => 'Ashraf',
            'customer_email' => $customer->email,
            'grand_total' => 120,
            'placed_at' => now(),
        ]);

        $this->actingAs($this->orderAdmin)->put(route('admin.orders.update', $order), [
            'status' => 'pending',
            'payment_status' => 'paid',
            'fulfillment_status' => 'unfulfilled',
        ])->assertRedirect();

        Notification::assertSentTo(
            $customer,
            CustomerOrderMilestoneNotification::class,
            fn (CustomerOrderMilestoneNotification $notification) => $notification->milestone === 'paid'
        );

        Notification::fake();

        $this->actingAs($this->orderAdmin)->put(route('admin.orders.update', $order), [
            'status' => 'processing',
            'payment_status' => 'paid',
            'fulfillment_status' => 'packed',
        ])->assertRedirect();

        Notification::assertNotSentTo($customer, CustomerOrderMilestoneNotification::class);

        Notification::fake();

        $this->actingAs($this->orderAdmin)->put(route('admin.orders.update', $order), [
            'status' => 'processing',
            'payment_status' => 'paid',
            'fulfillment_status' => 'shipped',
        ])->assertRedirect();

        Notification::assertSentTo(
            $customer,
            CustomerOrderMilestoneNotification::class,
            fn (CustomerOrderMilestoneNotification $notification) => $notification->milestone === 'shipped'
        );

        Notification::fake();

        Setting::query()->where('key', 'customer_order_delivered_notification_enabled')->update(['value' => '0']);
        app()->forgetInstance(\App\Support\SettingsManager::class);

        $this->actingAs($this->orderAdmin)->put(route('admin.orders.update', $order), [
            'status' => 'processing',
            'payment_status' => 'paid',
            'fulfillment_status' => 'delivered',
        ])->assertRedirect();

        Notification::assertNotSentTo($customer, CustomerOrderMilestoneNotification::class);

        Notification::fake();

        $this->actingAs($this->orderAdmin)->put(route('admin.orders.update', $order), [
            'status' => 'canceled',
            'payment_status' => 'canceled',
            'fulfillment_status' => 'delivered',
        ])->assertRedirect();

        Notification::assertSentTo(
            $customer,
            CustomerOrderMilestoneNotification::class,
            fn (CustomerOrderMilestoneNotification $notification) => $notification->milestone === 'canceled'
        );
    }

    public function test_guest_order_notification_uses_routed_mail_when_order_has_no_customer(): void
    {
        Notification::fake();

        $order = Order::query()->create([
            'order_number' => 'ORD-GUEST-1',
            'status' => 'processing',
            'payment_status' => 'paid',
            'fulfillment_status' => 'unfulfilled',
            'currency' => 'BHD',
            'customer_first_name' => 'Guest',
            'customer_last_name' => 'Buyer',
            'customer_email' => 'guest@example.com',
            'grand_total' => 55,
            'placed_at' => now(),
        ]);

        app(OrderNotificationService::class)->notifyPlaced($order, 'en');

        Notification::assertSentOnDemand(
            CustomerOrderPlacedNotification::class,
            function (CustomerOrderPlacedNotification $notification, array $channels, object $notifiable) use ($order): bool {
                return $notification->order->is($order)
                    && ($notifiable->routes['mail'] ?? null) === 'guest@example.com';
            }
        );
    }

    public function test_settings_seeder_adds_notification_keys_without_overwriting_existing_values(): void
    {
        Setting::query()->where('key', 'admin_new_order_notification_enabled')->update([
            'value' => '0',
        ]);

        $this->seed(\Database\Seeders\SettingsSeeder::class);

        $this->assertDatabaseHas('settings', [
            'key' => 'admin_new_order_notification_enabled',
            'value' => '0',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'customer_order_paid_notification_enabled',
            'group' => 'notifications',
            'input_type' => 'boolean',
        ]);
    }

    private function seedCart(int $quantity = 1): void
    {
        $cart = Cart::query()->create([
            'session_id' => $this->sessionId,
            'currency' => 'BHD',
            'item_count' => $quantity,
            'subtotal' => $quantity * 100,
            'last_activity_at' => now(),
        ]);

        $cart->items()->create([
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'product_name' => $this->product->name,
            'variant_name' => $this->variant->name,
            'sku' => $this->variant->sku,
            'unit_price' => 100,
            'quantity' => $quantity,
            'line_total' => $quantity * 100,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function checkoutPayload(): array
    {
        return [
            'first_name' => 'John',
            'last_name' => 'Customer',
            'email' => 'john@example.com',
            'phone' => '12345678',
            'country' => 'Bahrain',
            'state' => 'Capital',
            'city' => 'Manama',
            'address_line_1' => 'Street 1',
            'address_line_2' => 'Building 2',
            'postal_code' => '100',
            'customer_note' => 'Leave at the desk',
            'coupon_code' => '',
        ];
    }

    private function managerRequest(): Request
    {
        $request = Request::create('/en/checkout', 'POST');
        $session = app('session.store');
        $session->setId($this->sessionId);
        $session->start();
        $request->setLaravelSession($session);

        return $request;
    }

    private function seedPaymentAndShippingSettings(): void
    {
        $settings = [
            ['group' => 'payment', 'key' => 'tap_secret_key', 'value' => 'tap-secret', 'input_type' => 'password'],
            ['group' => 'payment', 'key' => 'tap_public_key', 'value' => 'tap-public', 'input_type' => 'password'],
            ['group' => 'shipping', 'key' => 'shipping_gulf_cost', 'value' => '4', 'input_type' => 'number'],
            ['group' => 'shipping', 'key' => 'shipping_europe_america_1_2_cost', 'value' => '15', 'input_type' => 'number'],
            ['group' => 'shipping', 'key' => 'shipping_europe_america_3_plus_cost', 'value' => '10', 'input_type' => 'number'],
            ['group' => 'shipping', 'key' => 'enable_vat', 'value' => '1', 'input_type' => 'boolean'],
            ['group' => 'shipping', 'key' => 'vat_value', 'value' => '10', 'input_type' => 'number'],
        ];

        foreach ($settings as $index => $setting) {
            Setting::query()->create([
                'group' => $setting['group'],
                'key' => $setting['key'],
                'label' => $setting['key'],
                'value' => $setting['value'],
                'input_type' => $setting['input_type'],
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function seedNotificationSettings(): void
    {
        $keys = [
            'customer_order_placed_notification_enabled',
            'customer_order_paid_notification_enabled',
            'customer_order_shipped_notification_enabled',
            'customer_order_delivered_notification_enabled',
            'customer_order_canceled_notification_enabled',
            'admin_new_order_notification_enabled',
        ];

        foreach ($keys as $index => $key) {
            Setting::query()->create([
                'group' => 'notifications',
                'key' => $key,
                'label' => $key,
                'value' => '1',
                'input_type' => 'boolean',
                'sort_order' => $index + 20,
            ]);
        }
    }
}
