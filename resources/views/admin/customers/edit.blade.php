@php
    $title = 'تعديل عميل';
    $pageTitle = 'تعديل بيانات العميل';
    $pageDescription = 'تحديث بيانات الحساب وحالة العميل دون التأثير على الطلبات السابقة.';
    $breadcrumbs = ['الإدارة', 'العملاء', $customer->name];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
            @include('admin.customers.partials.form', ['submitLabel' => 'حفظ التغييرات', 'method' => 'PUT'])
        </form>
    </section>

    <section class="admin-card">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <h2 class="text-xl font-bold text-white">ملخص الحساب</h2>
                <p class="mt-3 text-sm text-slate-300">عدد العناوين: {{ $customer->addresses_count }}</p>
                <p class="mt-1 text-sm text-slate-300">عدد الطلبات: {{ $customer->orders_count }}</p>
            </div>

            @can('customers.delete')
                <div class="md:text-left">
                    <h2 class="text-xl font-bold text-white">حذف العميل</h2>
                    <p class="mt-2 text-sm leading-7 text-slate-300">الحذف يزيل حساب العميل وعناوينه، بينما تبقى بيانات الطلبات القديمة محفوظة داخل الطلب نفسه.</p>
                    <form class="mt-4"
                          method="POST"
                          action="{{ route('admin.customers.destroy', $customer) }}"
                          data-loading-form
                          data-confirm-title="حذف العميل"
                          data-confirm-text="سيتم حذف حساب العميل وعناوينه المرتبطة نهائيا.">
                        @csrf
                        @method('DELETE')
                        <button class="admin-btn-danger" type="submit" data-loading-label="جاري الحذف...">حذف العميل</button>
                    </form>
                </div>
            @endcan
        </div>
    </section>
@endsection
