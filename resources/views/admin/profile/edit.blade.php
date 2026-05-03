@php
    $title = 'حساب الإدارة';
    $pageTitle = 'الحساب الشخصي';
    $pageDescription = 'من هنا يمكن للمسؤول تعديل اسمه وبريده الإلكتروني وتحديث كلمة المرور بدون تعقيد.';
    $breadcrumbs = ['الإدارة', 'الحساب الشخصي'];
@endphp

@extends('layouts.admin')

@section('content')
    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
            <h2 class="text-xl font-bold text-white">البيانات الأساسية</h2>
            <p class="mt-2 text-sm leading-7 text-slate-300">هذه البيانات تظهر داخل لوحة الإدارة وتستخدم في التحقق من هوية الحساب.</p>

            <form method="POST" action="{{ route('admin.profile.update') }}" class="mt-6 grid gap-4">
                @csrf
                @method('PATCH')

                <label class="grid gap-2 text-sm text-slate-200">
                    <span>الاسم</span>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" class="rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-amber-300/40">
                </label>

                <label class="grid gap-2 text-sm text-slate-200">
                    <span>البريد الإلكتروني</span>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" class="rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-amber-300/40">
                </label>

                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-amber-400 px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-amber-300">
                        حفظ البيانات
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
            <h2 class="text-xl font-bold text-white">تحديث كلمة المرور</h2>
            <p class="mt-2 text-sm leading-7 text-slate-300">استخدم كلمة مرور قوية وسهلة التذكر للمسؤول المعتمد فقط.</p>

            <form method="POST" action="{{ route('admin.profile.password.update') }}" class="mt-6 grid gap-4">
                @csrf
                @method('PUT')

                <label class="grid gap-2 text-sm text-slate-200">
                    <span>كلمة المرور الحالية</span>
                    <input type="password" name="current_password" class="rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-amber-300/40">
                </label>

                <label class="grid gap-2 text-sm text-slate-200">
                    <span>كلمة المرور الجديدة</span>
                    <input type="password" name="password" class="rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-amber-300/40">
                </label>

                <label class="grid gap-2 text-sm text-slate-200">
                    <span>تأكيد كلمة المرور الجديدة</span>
                    <input type="password" name="password_confirmation" class="rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-amber-300/40">
                </label>

                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/10 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/15">
                        تحديث كلمة المرور
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
