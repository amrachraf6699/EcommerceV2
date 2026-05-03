@php
    $title = 'المنتجات';
    $pageTitle = 'إدارة المنتجات';
    $pageDescription = 'شاشة واحدة للبحث والتصفية ومراجعة حالة المنتجات والنسخ.';
    $breadcrumbs = ['الإدارة', 'المنتجات'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" class="grid gap-3 xl:grid-cols-5 lg:flex-1">
                <input class="admin-input" type="text" name="search" placeholder="ابحث بالاسم أو الرابط" value="{{ request('search') }}">
                <select class="admin-select" name="category">
                    <option value="">كل الأقسام</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select class="admin-select" name="status">
                    <option value="">كل الحالات</option>
                    <option value="active" @selected(request('status') === 'active')>مفعل</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>غير مفعل</option>
                </select>
                <select class="admin-select" name="featured">
                    <option value="">الكل</option>
                    <option value="yes" @selected(request('featured') === 'yes')>مميز فقط</option>
                    <option value="no" @selected(request('featured') === 'no')>غير مميز فقط</option>
                </select>
                <button class="admin-btn-secondary" type="submit">تطبيق</button>
            </form>
            @can('products.create')
                <a class="admin-btn-primary" href="{{ route('admin.products.create') }}">إضافة منتج</a>
            @endcan
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="bg-slate-950/50">
                    <tr>
                        <th>المنتج</th>
                        <th>الأقسام</th>
                        <th>النسخ</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-white/5">
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <p class="font-bold text-white">{{ $product->name }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $product->slug }}</p>
                            </td>
                            <td>{{ $product->categories->pluck('name')->join(' / ') ?: 'بدون أقسام' }}</td>
                            <td>{{ $product->variants_count }}</td>
                            <td>
                                <span class="px-3 py-1 text-xs {{ $product->is_active ? 'bg-emerald-400/10 text-emerald-200' : 'bg-slate-400/10 text-slate-300' }}">
                                    {{ $product->is_active ? 'مفعل' : 'غير مفعل' }}
                                </span>
                                @if ($product->is_featured)
                                    <span class="mr-2 bg-amber-300/10 px-3 py-1 text-xs text-amber-200">مميز</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <a class="admin-btn-icon" href="{{ route('admin.products.edit', $product) }}" aria-label="تعديل المنتج" title="تعديل المنتج">
                                        <i class="bx bx-pencil" aria-hidden="true"></i>
                                    </a>
                                    @can('products.delete')
                                        <form method="POST"
                                              action="{{ route('admin.products.destroy', $product) }}"
                                              data-loading-form
                                              data-confirm-title="حذف المنتج"
                                              data-confirm-text="سيتم إخفاء المنتج من الإدارة والمتجر مع الاحتفاظ بالسجلات المرتبطة به حسب النظام.">
                                            @csrf
                                            @method('DELETE')
                                            <button class="admin-btn-icon admin-btn-icon--danger" type="submit" aria-label="حذف المنتج" title="حذف المنتج" data-loading-label="جاري الحذف...">
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
                                <x-admin.empty-state title="لا توجد منتجات" description="ابدأ بإضافة أول منتج ثم أضف نسخه وصوره من شاشة التعديل." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $products->links() }}</div>
    </section>
@endsection
