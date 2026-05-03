<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Support\AdminArabic;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AdminDashboardReportService
{
    public function build(?string $range = null): array
    {
        $rangeData = $this->resolveRange($range);
        [$start, $end] = [$rangeData['start'], $rangeData['end']];

        $paidOrdersQuery = Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween(DB::raw('COALESCE(placed_at, created_at)'), [$start, $end]);

        $ordersInRangeQuery = Order::query()
            ->whereBetween('created_at', [$start, $end]);

        $cartsCreatedQuery = Cart::query()
            ->whereBetween('created_at', [$start, $end]);

        $revenue = (float) (clone $paidOrdersQuery)->sum('grand_total');
        $paidOrders = (int) (clone $paidOrdersQuery)->count();
        $totalOrders = (int) (clone $ordersInRangeQuery)->count();
        $newCustomers = (int) Customer::query()->whereBetween('created_at', [$start, $end])->count();
        $activeCarts = (int) Cart::query()
            ->whereBetween('last_activity_at', [$start, $end])
            ->count();
        $cartsCreated = (int) (clone $cartsCreatedQuery)->count();
        $ordersCreated = $totalOrders;

        return [
            'range' => $rangeData['key'],
            'range_label' => $rangeData['label'],
            'available_ranges' => $this->availableRanges(),
            'kpis' => [
                'revenue' => $revenue,
                'paid_orders' => $paidOrders,
                'total_orders' => $totalOrders,
                'average_order_value' => $paidOrders > 0 ? round($revenue / $paidOrders, 2) : 0.0,
                'new_customers' => $newCustomers,
                'active_carts' => $activeCarts,
                'cart_to_order_conversion_rate' => $this->percent($ordersCreated, $cartsCreated),
                'low_stock_variants' => $this->lowStockVariantsQuery()->count(),
            ],
            'sales_trend' => $this->salesTrend($rangeData),
            'funnel' => [
                'carts_created' => $cartsCreated,
                'orders_created' => $ordersCreated,
                'conversion_rate' => $this->percent($ordersCreated, $cartsCreated),
                'abandoned_carts' => $this->abandonedCartsQuery($start, $end)->count(),
            ],
            'top_products_by_quantity' => $this->topProducts($start, $end, 'quantity'),
            'top_products_by_revenue' => $this->topProducts($start, $end, 'revenue'),
            'order_status_breakdown' => (clone $ordersInRangeQuery)
                ->select('status', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('status')
                ->orderByDesc('aggregate')
                ->get()
                ->map(fn ($row) => [
                    'status' => (string) $row->status,
                    'count' => (int) $row->aggregate,
                ])
                ->values()
                ->all(),
            'low_stock_variants' => $this->lowStockVariantsQuery()
                ->with('product')
                ->orderBy('stock_quantity')
                ->limit(10)
                ->get()
                ->map(fn (ProductVariant $variant) => [
                    'id' => $variant->id,
                    'name' => $variant->display_name,
                    'product_name' => (string) $variant->product?->name,
                    'stock_quantity' => (int) $variant->stock_quantity,
                    'price' => (float) $variant->price,
                ])
                ->all(),
            'recent_orders' => (clone $ordersInRangeQuery)
                ->latest()
                ->limit(5)
                ->get(),
            'recent_carts' => Cart::query()
                ->whereBetween('last_activity_at', [$start, $end])
                ->latest('last_activity_at')
                ->limit(5)
                ->get(),
            'analytics_config_status' => $this->analyticsConfigStatus(),
        ];
    }

    public function resolveRange(?string $range): array
    {
        $now = now();
        $key = array_key_exists((string) $range, $this->availableRanges())
            ? (string) $range
            : '30d';

        return match ($key) {
            'today' => [
                'key' => 'today',
                'label' => 'اليوم',
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'interval' => 'hour',
            ],
            '7d' => [
                'key' => '7d',
                'label' => 'هذا الأسبوع',
                'start' => $now->copy()->startOfWeek()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'interval' => 'day',
            ],
            '90d' => [
                'key' => '90d',
                'label' => 'آخر 90 يومًا',
                'start' => $now->copy()->subDays(89)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'interval' => 'day',
            ],
            'year' => [
                'key' => 'year',
                'label' => 'هذه السنة',
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfDay(),
                'interval' => 'month',
            ],
            default => [
                'key' => '30d',
                'label' => 'هذا الشهر',
                'start' => $now->copy()->startOfMonth()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'interval' => 'day',
            ],
        };
    }

    public function availableRanges(): array
    {
        return [
            'today' => 'اليوم',
            '7d' => 'هذا الأسبوع',
            '30d' => 'هذا الشهر',
            '90d' => 'آخر 90 يومًا',
            'year' => 'هذه السنة',
        ];
    }

    private function salesTrend(array $rangeData): array
    {
        $interval = $rangeData['interval'];
        $labels = [];
        $orderBuckets = [];
        $revenueBuckets = [];

        foreach ($this->period($rangeData['start'], $rangeData['end'], $interval) as $point) {
            $key = $this->bucketKey($point, $interval);
            $labels[$key] = $this->bucketLabel($point, $interval);
            $orderBuckets[$key] = 0;
            $revenueBuckets[$key] = 0.0;
        }

        Order::query()
            ->whereBetween('created_at', [$rangeData['start'], $rangeData['end']])
            ->get(['created_at'])
            ->each(function (Order $order) use (&$orderBuckets, $interval): void {
                $key = $this->bucketKey(Carbon::parse($order->created_at), $interval);

                if (array_key_exists($key, $orderBuckets)) {
                    $orderBuckets[$key]++;
                }
            });

        Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween(DB::raw('COALESCE(placed_at, created_at)'), [$rangeData['start'], $rangeData['end']])
            ->get(['created_at', 'placed_at', 'grand_total'])
            ->each(function (Order $order) use (&$revenueBuckets, $interval): void {
                $date = $order->placed_at ? Carbon::parse($order->placed_at) : Carbon::parse($order->created_at);
                $key = $this->bucketKey($date, $interval);

                if (array_key_exists($key, $revenueBuckets)) {
                    $revenueBuckets[$key] += (float) $order->grand_total;
                }
            });

        return [
            'labels' => array_values($labels),
            'orders' => array_values($orderBuckets),
            'revenue' => array_map(fn ($value) => round((float) $value, 2), array_values($revenueBuckets)),
        ];
    }

    private function topProducts(CarbonInterface $start, CarbonInterface $end, string $sortBy): array
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween(DB::raw('COALESCE(orders.placed_at, orders.created_at)'), [$start, $end])
            ->select(
                'order_items.product_id',
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.line_total) as revenue')
            )
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc($sortBy === 'revenue' ? 'revenue' : 'quantity_sold')
            ->limit(5)
            ->get();

        return $rows->map(fn ($row) => [
            'product_id' => $row->product_id,
            'product_name' => (string) $row->product_name,
            'quantity_sold' => (int) $row->quantity_sold,
            'revenue' => (float) $row->revenue,
        ])->all();
    }

    private function analyticsConfigStatus(): array
    {
        $keys = [
            'google_analytics_measurement_id',
            'google_tag_manager_id',
            'google_search_console_verification_id',
            'google_ads_conversion_id',
            'google_ads_conversion_label',
        ];

        $settings = Setting::query()
            ->where('group', 'analytics')
            ->whereIn('key', $keys)
            ->pluck('value', 'key');

        $items = collect($keys)->map(fn (string $key) => [
            'key' => $key,
            'label' => AdminArabic::settingsLabel($key),
            'configured' => filled($settings->get($key)),
        ]);

        return [
            'items' => $items->all(),
            'configured_count' => $items->where('configured', true)->count(),
            'total_count' => $items->count(),
        ];
    }

    private function abandonedCartsQuery(CarbonInterface $start, CarbonInterface $end): Builder
    {
        return Cart::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('item_count', '>', 0)
            ->whereNotNull('last_activity_at')
            ->where('last_activity_at', '<=', now()->subDay())
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('orders')
                    ->whereColumn('orders.session_id', 'carts.session_id')
                    ->where('orders.payment_status', 'paid');
            });
    }

    private function lowStockVariantsQuery(): Builder
    {
        return ProductVariant::query()
            ->where('is_active', true)
            ->where('stock_quantity', '<=', 5);
    }

    private function percent(int $numerator, int $denominator): float
    {
        if ($denominator === 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 2);
    }

    private function period(CarbonInterface $start, CarbonInterface $end, string $interval): CarbonPeriod
    {
        return match ($interval) {
            'hour' => CarbonPeriod::create($start->copy(), '1 hour', $end->copy()->startOfHour()),
            'month' => CarbonPeriod::create($start->copy()->startOfMonth(), '1 month', $end->copy()->startOfMonth()),
            default => CarbonPeriod::create($start->copy()->startOfDay(), '1 day', $end->copy()->startOfDay()),
        };
    }

    private function bucketKey(CarbonInterface $date, string $interval): string
    {
        return match ($interval) {
            'hour' => $date->format('Y-m-d H:00'),
            'month' => $date->format('Y-m'),
            default => $date->format('Y-m-d'),
        };
    }

    private function bucketLabel(CarbonInterface $date, string $interval): string
    {
        return match ($interval) {
            'hour' => $date->format('H:00'),
            'month' => $date->locale('ar')->isoFormat('MMMM'),
            default => $date->locale('ar')->isoFormat('D MMM'),
        };
    }
}
