@php
    $title = 'تفاصيل الطلب';
    $pageTitle = 'تفاصيل الطلب '.$order->order_number;
    $pageDescription = 'مراجعة بيانات العميل والعناصر والحالات في شاشة واحدة.';
    $breadcrumbs = ['الإدارة', 'الطلبات', $order->order_number];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="grid gap-6 xl:grid-cols-[2fr_1fr]">
        <article class="admin-card">
            <h2 class="text-xl font-bold text-white">عناصر الطلب</h2>
            <div class="mt-4 grid gap-3">
                @foreach ($order->items as $item)
                    <div class="admin-subcard flex items-start justify-between gap-4">
                        <div>
                            <p class="font-bold text-white">{{ $item->product_name }}</p>
                            <p class="mt-1 text-sm text-slate-300">{{ $item->variant_name ?: 'نسخة افتراضية' }}</p>
                            <p class="mt-1 text-xs text-slate-400">SKU: {{ $item->sku ?: 'غير متوفر' }}</p>
                            @if (($item->product_id && ! $item->product) || ($item->product_variant_id && ! $item->variant))
                                <p class="mt-2 text-xs text-amber-300">السجل المرتبط محذوف من الكتالوج وتم الاحتفاظ ببيانات الطلب كما هي.</p>
                            @endif
                        </div>
                        <div class="text-left">
                            <p class="font-bold text-white">{{ number_format($item->line_total, 2) }} {{ $order->currency }}</p>
                            <p class="mt-1 text-xs text-slate-400">الكمية: {{ $item->quantity }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <aside class="space-y-6">
            <section class="admin-card">
                <h2 class="text-xl font-bold text-white">تحديث الحالة</h2>
                <form class="mt-4 space-y-3" method="POST" action="{{ route('admin.orders.update', $order) }}">
                    @csrf
                    @method('PUT')
                    <input class="admin-input" type="text" name="status" value="{{ old('status', $order->status) }}" required>
                    <input class="admin-input" type="text" name="payment_status" value="{{ old('payment_status', $order->payment_status) }}" required>
                    <input class="admin-input" type="text" name="fulfillment_status" value="{{ old('fulfillment_status', $order->fulfillment_status) }}" required>
                    <button class="admin-btn-primary w-full" type="submit">حفظ الحالة</button>
                </form>
            </section>

            <section class="admin-card">
                <h2 class="text-xl font-bold text-white">بيانات العميل</h2>
                <div class="mt-4 space-y-2 text-sm text-slate-300">
                    <p><strong class="text-white">الاسم:</strong> {{ $order->customer_first_name }} {{ $order->customer_last_name }}</p>
                    <p><strong class="text-white">البريد:</strong> {{ $order->customer_email }}</p>
                    <p><strong class="text-white">الهاتف:</strong> {{ $order->customer_phone ?: 'غير متوفر' }}</p>
                    <p><strong class="text-white">الكوبون:</strong> {{ $order->coupon_code ?: 'غير مطبق' }}</p>
                    <p><strong class="text-white">العنوان:</strong> {{ $order->shipping_address_line_1 ?: $order->billing_address_line_1 ?: 'غير متوفر' }}</p>
                    <p><strong class="text-white">الملاحظة:</strong> {{ $order->customer_note ?: 'لا توجد ملاحظات' }}</p>
                    <p><strong class="text-white">الإجمالي:</strong> {{ number_format($order->grand_total, 2) }} {{ $order->currency }}</p>
                </div>
            </section>
        </aside>
    </section>
@endsection
