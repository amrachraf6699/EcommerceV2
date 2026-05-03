@php
    $title = 'تعديل قسم';
    $pageTitle = 'تعديل بيانات القسم';
    $pageDescription = 'تحديث الاسم والرابط والحالة دون تعقيد.';
    $breadcrumbs = ['الإدارة', 'الأقسام', $category->name];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
            @include('admin.categories.partials.form', ['submitLabel' => 'حفظ التعديلات', 'method' => 'PUT'])
        </form>
    </section>
@endsection
