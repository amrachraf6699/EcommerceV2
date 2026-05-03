@php
    $title = 'الصفحات';
    $pageTitle = 'إدارة الصفحات';
    $pageDescription = 'إدارة صفحات المحتوى الثابت للمتجر من مكان واحد.';
    $breadcrumbs = ['الإدارة', 'الصفحات'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" class="flex-1">
                <div class="grid gap-3 lg:grid-cols-[1fr_auto]">
                    <input class="admin-input" type="text" name="search" placeholder="ابحث بالعنوان أو الرابط أو المحتوى" value="{{ request('search') }}">
                    <button class="admin-btn-secondary" type="submit">بحث</button>
                </div>
            </form>

            @can('pages.create')
                <a class="admin-btn-primary" href="{{ route('admin.pages.create') }}">إضافة صفحة</a>
            @endcan
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="bg-slate-950/50">
                    <tr>
                        <th>العنوان</th>
                        <th>الرابط</th>
                        <th>المحتوى</th>
                        <th>آخر تحديث</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-white/5">
                    @forelse ($pages as $page)
                        <tr>
                            <td class="font-bold text-white">{{ $page->title }}</td>
                            <td>{{ $page->slug }}</td>
                            <td class="admin-table-wrap-text">{{ \Illuminate\Support\Str::limit(strip_tags($page->content ?? ''), 140) ?: 'لا يوجد محتوى بعد.' }}</td>
                            <td>{{ $page->updated_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <a class="admin-btn-icon" href="{{ route('admin.pages.edit', $page) }}" aria-label="تعديل الصفحة" title="تعديل الصفحة">
                                        <i class="bx bx-pencil" aria-hidden="true"></i>
                                    </a>
                                    @can('pages.delete')
                                        <form method="POST"
                                              action="{{ route('admin.pages.destroy', $page) }}"
                                              data-loading-form
                                              data-confirm-title="حذف الصفحة"
                                              data-confirm-text="سيتم نقل الصفحة إلى السجلات المحذوفة.">
                                            @csrf
                                            @method('DELETE')
                                            <button class="admin-btn-icon admin-btn-icon--danger" type="submit" aria-label="حذف الصفحة" title="حذف الصفحة" data-loading-label="جارٍ الحذف...">
                                                <i class="bx bx-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin.empty-state title="لا توجد صفحات بعد" description="ابدأ بإنشاء أول صفحة مخصصة لعرض المحتوى الثابت أو التعريفي داخل المتجر." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $pages->links() }}</div>
    </section>
@endsection
