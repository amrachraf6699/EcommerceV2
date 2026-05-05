@php
    $title = 'الكوبونات';
    $pageTitle = 'إدارة الكوبونات';
    $pageDescription = 'أنشئ أكواد خصم عادية مع حدود الاستخدام والتاريخ والدول المسموح بها.';
    $breadcrumbs = ['الإدارة', 'الكوبونات'];
    $couponService = app(\App\Services\CouponService::class);
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" class="grid gap-3 lg:grid-cols-3 lg:flex-1">
                <input class="admin-input" type="text" name="search" placeholder="ابحث بالكود" value="{{ request('search') }}">
                <select class="admin-select" name="status">
                    <option value="">كل الحالات</option>
                    <option value="active" @selected(request('status') === 'active')>نشط</option>
                    <option value="scheduled" @selected(request('status') === 'scheduled')>مجدول</option>
                    <option value="expired" @selected(request('status') === 'expired')>منتهي</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>غير مفعل</option>
                </select>
                <button class="admin-btn-secondary" type="submit">بحث</button>
            </form>

            @can('coupons.create')
                <a class="admin-btn-primary" href="{{ route('admin.coupons.create') }}">إضافة كوبون</a>
            @endcan
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="bg-slate-950/50">
                    <tr>
                        <th>الكود</th>
                        <th>الخصم</th>
                        <th>الحالة</th>
                        <th>الفترة</th>
                        <th>الاستخدام</th>
                        <th>الدول</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-white/5">
                    @forelse ($coupons as $coupon)
                        @php($statusKey = $couponService->statusKey($coupon))
                        <tr>
                            <td>
                                <div class="font-mono text-sm font-bold text-white">{{ $coupon->code }}</div>
                                <div class="mt-1 text-xs text-slate-400">
                                    @if ($coupon->min_order_subtotal !== null || $coupon->max_order_subtotal !== null)
                                        {{ $coupon->min_order_subtotal !== null ? number_format((float) $coupon->min_order_subtotal, 2) : '0.00' }}
                                        -
                                        {{ $coupon->max_order_subtotal !== null ? number_format((float) $coupon->max_order_subtotal, 2) : '∞' }}
                                    @else
                                        بلا حدود سعر
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if ($coupon->discount_type === 'amount')
                                    {{ number_format((float) $coupon->discount_value, 2) }} دينار
                                @else
                                    {{ rtrim(rtrim(number_format((float) $coupon->discount_value, 2, '.', ''), '0'), '.') }}%
                                @endif
                            </td>
                            <td>
                                <span class="text-xs font-bold {{ $statusKey === 'active' ? 'text-emerald-300' : ($statusKey === 'scheduled' ? 'text-sky-300' : ($statusKey === 'expired' ? 'text-amber-300' : 'text-slate-400')) }}">
                                    {{ $statusKey === 'active' ? 'نشط' : ($statusKey === 'scheduled' ? 'مجدول' : ($statusKey === 'expired' ? 'منتهي' : 'غير مفعل')) }}
                                </span>
                            </td>
                            <td class="text-sm text-slate-300">
                                {{ $coupon->starts_at?->format('Y-m-d H:i') ?: 'بدون بداية' }}<br>
                                {{ $coupon->ends_at?->format('Y-m-d H:i') ?: 'بدون نهاية' }}
                            </td>
                            <td class="text-sm text-slate-300">
                                {{ $coupon->redemptions_count }}
                                /
                                {{ $coupon->usage_limit ?: '∞' }}
                                <div class="mt-1 text-xs text-slate-400">لكل عميل: {{ $coupon->usage_limit_per_customer ?: '∞' }}</div>
                            </td>
                            <td class="text-sm text-slate-300">{{ $couponService->allowedCountriesSummary($coupon) }}</td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    @can('coupons.update')
                                        <a class="admin-btn-icon" href="{{ route('admin.coupons.edit', $coupon) }}" aria-label="تعديل الكوبون" title="تعديل الكوبون">
                                            <i class="bx bx-pencil" aria-hidden="true"></i>
                                        </a>
                                    @endcan
                                    @can('coupons.delete')
                                        <form method="POST"
                                              action="{{ route('admin.coupons.destroy', $coupon) }}"
                                              data-loading-form
                                              data-confirm-title="حذف الكوبون"
                                              data-confirm-text="سيتم حذف الكوبون نهائياً، بينما ستبقى بيانات الخصم المحفوظة داخل الطلبات السابقة.">
                                            @csrf
                                            @method('DELETE')
                                            <button class="admin-btn-icon admin-btn-icon--danger" type="submit" aria-label="حذف الكوبون" title="حذف الكوبون" data-loading-label="جاري الحذف...">
                                                <i class="bx bx-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin.empty-state title="لا توجد كوبونات" description="ابدأ بإضافة أول كود خصم عادي ليظهر للعملاء أثناء الدفع." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $coupons->links() }}</div>
    </section>
@endsection
