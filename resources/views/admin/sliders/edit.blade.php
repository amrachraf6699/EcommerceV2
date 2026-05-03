@php
    $title = 'تعديل شريحة';
    $pageTitle = 'تعديل بيانات الشريحة';
    $pageDescription = 'حدث الصورة والنصوص والألوان والرابط دون تعقيد.';
    $breadcrumbs = ['الإدارة', 'السلايدر', $slider->title ?: 'تعديل'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.sliders.update', $slider) }}" enctype="multipart/form-data">
            @include('admin.sliders.partials.form', ['submitLabel' => 'حفظ التغييرات', 'method' => 'PUT'])
        </form>
    </section>
@endsection
