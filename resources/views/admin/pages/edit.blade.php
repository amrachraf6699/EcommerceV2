@php
    $title = 'تعديل صفحة';
    $pageTitle = 'تعديل الصفحة';
    $pageDescription = 'حدّث العنوان والرابط والمحتوى دون فقدان التنسيق.';
    $breadcrumbs = ['الإدارة', 'الصفحات', $page->title];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.pages.update', $page) }}">
            @include('admin.pages.partials.form', ['submitLabel' => 'حفظ التعديلات', 'method' => 'PUT'])
        </form>
    </section>
@endsection
