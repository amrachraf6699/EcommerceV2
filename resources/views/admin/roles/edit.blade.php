@php
    $title = 'تحرير الدور';
    $pageTitle = 'إدارة الدور';
    $pageDescription = 'حدث اسم الدور واختر الصلاحيات المناسبة له.';
    $breadcrumbs = ['الإدارة', 'الأدوار', $role->name];
@endphp

@extends('layouts.admin')

@section('content')
    @include('admin.roles.partials.form', [
        'action' => route('admin.roles.update', $role),
        'method' => 'PUT',
        'submitLabel' => 'حفظ الدور',
    ])
@endsection
