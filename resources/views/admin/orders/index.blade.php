@php
    $title = 'الطلبات';
    $pageTitle = 'إدارة الطلبات';
    $pageDescription = 'تصفية سريعة ومراجعة واضحة لحالة كل طلب.';
    $breadcrumbs = ['الإدارة', 'الطلبات'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="GET" class="grid gap-3 xl:grid-cols-4">
            <input class="admin-input" type="text" name="search" placeholder="رقم الطلب أو البريد أو الاسم" value="{{ request('search') }}">

            <select class="admin-select" name="status">
                <option value="">حالة الطلب</option>
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <select class="admin-select" name="payment_status">
                <option value="">حالة الدفع</option>
                @foreach ($paymentStatusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <select class="admin-select" name="fulfillment_status">
                <option value="">حالة التجهيز</option>
                @foreach ($fulfillmentStatusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(request('fulfillment_status') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <button class="admin-btn-secondary xl:col-span-4" type="submit">تطبيق الفلاتر</button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="bg-slate-950/50">
                    <tr>
                        <th>الطلب</th>
                        <th>العميل</th>
                        <th>الإجمالي</th>
                        <th>الحالات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-white/5">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="font-bold text-white">{{ $order->order_number }}</td>
                            <td>{{ $order->customer_first_name }} {{ $order->customer_last_name }}<br><span class="text-xs text-slate-400">{{ $order->customer_email }}</span></td>
                            <td>{{ number_format($order->grand_total, 2) }} {{ $order->currency }}</td>
                            <td>
                                <div class="grid gap-1 text-xs text-slate-300">
                                    <span>طلب: {{ $order->status_label }}</span>
                                    <span>دفع: {{ $order->payment_status_label }}</span>
                                    <span>تجهيز: {{ $order->fulfillment_status_label }}</span>
                                </div>
                            </td>
                            <td><a class="admin-btn-secondary" href="{{ route('admin.orders.show', $order) }}">عرض</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin.empty-state title="لا توجد طلبات" description="ستظهر الطلبات هنا عند بدء الشراء من المتجر." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $orders->links() }}</div>
    </section>
@endsection
