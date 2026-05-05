@php
    $title = 'إضافة كوبون';
    $pageTitle = 'إنشاء كوبون جديد';
    $pageDescription = 'أضف كود خصم عادي مع التواريخ والحدود والدول المسموح بها.';
    $breadcrumbs = ['الإدارة', 'الكوبونات', 'إضافة'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.coupons.store') }}">
            @include('admin.coupons.partials.form', ['submitLabel' => 'إنشاء الكوبون'])
        </form>
    </section>
@endsection
