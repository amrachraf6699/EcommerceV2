@php
    $title = 'إضافة صفحة';
    $pageTitle = 'إنشاء صفحة جديدة';
    $pageDescription = 'أضف صفحة محتوى مستقلة مع رابط مخصص ومحرر نصوص كامل.';
    $breadcrumbs = ['الإدارة', 'الصفحات', 'إضافة'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.pages.store') }}">
            @include('admin.pages.partials.form', ['submitLabel' => 'إنشاء الصفحة'])
        </form>
    </section>
@endsection
