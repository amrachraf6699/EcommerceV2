@php
    $title = 'كوبونات الترحيب';
    $pageTitle = 'تقارير كوبونات الترحيب';
    $pageDescription = 'متابعة الكوبونات المرسلة وحالة استخدامها وربطها بالطلبات المكتملة.';
    $breadcrumbs = ['الإدارة', 'كوبونات الترحيب'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="GET" class="grid gap-3 xl:grid-cols-4">
            <input class="admin-input" type="text" name="search" placeholder="ابحث بالبريد أو الكود" value="{{ request('search') }}">
            <select class="admin-select" name="status">
                <option value="">كل الحالات</option>
                <option value="unused" @selected(request('status') === 'unused')>غير مستخدم</option>
                <option value="used" @selected(request('status') === 'used')>مستخدم</option>
            </select>
            <input class="admin-input" type="date" name="sent_from" value="{{ request('sent_from') }}">
            <input class="admin-input" type="date" name="sent_to" value="{{ request('sent_to') }}">
            <button class="admin-btn-secondary xl:col-span-4" type="submit">تطبيق الفلاتر</button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="bg-slate-950/50">
                    <tr>
                        <th>البريد</th>
                        <th>الكود</th>
                        <th>الخصم</th>
                        <th>اللغة</th>
                        <th>الإرسال</th>
                        <th>الاستخدام</th>
                        <th>الطلب المرتبط</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-white/5">
                    @forelse ($coupons as $coupon)
                        <tr>
                            <td>
                                <div class="font-bold text-white">{{ $coupon->email }}</div>
                                <div class="text-xs text-slate-400">{{ $coupon->customer?->name ?: 'زائر' }}</div>
                            </td>
                            <td class="font-mono text-sm text-white">{{ $coupon->code }}</td>
                            <td>
                                @if ($coupon->discount_type === 'amount')
                                    {{ number_format((float) $coupon->discount_value, 2) }} دينار
                                @else
                                    {{ rtrim(rtrim(number_format((float) $coupon->discount_value, 2, '.', ''), '0'), '.') }}%
                                @endif
                            </td>
                            <td>{{ strtoupper($coupon->locale) }}</td>
                            <td>{{ optional($coupon->sent_at)->format('Y-m-d H:i') ?: 'غير متوفر' }}</td>
                            <td>
                                @if ($coupon->used_at)
                                    <span class="text-emerald-300">{{ $coupon->used_at->format('Y-m-d H:i') }}</span>
                                @else
                                    <span class="text-amber-200">غير مستخدم</span>
                                @endif
                            </td>
                            <td>
                                @if ($coupon->order)
                                    <a class="admin-btn-secondary" href="{{ route('admin.orders.show', $coupon->order) }}">{{ $coupon->order->order_number }}</a>
                                @else
                                    <span class="text-slate-400">لا يوجد</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin.empty-state title="لا توجد كوبونات" description="ستظهر هنا كوبونات الترحيب التي تم إصدارها للزوار والعملاء." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $coupons->links() }}</div>
    </section>
@endsection
