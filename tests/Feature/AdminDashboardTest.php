<?php

namespace Tests\Feature;

use App\Exports\AdminDashboardReportExport;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Support\AdminDashboardReportService;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAuthorizationSeeder::class);
        Carbon::setTestNow('2026-04-29 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_dashboard_page_renders_for_authorized_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSeeText('تقرير لوحة التحكم')
            ->assertSeeText('اتجاه المبيعات والطلبات')
            ->assertSeeText('إجراءات سريعة')
            ->assertSeeText('أحدث الطلبات');
    }

    public function test_dashboard_shows_empty_states_when_no_range_data_exists(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get('/admin?range=30d')
            ->assertOk()
            ->assertSeeText('لا توجد طلبات لهذه الفترة')
            ->assertSeeText('لا توجد سلات لهذه الفترة')
            ->assertSeeText('لا توجد بيانات مبيعات لهذه الفترة.');
    }

    public function test_dashboard_displays_expected_analytics_for_selected_range(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Setting::query()->create([
            'group' => 'analytics',
            'key' => 'google_analytics_measurement_id',
            'label' => 'Google Analytics',
            'value' => 'G-TEST123',
            'input_type' => 'text',
            'is_public' => true,
            'sort_order' => 1,
        ]);

        $category = Category::query()->create(['name' => 'Shoes', 'slug' => 'shoes']);
        $product = Product::query()->create(['name' => 'Runner', 'slug' => 'runner']);
        $product->categories()->sync([$category->id]);

        $variantLow = ProductVariant::query()->create([
            'product_id' => $product->id,
            'name' => '40',
            'sku' => 'RUN-40',
            'price' => 50,
            'stock_quantity' => 3,
            'is_default' => true,
            'is_active' => true,
        ]);

        ProductVariant::query()->create([
            'product_id' => $product->id,
            'name' => '41',
            'sku' => 'RUN-41',
            'price' => 55,
            'stock_quantity' => 8,
            'is_active' => true,
        ]);

        $customer = Customer::factory()->create([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $paidOrder = Order::query()->create([
            'order_number' => 'ORD-PAID',
            'session_id' => 'session-paid',
            'customer_id' => $customer->id,
            'status' => 'processing',
            'payment_status' => 'paid',
            'fulfillment_status' => 'packed',
            'currency' => 'BHD',
            'customer_first_name' => 'Sara',
            'customer_last_name' => 'Ali',
            'customer_email' => 'sara@example.com',
            'subtotal' => 100,
            'grand_total' => 110,
            'placed_at' => now()->subDay(),
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        OrderItem::query()->create([
            'order_id' => $paidOrder->id,
            'product_id' => $product->id,
            'product_variant_id' => $variantLow->id,
            'product_name' => 'Runner',
            'variant_name' => '40',
            'unit_price' => 50,
            'quantity' => 2,
            'line_total' => 100,
        ]);

        Order::query()->create([
            'order_number' => 'ORD-PENDING',
            'session_id' => 'session-pending',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'fulfillment_status' => 'unfulfilled',
            'currency' => 'BHD',
            'customer_first_name' => 'Omar',
            'customer_last_name' => 'Saleh',
            'customer_email' => 'omar@example.com',
            'subtotal' => 70,
            'grand_total' => 70,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        Cart::query()->create([
            'session_id' => 'session-paid',
            'currency' => 'BHD',
            'item_count' => 1,
            'subtotal' => 110,
            'last_activity_at' => now()->subDay(),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        Cart::query()->create([
            'session_id' => 'session-abandoned',
            'currency' => 'BHD',
            'item_count' => 2,
            'subtotal' => 75,
            'last_activity_at' => now()->subDays(2),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($user)->get('/admin?range=30d');

        $response->assertOk();
        $response->assertSeeText('110.00');
        $response->assertSeeText('ORD-PAID');
        $response->assertSeeText('Runner');
        $response->assertSeeText('1 / 5');
    }

    public function test_dashboard_range_resolution_matches_expected_window(): void
    {
        $range = app(AdminDashboardReportService::class)->resolveRange('7d');

        $this->assertSame('7d', $range['key']);
        $this->assertSame('2026-04-27 00:00:00', $range['start']->format('Y-m-d H:i:s'));
        $this->assertSame('day', $range['interval']);
    }

    public function test_dashboard_returns_html_fragment_for_ajax_range_changes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('admin.dashboard', ['range' => 'today']), [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->assertOk()
            ->assertJsonStructure(['html']);
    }

    public function test_dashboard_export_pdf_downloads_for_authorized_user(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('dashboard.view');

        $this->actingAs($user)
            ->get(route('admin.dashboard.export.pdf', ['range' => '30d']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_dashboard_export_excel_downloads_expected_export(): void
    {
        Excel::fake();

        $user = User::factory()->create();
        $user->givePermissionTo('dashboard.view');

        $this->actingAs($user)->get(route('admin.dashboard.export.excel', ['range' => '30d']))->assertOk();

        Excel::assertDownloaded('dashboard-report-30d.xlsx', function (AdminDashboardReportExport $export) {
            return count($export->sheets()) === 5;
        });
    }

    public function test_restricted_admin_only_sees_permitted_quick_actions(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('dashboard.view');

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertDontSeeText('إضافة منتج جديد');
        $response->assertDontSeeText('إدارة الأقسام');
        $response->assertDontSeeText('الإعدادات العامة');
        $response->assertSeeText('تم تقييد الاختصارات حسب الصلاحيات');
    }
}
