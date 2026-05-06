@php
    $title = 'تفاصيل الطلب';
    $pageTitle = 'تفاصيل الطلب ' . $order->order_number;
    $pageDescription = 'مراجعة جميع بيانات الطلب والعميل والدفع والشحن من شاشة واحدة.';
    $breadcrumbs = ['الإدارة', 'الطلبات', $order->order_number];

    $money = static fn ($amount) => number_format((float) $amount, 2) . ' ' . $order->currency;
    $valueOrFallback = static fn ($value) => filled($value) ? $value : 'غير متوفر';
    $boolLabel = static fn (?bool $value) => $value === null ? 'غير متوفر' : ($value ? 'نعم' : 'لا');
    $dateLabel = static fn ($value) => $value ? $value->format('Y-m-d H:i') : 'غير متوفر';
    $shippingBoxLabel = $order->shipping_with_box === null
        ? 'غير متوفر'
        : __($order->shipping_with_box ? 'storefront.checkout_shipping_with_box' : 'storefront.checkout_shipping_without_box');

    $customerName = trim($order->customer_first_name . ' ' . $order->customer_last_name) ?: 'غير متوفر';

    $summaryCards = [
        ['label' => 'حالة الطلب', 'value' => $order->status_label],
        ['label' => 'حالة الدفع', 'value' => $order->payment_status_label],
        ['label' => 'حالة التجهيز', 'value' => $order->fulfillment_status_label],
        ['label' => 'الإجمالي', 'value' => $money($order->grand_total)],
    ];

    $customerRows = [
        'الاسم الكامل' => $customerName,
        'البريد الإلكتروني' => $valueOrFallback($order->customer_email),
        'رقم الهاتف' => $valueOrFallback($order->customer_phone),
        'معرّف العميل' => $order->customer_id ? '#' . $order->customer_id : 'غير متوفر',
    ];

    $billingRows = [
        'الدولة' => $valueOrFallback($order->billing_country),
        'المنطقة / الولاية' => $valueOrFallback($order->billing_state),
        'المدينة' => $valueOrFallback($order->billing_city),
        'العنوان 1' => $valueOrFallback($order->billing_address_line_1),
        'العنوان 2' => $valueOrFallback($order->billing_address_line_2),
        'الرمز البريدي' => $valueOrFallback($order->billing_postal_code),
    ];

    $shippingRows = [
        'مطابق للفوترة' => $boolLabel($order->shipping_same_as_billing),
        'الدولة' => $valueOrFallback($order->shipping_country),
        'المنطقة / الولاية' => $valueOrFallback($order->shipping_state),
        'المدينة' => $valueOrFallback($order->shipping_city),
        'العنوان 1' => $valueOrFallback($order->shipping_address_line_1),
        'العنوان 2' => $valueOrFallback($order->shipping_address_line_2),
        'الرمز البريدي' => $valueOrFallback($order->shipping_postal_code),
        'خيار الشحن' => $shippingBoxLabel,
        'منطقة الشحن' => $valueOrFallback($order->shipping_zone),
    ];

    $paymentRows = [
        'حالة الدفع' => $order->payment_status_label,
        'مزود الدفع' => $valueOrFallback($order->payment_provider),
        'مرجع الدفع' => $valueOrFallback($order->payment_reference),
        'معرّف العملية' => $valueOrFallback($order->payment_transaction_id),
        'رابط إعادة التوجيه' => $valueOrFallback($order->payment_redirect_url),
    ];

    $shippingAuditRows = [
        'كود الكوبون' => $valueOrFallback($order->coupon_code),
        'نوع الكوبون' => $valueOrFallback($order->coupon_type),
        'قيمة الكوبون' => $order->coupon_value !== null ? $money($order->coupon_value) : 'غير متوفر',
        'معرّف الكوبون' => $order->coupon_id ? '#' . $order->coupon_id : 'غير متوفر',
        'إجمالي الخصم' => $money($order->discount_total),
        'مصدر تسعير الشحن' => $valueOrFallback($order->shipping_rate_source),
        'سعر وحدة الشحن' => $order->shipping_unit_cost !== null ? $money($order->shipping_unit_cost) : 'غير متوفر',
        'معامل الكمية للشحن' => $order->shipping_quantity_multiplier !== null ? number_format((float) $order->shipping_quantity_multiplier, 2) : 'غير متوفر',
    ];

    $metadataRows = [
        'رقم الطلب' => $order->order_number,
        'رقم الجلسة' => $valueOrFallback($order->session_id),
        'تاريخ التثبيت' => $dateLabel($order->placed_at),
        'تاريخ الإنشاء' => $dateLabel($order->created_at),
        'آخر تحديث' => $dateLabel($order->updated_at),
        'العملة' => $valueOrFallback($order->currency),
    ];

    $totalsRows = [
        'المجموع الفرعي' => $money($order->subtotal),
        'الخصم' => $money($order->discount_total),
        'الشحن' => $money($order->shipping_total),
        'الضريبة' => $money($order->tax_total),
        'الإجمالي' => $money($order->grand_total),
    ];
