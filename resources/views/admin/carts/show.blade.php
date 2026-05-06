@php
    $title = 'تفاصيل السلة';
    $pageTitle = 'تفاصيل السلة';
    $pageDescription = 'عرض العناصر الموجودة داخل سلة الجلسة بدون تعديل مباشر.';
    $breadcrumbs = ['الإدارة', 'السلات', $cart->session_id];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="grid gap-6 xl:grid-cols-[2fr_1fr]">
        <article class="admin-card">
            <h2 class="text-xl font-bold text-white">العناصر</h2>
            <div class="mt-4 grid gap-3">
                @forelse ($cart->items as $item)
                    <div class="admin-subcard flex items-start justify-between gap-4">
                        <div>
                            <p class="font-bold text-white">{{ $item->product_name }}</p>
                            <p class="mt-1 text-sm text-slate-300">{{ $item->display_variant_name ?: 'نسخة افتراضية' }}</p>
                        </div>
                        <div class="text-left">
                            <p class="font-bold text-white">{{ number_format($item->line_total, 2) }} {{ $cart->currency }}</p>
                            <p class="mt-1 text-xs text-slate-400">الكمية: {{ $item->quantity }}</p>
                        </div>
                    </div>
                @empty
                    <x-admin.empty-state title="السلة فارغة" description="لا توجد عناصر محفوظة داخل هذه السلة حالياً." />
                @endforelse
            </div>
        </article>

        <aside class="admin-card">
            <h2 class="text-xl font-bold text-white">ملخص السلة</h2>
            <div class="mt-4 space-y-2 text-sm text-slate-300">
                <p><strong class="text-white">رقم الجلسة:</strong> {{ $cart->session_id }}</p>
                <p><strong class="text-white">عدد العناصر:</strong> {{ $cart->item_count }}</p>
                <p><strong class="text-white">الإجمالي:</strong> {{ number_format($cart->subtotal, 2) }} {{ $cart->currency }}</p>
                <p><strong class="text-white">آخر نشاط:</strong> {{ optional($cart->last_activity_at)->format('Y-m-d H:i') ?: 'غير متوفر' }}</p>
                <p><strong class="text-white">تاريخ الانتهاء:</strong> {{ optional($cart->expires_at)->format('Y-m-d H:i') ?: 'غير محدد' }}</p>
            </div>
        </aside>
    </section>
@endsection
