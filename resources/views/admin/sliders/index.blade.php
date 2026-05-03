@php
    $title = 'السلايدر';
    $pageTitle = 'إدارة السلايدر';
    $pageDescription = 'إدارة الشرائح الرئيسية والصور والألوان وروابط الواجهة الأمامية.';
    $breadcrumbs = ['الإدارة', 'السلايدر'];
    $headerActions = view('admin.sliders.partials.index-actions');
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="GET" class="grid gap-3 lg:grid-cols-3">
            <input class="admin-input" type="text" name="search" placeholder="ابحث بالعنوان أو الرابط" value="{{ request('search') }}">
            <select class="admin-select" name="status">
                <option value="">كل الحالات</option>
                <option value="active" @selected(request('status') === 'active')>مفعل</option>
                <option value="inactive" @selected(request('status') === 'inactive')>غير مفعل</option>
            </select>
            <button class="admin-btn-secondary" type="submit">تطبيق الفلاتر</button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="bg-slate-950/50">
                    <tr>
                        <th>الصورة</th>
                        <th>العنوان</th>
                        <th>الرابط</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-white/5">
                    @forelse ($sliders as $slider)
                        <tr>
                            <td>
                                <img class="h-16 w-28 border border-black/10 object-cover" src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->title }}">
                            </td>
                            <td>
                                <p class="font-bold text-white">{{ $slider->title ?: 'بدون عنوان' }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $slider->subtitle ?: 'بدون عنوان فرعي' }}</p>
                            </td>
                            <td class="max-w-xs break-all">{{ Str::limit($slider->link, 15) }}</td>
                            <td>{{ $slider->is_active ? 'مفعل' : 'غير مفعل' }}</td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <a class="admin-btn-icon" href="{{ route('admin.sliders.edit', $slider) }}" aria-label="تعديل الشريحة" title="تعديل الشريحة">
                                        <i class="bx bx-pencil" aria-hidden="true"></i>
                                    </a>
                                    @can('sliders.delete')
                                        <form method="POST"
                                              action="{{ route('admin.sliders.destroy', $slider) }}"
                                              data-loading-form
                                              data-confirm-title="حذف الشريحة"
                                              data-confirm-text="سيتم حذف الشريحة وصورتها من الملفات نهائيا.">
                                            @csrf
                                            @method('DELETE')
                                            <button class="admin-btn-icon admin-btn-icon--danger" type="submit" aria-label="حذف الشريحة" title="حذف الشريحة" data-loading-label="جاري الحذف...">
                                                <i class="bx bx-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin.empty-state title="لا توجد شرائح" description="ابدأ بإضافة أول شريحة لعرضها في الواجهة الرئيسية." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $sliders->links() }}</div>
    </section>
@endsection
