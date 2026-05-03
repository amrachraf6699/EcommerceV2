@php
    $title = 'إضافة عميل مميز';
    $pageTitle = 'إنشاء عميل مميز جديد';
    $pageDescription = 'أضف اسم العميل وصورته والمسمى الوظيفي لعرضه في الواجهة الرئيسية.';
    $breadcrumbs = ['الإدارة', 'العملاء المميزون', 'إضافة'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.clients.store') }}" enctype="multipart/form-data">
            @include('admin.clients.partials.form', ['submitLabel' => 'إنشاء العميل المميز'])
        </form>
    </section>
@endsection