@endphp

@extends('layouts.admin')

@section('content')
    <style>
        .order-show-summary-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }

        .order-show-columns {
            display: grid;
            gap: 24px;
        }

        .order-show-card-grid {
            display: grid;
            gap: 16px;
        }

        .order-show-card-grid--two {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        .order-show-detail-list {
            display: grid;
            gap: 10px;
        }

        .order-show-detail-list--two {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .order-show-detail-row {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .order-show-detail-label {
            color: rgb(148 163 184);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.14em;
        }

        .order-show-detail-value {
            color: rgb(226 232 240);
            font-size: 14px;
            line-height: 1.7;
            word-break: break-word;
        }

        .order-show-detail-value--strong {
            font-weight: 700;
        }

        .order-show-stack {
            display: grid;
            gap: 24px;
        }

        @media (min-width: 1280px) {
            .order-show-columns {
                grid-template-columns: minmax(0, 1.45fr) minmax(320px, 0.85fr);
            }
        }
    </style>

    <section class="order-show-stack">
        <article class="admin-card space-y-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-100">نظرة سريعة</h2>
                    <p class="mt-1 text-sm text-slate-400">الملخص الأساسي للحالة والمبالغ المهمة في الطلب.</p>
                </div>
                <div class="text-sm text-slate-400">
                    <span class="font-bold text-slate-200">رقم الطلب:</span> {{ $order->order_number }}
                </div>
            </div>

            <div class="order-show-summary-grid">
                @foreach ($summaryCards as $card)
                    <div class="admin-subcard">
                        <p class="order-show-detail-label">{{ $card['label'] }}</p>
                        <p class="mt-2 order-show-detail-value order-show-detail-value--strong">{{ $card['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </article>

        <section class="order-show-columns">
            <div class="order-show-stack min-w-0">
                <article class="admin-card space-y-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-100">بيانات العميل</h2>
                        <p class="mt-1 text-sm text-slate-400">بيانات العميل الأساسية وتفاصيل الفوترة والشحن.</p>
                    </div>

                    <div class="order-show-card-grid">
                        <div class="admin-subcard">
                            <div class="order-show-detail-list order-show-detail-list--two">
                                @foreach ($customerRows as $label => $value)
                                    <div class="order-show-detail-row">
                                        <p class="order-show-detail-label">{{ $label }}</p>
                                        <p class="order-show-detail-value {{ $label === 'الاسم الكامل' ? 'order-show-detail-value--strong' : '' }}">{{ $value }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="order-show-card-grid order-show-card-grid--two">
                            <div class="admin-subcard space-y-4">
                                <h3 class="text-sm font-bold text-slate-200">عنوان الفوترة</h3>
                                <div class="order-show-detail-list">
                                    @foreach ($billingRows as $label => $value)
                                        <div class="order-show-detail-row">
                                            <p class="order-show-detail-label">{{ $label }}</p>
                                            <p class="order-show-detail-value">{{ $value }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="admin-subcard space-y-4">
                                <h3 class="text-sm font-bold text-slate-200">عنوان الشحن</h3>
                                <div class="order-show-detail-list">
                                    @foreach ($shippingRows as $label => $value)
                                        <div class="order-show-detail-row">
                                            <p class="order-show-detail-label">{{ $label }}</p>
                                            <p class="order-show-detail-value">{{ $value }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="admin-card">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-slate-100">عناصر الطلب</h2>
                            <p class="mt-1 text-sm text-slate-400">العناصر المحفوظة داخل الطلب كما تم تثبيتها وقت الشراء.</p>
                        </div>
                        <div class="text-sm text-slate-400">
                            <span class="font-bold text-slate-200">عدد العناصر:</span> {{ $order->items->count() }}
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3">
                        @foreach ($order->items as $item)
                            <div class="admin-subcard flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-100">{{ $item->product_name }}</p>
                                    <p class="mt-1 text-sm text-slate-300">{{ $item->display_variant_name ?: 'نسخة افتراضية' }}</p>
                                    @if (($item->product_id && ! $item->product) || ($item->product_variant_id && ! $item->variant))
                                        <p class="mt-2 text-xs text-amber-300">السجل المرتبط محذوف من الكتالوج وتم الاحتفاظ ببيانات الطلب كما هي.</p>
                                    @endif
                                </div>
                                <div class="text-left">
                                    <p class="font-bold text-slate-100">{{ $money($item->line_total) }}</p>
                                    <p class="mt-1 text-xs text-slate-400">الكمية: {{ $item->quantity }}</p>
                                    <p class="mt-1 text-xs text-slate-400">سعر الوحدة: {{ $money($item->unit_price) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="admin-card space-y-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-100">الدفع والكوبون والشحن</h2>
                        <p class="mt-1 text-sm text-slate-400">تفاصيل التدقيق الخاصة بالدفع والخصم وطريقة احتساب الشحن.</p>
                    </div>

                    <div class="order-show-card-grid order-show-card-grid--two">
                        <div class="admin-subcard space-y-4">
                            <h3 class="text-sm font-bold text-slate-200">بيانات الدفع</h3>
                            <div class="order-show-detail-list">
                                @foreach ($paymentRows as $label => $value)
                                    <div class="order-show-detail-row">
                                        <p class="order-show-detail-label">{{ $label }}</p>
                                        @if ($label === 'رابط إعادة التوجيه' && $order->payment_redirect_url)
                                            <a href="{{ $order->payment_redirect_url }}" target="_blank" rel="noopener noreferrer" class="order-show-detail-value text-amber-300 hover:text-amber-200">
                                                {{ $order->payment_redirect_url }}
                                            </a>
                                        @else
                                            <p class="order-show-detail-value {{ $label === 'حالة الدفع' ? 'order-show-detail-value--strong' : '' }}">{{ $value }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="admin-subcard space-y-4">
                            <h3 class="text-sm font-bold text-slate-200">الكوبون وبيانات الشحن</h3>
                            <div class="order-show-detail-list">
                                @foreach ($shippingAuditRows as $label => $value)
                                    <div class="order-show-detail-row">
                                        <p class="order-show-detail-label">{{ $label }}</p>
                                        <p class="order-show-detail-value">{{ $value }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <aside class="order-show-stack">
                <section class="admin-card">
                    <h2 class="text-xl font-bold text-slate-100">تحديث الحالة</h2>
                    <form class="mt-4 space-y-4" method="POST" action="{{ route('admin.orders.update', $order) }}">
                        @csrf
                        @method('PUT')

                        <label class="block space-y-2">
                            <span class="text-sm font-bold text-slate-200">حالة الطلب</span>
                            <select class="admin-select" name="status" required>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $order->status?->value) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block space-y-2">
                            <span class="text-sm font-bold text-slate-200">حالة الدفع</span>
                            <select class="admin-select" name="payment_status" required>
                                @foreach ($paymentStatusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('payment_status', $order->payment_status?->value) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block space-y-2">
                            <span class="text-sm font-bold text-slate-200">حالة التجهيز</span>
                            <select class="admin-select" name="fulfillment_status" required>
                                @foreach ($fulfillmentStatusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('fulfillment_status', $order->fulfillment_status?->value) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <button class="admin-btn-primary w-full" type="submit">حفظ الحالة</button>
                    </form>
                </section>

                <section class="admin-card space-y-4">
                    <h2 class="text-xl font-bold text-slate-100">ملخص المبالغ</h2>
                    <div class="order-show-detail-list">
                        @foreach ($totalsRows as $label => $value)
                            <div class="admin-subcard flex items-center justify-between gap-4 {{ $label === 'الإجمالي' ? 'border border-emerald-400/20 bg-emerald-500/10' : '' }}">
                                <span class="{{ $label === 'الإجمالي' ? 'text-slate-100' : 'text-slate-300' }}">{{ $label }}</span>
                                <strong class="text-slate-100">{{ $value }}</strong>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="admin-card space-y-4">
                    <h2 class="text-xl font-bold text-slate-100">بيانات الطلب</h2>
                    <div class="order-show-detail-list">
                        @foreach ($metadataRows as $label => $value)
                            <div class="admin-subcard order-show-detail-row">
                                <p class="order-show-detail-label">{{ $label }}</p>
                                <p class="order-show-detail-value {{ $label === 'رقم الطلب' ? 'order-show-detail-value--strong' : '' }}">{{ $value }}</p>
                            </div>
                        @endforeach
                        <div class="admin-subcard order-show-detail-row">
                            <p class="order-show-detail-label">ملاحظة العميل</p>
                            <p class="order-show-detail-value">{{ $valueOrFallback($order->customer_note) }}</p>
                        </div>
                    </div>
                </section>
            </aside>
        </section>
    </section>
@endsection
