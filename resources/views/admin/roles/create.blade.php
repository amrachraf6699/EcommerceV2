@php
    $title = 'إضافة دور';
    $pageTitle = 'إنشاء دور جديد';
    $pageDescription = 'أضف اسم الدور وحدد الصلاحيات المناسبة له.';
    $breadcrumbs = ['الإدارة', 'الأدوار', 'إضافة'];
@endphp

@extends('layouts.admin')

@section('content')
    @include('admin.roles.partials.form', [
        'action' => route('admin.roles.store'),
        'method' => 'POST',
        'submitLabel' => 'إنشاء الدور',
    ])
@endsection
