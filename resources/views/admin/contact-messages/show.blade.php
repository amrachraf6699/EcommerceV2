@php
    $title = 'تفاصيل رسالة التواصل';
    $pageTitle = 'رسالة من ' . $contactMessage->name;
    $pageDescription = 'مراجعة بيانات المرسل والرد عليه مباشرة من لوحة التحكم.';
    $breadcrumbs = ['الإدارة', 'رسائل التواصل', $contactMessage->subject];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
        <article class="admin-card">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold text-slate-400">الموضوع</p>
                    <h2 class="mt-2 text-2xl font-bold text-white">{{ $contactMessage->subject }}</h2>
                </div>
                <form method="POST" action="{{ route('admin.contact-messages.destroy', $contactMessage) }}" data-confirm-title="حذف الرسالة" data-confirm-text="هل تريد حذف هذه الرسالة؟">
                    @csrf
                    @method('DELETE')
                    <button class="admin-btn-secondary" type="submit">حذف الرسالة</button>
                </form>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="admin-subcard">
                    <p class="text-xs font-bold text-slate-400">الاسم</p>
                    <p class="mt-2 font-bold text-white">{{ $contactMessage->name }}</p>
                </div>
                <div class="admin-subcard">
                    <p class="text-xs font-bold text-slate-400">البريد الإلكتروني</p>
                    <p class="mt-2 font-bold text-white">{{ $contactMessage->email }}</p>
                </div>
                <div class="admin-subcard">
                    <p class="text-xs font-bold text-slate-400">الهاتف</p>
                    <p class="mt-2 font-bold text-white">{{ $contactMessage->phone ?: 'غير متوفر' }}</p>
                </div>
                <div class="admin-subcard">
                    <p class="text-xs font-bold text-slate-400">تاريخ الإرسال</p>
                    <p class="mt-2 font-bold text-white">{{ $contactMessage->created_at?->format('Y-m-d H:i') }}</p>
                </div>
            </div>

            <div class="admin-subcard mt-6">
                <p class="text-xs font-bold text-slate-400">الرسالة</p>
                <div class="mt-3 whitespace-pre-line leading-7 text-slate-200">{{ $contactMessage->message }}</div>
            </div>
        </article>

        <aside class="admin-card">
            <h2 class="text-xl font-bold text-white">إرسال رد</h2>
            <form class="mt-4 space-y-4" method="POST" action="{{ route('admin.contact-messages.reply', $contactMessage) }}">
                @csrf
                <label class="space-y-2 block">
                    <span class="text-sm font-bold text-white">عنوان الرسالة</span>
                    <input class="admin-input" type="text" name="subject" value="{{ old('subject', 'Re: ' . $contactMessage->subject) }}" required>
                </label>

                <label class="space-y-2 block">
                    <span class="text-sm font-bold text-white">محتوى الرد</span>
                    <textarea class="admin-textarea" name="message" rows="14" data-rich-text>{{ old('message') }}</textarea>
                </label>

                <button class="admin-btn-primary w-full" type="submit">إرسال الرد</button>
            </form>
        </aside>
    </section>
@endsection
