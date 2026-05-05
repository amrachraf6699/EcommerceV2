@php
    $title = 'تعديل كوبون';
    $pageTitle = 'تعديل الكوبون ' . $coupon->code;
    $pageDescription = 'حدّث كود الخصم وشروطه دون التأثير على الطلبات السابقة.';
    $breadcrumbs = ['الإدارة', 'الكوبونات', $coupon->code];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}">
            @include('admin.coupons.partials.form', ['submitLabel' => 'حفظ التعديلات', 'method' => 'PUT'])
        </form>
    </section>
@endsection
