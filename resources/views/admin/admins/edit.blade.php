@php
    $title = 'تعديل مسؤول';
    $pageTitle = 'تعديل الحساب الإداري';
    $pageDescription = 'تحديث البيانات والدور وحالة الحساب من نفس الشاشة.';
    $breadcrumbs = ['الإدارة', 'المسؤولون', $admin->name];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.admins.update', $admin) }}">
            @include('admin.admins.partials.form', ['submitLabel' => 'حفظ التغييرات', 'method' => 'PUT'])
        </form>
    </section>

    @if ($admin->id !== auth()->id())
        <section class="admin-card">
            <h2 class="text-xl font-bold text-white">تعطيل الحساب</h2>
            <p class="mt-2 text-sm leading-7 text-slate-300">التعطيل يمنع هذا الحساب من الدخول للوحة الإدارة دون حذف سجله.</p>
            <form class="mt-4"
                  method="POST"
                  action="{{ route('admin.admins.destroy', $admin) }}"
                  data-loading-form
                  data-confirm-title="تعطيل الحساب"
                  data-confirm-text="سيتم منع هذا المسؤول من تسجيل الدخول حتى إعادة تفعيله.">
                @csrf
                @method('DELETE')
                <button class="admin-btn-danger" type="submit" data-loading-label="جاري التعطيل...">تعطيل الحساب</button>
            </form>
        </section>
    @endif
@endsection
