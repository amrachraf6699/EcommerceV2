@php
    $title = 'إضافة منتج';
    $pageTitle = 'إنشاء منتج جديد';
    $pageDescription = 'أنشئ المنتج من شاشة واحدة مقسمة إلى تبويبات واضحة بدل الخطوات المتتابعة.';
    $breadcrumbs = ['الإدارة', 'المنتجات', 'إضافة'];
@endphp

@extends('layouts.admin')

@section('content')
    <form
        method="POST"
        action="{{ route('admin.products.store') }}"
        enctype="multipart/form-data"
        class="admin-card min-w-0 space-y-6"
        data-admin-tabs
        data-product-variants
    >
        @csrf

        @include('admin.products.partials.tabs-nav')

        <div class="hidden admin-validation-errors rounded-2xl px-4 py-3 text-sm" data-ajax-errors></div>

        <section class="admin-tab-panel space-y-6" data-admin-tab-panel="basic">
            @include('admin.products.partials.basic-fields', ['product' => null, 'categories' => $categories])
        </section>

        <section class="admin-tab-panel hidden space-y-6" data-admin-tab-panel="seo">
            @include('admin.products.partials.seo-fields', ['product' => null])
        </section>

        <section class="admin-tab-panel hidden min-w-0 space-y-5" data-admin-tab-panel="variants">
            @php
                $oldVariants = old('variants', [
                    ['size' => '', 'color' => '', 'price' => '', 'compare_at_price' => '', 'stock_quantity' => '', 'is_default' => '1', 'is_active' => '1'],
                ]);
            @endphp

            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-white">نسخ المنتج</h2>
                <button class="admin-btn-secondary" type="button" data-add-variant>إضافة نسخة أخرى</button>
            </div>

            <div class="min-w-0 overflow-x-auto rounded-2xl border border-white/10">
                <table class="min-w-full divide-y divide-white/10 text-sm text-slate-200">
                    <thead class="bg-white/5 text-xs uppercase tracking-[0.2em] text-slate-400">
                        <tr>
                            <th class="px-4 py-3 text-right">#</th>
                            <th class="px-4 py-3 text-right">المقاس</th>
                            <th class="px-4 py-3 text-right">اللون</th>
                            <th class="px-4 py-3 text-right">السعر</th>
                            <th class="px-4 py-3 text-right">السعر قبل الخصم</th>
                            <th class="px-4 py-3 text-right">المخزون</th>
                            <th class="px-4 py-3 text-center">افتراضية</th>
                            <th class="px-4 py-3 text-center">مفعلة</th>
                            <th class="px-4 py-3 text-left">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10" data-variant-list>
                        @foreach ($oldVariants as $index => $variant)
                            <tr class="align-top" data-variant-row>
                                <td class="px-4 py-3 font-semibold text-white">
                                    <span data-variant-number>{{ $loop->iteration }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <input class="admin-input min-w-[140px]" type="text" name="variants[{{ $index }}][size]" value="{{ $variant['size'] ?? '' }}" placeholder="المقاس">
                                </td>
                                <td class="px-4 py-3">
                                    <input class="admin-input min-w-[140px]" type="text" name="variants[{{ $index }}][color]" value="{{ $variant['color'] ?? '' }}" placeholder="اللون">
                                </td>
                                <td class="px-4 py-3">
                                    <input class="admin-input min-w-[140px]" type="number" step="0.01" name="variants[{{ $index }}][price]" value="{{ $variant['price'] ?? '' }}" placeholder="السعر">
                                </td>
                                <td class="px-4 py-3">
                                    <input class="admin-input min-w-[160px]" type="number" step="0.01" name="variants[{{ $index }}][compare_at_price]" value="{{ $variant['compare_at_price'] ?? '' }}" placeholder="السعر قبل الخصم">
                                </td>
                                <td class="px-4 py-3">
                                    <input class="admin-input min-w-[120px]" type="number" name="variants[{{ $index }}][stock_quantity]" value="{{ $variant['stock_quantity'] ?? '' }}" placeholder="المخزون">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input class="admin-checkbox" type="checkbox" name="variants[{{ $index }}][is_default]" value="1" @checked(! empty($variant['is_default']))>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input class="admin-checkbox" type="checkbox" name="variants[{{ $index }}][is_active]" value="1" @checked($variant['is_active'] ?? true)>
                                </td>
                                <td class="px-4 py-3 text-left">
                                    <button class="admin-btn-secondary whitespace-nowrap" type="button" data-remove-variant>حذف</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <template data-variant-template>
                <tr class="align-top" data-variant-row>
                    <td class="px-4 py-3 font-semibold text-white">
                        <span data-variant-number></span>
                    </td>
                    <td class="px-4 py-3">
                        <input class="admin-input min-w-[140px]" type="text" name="variants[__INDEX__][size]" placeholder="المقاس">
                    </td>
                    <td class="px-4 py-3">
                        <input class="admin-input min-w-[140px]" type="text" name="variants[__INDEX__][color]" placeholder="اللون">
                    </td>
                    <td class="px-4 py-3">
                        <input class="admin-input min-w-[140px]" type="number" step="0.01" name="variants[__INDEX__][price]" placeholder="السعر">
                    </td>
                    <td class="px-4 py-3">
                        <input class="admin-input min-w-[160px]" type="number" step="0.01" name="variants[__INDEX__][compare_at_price]" placeholder="السعر قبل الخصم">
                    </td>
                    <td class="px-4 py-3">
                        <input class="admin-input min-w-[120px]" type="number" name="variants[__INDEX__][stock_quantity]" placeholder="المخزون">
                    </td>
                    <td class="px-4 py-3 text-center">
                        <input class="admin-checkbox" type="checkbox" name="variants[__INDEX__][is_default]" value="1">
                    </td>
                    <td class="px-4 py-3 text-center">
                        <input class="admin-checkbox" type="checkbox" name="variants[__INDEX__][is_active]" value="1" checked>
                    </td>
                    <td class="px-4 py-3 text-left">
                        <button class="admin-btn-secondary whitespace-nowrap" type="button" data-remove-variant>حذف</button>
                    </td>
                </tr>
            </template>
        </section>

        <section class="admin-tab-panel hidden space-y-5" data-admin-tab-panel="images">
            <input class="admin-input" type="file" name="images[]" data-filepond multiple>
            <input class="admin-input" type="text" name="image_alt_text" value="{{ old('image_alt_text') }}" placeholder="نص بديل للصور">

            <div class="grid gap-3 md:grid-cols-2">
                <input class="admin-input" type="number" name="image_sort_order" value="{{ old('image_sort_order', 0) }}" min="0" placeholder="ترتيب البداية">
                <select class="admin-select" name="image_variant_index" data-image-variant-select>
                    <option value="">لكل النسخ</option>
                </select>
            </div>

            <label class="flex items-center gap-3 text-slate-200">
                <input class="admin-checkbox" type="checkbox" name="images_primary" value="1" @checked(old('images_primary', false))>
                جعل أول صورة هي الصورة الرئيسية
            </label>
        </section>

        <div class="flex flex-wrap justify-between gap-3 border-t border-black/10 pt-5">
            <a class="admin-btn-secondary" href="{{ route('admin.products.index') }}">عودة</a>
            <button class="admin-btn-primary" type="submit">إنشاء المنتج</button>
        </div>
    </form>
@endsection
