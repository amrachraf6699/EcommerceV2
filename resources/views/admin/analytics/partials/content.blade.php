@php
    $selectedSection = $selectedSection ?? request('section');
    $selectedCategory = $selectedCategory ?? request('category');
    $selectedReport = $selectedReport ?? request('report');
    $analyticsNavigation = $analyticsNavigation ?? ['categories' => [], 'reports' => []];
    $query = [
        'range' => $report['range'],
        'start_date' => $report['range'] === 'custom' ? $report['start_date'] : request('start_date'),
        'end_date' => $report['range'] === 'custom' ? $report['end_date'] : request('end_date'),
    ];
    $categoryQuery = fn (string $category) => array_filter(array_merge($query, ['category' => $category]));
    $reportQuery = fn (string $reportKey) => array_filter(array_merge($query, [
        'category' => data_get($analyticsNavigation, "reports.$reportKey.category"),
        'report' => $reportKey,
    ]));
    $sectionQuery = fn (string $section) => array_filter(array_merge($query, ['section' => $section]));
    $exportQuery = fn (?string $section = null) => array_filter(array_merge($query, ['section' => $section]));
    $kpis = $report['kpis'];
    $sales = $report['sales'];
    $products = $report['products'];
    $inventory = $report['inventory'];
    $customers = $report['customers'];
    $carts = $report['carts'];
    $coupons = $report['coupons'];
    $fallbackCards = [
        'summary' => ['title' => 'الملخص التنفيذي', 'copy' => 'أهم أرقام المتجر في الفترة المختارة.', 'icon' => 'bx bx-pulse', 'meta' => number_format($kpis['revenue'], 2) . ' إيرادات'],
        'sales' => ['title' => 'المبيعات', 'copy' => 'اتجاه الإيرادات والطلبات وحالات الطلبات.', 'icon' => 'bx bx-line-chart', 'meta' => number_format($kpis['paid_orders']) . ' طلب مدفوع'],
        'products' => ['title' => 'المنتجات', 'copy' => 'الأعلى مبيعاً، النسخ، الأقسام، وطلبات التنبيه.', 'icon' => 'bx bx-package', 'meta' => count($products['top_by_revenue']) . ' تقرير'],
        'inventory' => ['title' => 'المخزون', 'copy' => 'النفاد القريب والمنتجات المنتهية.', 'icon' => 'bx bx-archive', 'meta' => count($inventory['low_stock']) + count($inventory['sold_out']) . ' عنصر'],
        'customers' => ['title' => 'العملاء', 'copy' => 'نمو العملاء، الدول، وأعلى العملاء قيمة.', 'icon' => 'bx bx-group', 'meta' => number_format($kpis['new_customers']) . ' عميل جديد'],
        'carts' => ['title' => 'السلات', 'copy' => 'النشطة والمتروكة وقيمة المنتجات داخل السلات.', 'icon' => 'bx bx-cart', 'meta' => number_format($carts['summary']['abandoned']) . ' متروكة'],
        'coupons' => ['title' => 'الكوبونات', 'copy' => 'الاستخدام، الخصومات، وكوبونات الترحيب.', 'icon' => 'bx bx-purchase-tag-alt', 'meta' => number_format($coupons['summary']['redemptions']) . ' استخدام'],
    ];
    $categoryCards = $analyticsNavigation['categories'] ?: $fallbackCards;
    $selectedReportData = $selectedReport ? data_get($analyticsNavigation, "reports.$selectedReport") : null;
    $selectedTitle = $selectedReportData['title'] ?? ($selectedCategory ? data_get($analyticsNavigation, "categories.$selectedCategory.title") : 'مركز التحليلات');
    $reportsInCategory = $selectedCategory
        ? collect($analyticsNavigation['reports'])->filter(fn ($item) => ($item['category'] ?? null) === $selectedCategory)
        : collect();
    $showReport = fn (string ...$reports) => ! $selectedReport || in_array($selectedReport, $reports, true);
