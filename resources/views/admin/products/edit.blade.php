@php
    $title = 'تعديل منتج';
    $pageTitle = 'إدارة المنتج';
    $pageDescription = 'حدّث بيانات المنتج من تبويبات مستقلة مع حفظ فوري بدون إعادة تحميل الصفحة.';
    $breadcrumbs = ['الإدارة', 'المنتجات', $product->name];
@endphp

@extends('layouts.admin')

@section('content')
    <div class="admin-card min-w-0 space-y-6" data-admin-tabs data-product-editor data-product-variants>
        @include('admin.products.partials.tabs-nav')

        <div class="hidden admin-validation-errors rounded-2xl px-4 py-3 text-sm" data-product-editor-errors></div>

        <section class="admin-tab-panel space-y-6" data-admin-tab-panel="basic" id="productBasicPanel">
            @include('admin.products.partials.basic-panel', ['product' => $product, 'categories' => $categories])
        </section>

        <section class="admin-tab-panel hidden space-y-6" data-admin-tab-panel="seo" id="productSeoPanel">
            @include('admin.products.partials.seo-panel', ['product' => $product])
        </section>

        <section class="admin-tab-panel hidden min-w-0" data-admin-tab-panel="variants" id="productVariantsPanel">
            @include('admin.products.partials.variants-panel', ['product' => $product])
        </section>

        <section class="admin-tab-panel hidden" data-admin-tab-panel="images" id="productImagesPanel">
            @include('admin.products.partials.images-panel', ['product' => $product])
        </section>

        <div class="flex justify-start border-t border-black/10 pt-5">
            <a class="admin-btn-secondary" href="{{ route('admin.products.index') }}">عودة</a>
        </div>
    </div>
@endsection
