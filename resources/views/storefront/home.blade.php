@php
    $title = ($storefrontBrand['name'] ?? config('app.name')) . ' | الصفحة الرئيسية';
@endphp

@extends('layouts.storefront')

@section('content')
    <section class="sf-section sf-home-hero" id="foundation-hero">
        <div class="sf-container space-y-8">
            <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                <article class="sf-panel sf-panel--deep sf-home-hero-copy p-6 md:p-10" data-sf-surface>
                    <div class="space-y-6">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="sf-badge">Homepage</span>
                            <span class="sf-badge">Editorial Luxury</span>
                            <span class="sf-badge">Black / White Motion</span>
                        </div>

                        <div class="space-y-4">
                            <p class="sf-kicker">Homepage System</p>
                            <h1 class="sf-display">صفحة رئيسية ديناميكية تعرض الشرائح، الأقسام، والمنتجات بأسلوب تحريري حديث.</h1>
                            <p class="max-w-3xl text-base leading-8 sf-text-muted md:text-lg">
                                هذه المرحلة تحول الواجهة من مجرد هيكل جاهز إلى صفحة رئيسية حقيقية تعتمد على البيانات الفعلية في النظام: شرائح العرض، الأقسام المميزة، المنتجات الموصى بها، والإطلاقات الجديدة مع طبقات بصرية واضحة وتحركات خفيفة ومحسوبة.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <a class="sf-button sf-button-primary" href="#featured-products">المنتجات المميزة</a>
                            <a class="sf-button sf-button-secondary" href="#new-arrivals">أحدث الإضافات</a>
                        </div>
                    </div>
                </article>

                <aside class="sf-card grid gap-4 p-6 md:p-8">
                    <div class="space-y-2">
                        <p class="sf-kicker">Storefront Snapshot</p>
                        <h2 class="sf-heading-md">البيانات الحية في الواجهة</h2>
                    </div>

                    <div class="sf-divider"></div>

                    @foreach ($storefrontStats as $item)
                        <div class="flex items-end justify-between gap-4">
                            <span class="text-sm sf-text-muted">{{ $item['label'] }}</span>
                            <span class="text-3xl font-extrabold">{{ $item['value'] }}</span>
                        </div>
                    @endforeach
                </aside>
            </div>

            <div class="sf-slider-grid">
                @forelse ($heroSliders as $slider)
                    <article class="sf-slider-card sf-card p-6 md:p-8" data-sf-surface>
                        <div class="sf-slider-card__media">
                            <img src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->title ?: $storefrontBrand['name'] }}">
                        </div>

                        <div class="sf-slider-card__body">
                            <p class="sf-kicker">Active Slider</p>
                            <h2 class="sf-heading-md">{{ $slider->title ?: 'شريحة رئيسية' }}</h2>
                            @if ($slider->subtitle)
                                <p class="text-sm leading-7 sf-text-muted">{{ $slider->subtitle }}</p>
                            @endif

                            @if ($slider->link)
                                <a href="{{ $slider->link }}" class="sf-button sf-button-secondary" target="_blank" rel="noreferrer">
                                    افتح الرابط
                                </a>
                            @endif
                        </div>
                    </article>
                @empty
                    <article class="sf-empty p-6 md:p-8">
                        <span class="sf-badge">Sliders</span>
                        <h2 class="mt-4 text-xl font-extrabold">لا توجد شرائح مفعلة حاليا</h2>
                        <p class="mt-3 text-sm leading-7 sf-text-muted">
                            عند إضافة شرائح مفعلة من لوحة الإدارة ستظهر هنا مباشرة ضمن المشهد الرئيسي للواجهة.
                        </p>
                    </article>
                @endforelse
            </div>
        </div>
    </section>

    <section class="sf-section pt-0" id="foundation-categories">
        <div class="sf-container space-y-5">
            <div class="space-y-2">
                <p class="sf-kicker">Featured Categories</p>
                <h2 class="sf-heading-lg">أقسام مهيأة للملاحة السريعة</h2>
            </div>

            <div class="sf-home-grid-categories">
                @forelse ($featuredCategories as $category)
                    <article class="sf-category-card sf-card p-6" data-sf-surface>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between gap-4">
                                <span class="sf-badge">قسم</span>
                                <span class="text-sm sf-text-muted">{{ $category->products_count }} منتج</span>
                            </div>

                            <div>
                                <h3 class="sf-heading-md">{{ $category->name }}</h3>
                                <p class="mt-3 text-sm leading-7 sf-text-muted">
                                    {{ $category->description ?: 'قسم جاهز للعرض ضمن الكتالوج وتوجيه الزائر إلى المنتجات المناسبة.' }}
                                </p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="sf-empty p-6">
                        <p class="font-bold">لا توجد أقسام نشطة بعد</p>
                        <p class="mt-2 text-sm sf-text-muted">أضف أقساما من لوحة الإدارة لتظهر في الصفحة الرئيسية.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="sf-section pt-0" id="featured-products">
        <div class="sf-container space-y-5">
            <div class="space-y-2">
                <p class="sf-kicker">Featured Products</p>
                <h2 class="sf-heading-lg">منتجات مميزة ببطاقات أكثر حضورًا</h2>
            </div>

            <div class="sf-home-grid-products">
                @forelse ($featuredProducts as $product)
                    <article class="sf-product-card sf-card" data-sf-surface>
                        <div class="sf-product-card__media">
                            @if ($product->primary_image_url)
                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}">
                            @else
                                <div class="sf-product-card__placeholder">NO IMAGE</div>
                            @endif
                        </div>

                        <div class="space-y-4 p-5">
                            <div class="flex items-center justify-between gap-3">
                                <span class="sf-badge">مميز</span>
                                <span class="text-xs sf-text-muted">{{ $product->categories->pluck('name')->join(' / ') ?: 'بدون أقسام' }}</span>
                            </div>

                            <div>
                                <h3 class="text-lg font-extrabold">{{ $product->name }}</h3>
                                <p class="mt-2 text-sm leading-7 sf-text-muted">
                                    {{ $product->short_description ?: 'منتج جاهز للعرض ضمن الواجهة الجديدة مع وصف مختصر قابل للتوسع لاحقا.' }}
                                </p>
                            </div>

                            <div class="flex items-end justify-between gap-4">
                                <div>
                                    <p class="text-xs sf-text-muted">السعر الابتدائي</p>
                                    <p class="sf-product-price">
                                        {{ $product->default_variant ? number_format((float) $product->default_variant->price, 2) . ' ' . $storefrontCartSummary['currency'] : 'غير محدد' }}
                                    </p>
                                </div>

                                <span class="text-xs sf-text-muted">
                                    {{ $product->default_variant?->stock_quantity ? 'متاح' : 'بانتظار التحديث' }}
                                </span>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="sf-empty p-6">
                        <p class="font-bold">لا توجد منتجات مميزة حاليا</p>
                        <p class="mt-2 text-sm sf-text-muted">عند تفعيل منتجات مميزة من لوحة الإدارة ستظهر هنا.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="sf-section pt-0" id="foundation-primitives">
        <div class="sf-container space-y-5">
            <div class="space-y-2">
                <p class="sf-kicker">Editorial Bands</p>
                <h2 class="sf-heading-lg">شرائط تحريرية تدعم هوية المتجر</h2>
            </div>

            <div class="sf-editorial-grid">
                @foreach ($editorialBlocks as $block)
                    <article class="sf-editorial-card sf-panel p-6 md:p-8" data-sf-surface>
                        <p class="sf-kicker">{{ $block['eyebrow'] }}</p>
                        <h3 class="mt-4 text-2xl font-extrabold">{{ $block['title'] }}</h3>
                        <p class="mt-4 text-sm leading-8 sf-text-muted">{{ $block['body'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="sf-section pt-0" id="new-arrivals">
        <div class="sf-container space-y-5">
            <div class="space-y-2">
                <p class="sf-kicker">New Arrivals</p>
                <h2 class="sf-heading-lg">أحدث الإضافات في الكتالوج</h2>
            </div>

            <div class="sf-home-grid-products sf-home-grid-products--compact">
                @forelse ($newArrivalProducts as $product)
                    <article class="sf-card p-5" data-sf-surface>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="sf-badge">جديد</span>
                                <span class="text-xs sf-text-muted">{{ $product->default_variant?->display_name ?: 'بدون اسم نسخة' }}</span>
                            </div>

                            <div>
                                <h3 class="text-lg font-extrabold">{{ $product->name }}</h3>
                                <p class="mt-2 text-sm leading-7 sf-text-muted">
                                    {{ $product->short_description ?: 'وصف مختصر جاهز للتوسع عند بناء صفحة المنتج لاحقا.' }}
                                </p>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <p class="sf-product-price">
                                    {{ $product->default_variant ? number_format((float) $product->default_variant->price, 2) . ' ' . $storefrontCartSummary['currency'] : 'غير محدد' }}
                                </p>
                                <span class="text-xs sf-text-muted">{{ optional($product->created_at)->format('Y-m-d') }}</span>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="sf-empty p-6">
                        <p class="font-bold">لا توجد منتجات حديثة للعرض</p>
                        <p class="mt-2 text-sm sf-text-muted">أضف منتجات جديدة أو فعّلها لتظهر هنا.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