@endphp

<div class="admin-analytics space-y-6" data-analytics-root>
    <section class="analytics-panel rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h2 class="text-xl font-bold text-white">
                    {{ $selectedTitle }}
                </h2>
                <p class="mt-2 text-sm text-slate-300">
                    {{ $selectedReportData['copy'] ?? ($selectedCategory ? data_get($analyticsNavigation, "categories.$selectedCategory.copy") : 'اختر مجموعة تحليلات ثم اختر التقرير المناسب.') }}
                    <span class="block mt-1">الفترة الحالية: {{ $report['range_label'] }}</span>
                </p>
            </div>

            <form class="grid gap-3 lg:flex lg:flex-wrap lg:items-center lg:justify-end" data-analytics-filter-form action="{{ route('admin.analytics.index') }}" method="GET">
                @if ($selectedCategory)
                    <input type="hidden" name="category" value="{{ $selectedCategory }}">
                @endif
                @if ($selectedReport)
                    <input type="hidden" name="report" value="{{ $selectedReport }}">
                @endif

                <select name="range" class="admin-select analytics-filter-input min-w-[190px]" data-analytics-range-select>
                    @foreach ($report['available_ranges'] as $rangeKey => $rangeLabel)
                        <option value="{{ $rangeKey }}" @selected($report['range'] === $rangeKey)>{{ $rangeLabel }}</option>
                    @endforeach
                    <option value="custom" @selected($report['range'] === 'custom')>فترة مخصصة</option>
                </select>

                <input class="admin-input analytics-filter-input min-w-[160px]" type="date" name="start_date" value="{{ $report['range'] === 'custom' ? $report['start_date'] : request('start_date') }}" data-analytics-date-input>
                <input class="admin-input analytics-filter-input min-w-[160px]" type="date" name="end_date" value="{{ $report['range'] === 'custom' ? $report['end_date'] : request('end_date') }}" data-analytics-date-input>

                <button type="submit" class="admin-btn-secondary analytics-action">تطبيق</button>
                @if ($selectedReport)
                    <a href="{{ route('admin.analytics.index', $query) }}" class="admin-btn-secondary analytics-action">كل التقارير</a>
                    <a href="{{ route('admin.analytics.index', $categoryQuery($selectedCategory)) }}" class="admin-btn-secondary analytics-action">رجوع للقائمة</a>
                    <a href="{{ route('admin.analytics.export.pdf', $exportQuery($selectedSection)) }}" class="admin-btn-secondary analytics-action">PDF</a>
                    <a href="{{ route('admin.analytics.export.excel', $exportQuery($selectedSection)) }}" class="admin-btn-primary analytics-action">Excel</a>
                @elseif ($selectedCategory)
                    <a href="{{ route('admin.analytics.index', $query) }}" class="admin-btn-secondary analytics-action">كل التقارير</a>
                @else
                    <a href="{{ route('admin.analytics.export.pdf', $exportQuery()) }}" class="admin-btn-secondary analytics-action">PDF كامل</a>
                    <a href="{{ route('admin.analytics.export.excel', $exportQuery()) }}" class="admin-btn-primary analytics-action">Excel كامل</a>
                @endif
            </form>
        </div>
    </section>

    @if (! $selectedCategory && ! $selectedReport)
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($categoryCards as $category => $card)
                <a href="{{ route('admin.analytics.index', $categoryQuery($category)) }}" class="analytics-card analytics-card-link rounded-[2rem] border border-white/10 bg-white/5 p-5 transition hover:border-amber-300/30 hover:bg-white/10">
                    <span class="inline-flex h-12 w-12 items-center justify-center border border-white/10 bg-slate-950/50 text-2xl text-amber-200">
                        <i class="{{ $card['icon'] }}" aria-hidden="true"></i>
                    </span>
                    <strong class="mt-5 block text-xl font-extrabold text-white">{{ $card['title'] }}</strong>
                    <span class="mt-2 block text-sm leading-7 text-slate-300">{{ $card['copy'] }}</span>
                    <span class="mt-5 inline-flex border border-white/10 bg-slate-950/50 px-3 py-2 text-xs font-bold text-slate-200">
                        {{ collect($analyticsNavigation['reports'])->where('category', $category)->count() ?: ($card['meta'] ?? '') }} تقارير
                    </span>
                </a>
            @endforeach
        </section>
    @endif

    @if ($selectedCategory && ! $selectedReport)
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($reportsInCategory as $reportKey => $reportCard)
                <a href="{{ route('admin.analytics.index', $reportQuery($reportKey)) }}" class="analytics-card analytics-card-link rounded-[2rem] border border-white/10 bg-white/5 p-5 transition hover:border-amber-300/30 hover:bg-white/10">
                    <span class="inline-flex h-10 w-10 items-center justify-center border border-white/10 bg-slate-950/50 text-xl text-amber-200">
                        <i class="{{ data_get($analyticsNavigation, 'categories.' . $reportCard['category'] . '.icon', 'bx bx-bar-chart') }}" aria-hidden="true"></i>
                    </span>
                    <strong class="mt-5 block text-lg font-extrabold text-white">{{ $reportCard['title'] }}</strong>
                    <span class="mt-2 block text-sm leading-7 text-slate-300">{{ $reportCard['copy'] }}</span>
                    <span class="mt-5 inline-flex border border-white/10 bg-slate-950/50 px-3 py-2 text-xs font-bold text-slate-200">فتح التقرير</span>
                </a>
            @endforeach
        </section>
    @endif

    @if ($selectedSection === 'summary')
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                ['label' => 'الإيرادات', 'value' => number_format($kpis['revenue'], 2), 'tone' => 'emerald'],
                ['label' => 'الطلبات المدفوعة', 'value' => number_format($kpis['paid_orders']), 'tone' => 'slate'],
                ['label' => 'كل الطلبات', 'value' => number_format($kpis['total_orders']), 'tone' => 'slate'],
                ['label' => 'متوسط قيمة الطلب', 'value' => number_format($kpis['average_order_value'], 2), 'tone' => 'slate'],
                ['label' => 'عملاء جدد', 'value' => number_format($kpis['new_customers']), 'tone' => 'slate'],
                ['label' => 'سلات نشطة', 'value' => number_format($kpis['active_carts']), 'tone' => 'slate'],
                ['label' => 'سلات متروكة', 'value' => number_format($kpis['abandoned_carts']), 'tone' => 'red'],
                ['label' => 'معدل التحويل', 'value' => number_format($kpis['cart_to_order_conversion_rate'], 2) . '%', 'tone' => 'amber'],
                ['label' => 'إجمالي الخصومات', 'value' => number_format($kpis['discount_total'], 2), 'tone' => 'slate'],
                ['label' => 'إجمالي الشحن', 'value' => number_format($kpis['shipping_total'], 2), 'tone' => 'slate'],
            ] as $card)
                <article class="analytics-card rounded-[2rem] border p-5 {{ $card['tone'] === 'emerald' ? 'border-emerald-400/20 bg-emerald-500/10' : ($card['tone'] === 'red' ? 'border-red-400/20 bg-red-500/10' : ($card['tone'] === 'amber' ? 'border-amber-300/20 bg-amber-400/10' : 'border-white/10 bg-white/5')) }}">
                    <p class="text-sm font-semibold text-slate-300">{{ $card['label'] }}</p>
                    <strong class="mt-3 block text-2xl font-extrabold text-white">{{ $card['value'] }}</strong>
                </article>
            @endforeach
        </section>
    @endif

    @if ($selectedSection === 'sales')
        @if ($showReport('revenue-trend'))
            <section class="grid gap-6">
                <article class="analytics-panel rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
                    <h3 class="text-lg font-bold text-white">اتجاه الإيرادات والطلبات</h3>
                    <div class="admin-analytics-chart mt-5" data-analytics-chart data-chart-kind="sales-trend" data-chart-labels='@json($sales['trend']['labels'])' data-chart-orders='@json($sales['trend']['orders'])' data-chart-revenue='@json($sales['trend']['revenue'])' data-chart-aov='@json($sales['trend']['aov'])'></div>
                </article>
            </section>
        @endif

        @if ($showReport('payment-statuses', 'currency-breakdown', 'payment-providers'))
            <section class="grid gap-6 xl:grid-cols-3">
                @if ($showReport('payment-statuses'))
                    <article class="analytics-panel rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
                        <h3 class="text-lg font-bold text-white">حالات الدفع</h3>
                        <div class="admin-analytics-chart mt-5" data-analytics-chart data-chart-kind="donut" data-chart-labels='@json(collect($sales['payment_status'])->pluck('label')->values())' data-chart-values='@json(collect($sales['payment_status'])->pluck('count')->values())'></div>
                    </article>
                @endif
                @if ($showReport('currency-breakdown'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'الطلبات حسب العملة', 'rows' => $sales['currency'], 'headers' => ['العملة', 'الطلبات', 'القيمة'], 'columns' => ['label', 'count', 'value']])
                @endif
                @if ($showReport('payment-providers'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'الطلبات حسب مزود الدفع', 'rows' => $sales['payment_provider'], 'headers' => ['المزود', 'الطلبات', 'القيمة'], 'columns' => ['label', 'count', 'value']])
                @endif
            </section>
        @endif

        @if ($showReport('hourly-sales', 'weekday-sales'))
            <section class="grid gap-6 xl:grid-cols-2">
                @if ($showReport('hourly-sales'))
                    <article class="analytics-panel rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
                        <h3 class="text-lg font-bold text-white">أفضل ساعات البيع</h3>
                        <div class="admin-analytics-chart mt-5" data-analytics-chart data-chart-kind="bar" data-chart-labels='@json(collect($sales['hourly_performance'])->pluck('label')->values())' data-chart-values='@json(collect($sales['hourly_performance'])->pluck('value')->values())' data-chart-series-name="الإيراد"></div>
                    </article>
                @endif
                @if ($showReport('weekday-sales'))
                    <article class="analytics-panel rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
                        <h3 class="text-lg font-bold text-white">أداء أيام الأسبوع</h3>
                        <div class="admin-analytics-chart mt-5" data-analytics-chart data-chart-kind="bar" data-chart-labels='@json(collect($sales['weekday_performance'])->pluck('label')->values())' data-chart-values='@json(collect($sales['weekday_performance'])->pluck('value')->values())' data-chart-series-name="الإيراد"></div>
                    </article>
                @endif
            </section>
        @endif

        @if ($showReport('fulfillment-statuses', 'order-statuses'))
            <section class="grid gap-6 xl:grid-cols-2">
                @if ($showReport('fulfillment-statuses'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'قيمة الطلبات حسب حالة التجهيز', 'rows' => $sales['fulfillment_status'], 'headers' => ['الحالة', 'الطلبات', 'القيمة'], 'columns' => ['label', 'count', 'value']])
                @endif
                @if ($showReport('order-statuses'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'قيمة الطلبات حسب حالة الطلب', 'rows' => $sales['order_status'], 'headers' => ['الحالة', 'الطلبات', 'القيمة'], 'columns' => ['label', 'count', 'value']])
                @endif
            </section>
        @endif
    @endif

    @if ($selectedSection === 'products')
        @if ($showReport('top-products-revenue', 'category-performance'))
            <section class="grid gap-6 xl:grid-cols-2">
                @if ($showReport('top-products-revenue'))
                    <article class="analytics-panel rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
                        <h3 class="text-lg font-bold text-white">أعلى المنتجات حسب الإيراد</h3>
                        <div class="admin-analytics-chart mt-5" data-analytics-chart data-chart-kind="bar" data-chart-labels='@json(collect($products['top_by_revenue'])->pluck('product_name')->values())' data-chart-values='@json(collect($products['top_by_revenue'])->pluck('revenue')->values())' data-chart-series-name="الإيراد"></div>
                    </article>
                @endif
                @if ($showReport('category-performance'))
                    <article class="analytics-panel rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
                        <h3 class="text-lg font-bold text-white">الأقسام حسب الإيراد</h3>
                        <div class="admin-analytics-chart mt-5" data-analytics-chart data-chart-kind="bar" data-chart-labels='@json(collect($products['categories'])->pluck('category_name')->values())' data-chart-values='@json(collect($products['categories'])->pluck('revenue')->values())' data-chart-series-name="الإيراد"></div>
                    </article>
                @endif
            </section>
        @endif
        @if ($showReport('top-products-quantity', 'top-variants', 'restock-demand', 'category-performance'))
            <section class="grid gap-6 xl:grid-cols-3">
                @if ($showReport('top-products-quantity'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'أعلى المنتجات كمية', 'rows' => $products['top_by_quantity'], 'headers' => ['المنتج', 'الكمية', 'الإيراد'], 'columns' => ['product_name', 'quantity_sold', 'revenue']])
                @endif
                @if ($showReport('top-variants'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'أعلى النسخ كمية', 'rows' => $products['top_variants'], 'headers' => ['المنتج', 'النسخة', 'الكمية'], 'columns' => ['product_name', 'variant_name', 'quantity_sold']])
                @endif
                @if ($showReport('category-performance'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'ترتيب الأقسام', 'rows' => $products['categories'], 'headers' => ['القسم', 'الكمية', 'الإيراد'], 'columns' => ['category_name', 'quantity_sold', 'revenue']])
                @endif
                @if ($showReport('restock-demand'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'طلبات التنبيه عند التوفر', 'rows' => $products['reminders'], 'headers' => ['المنتج', 'النسخة', 'الطلبات'], 'columns' => ['product_name', 'variant_name', 'requests']])
                @endif
            </section>
        @endif
    @endif

    @if ($selectedSection === 'inventory')
        @if ($showReport('inventory-value'))
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                @foreach ([
                    ['label' => 'النسخ النشطة', 'value' => number_format($inventory['stock_value']['active_variants'])],
                    ['label' => 'إجمالي الوحدات', 'value' => number_format($inventory['stock_value']['total_units'])],
                    ['label' => 'قيمة المخزون', 'value' => number_format($inventory['stock_value']['retail_value'], 2)],
                    ['label' => 'نفاد قريب', 'value' => number_format($inventory['stock_value']['low_stock_count'])],
                    ['label' => 'نفد المخزون', 'value' => number_format($inventory['stock_value']['sold_out_count'])],
                ] as $card)
                    <article class="analytics-card rounded-[2rem] border border-white/10 bg-white/5 p-5">
                        <p class="text-sm font-semibold text-slate-300">{{ $card['label'] }}</p>
                        <strong class="mt-3 block text-2xl font-extrabold text-white">{{ $card['value'] }}</strong>
                    </article>
                @endforeach
            </section>
        @endif
        @if ($showReport('inventory-alerts', 'sold-out', 'highest-stock'))
            <section class="grid gap-6 xl:grid-cols-3">
                @if ($showReport('inventory-alerts'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'نفاد قريب', 'rows' => $inventory['low_stock'], 'headers' => ['المنتج', 'النسخة', 'المتبقي'], 'columns' => ['product_name', 'variant_name', 'stock_quantity']])
                @endif
                @if ($showReport('sold-out'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'نفد من المخزون', 'rows' => $inventory['sold_out'], 'headers' => ['المنتج', 'النسخة', 'المخزون'], 'columns' => ['product_name', 'variant_name', 'stock_quantity']])
                @endif
                @if ($showReport('highest-stock'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'أعلى مخزون', 'rows' => $inventory['highest_stock'], 'headers' => ['المنتج', 'النسخة', 'المخزون'], 'columns' => ['product_name', 'variant_name', 'stock_quantity']])
                @endif
            </section>
        @endif
    @endif

    @if ($selectedSection === 'customers')
        @if ($showReport('customer-growth', 'registered-vs-guest'))
            <section class="grid gap-6 xl:grid-cols-[2fr_1fr]">
                @if ($showReport('customer-growth'))
                    <article class="analytics-panel rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
                        <h3 class="text-lg font-bold text-white">نمو العملاء</h3>
                        <div class="admin-analytics-chart mt-5" data-analytics-chart data-chart-kind="line" data-chart-labels='@json($customers['new_customers_trend']['labels'])' data-chart-values='@json($customers['new_customers_trend']['counts'])' data-chart-series-name="عملاء جدد"></div>
                    </article>
                @endif
                @if ($showReport('registered-vs-guest'))
                    <article class="analytics-panel rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
                        <h3 class="text-lg font-bold text-white">مسجلون مقابل زوار</h3>
                        <div class="admin-analytics-chart mt-5" data-analytics-chart data-chart-kind="donut" data-chart-labels='@json(collect($customers['registered_vs_guest'])->pluck('label')->values())' data-chart-values='@json(collect($customers['registered_vs_guest'])->pluck('count')->values())'></div>
                    </article>
                @endif
            </section>
        @endif
        @if ($showReport('top-customers', 'customer-countries', 'customer-cities', 'email-domains'))
            <section class="grid gap-6 xl:grid-cols-3">
                @if ($showReport('top-customers'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'أعلى العملاء إيراداً', 'rows' => $customers['top_by_revenue'], 'headers' => ['العميل', 'الطلبات', 'الإيراد'], 'columns' => ['customer_name', 'orders_count', 'revenue']])
                    @include('admin.analytics.partials.metric-table', ['title' => 'أعلى العملاء طلباً', 'rows' => $customers['top_by_orders'], 'headers' => ['العميل', 'الطلبات', 'الإيراد'], 'columns' => ['customer_name', 'orders_count', 'revenue']])
                @endif
                @if ($showReport('customer-countries'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'دول العملاء', 'rows' => $customers['customer_countries'], 'headers' => ['الدولة', 'العدد'], 'columns' => ['label', 'count']])
                    @include('admin.analytics.partials.metric-table', ['title' => 'دول الشحن', 'rows' => $customers['shipping_countries'], 'headers' => ['الدولة', 'الطلبات', 'القيمة'], 'columns' => ['label', 'count', 'value']])
                @endif
                @if ($showReport('customer-cities'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'مدن الفوترة', 'rows' => $customers['billing_cities'], 'headers' => ['المدينة', 'الطلبات', 'القيمة'], 'columns' => ['label', 'count', 'value']])
                    @include('admin.analytics.partials.metric-table', ['title' => 'مدن الشحن', 'rows' => $customers['shipping_cities'], 'headers' => ['المدينة', 'الطلبات', 'القيمة'], 'columns' => ['label', 'count', 'value']])
                @endif
                @if ($showReport('email-domains'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'نطاقات البريد', 'rows' => $customers['email_domains'], 'headers' => ['النطاق', 'العملاء'], 'columns' => ['label', 'count']])
                @endif
            </section>
        @endif
    @endif

    @if ($selectedSection === 'carts')
        @if ($showReport('cart-summary', 'active-cart-products', 'abandoned-cart-products'))
            <section class="grid gap-6 xl:grid-cols-3">
                @if ($showReport('cart-summary'))
                    <article class="analytics-panel rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
                        <h3 class="text-lg font-bold text-white">ملخص السلات</h3>
                        <div class="mt-5 grid gap-3">
                            @foreach ([['السلات المنشأة', $carts['summary']['created']], ['السلات النشطة', $carts['summary']['active']], ['السلات المتروكة', $carts['summary']['abandoned']], ['قيمة السلات المتروكة', number_format($carts['summary']['abandoned_value'], 2)], ['متوسط قيمة السلة', number_format($carts['summary']['average_cart_value'], 2)]] as $row)
                                <div class="flex items-center justify-between gap-3 border border-white/10 bg-slate-950/40 px-4 py-3">
                                    <span class="text-sm text-slate-300">{{ $row[0] }}</span>
                                    <strong class="text-white">{{ $row[1] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endif
                @if ($showReport('active-cart-products'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'منتجات السلات النشطة', 'rows' => $carts['active_products'], 'headers' => ['المنتج', 'الكمية', 'القيمة'], 'columns' => ['product_name', 'quantity', 'value']])
                @endif
                @if ($showReport('abandoned-cart-products'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'منتجات السلات المتروكة', 'rows' => $carts['abandoned_products'], 'headers' => ['المنتج', 'الكمية', 'القيمة'], 'columns' => ['product_name', 'quantity', 'value']])
                @endif
            </section>
        @endif
        @if ($showReport('active-cart-variants', 'abandoned-cart-variants', 'cart-age'))
            <section class="grid gap-6 xl:grid-cols-3">
                @if ($showReport('active-cart-variants'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'نسخ السلات النشطة', 'rows' => $carts['active_variants'], 'headers' => ['المنتج', 'النسخة', 'الكمية'], 'columns' => ['product_name', 'variant_name', 'quantity']])
                @endif
                @if ($showReport('abandoned-cart-variants'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'نسخ السلات المتروكة', 'rows' => $carts['abandoned_variants'], 'headers' => ['المنتج', 'النسخة', 'الكمية'], 'columns' => ['product_name', 'variant_name', 'quantity']])
                @endif
                @if ($showReport('cart-age'))
                    @include('admin.analytics.partials.metric-table', ['title' => 'أعمار السلات', 'rows' => $carts['age_buckets'], 'headers' => ['العمر', 'السلات', 'القيمة'], 'columns' => ['label', 'count', 'value']])
                @endif
            </section>
        @endif
    @endif

    @if ($selectedSection === 'coupons')
        <section class="grid gap-6 xl:grid-cols-3">
            @if ($showReport('coupon-summary', 'welcome-coupons'))
                <article class="analytics-panel rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
                    <h3 class="text-lg font-bold text-white">{{ $showReport('coupon-summary') && $showReport('welcome-coupons') ? 'ملخص الكوبونات' : ($showReport('welcome-coupons') ? 'كوبونات الترحيب' : 'ملخص الكوبونات') }}</h3>
                    <div class="mt-5 grid gap-3">
                        @foreach ([
                            ...($showReport('coupon-summary') ? [['مرات الاستخدام', $coupons['summary']['redemptions']], ['إيرادات مع كوبونات', number_format($coupons['summary']['revenue_with_coupons'], 2)], ['إجمالي الخصومات', number_format($coupons['summary']['discount_total'], 2)]] : []),
                            ...($showReport('welcome-coupons') ? [['ترحيبية مرسلة', $coupons['welcome']['sent']], ['ترحيبية مستخدمة', $coupons['welcome']['used']], ['تحويل الترحيبية', number_format($coupons['welcome']['conversion_rate'], 2) . '%']] : []),
                        ] as $row)
                            <div class="flex items-center justify-between gap-3 border border-white/10 bg-slate-950/40 px-4 py-3">
                                <span class="text-sm text-slate-300">{{ $row[0] }}</span>
                                <strong class="text-white">{{ $row[1] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endif
            @if ($showReport('top-coupons'))
                <div class="xl:col-span-2">
                    @include('admin.analytics.partials.metric-table', ['title' => 'أعلى الكوبونات استخداماً', 'rows' => $coupons['by_coupon'], 'headers' => ['الكود', 'الاستخدام', 'الخصم', 'الإيراد'], 'columns' => ['coupon_code', 'usage_count', 'discount_total', 'revenue']])
                </div>
            @endif
        </section>
    @endif
</div>
