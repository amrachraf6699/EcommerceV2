@php
    $title = 'إضافة عميل';
    $pageTitle = 'إنشاء حساب عميل';
    $pageDescription = 'أضف بيانات العميل الأساسية ليصبح جاهزا لتسجيل الدخول من الواجهة الأمامية.';
    $breadcrumbs = ['الإدارة', 'العملاء', 'إضافة'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.customers.store') }}">
            @include('admin.customers.partials.form', ['submitLabel' => 'إنشاء العميل'])
        </form>
    </section>
@endsection
