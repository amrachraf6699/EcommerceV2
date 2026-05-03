@php
    $title = 'رسائل التواصل';
    $pageTitle = 'صندوق رسائل التواصل';
    $pageDescription = 'استعراض الرسائل الواردة من صفحة تواصل معنا والانتقال مباشرة إلى الرد أو الحذف.';
    $breadcrumbs = ['الإدارة', 'رسائل التواصل'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="GET" class="grid gap-3 xl:grid-cols-[minmax(0,1fr)_180px]">
            <input class="admin-input" type="text" name="search" placeholder="ابحث بالاسم أو البريد أو الموضوع" value="{{ request('search') }}">
            <button class="admin-btn-secondary" type="submit">تطبيق البحث</button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="bg-slate-950/50">
                    <tr>
                        <th>المرسل</th>
                        <th>الموضوع</th>
                        <th>الحالة</th>
                        <th>الهاتف</th>
                        <th>تاريخ الإرسال</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-white/5">
                    @forelse ($messages as $message)
                        <tr>
                            <td>
                                <div class="font-bold text-white">{{ $message->name }}</div>
                                <div class="text-xs text-slate-400">{{ $message->email }}</div>
                            </td>
                            <td>
                                <div class="font-bold text-white">{{ $message->subject }}</div>
                                <div class="text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($message->message, 90) }}</div>
                            </td>
                            <td>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $message->is_replied ? 'bg-emerald-500/15 text-emerald-300' : 'bg-amber-500/15 text-amber-200' }}">
                                    {{ $message->is_replied ? 'تم الرد' : 'بانتظار الرد' }}
                                </span>
                            </td>
                            <td>{{ $message->phone ?: 'غير متوفر' }}</td>
                            <td>{{ $message->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="flex flex-wrap gap-2">
                                <a class="admin-btn-secondary" href="{{ route('admin.contact-messages.show', $message) }}">عرض</a>
                                <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" data-confirm-title="حذف الرسالة" data-confirm-text="هل تريد حذف هذه الرسالة؟">
                                    @csrf
                                    @method('DELETE')
                                    <button class="admin-btn-secondary" type="submit">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin.empty-state title="لا توجد رسائل" description="ستظهر هنا رسائل نموذج التواصل عند إرسالها من واجهة المتجر." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $messages->links() }}</div>
    </section>
@endsection
