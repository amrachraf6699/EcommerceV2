@php
    $title = 'تعديل عميل مميز';
    $pageTitle = 'تعديل العميل المميز';
    $pageDescription = 'حدّث بيانات العميل والصورة والمسمى الوظيفي.';
    $breadcrumbs = ['الإدارة', 'العملاء المميزون', $client->name];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.clients.update', $client) }}" enctype="multipart/form-data">
            @include('admin.clients.partials.form', ['submitLabel' => 'حفظ التعديلات', 'method' => 'PUT'])
        </form>
    </section>
@endsection
