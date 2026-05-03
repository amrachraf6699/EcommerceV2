@php
    $title = 'إضافة قسم';
    $pageTitle = 'إنشاء قسم جديد';
    $pageDescription = 'أدخل البيانات الأساسية فقط، ويمكن تطوير القسم لاحقاً بسهولة.';
    $breadcrumbs = ['الإدارة', 'الأقسام', 'إضافة'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
            @include('admin.categories.partials.form', ['submitLabel' => 'إنشاء القسم'])
        </form>
    </section>
@endsection
