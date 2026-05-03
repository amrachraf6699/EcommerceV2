@php
    $title = 'السلات';
    $pageTitle = 'السلات الحالية';
    $pageDescription = 'متابعة السلات النشطة أو المنتهية لدعم فريق المتجر.';
    $breadcrumbs = ['الإدارة', 'السلات'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="GET" class="grid gap-3 lg:grid-cols-3">
            <input class="admin-input" type="text" name="search" placeholder="ابحث برقم الجلسة" value="{{ request('search') }}">
            <select class="admin-select" name="status">
                <option value="">كل الحالات</option>
                <option value="active" @selected(request('status') === 'active')>نشطة</option>
                <option value="expired" @selected(request('status') === 'expired')>منتهية</option>
            </select>
            <button class="admin-btn-secondary" type="submit">تطبيق</button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="bg-slate-950/50">
                    <tr>
                        <th>الجلسة</th>
                        <th>العناصر</th>
                        <th>الإجمالي</th>
                        <th>آخر نشاط</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-white/5">
                    @forelse ($carts as $cart)
                        <tr>
                            <td class="font-bold text-white">{{ $cart->session_id }}</td>
                            <td>{{ $cart->item_count }}</td>
                            <td>{{ number_format($cart->subtotal, 2) }} {{ $cart->currency }}</td>
                            <td>{{ optional($cart->last_activity_at)->format('Y-m-d H:i') ?: 'غير متوفر' }}</td>
                            <td><a class="admin-btn-secondary" href="{{ route('admin.carts.show', $cart) }}">عرض</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin.empty-state title="لا توجد سلات" description="ستظهر هنا السلات التي يحتفظ بها الزوار داخل الجلسات." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $carts->links() }}</div>
    </section>
@endsection
