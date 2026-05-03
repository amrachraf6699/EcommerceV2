@php
    $title = 'إضافة شريحة';
    $pageTitle = 'إنشاء شريحة جديدة';
    $pageDescription = 'أضف صورة ومحتوى الشريحة وحدد الألوان والمحاذاة وحالة الظهور.';
    $breadcrumbs = ['الإدارة', 'السلايدر', 'إضافة'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.sliders.store') }}" enctype="multipart/form-data">
            @include('admin.sliders.partials.form', ['submitLabel' => 'إنشاء الشريحة'])
        </form>
    </section>
@endsection
