<?php

namespace App\Support;

use BackedEnum;
use App\Enums\OrderFulfillmentStatus;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminAnalyticsReportService
{
    public function build(?string $range = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $rangeData = $this->resolveRange($range, $startDate, $endDate);
        [$start, $end] = [$rangeData['start'], $rangeData['end']];

        $paidOrders = Order::query()
            ->where('payment_status', OrderPaymentStatus::PAID->value)
            ->whereBetween(DB::raw('COALESCE(placed_at, created_at)'), [$start, $end]);

        $orders = Order::query()->whereBetween('created_at', [$start, $end]);
        $cartsCreated = Cart::query()->whereBetween('created_at', [$start, $end]);
        $activeCarts = Cart::query()
            ->whereBetween('last_activity_at', [$start, $end]);
        $abandonedCarts = $this->abandonedCartsQuery($start, $end);

        $revenue = (float) (clone $paidOrders)->sum('grand_total');
        $paidOrderCount = (int) (clone $paidOrders)->count();
        $ordersCreated = (int) (clone $orders)->count();
        $cartsCreatedCount = (int) (clone $cartsCreated)->count();
        $abandonedCartsCount = (int) (clone $abandonedCarts)->count();

        $report = [
            'range' => $rangeData['key'],
            'range_label' => $rangeData['label'],
            'start_date' => $rangeData['start']->toDateString(),
            'end_date' => $rangeData['end']->toDateString(),
            'available_ranges' => $this->availableRanges(),
            'kpis' => [
                'revenue' => $revenue,
                'paid_orders' => $paidOrderCount,
                'total_orders' => $ordersCreated,
                'average_order_value' => $paidOrderCount > 0 ? round($revenue / $paidOrderCount, 2) : 0.0,
                'new_customers' => Customer::query()->whereBetween('created_at', [$start, $end])->count(),
                'active_carts' => (clone $activeCarts)->count(),
                'abandoned_carts' => $abandonedCartsCount,
                'cart_to_order_conversion_rate' => $this->percent($ordersCreated, $cartsCreatedCount),
                'discount_total' => (float) (clone $paidOrders)->sum('discount_total'),
                'shipping_total' => (float) (clone $paidOrders)->sum('shipping_total'),
            ],
            'sales' => [
                'trend' => $this->salesTrend($rangeData),
                'payment_status' => $this->orderBreakdown($orders, 'payment_status', OrderPaymentStatus::options()),
                'fulfillment_status' => $this->orderBreakdown($orders, 'fulfillment_status', OrderFulfillmentStatus::options()),
                'order_status' => $this->orderBreakdown($orders, 'status', OrderStatus::options()),
                'currency' => $this->valueBreakdown($orders, 'currency', 'غير محدد'),
                'payment_provider' => $this->valueBreakdown($orders, 'payment_provider', 'غير محدد'),
                'hourly_performance' => $this->hourlyOrderPerformance($start, $end),
                'weekday_performance' => $this->weekdayOrderPerformance($start, $end),
            ],
            'products' => [
                'top_by_quantity' => $this->topProducts($start, $end, 'quantity'),
                'top_by_revenue' => $this->topProducts($start, $end, 'revenue'),
                'top_variants' => $this->topVariants($start, $end),
                'categories' => $this->categoryPerformance($start, $end),
                'reminders' => $this->productReminderDemand($start, $end),
            ],
            'inventory' => [
                'low_stock' => $this->lowStockVariants(10),
                'sold_out' => $this->soldOutVariants(10),
                'stock_value' => $this->inventoryValueSummary(),
                'highest_stock' => $this->highestStockVariants(10),
            ],
            'customers' => [
                'new_customers_trend' => $this->customersTrend($rangeData),
                'registered_vs_guest' => $this->registeredVsGuest($start, $end),
                'top_by_revenue' => $this->topCustomers($start, $end, 'revenue'),
                'top_by_orders' => $this->topCustomers($start, $end, 'orders'),
                'customer_countries' => $this->customerCountries($start, $end),
                'billing_countries' => $this->orderCountries($start, $end, 'billing_country'),
                'shipping_countries' => $this->orderCountries($start, $end, 'shipping_country'),
                'billing_cities' => $this->orderLocations($start, $end, 'billing_city'),
                'shipping_cities' => $this->orderLocations($start, $end, 'shipping_city'),
                'email_domains' => $this->customerEmailDomains($start, $end),
            ],
            'carts' => [
                'summary' => [
                    'created' => $cartsCreatedCount,
                    'active' => (clone $activeCarts)->count(),
                    'abandoned' => $abandonedCartsCount,
                    'abandoned_value' => (float) (clone $abandonedCarts)->sum('subtotal'),
                    'average_cart_value' => $cartsCreatedCount > 0 ? round((float) (clone $cartsCreated)->sum('subtotal') / $cartsCreatedCount, 2) : 0.0,
                ],
                'active_products' => $this->cartProducts($activeCarts, 'active'),
                'abandoned_products' => $this->cartProducts($abandonedCarts, 'abandoned'),
                'active_variants' => $this->cartVariants($activeCarts, 'active'),
                'abandoned_variants' => $this->cartVariants($abandonedCarts, 'abandoned'),
                'age_buckets' => $this->cartAgeBuckets($start, $end),
            ],
            'coupons' => [
                'summary' => $this->couponSummary($start, $end),
                'by_coupon' => $this->couponPerformance($start, $end),
                'welcome' => $this->welcomeCouponSummary($start, $end),
            ],
        ];

        $report['exports'] = $this->exportTables($report);

        return $report;
    }

    public function sectionLabels(): array
    {
        return [
            'summary' => 'Executive Summary',
            'sales' => 'Sales',
            'products' => 'Products',
            'inventory' => 'Inventory',
            'customers' => 'Customers',
            'carts' => 'Carts',
            'coupons' => 'Coupons',
        ];
    }

    public function availableRanges(): array
    {
        return [
            'today' => 'اليوم',
            '7d' => 'هذا الأسبوع',
            '30d' => 'هذا الشهر',
            '90d' => 'آخر 90 يوماً',
            'year' => 'هذه السنة',
        ];
    }

    public function exportTables(array $report, ?string $section = null): array
    {
        $tables = [
            'summary' => [
                'title' => 'Executive Summary',
                'rows' => [
                    ['Metric', 'Value'],
                    ['Range', $report['range']],
                    ['Revenue', $report['kpis']['revenue']],
                    ['Paid Orders', $report['kpis']['paid_orders']],
                    ['Total Orders', $report['kpis']['total_orders']],
                    ['Average Order Value', $report['kpis']['average_order_value']],
                    ['New Customers', $report['kpis']['new_customers']],
                    ['Active Carts', $report['kpis']['active_carts']],
                    ['Abandoned Carts', $report['kpis']['abandoned_carts']],
                    ['Conversion Rate', $report['kpis']['cart_to_order_conversion_rate'] . '%'],
                    ['Discount Total', $report['kpis']['discount_total']],
                    ['Shipping Total', $report['kpis']['shipping_total']],
                ],
            ],
            'sales' => [
                'title' => 'Sales Analytics',
                'rows' => $this->salesExportRows($report),
            ],
            'products' => [
                'title' => 'Product Analytics',
                'rows' => $this->productsExportRows($report),
            ],
            'inventory' => [
                'title' => 'Inventory Analytics',
                'rows' => $this->inventoryExportRows($report),
            ],
            'customers' => [
                'title' => 'Customer Analytics',
                'rows' => $this->customersExportRows($report),
            ],
            'carts' => [
                'title' => 'Cart Analytics',
                'rows' => $this->cartsExportRows($report),
            ],
            'coupons' => [
                'title' => 'Coupon Analytics',
                'rows' => $this->couponsExportRows($report),
            ],
        ];

        if ($section && isset($tables[$section])) {
            return [$section => $tables[$section]];
        }

        return $tables;
    }

    public function resolveRange(?string $range, ?string $startDate = null, ?string $endDate = null): array
    {
        if ($startDate && $endDate) {
            try {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();

                if ($start->lte($end)) {
                    return [
                        'key' => 'custom',
                        'label' => $start->toDateString() . ' إلى ' . $end->toDateString(),
                        'start' => $start,
                        'end' => $end,
                        'interval' => $start->diffInDays($end) > 90 ? 'month' : ($start->isSameDay($end) ? 'hour' : 'day'),
                    ];
                }
            } catch (\Throwable) {
                // Invalid custom dates fall back to the selected preset.
            }
        }

        $now = now();
        $key = array_key_exists((string) $range, $this->availableRanges()) ? (string) $range : '30d';

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
                'label' => 'آخر 90 يوماً',
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

    private function salesTrend(array $rangeData): array
    {
        $interval = $rangeData['interval'];
        $labels = [];
        $orders = [];
        $revenue = [];
        $paidCounts = [];

        foreach ($this->period($rangeData['start'], $rangeData['end'], $interval) as $point) {
            $key = $this->bucketKey($point, $interval);
            $labels[$key] = $this->bucketLabel($point, $interval);
            $orders[$key] = 0;
            $revenue[$key] = 0.0;
            $paidCounts[$key] = 0;
        }

        Order::query()
            ->whereBetween('created_at', [$rangeData['start'], $rangeData['end']])
            ->get(['created_at'])
            ->each(function (Order $order) use (&$orders, $interval): void {
                $key = $this->bucketKey(Carbon::parse($order->created_at), $interval);

                if (array_key_exists($key, $orders)) {
                    $orders[$key]++;
                }
            });

        Order::query()
            ->where('payment_status', OrderPaymentStatus::PAID->value)
            ->whereBetween(DB::raw('COALESCE(placed_at, created_at)'), [$rangeData['start'], $rangeData['end']])
            ->get(['created_at', 'placed_at', 'grand_total'])
            ->each(function (Order $order) use (&$revenue, &$paidCounts, $interval): void {
                $date = $order->placed_at ? Carbon::parse($order->placed_at) : Carbon::parse($order->created_at);
                $key = $this->bucketKey($date, $interval);

                if (array_key_exists($key, $revenue)) {
                    $revenue[$key] += (float) $order->grand_total;
                    $paidCounts[$key]++;
                }
            });

        $aov = [];
        foreach ($revenue as $key => $value) {
            $aov[] = ($paidCounts[$key] ?? 0) > 0 ? round($value / $paidCounts[$key], 2) : 0.0;
        }

        return [
            'labels' => array_values($labels),
            'orders' => array_values($orders),
            'revenue' => array_map(fn ($value) => round((float) $value, 2), array_values($revenue)),
            'aov' => $aov,
        ];
    }

    private function orderBreakdown(Builder $baseQuery, string $column, array $labels): array
    {
        return (clone $baseQuery)
            ->select($column, DB::raw('COUNT(*) as aggregate'), DB::raw('SUM(grand_total) as total_value'))
            ->groupBy($column)
            ->orderByDesc('aggregate')
            ->get()
            ->map(fn ($row) => [
                'key' => $this->scalarValue($row->{$column}),
                'label' => $labels[$this->scalarValue($row->{$column})] ?? Str::headline($this->scalarValue($row->{$column})),
                'count' => (int) $row->aggregate,
                'value' => (float) $row->total_value,
            ])
            ->values()
            ->all();
    }

    private function valueBreakdown(Builder $baseQuery, string $column, string $emptyLabel): array
    {
        return (clone $baseQuery)
            ->select($column, DB::raw('COUNT(*) as aggregate'), DB::raw('SUM(grand_total) as total_value'))
            ->groupBy($column)
            ->orderByDesc('aggregate')
            ->limit(12)
            ->get()
            ->map(function ($row) use ($column, $emptyLabel) {
                $key = trim($this->scalarValue($row->{$column}));

                return [
                    'key' => $key,
                    'label' => $key !== '' ? Str::headline($key) : $emptyLabel,
                    'count' => (int) $row->aggregate,
                    'value' => (float) $row->total_value,
                ];
            })
            ->values()
            ->all();
    }

    private function hourlyOrderPerformance(CarbonInterface $start, CarbonInterface $end): array
    {
        $hours = collect(range(0, 23))
            ->mapWithKeys(fn (int $hour) => [
                $hour => [
                    'label' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':00',
                    'count' => 0,
                    'value' => 0.0,
                ],
            ])
            ->all();

        Order::query()
            ->where('payment_status', OrderPaymentStatus::PAID->value)
            ->whereBetween(DB::raw('COALESCE(placed_at, created_at)'), [$start, $end])
            ->get(['created_at', 'placed_at', 'grand_total'])
            ->each(function (Order $order) use (&$hours): void {
                $date = $order->placed_at ? Carbon::parse($order->placed_at) : Carbon::parse($order->created_at);
                $hour = (int) $date->format('G');

                $hours[$hour]['count']++;
                $hours[$hour]['value'] += (float) $order->grand_total;
            });

        return array_values($hours);
    }

    private function weekdayOrderPerformance(CarbonInterface $start, CarbonInterface $end): array
    {
        $days = [
            0 => ['label' => 'الأحد', 'count' => 0, 'value' => 0.0],
            1 => ['label' => 'الإثنين', 'count' => 0, 'value' => 0.0],
            2 => ['label' => 'الثلاثاء', 'count' => 0, 'value' => 0.0],
            3 => ['label' => 'الأربعاء', 'count' => 0, 'value' => 0.0],
            4 => ['label' => 'الخميس', 'count' => 0, 'value' => 0.0],
            5 => ['label' => 'الجمعة', 'count' => 0, 'value' => 0.0],
            6 => ['label' => 'السبت', 'count' => 0, 'value' => 0.0],
        ];

        Order::query()
            ->where('payment_status', OrderPaymentStatus::PAID->value)
            ->whereBetween(DB::raw('COALESCE(placed_at, created_at)'), [$start, $end])
            ->get(['created_at', 'placed_at', 'grand_total'])
            ->each(function (Order $order) use (&$days): void {
                $date = $order->placed_at ? Carbon::parse($order->placed_at) : Carbon::parse($order->created_at);
                $day = (int) $date->format('w');

                $days[$day]['count']++;
                $days[$day]['value'] += (float) $order->grand_total;
            });

        return array_values($days);
    }

    private function topProducts(CarbonInterface $start, CarbonInterface $end, string $sortBy): array
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', OrderPaymentStatus::PAID->value)
            ->whereBetween(DB::raw('COALESCE(orders.placed_at, orders.created_at)'), [$start, $end])
            ->select(
                'order_items.product_id',
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.line_total) as revenue')
            )
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc($sortBy === 'revenue' ? 'revenue' : 'quantity_sold')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'product_id' => $row->product_id,
                'product_name' => (string) $row->product_name,
                'quantity_sold' => (int) $row->quantity_sold,
                'revenue' => (float) $row->revenue,
            ])
            ->all();
    }

    private function topVariants(CarbonInterface $start, CarbonInterface $end): array
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', OrderPaymentStatus::PAID->value)
            ->whereBetween(DB::raw('COALESCE(orders.placed_at, orders.created_at)'), [$start, $end])
            ->select(
                'order_items.product_variant_id',
                'order_items.product_name',
                'order_items.variant_name',
                'order_items.sku',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.line_total) as revenue')
            )
            ->groupBy('order_items.product_variant_id', 'order_items.product_name', 'order_items.variant_name', 'order_items.sku')
            ->orderByDesc('quantity_sold')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'variant_id' => $row->product_variant_id,
                'product_name' => (string) $row->product_name,
                'variant_name' => (string) ($row->variant_name ?: 'Default'),
                'sku' => (string) ($row->sku ?: '-'),
                'quantity_sold' => (int) $row->quantity_sold,
                'revenue' => (float) $row->revenue,
            ])
            ->all();
    }

    private function categoryPerformance(CarbonInterface $start, CarbonInterface $end): array
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('category_product', 'category_product.product_id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'category_product.category_id')
            ->where('orders.payment_status', OrderPaymentStatus::PAID->value)
            ->whereBetween(DB::raw('COALESCE(orders.placed_at, orders.created_at)'), [$start, $end])
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.line_total) as revenue')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'category_id' => $row->id,
                'category_name' => $this->decodeTranslatable((string) $row->name),
                'quantity_sold' => (int) $row->quantity_sold,
                'revenue' => (float) $row->revenue,
            ])
            ->all();
    }

    private function productReminderDemand(CarbonInterface $start, CarbonInterface $end): array
    {
        return DB::table('product_reminders')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'product_reminders.product_variant_id')
            ->leftJoin('products', 'products.id', '=', 'product_variants.product_id')
            ->whereBetween('product_reminders.created_at', [$start, $end])
            ->select(
                'product_reminders.product_variant_id',
                'products.name as product_name',
                'product_variants.size',
                'product_variants.color',
                DB::raw('COUNT(*) as requests'),
                DB::raw('SUM(CASE WHEN product_reminders.notified_at IS NULL THEN 0 ELSE 1 END) as notified_count')
            )
            ->groupBy('product_reminders.product_variant_id', 'products.name', 'product_variants.size', 'product_variants.color')
            ->orderByDesc('requests')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'variant_id' => $row->product_variant_id,
                'product_name' => $this->decodeTranslatable((string) ($row->product_name ?: 'منتج محذوف')),
                'variant_name' => trim(($row->size ?: '') . ' - ' . ($row->color ?: ''), ' -') ?: 'Default',
                'requests' => (int) $row->requests,
                'notified_count' => (int) $row->notified_count,
            ])
            ->all();
    }

    private function lowStockVariants(int $limit): array
    {
        return ProductVariant::query()
            ->with('product')
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->where('stock_quantity', '<=', 5)
            ->orderBy('stock_quantity')
            ->limit($limit)
            ->get()
            ->map(fn (ProductVariant $variant) => $this->variantRow($variant))
            ->all();
    }

    private function soldOutVariants(int $limit): array
    {
        return ProductVariant::query()
            ->with('product')
            ->where('is_active', true)
            ->where('stock_quantity', '<=', 0)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (ProductVariant $variant) => $this->variantRow($variant))
            ->all();
    }

    private function inventoryValueSummary(): array
    {
        $activeVariants = ProductVariant::query()->where('is_active', true);

        return [
            'active_variants' => (int) (clone $activeVariants)->count(),
            'total_units' => (int) (clone $activeVariants)->sum('stock_quantity'),
            'retail_value' => (float) (clone $activeVariants)->selectRaw('SUM(stock_quantity * price) as value')->value('value'),
            'low_stock_count' => (int) (clone $activeVariants)->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5)->count(),
            'sold_out_count' => (int) (clone $activeVariants)->where('stock_quantity', '<=', 0)->count(),
        ];
    }

    private function highestStockVariants(int $limit): array
    {
        return ProductVariant::query()
            ->with('product')
            ->where('is_active', true)
            ->orderByDesc('stock_quantity')
            ->limit($limit)
            ->get()
            ->map(fn (ProductVariant $variant) => $this->variantRow($variant))
            ->all();
    }

    private function customersTrend(array $rangeData): array
    {
        $interval = $rangeData['interval'];
        $labels = [];
        $counts = [];

        foreach ($this->period($rangeData['start'], $rangeData['end'], $interval) as $point) {
            $key = $this->bucketKey($point, $interval);
            $labels[$key] = $this->bucketLabel($point, $interval);
            $counts[$key] = 0;
        }

        Customer::query()
            ->whereBetween('created_at', [$rangeData['start'], $rangeData['end']])
            ->get(['created_at'])
            ->each(function (Customer $customer) use (&$counts, $interval): void {
                $key = $this->bucketKey(Carbon::parse($customer->created_at), $interval);

                if (array_key_exists($key, $counts)) {
                    $counts[$key]++;
                }
            });

        return [
            'labels' => array_values($labels),
            'counts' => array_values($counts),
        ];
    }

    private function registeredVsGuest(CarbonInterface $start, CarbonInterface $end): array
    {
        $rows = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('CASE WHEN customer_id IS NULL THEN "guest" ELSE "registered" END as customer_type, COUNT(*) as aggregate, SUM(grand_total) as value')
            ->groupBy('customer_type')
            ->get()
            ->keyBy('customer_type');

        return [
            ['label' => 'عملاء مسجلون', 'count' => (int) ($rows->get('registered')->aggregate ?? 0), 'value' => (float) ($rows->get('registered')->value ?? 0)],
            ['label' => 'زوار', 'count' => (int) ($rows->get('guest')->aggregate ?? 0), 'value' => (float) ($rows->get('guest')->value ?? 0)],
        ];
    }

    private function topCustomers(CarbonInterface $start, CarbonInterface $end, string $sortBy): array
    {
        return Order::query()
            ->where('payment_status', OrderPaymentStatus::PAID->value)
            ->whereBetween(DB::raw('COALESCE(placed_at, created_at)'), [$start, $end])
            ->select(
                'customer_id',
                'customer_email',
                DB::raw('MAX(CONCAT(customer_first_name, " ", customer_last_name)) as customer_name'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(grand_total) as revenue')
            )
            ->groupBy('customer_id', 'customer_email')
            ->orderByDesc($sortBy === 'orders' ? 'orders_count' : 'revenue')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'customer_id' => $row->customer_id,
                'customer_name' => (string) ($row->customer_name ?: $row->customer_email),
                'customer_email' => (string) $row->customer_email,
                'orders_count' => (int) $row->orders_count,
                'revenue' => (float) $row->revenue,
            ])
            ->all();
    }

    private function customerCountries(CarbonInterface $start, CarbonInterface $end): array
    {
        return Customer::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('COALESCE(NULLIF(country, ""), "غير محدد") as label, COUNT(*) as aggregate')
            ->groupBy('label')
            ->orderByDesc('aggregate')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->label, 'count' => (int) $row->aggregate])
            ->all();
    }

    private function orderCountries(CarbonInterface $start, CarbonInterface $end, string $column): array
    {
        return Order::query()
            ->where('payment_status', OrderPaymentStatus::PAID->value)
            ->whereBetween(DB::raw('COALESCE(placed_at, created_at)'), [$start, $end])
            ->selectRaw('COALESCE(NULLIF(' . $column . ', ""), "غير محدد") as label, COUNT(*) as aggregate, SUM(grand_total) as value')
            ->groupBy('label')
            ->orderByDesc('aggregate')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->label, 'count' => (int) $row->aggregate, 'value' => (float) $row->value])
            ->all();
    }

    private function orderLocations(CarbonInterface $start, CarbonInterface $end, string $column): array
    {
        return Order::query()
            ->where('payment_status', OrderPaymentStatus::PAID->value)
            ->whereBetween(DB::raw('COALESCE(placed_at, created_at)'), [$start, $end])
            ->selectRaw('COALESCE(NULLIF(' . $column . ', ""), "غير محدد") as label, COUNT(*) as aggregate, SUM(grand_total) as value')
            ->groupBy('label')
            ->orderByDesc('aggregate')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->label, 'count' => (int) $row->aggregate, 'value' => (float) $row->value])
            ->all();
    }

    private function customerEmailDomains(CarbonInterface $start, CarbonInterface $end): array
    {
        return Customer::query()
            ->whereBetween('created_at', [$start, $end])
            ->get(['email'])
            ->map(function (Customer $customer) {
                $domain = Str::of((string) $customer->email)->after('@')->lower()->trim()->toString();

                return $domain !== '' ? $domain : 'غير محدد';
            })
            ->countBy()
            ->sortDesc()
            ->take(10)
            ->map(fn (int $count, string $domain) => ['label' => $domain, 'count' => $count])
            ->values()
            ->all();
    }

    private function cartProducts(Builder $cartQuery, string $type): array
    {
        $cartIds = (clone $cartQuery)->limit(500)->pluck('id');

        if ($cartIds->isEmpty()) {
            return [];
        }

        return DB::table('cart_items')
            ->whereIn('cart_id', $cartIds)
            ->select(
                'product_id',
                'product_name',
                DB::raw('SUM(quantity) as quantity'),
                DB::raw('SUM(line_total) as value')
            )
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('quantity')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'type' => $type,
                'product_id' => $row->product_id,
                'product_name' => (string) $row->product_name,
                'quantity' => (int) $row->quantity,
                'value' => (float) $row->value,
            ])
            ->all();
    }

    private function cartVariants(Builder $cartQuery, string $type): array
    {
        $cartIds = (clone $cartQuery)->limit(500)->pluck('id');

        if ($cartIds->isEmpty()) {
            return [];
        }

        return DB::table('cart_items')
            ->whereIn('cart_id', $cartIds)
            ->select(
                'product_variant_id',
                'product_name',
                'variant_name',
                'sku',
                DB::raw('SUM(quantity) as quantity'),
                DB::raw('SUM(line_total) as value')
            )
            ->groupBy('product_variant_id', 'product_name', 'variant_name', 'sku')
            ->orderByDesc('quantity')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'type' => $type,
                'variant_id' => $row->product_variant_id,
                'product_name' => (string) $row->product_name,
                'variant_name' => (string) ($row->variant_name ?: 'Default'),
                'sku' => (string) ($row->sku ?: '-'),
                'quantity' => (int) $row->quantity,
                'value' => (float) $row->value,
            ])
            ->all();
    }

    private function cartAgeBuckets(CarbonInterface $start, CarbonInterface $end): array
    {
        $buckets = [
            'under_1h' => ['label' => 'أقل من ساعة', 'count' => 0, 'value' => 0.0],
            '1_6h' => ['label' => '1-6 ساعات', 'count' => 0, 'value' => 0.0],
            '6_24h' => ['label' => '6-24 ساعة', 'count' => 0, 'value' => 0.0],
            '1_3d' => ['label' => '1-3 أيام', 'count' => 0, 'value' => 0.0],
            'over_3d' => ['label' => 'أكثر من 3 أيام', 'count' => 0, 'value' => 0.0],
        ];

        Cart::query()
            ->whereBetween('created_at', [$start, $end])
            ->get(['created_at', 'subtotal'])
            ->each(function (Cart $cart) use (&$buckets): void {
                $hours = Carbon::parse($cart->created_at)->diffInHours(now());
                $key = match (true) {
                    $hours < 1 => 'under_1h',
                    $hours < 6 => '1_6h',
                    $hours < 24 => '6_24h',
                    $hours < 72 => '1_3d',
                    default => 'over_3d',
                };

                $buckets[$key]['count']++;
                $buckets[$key]['value'] += (float) $cart->subtotal;
            });

        return array_values($buckets);
    }

    private function couponSummary(CarbonInterface $start, CarbonInterface $end): array
    {
        $redemptions = DB::table('coupon_redemptions')
            ->whereBetween('used_at', [$start, $end])
            ->count();

        $paidOrdersWithCoupons = Order::query()
            ->where('payment_status', OrderPaymentStatus::PAID->value)
            ->whereNotNull('coupon_id')
            ->whereBetween(DB::raw('COALESCE(placed_at, created_at)'), [$start, $end]);

        return [
            'redemptions' => (int) $redemptions,
            'revenue_with_coupons' => (float) (clone $paidOrdersWithCoupons)->sum('grand_total'),
            'discount_total' => (float) (clone $paidOrdersWithCoupons)->sum('discount_total'),
        ];
    }

    private function couponPerformance(CarbonInterface $start, CarbonInterface $end): array
    {
        return Order::query()
            ->where('payment_status', OrderPaymentStatus::PAID->value)
            ->whereNotNull('coupon_code')
            ->whereBetween(DB::raw('COALESCE(placed_at, created_at)'), [$start, $end])
            ->select(
                'coupon_id',
                'coupon_code',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('SUM(discount_total) as discount_total'),
                DB::raw('SUM(grand_total) as revenue')
            )
            ->groupBy('coupon_id', 'coupon_code')
            ->orderByDesc('usage_count')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'coupon_id' => $row->coupon_id,
                'coupon_code' => (string) $row->coupon_code,
                'usage_count' => (int) $row->usage_count,
                'discount_total' => (float) $row->discount_total,
                'revenue' => (float) $row->revenue,
            ])
            ->all();
    }

    private function welcomeCouponSummary(CarbonInterface $start, CarbonInterface $end): array
    {
        $sent = DB::table('welcome_coupons')->whereBetween('created_at', [$start, $end])->count();
        $used = DB::table('welcome_coupons')->whereBetween('used_at', [$start, $end])->count();

        return [
            'sent' => (int) $sent,
            'used' => (int) $used,
            'conversion_rate' => $this->percent((int) $used, (int) $sent),
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
                    ->where('orders.payment_status', OrderPaymentStatus::PAID->value);
            });
    }

    private function variantRow(ProductVariant $variant): array
    {
        return [
            'variant_id' => $variant->id,
            'product_name' => (string) $variant->product?->name,
            'variant_name' => $variant->display_name,
            'stock_quantity' => (int) $variant->stock_quantity,
            'price' => (float) $variant->price,
        ];
    }

    private function percent(int $numerator, int $denominator): float
    {
        return $denominator > 0 ? round(($numerator / $denominator) * 100, 2) : 0.0;
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

    private function decodeTranslatable(string $value): string
    {
        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return (string) ($decoded[app()->getLocale()] ?? $decoded['ar'] ?? $decoded['en'] ?? reset($decoded));
        }

        return $value;
    }

    private function scalarValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return (string) $value;
    }

    private function salesExportRows(array $report): array
    {
        $rows = [['Type', 'Item', 'Orders / Count', 'Value']];

        foreach ($report['sales']['trend']['labels'] as $index => $label) {
            $rows[] = ['Sales Trend', 'Period ' . ($index + 1), $report['sales']['trend']['orders'][$index] ?? 0, $report['sales']['trend']['revenue'][$index] ?? 0];
        }

        foreach (['payment_status' => 'Payment Status', 'fulfillment_status' => 'Fulfillment Status', 'order_status' => 'Order Status', 'currency' => 'Currency', 'payment_provider' => 'Payment Provider'] as $key => $label) {
            foreach ($report['sales'][$key] as $row) {
                $rows[] = [$label, $row['key'] ?? $row['label'], $row['count'], $row['value']];
            }
        }

        foreach (['hourly_performance' => 'Hourly Performance', 'weekday_performance' => 'Weekday Performance'] as $key => $label) {
            foreach ($report['sales'][$key] as $row) {
                $rows[] = [$label, $row['label'], $row['count'], $row['value']];
            }
        }

        return $rows;
    }

    private function productsExportRows(array $report): array
    {
        $rows = [['Type', 'Name', 'Quantity / Requests', 'Revenue / Value']];

        foreach ($report['products']['top_by_quantity'] as $row) {
            $rows[] = ['Top by Quantity', $row['product_name'], $row['quantity_sold'], $row['revenue']];
        }

        foreach ($report['products']['top_by_revenue'] as $row) {
            $rows[] = ['Top by Revenue', $row['product_name'], $row['quantity_sold'], $row['revenue']];
        }

        foreach ($report['products']['top_variants'] as $row) {
            $rows[] = ['Top Variants', $row['product_name'] . ' ' . $row['variant_name'], $row['quantity_sold'], $row['revenue']];
        }

        foreach ($report['products']['categories'] as $row) {
            $rows[] = ['Categories', $row['category_name'], $row['quantity_sold'], $row['revenue']];
        }

        foreach ($report['products']['reminders'] as $row) {
            $rows[] = ['Reminder Requests', $row['product_name'] . ' ' . $row['variant_name'], $row['requests'], $row['notified_count']];
        }

        return $rows;
    }

    private function inventoryExportRows(array $report): array
    {
        $rows = [['Type', 'Product', 'Variant', 'Stock', 'Price']];
        $stockValue = $report['inventory']['stock_value'];

        $rows[] = ['Inventory Summary', 'Active Variants', '', $stockValue['active_variants'], ''];
        $rows[] = ['Inventory Summary', 'Total Units', '', $stockValue['total_units'], ''];
        $rows[] = ['Inventory Summary', 'Retail Value', '', '', $stockValue['retail_value']];
        $rows[] = ['Inventory Summary', 'Low Stock Count', '', $stockValue['low_stock_count'], ''];
        $rows[] = ['Inventory Summary', 'Sold Out Count', '', $stockValue['sold_out_count'], ''];

        foreach ($report['inventory']['low_stock'] as $row) {
            $rows[] = ['Low Stock', $row['product_name'], $row['variant_name'], $row['stock_quantity'], $row['price']];
        }

        foreach ($report['inventory']['sold_out'] as $row) {
            $rows[] = ['Sold Out', $row['product_name'], $row['variant_name'], $row['stock_quantity'], $row['price']];
        }

        foreach ($report['inventory']['highest_stock'] as $row) {
            $rows[] = ['Highest Stock', $row['product_name'], $row['variant_name'], $row['stock_quantity'], $row['price']];
        }

        return $rows;
    }

    private function customersExportRows(array $report): array
    {
        $rows = [['Type', 'Item', 'Count / Orders', 'Value']];

        foreach ($report['customers']['new_customers_trend']['labels'] as $index => $label) {
            $rows[] = ['New Customers', 'Period ' . ($index + 1), $report['customers']['new_customers_trend']['counts'][$index] ?? 0, ''];
        }

        foreach ($report['customers']['registered_vs_guest'] as $row) {
            $rows[] = ['Registered / Guest', $row['label'], $row['count'], $row['value']];
        }

        foreach ($report['customers']['top_by_revenue'] as $row) {
            $rows[] = ['Top Customers by Revenue', $row['customer_name'], $row['orders_count'], $row['revenue']];
        }

        foreach ($report['customers']['top_by_orders'] as $row) {
            $rows[] = ['Top Customers by Orders', $row['customer_name'], $row['orders_count'], $row['revenue']];
        }

        foreach (['customer_countries' => 'Customer Countries', 'billing_countries' => 'Billing Countries', 'shipping_countries' => 'Shipping Countries', 'billing_cities' => 'Billing Cities', 'shipping_cities' => 'Shipping Cities', 'email_domains' => 'Email Domains'] as $key => $label) {
            foreach ($report['customers'][$key] as $row) {
                $rows[] = [$label, $row['label'], $row['count'], $row['value'] ?? ''];
            }
        }

        return $rows;
    }

    private function cartsExportRows(array $report): array
    {
        $summary = $report['carts']['summary'];
        $rows = [
            ['Type', 'Item', 'Count / Quantity', 'Value'],
            ['Summary', 'Carts Created', $summary['created'], ''],
            ['Summary', 'Active Carts', $summary['active'], ''],
            ['Summary', 'Abandoned Carts', $summary['abandoned'], $summary['abandoned_value']],
            ['Summary', 'Average Cart Value', '', $summary['average_cart_value']],
        ];

        foreach ($report['carts']['active_products'] as $row) {
            $rows[] = ['Active Cart Products', $row['product_name'], $row['quantity'], $row['value']];
        }

        foreach ($report['carts']['abandoned_products'] as $row) {
            $rows[] = ['Abandoned Cart Products', $row['product_name'], $row['quantity'], $row['value']];
        }

        foreach ($report['carts']['active_variants'] as $row) {
            $rows[] = ['Active Cart Variants', $row['product_name'] . ' - ' . $row['variant_name'], $row['quantity'], $row['value']];
        }

        foreach ($report['carts']['abandoned_variants'] as $row) {
            $rows[] = ['Abandoned Cart Variants', $row['product_name'] . ' - ' . $row['variant_name'], $row['quantity'], $row['value']];
        }

        foreach ($report['carts']['age_buckets'] as $index => $row) {
            $rows[] = ['Cart Age Buckets', 'Bucket ' . ($index + 1), $row['count'], $row['value']];
        }

        return $rows;
    }

    private function couponsExportRows(array $report): array
    {
        $summary = $report['coupons']['summary'];
        $welcome = $report['coupons']['welcome'];
        $rows = [
            ['Type', 'Item', 'Count', 'Value'],
            ['Summary', 'Coupon Redemptions', $summary['redemptions'], ''],
            ['Summary', 'Revenue with Coupons', '', $summary['revenue_with_coupons']],
            ['Summary', 'Discount Total', '', $summary['discount_total']],
            ['Welcome Coupons', 'Sent', $welcome['sent'], ''],
            ['Welcome Coupons', 'Used', $welcome['used'], ''],
            ['Welcome Coupons', 'Conversion Rate', $welcome['conversion_rate'] . '%', ''],
        ];

        foreach ($report['coupons']['by_coupon'] as $row) {
            $rows[] = ['Coupons', $row['coupon_code'], $row['usage_count'], $row['discount_total']];
        }

        return $rows;
    }
}
