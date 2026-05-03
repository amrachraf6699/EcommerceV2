@php
    $title = 'الأقسام';
    $pageTitle = 'إدارة الأقسام';
    $pageDescription = 'تنظيم أقسام المنتجات مع فلاتر واضحة وسريعة.';
    $breadcrumbs = ['الإدارة', 'الأقسام'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" class="grid gap-3 lg:grid-cols-3 lg:flex-1">
                <input class="admin-input" type="text" name="search" placeholder="ابحث باسم القسم أو الرابط" value="{{ request('search') }}">
                <select class="admin-select" name="status">
                    <option value="">كل الحالات</option>
                    <option value="active" @selected(request('status') === 'active')>مفعل</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>غير مفعل</option>
                </select>
                <button class="admin-btn-secondary" type="submit">بحث</button>
            </form>
            @can('categories.create')
                <a class="admin-btn-primary" href="{{ route('admin.categories.create') }}">إضافة قسم</a>
            @endcan
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="bg-slate-950/50">
                    <tr>
                        <th>القسم</th>
                        <th>الرابط</th>
                        <th>عدد المنتجات</th>
                        <th>الترتيب</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-white/5">
                    @forelse ($categories as $category)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if ($category->image)
                                        <img class="h-12 w-12 border border-black/10 object-cover" src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}">
                                    @else
                                        <div class="flex h-12 w-12 items-center justify-center border border-black/10 bg-neutral-100 text-xs font-bold text-neutral-500">
                                            IMG
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-bold text-white">{{ $category->name }}</p>
                                        <p class="mt-1 text-xs text-slate-400">{{ $category->is_active ? 'مفعل' : 'غير مفعل' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $category->slug }}</td>
                            <td>{{ $category->products_count }}</td>
                            <td>{{ $category->sort_order }}</td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <a class="admin-btn-icon" href="{{ route('admin.categories.edit', $category) }}" aria-label="تعديل القسم" title="تعديل القسم">
                                        <i class="bx bx-pencil" aria-hidden="true"></i>
                                    </a>
                                    @can('categories.delete')
                                        <form method="POST"
                                              action="{{ route('admin.categories.destroy', $category) }}"
                                              data-loading-form
                                              data-confirm-title="حذف القسم"
                                              data-confirm-text="سيتم نقل القسم إلى المحذوفات وحذف صورته من الملفات.">
                                            @csrf
                                            @method('DELETE')
                                            <button class="admin-btn-icon admin-btn-icon--danger" type="submit" aria-label="حذف القسم" title="حذف القسم" data-loading-label="جاري الحذف...">
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
                                <x-admin.empty-state title="لا توجد أقسام" description="ابدأ بإضافة أول قسم لتنظيم المنتجات داخل المتجر." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $categories->links() }}</div>
    </section>
@endsection
