@php
    $kpis = $report['kpis'];
    $funnel = $report['funnel'];
    $salesTrend = $report['sales_trend'];
    $analyticsConfig = $report['analytics_config_status'];
@endphp

<div class="space-y-6 admin-dashboard" data-dashboard-root>
    <section class="rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h2 class="text-xl font-bold text-white">تقرير لوحة التحكم</h2>
                <p class="mt-2 text-sm text-slate-300">الفترة الحالية: {{ $report['range_label'] }}</p>
            </div>

            <div class="flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:items-center">
                <form class="dashboard-range-form flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center" data-dashboard-range-form action="{{ route('admin.dashboard') }}" method="GET">
                    <select
                        name="range"
                        class="admin-select dashboard-range-select min-w-[220px]"
                        data-dashboard-range-select
                    >
                        @foreach ($report['available_ranges'] as $rangeKey => $rangeLabel)
                            <option value="{{ $rangeKey }}" @selected($report['range'] === $rangeKey)>{{ $rangeLabel }}</option>
                        @endforeach
                    </select>

                    <a href="{{ route('admin.dashboard.export.pdf', ['range' => $report['range']]) }}" class="admin-btn-secondary dashboard-range-action whitespace-nowrap" data-dashboard-export-pdf>تصدير PDF</a>
                    <a href="{{ route('admin.dashboard.export.excel', ['range' => $report['range']]) }}" class="admin-btn-primary dashboard-range-action whitespace-nowrap" data-dashboard-export-excel>تصدير إكسل</a>
                </form>
            </div>
        </div>
    </section>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[2rem] border border-emerald-400/20 bg-emerald-500/10 p-5">
            <p class="text-sm font-semibold text-emerald-200">الإيرادات</p>
            <strong class="mt-3 block text-3xl font-extrabold text-white">{{ number_format($kpis['revenue'], 2) }}</strong>
        </article>
        <article class="rounded-[2rem] border border-white/10 bg-white/5 p-5">
            <p class="text-sm font-semibold text-slate-300">الطلبات المدفوعة</p>
            <strong class="mt-3 block text-3xl font-extrabold text-white">{{ number_format($kpis['paid_orders']) }}</strong>
        </article>
        <article class="rounded-[2rem] border border-white/10 bg-white/5 p-5">
            <p class="text-sm font-semibold text-slate-300">كل الطلبات</p>
            <strong class="mt-3 block text-3xl font-extrabold text-white">{{ number_format($kpis['total_orders']) }}</strong>
        </article>
        <article class="rounded-[2rem] border border-white/10 bg-white/5 p-5">
            <p class="text-sm font-semibold text-slate-300">متوسط قيمة الطلب</p>
            <strong class="mt-3 block text-3xl font-extrabold text-white">{{ number_format($kpis['average_order_value'], 2) }}</strong>
        </article>
        <article class="rounded-[2rem] border border-white/10 bg-white/5 p-5">
            <p class="text-sm font-semibold text-slate-300">عملاء جدد</p>
            <strong class="mt-3 block text-3xl font-extrabold text-white">{{ number_format($kpis['new_customers']) }}</strong>
        </article>
        <article class="rounded-[2rem] border border-white/10 bg-white/5 p-5">
            <p class="text-sm font-semibold text-slate-300">سلات نشطة</p>
            <strong class="mt-3 block text-3xl font-extrabold text-white">{{ number_format($kpis['active_carts']) }}</strong>
        </article>
        <article class="rounded-[2rem] border border-white/10 bg-white/5 p-5">
            <p class="text-sm font-semibold text-slate-300">معدل التحويل</p>
            <strong class="mt-3 block text-3xl font-extrabold text-white">{{ number_format($kpis['cart_to_order_conversion_rate'], 2) }}%</strong>
        </article>
        <article class="rounded-[2rem] border border-amber-300/20 bg-amber-400/10 p-5">
            <p class="text-sm font-semibold text-amber-100">نفاد قريب</p>
            <strong class="mt-3 block text-3xl font-extrabold text-white">{{ number_format($kpis['low_stock_variants']) }}</strong>
        </article>
    </div>

    <div class="grid gap-6 xl:grid-cols-[2fr_1fr]">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-white">اتجاه المبيعات والطلبات</h2>
                    <p class="mt-2 text-sm text-slate-300">الإيرادات والطلبات حسب الفترة المختارة.</p>
                </div>
            </div>

            <div
                class="mt-6"
                data-dashboard-chart
                data-chart-labels='@json($salesTrend['labels'])'
                data-chart-orders='@json($salesTrend['orders'])'
                data-chart-revenue='@json($salesTrend['revenue'])'
            >
                <div class="h-72 sm:h-80" data-dashboard-chart-canvas></div>
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
            <h2 class="text-xl font-bold text-white">إعدادات التحليلات</h2>
            <p class="mt-2 text-sm text-slate-300">المفعل حالياً: {{ $analyticsConfig['configured_count'] }} / {{ $analyticsConfig['total_count'] }}</p>

            <div class="mt-5 grid gap-3">
                @foreach ($analyticsConfig['items'] as $item)
                    <div class="dashboard-split-row flex flex-col gap-2 rounded-[1.25rem] border border-white/10 bg-slate-950/40 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <span class="text-sm text-slate-200">{{ $item['label'] }}</span>
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $item['configured'] ? 'bg-emerald-400/15 text-emerald-200' : 'bg-slate-400/15 text-slate-300' }}">
                            {{ $item['configured'] ? 'مفعل' : 'غير مضاف' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
            <h2 class="text-xl font-bold text-white">القمع البيعي</h2>
            <div class="mt-5 grid gap-3">
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/40 px-4 py-4">
                    <p class="text-sm text-slate-300">السلات المنشأة</p>
                    <strong class="mt-2 block text-2xl font-bold text-white">{{ number_format($funnel['carts_created']) }}</strong>
                </div>
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/40 px-4 py-4">
                    <p class="text-sm text-slate-300">الطلبات المنشأة</p>
                    <strong class="mt-2 block text-2xl font-bold text-white">{{ number_format($funnel['orders_created']) }}</strong>
                </div>
                <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/40 px-4 py-4">
                    <p class="text-sm text-slate-300">معدل التحويل</p>
                    <strong class="mt-2 block text-2xl font-bold text-white">{{ number_format($funnel['conversion_rate'], 2) }}%</strong>
                </div>
                <div class="rounded-[1.5rem] border border-red-400/20 bg-red-500/10 px-4 py-4">
                    <p class="text-sm text-red-100">سلات متروكة</p>
                    <strong class="mt-2 block text-2xl font-bold text-white">{{ number_format($funnel['abandoned_carts']) }}</strong>
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-bold text-white">أعلى المنتجات مبيعاً</h2>
                <span class="text-sm text-slate-400">حسب الكمية</span>
            </div>

            @if (empty($report['top_products_by_quantity']))
                <p class="mt-5 text-sm text-slate-400">لا توجد بيانات مبيعات لهذه الفترة.</p>
            @else
                <div class="mt-5 grid gap-3">
                    @foreach ($report['top_products_by_quantity'] as $product)
                        <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/40 px-4 py-4">
                            <strong class="text-white">{{ $product['product_name'] }}</strong>
                            <div class="dashboard-split-row mt-2 flex flex-col gap-1 text-sm text-slate-300 sm:flex-row sm:items-center sm:justify-between">
                                <span>الكمية: {{ number_format($product['quantity_sold']) }}</span>
                                <span>الإيراد: {{ number_format($product['revenue'], 2) }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-bold text-white">أعلى المنتجات إيراداً</h2>
                <span class="text-sm text-slate-400">حسب الإيراد</span>
            </div>

            @if (empty($report['top_products_by_revenue']))
                <p class="mt-5 text-sm text-slate-400">لا توجد بيانات مبيعات لهذه الفترة.</p>
            @else
                <div class="mt-5 grid gap-3">
                    @foreach ($report['top_products_by_revenue'] as $product)
                        <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/40 px-4 py-4">
                            <strong class="text-white">{{ $product['product_name'] }}</strong>
                            <div class="dashboard-split-row mt-2 flex flex-col gap-1 text-sm text-slate-300 sm:flex-row sm:items-center sm:justify-between">
                                <span>الإيراد: {{ number_format($product['revenue'], 2) }}</span>
                                <span>الكمية: {{ number_format($product['quantity_sold']) }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-bold text-white">حالات الطلبات</h2>
                <span class="text-sm text-slate-400">توزيع حسب الحالة</span>
            </div>

            @if (empty($report['order_status_breakdown']))
                <p class="mt-5 text-sm text-slate-400">لا توجد طلبات لهذه الفترة.</p>
            @else
                <div class="mt-5 grid gap-3">
                    @foreach ($report['order_status_breakdown'] as $status)
                        <div class="dashboard-split-row flex flex-col gap-2 rounded-[1.5rem] border border-white/10 bg-slate-950/40 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <span class="text-slate-200">{{ $status['status'] }}</span>
                            <strong class="text-white">{{ number_format($status['count']) }}</strong>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-bold text-white">تنبيهات المخزون</h2>
                <span class="text-sm text-slate-400">أقل من أو يساوي 5</span>
            </div>

            @if (empty($report['low_stock_variants']))
                <p class="mt-5 text-sm text-slate-400">لا توجد نسخ منخفضة المخزون حالياً.</p>
            @else
                <div class="mt-5 grid gap-3">
                    @foreach ($report['low_stock_variants'] as $variant)
                        <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/40 px-4 py-4">
                            <strong class="text-white">{{ $variant['product_name'] }}</strong>
                            <p class="mt-2 text-sm text-slate-300">{{ $variant['name'] }}</p>
                            <div class="dashboard-split-row mt-2 flex flex-col gap-1 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                                <span>المتبقي: {{ number_format($variant['stock_quantity']) }}</span>
                                <span>{{ number_format($variant['price'], 2) }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <section class="rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-xl font-bold text-white">إجراءات سريعة</h2>
                <p class="mt-2 text-sm leading-7 text-slate-300">اختصارات واضحة لأكثر المهام استخداماً داخل لوحة الإدارة.</p>
            </div>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @can('products.create')
                <a href="{{ route('admin.products.index') }}" class="dashboard-quick-action rounded-[1.5rem] border border-white/10 bg-slate-950/40 px-4 py-4 text-sm font-bold text-white transition hover:border-amber-300/30 hover:bg-slate-950/60">
                    إضافة منتج جديد
                </a>
            @endcan

            @can('categories.create')
                <a href="{{ route('admin.categories.index') }}" class="dashboard-quick-action rounded-[1.5rem] border border-white/10 bg-slate-950/40 px-4 py-4 text-sm font-bold text-white transition hover:border-amber-300/30 hover:bg-slate-950/60">
                    إدارة الأقسام
                </a>
            @endcan

            @can('settings.view')
                <a href="{{ route('admin.settings.index') }}" class="dashboard-quick-action rounded-[1.5rem] border border-white/10 bg-slate-950/40 px-4 py-4 text-sm font-bold text-white transition hover:border-amber-300/30 hover:bg-slate-950/60">
                    الإعدادات العامة
                </a>
            @endcan

            @can('admins.view')
                <a href="{{ route('admin.admins.index') }}" class="dashboard-quick-action rounded-[1.5rem] border border-white/10 bg-slate-950/40 px-4 py-4 text-sm font-bold text-white transition hover:border-amber-300/30 hover:bg-slate-950/60">
                    المسؤولون والصلاحيات
                </a>
            @endcan
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-bold text-white">أحدث الطلبات</h2>
                <span class="text-sm text-slate-400">آخر 5 طلبات داخل الفترة</span>
            </div>

            @if ($report['recent_orders']->isEmpty())
                <x-admin.empty-state
                    class="mt-5"
                    title="لا توجد طلبات لهذه الفترة"
                    description="عند توفر طلبات ضمن الفترة المختارة ستظهر هنا لمراجعتها بسرعة."
                />
            @else
                <div class="mt-5 grid gap-3">
                    @foreach ($report['recent_orders'] as $order)
                        <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/40 px-4 py-4">
                            <div class="dashboard-split-row flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <strong class="text-white">{{ $order->order_number }}</strong>
                                <span class="text-xs text-slate-400">{{ $order->status }}</span>
                            </div>
                            <p class="mt-2 text-sm text-slate-300">{{ $order->customer_first_name }} {{ $order->customer_last_name }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ number_format($order->grand_total, 2) }} {{ $order->currency }}</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-bold text-white">أحدث السلات</h2>
                <span class="text-sm text-slate-400">آخر 5 سلات داخل الفترة</span>
            </div>

            @if ($report['recent_carts']->isEmpty())
                <x-admin.empty-state
                    class="mt-5"
                    title="لا توجد سلات لهذه الفترة"
                    description="عندما يتحرك الزوار داخل المتجر في الفترة المختارة ستظهر آخر السلات هنا."
                />
            @else
                <div class="mt-5 grid gap-3">
                    @foreach ($report['recent_carts'] as $cart)
                        <article class="rounded-[1.5rem] border border-white/10 bg-slate-950/40 px-4 py-4">
                            <div class="dashboard-split-row flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <strong class="text-white">{{ $cart->session_id }}</strong>
                                <span class="text-xs text-slate-400">{{ $cart->item_count }} عناصر</span>
                            </div>
                            <p class="mt-2 text-sm text-slate-300">الإجمالي: {{ number_format($cart->subtotal, 2) }} {{ $cart->currency }}</p>
                            <p class="mt-1 text-xs text-slate-400">
                                آخر نشاط:
                                {{ optional($cart->last_activity_at)->format('Y-m-d H:i') ?? 'غير متوفر' }}
                            </p>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    @cannot('products.create')
        <x-admin.empty-state
            title="تم تقييد الاختصارات حسب الصلاحيات"
            description="ستظهر لك فقط الإجراءات التي يسمح بها دورك أو صلاحياتك الحالية داخل النظام."
        >
            <a href="{{ route('admin.profile.edit') }}"
               class="inline-flex items-center justify-center rounded-2xl bg-amber-400 px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-amber-300">
                مراجعة الحساب الشخصي
            </a>
        </x-admin.empty-state>
    @endcannot
</div>
