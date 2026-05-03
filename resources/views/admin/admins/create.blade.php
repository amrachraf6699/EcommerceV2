@php
    $title = 'إضافة مسؤول';
    $pageTitle = 'إضافة حساب إداري';
    $pageDescription = 'أنشئ حساباً جديداً وحدد دوره من البداية حتى تكون صلاحياته واضحة.';
    $breadcrumbs = ['الإدارة', 'المسؤولون', 'إضافة'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.admins.store') }}">
            @include('admin.admins.partials.form', ['submitLabel' => 'إنشاء الحساب'])
        </form>
    </section>
@endsection
