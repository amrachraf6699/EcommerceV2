<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? ($storefrontBrand['name'] ?? config('app.name')) }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/storefront.css', 'resources/js/storefront.js'])
</head>
<body class="storefront-body">
    <div class="sf-shell">
        <header class="sf-site-header">
            <div class="sf-header-strip">
                <div class="sf-container">
                    <div class="sf-header-top">
                        <button
                            type="button"
                            class="sf-nav-toggle lg:hidden"
                            data-sf-nav-toggle
                            aria-label="فتح القائمة"
                            aria-expanded="false"
                        >
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>

                        <a href="{{ route('storefront.home') }}" class="sf-brand-lockup" aria-label="{{ $storefrontBrand['name'] }}">
                            <div class="sf-brand-mark">
                                @if ($storefrontBrand['logo_url'])
                                    <img src="{{ $storefrontBrand['logo_url'] }}" alt="{{ $storefrontBrand['name'] }}">
                                @else
                                    <span class="text-sm font-bold">SF</span>
                                @endif
                            </div>

                            <div class="space-y-1">
                                <p class="sf-kicker">SunFlower Storefront</p>
                                <p class="text-xl font-extrabold text-black">{{ $storefrontBrand['name'] }}</p>
                            </div>
                        </a>

                        <div class="sf-header-actions">
                            <a href="#catalog-preview" class="sf-header-pill">
                                السلة
                                <span>{{ $storefrontCartSummary['items_count'] }}</span>
                            </a>
                            <a href="#site-footer" class="sf-header-pill hidden sm:inline-flex">تواصل</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sf-nav-strip">
                <div class="sf-container">
                    <div class="sf-nav-row">
                        <nav class="sf-nav hidden lg:flex" aria-label="التنقل الرئيسي">
                            <a href="{{ route('storefront.home') }}" class="sf-nav-link {{ request()->routeIs('storefront.home') ? 'is-active' : '' }}">
                                البداية
                            </a>
                            <a href="#foundation-hero" class="sf-nav-link" data-sf-section-link>المشهد</a>
                            <a href="#foundation-primitives" class="sf-nav-link" data-sf-section-link>العناصر</a>
                            <a href="#foundation-categories" class="sf-nav-link" data-sf-section-link>الأقسام</a>
                            <a href="#catalog-preview" class="sf-nav-link" data-sf-section-link>الكتالوج</a>
                        </nav>

                        <div class="sf-nav-meta hidden lg:flex">
                            <span class="sf-badge">الأقسام {{ $storefrontNavCategories->count() }}</span>
                            <span class="sf-badge">الشبكات {{ count($storefrontSocialLinks) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <aside class="sf-mobile-drawer lg:hidden" data-sf-nav-drawer aria-hidden="true">
            <div class="sf-mobile-drawer__panel">
                <div class="sf-mobile-drawer__head">
                    <div>
                        <p class="sf-kicker">Navigation</p>
                        <p class="text-lg font-extrabold">{{ $storefrontBrand['name'] }}</p>
                    </div>

                    <button type="button" class="sf-drawer-close" data-sf-nav-close aria-label="إغلاق القائمة">×</button>
                </div>

                <nav class="sf-mobile-nav" aria-label="التنقل على الجوال">
                    <a href="{{ route('storefront.home') }}" class="sf-mobile-nav-link {{ request()->routeIs('storefront.home') ? 'is-active' : '' }}">
                        البداية
                    </a>
                    <a href="#foundation-hero" class="sf-mobile-nav-link" data-sf-section-link>المشهد الافتتاحي</a>
                    <a href="#foundation-primitives" class="sf-mobile-nav-link" data-sf-section-link>العناصر الأساسية</a>
                    <a href="#foundation-categories" class="sf-mobile-nav-link" data-sf-section-link>الأقسام</a>
                    <a href="#catalog-preview" class="sf-mobile-nav-link" data-sf-section-link>معاينة الكتالوج</a>
                </nav>

                <div class="sf-mobile-drawer__meta">
                    <span class="sf-badge">السلة {{ $storefrontCartSummary['items_count'] }}</span>
                    <span class="sf-badge">روابط اجتماعية {{ count($storefrontSocialLinks) }}</span>
                </div>
            </div>
        </aside>
        <div class="sf-mobile-overlay lg:hidden" data-sf-nav-overlay></div>

        <main>
            @yield('content')
        </main>

        <footer class="sf-footer-strip" id="site-footer">
            <div class="sf-container">
                <div class="sf-footer-grid">
                    <section class="space-y-4">
                        <p class="sf-kicker">Brand</p>
                        <h2 class="text-2xl font-extrabold">{{ $storefrontBrand['name'] }}</h2>
                        <p class="max-w-xl text-sm leading-7 sf-text-muted">
                            واجهة عربية أحادية اللون بحواف حادة وطبقات بصرية واضحة، مصممة لتكون الأساس للمراحل التجارية القادمة.
                        </p>
                        @if ($storefrontFooterData['address'])
                            <p class="text-sm sf-text-muted">{{ $storefrontFooterData['address'] }}</p>
                        @endif
                    </section>

                    <section class="space-y-4">
                        <p class="sf-kicker">Sections</p>
                        <div class="sf-footer-links">
                            <a href="#foundation-hero" class="sf-footer-link">المشهد الافتتاحي</a>
                            <a href="#foundation-primitives" class="sf-footer-link">العناصر الأساسية</a>
                            <a href="#foundation-categories" class="sf-footer-link">الأقسام</a>
                            <a href="#catalog-preview" class="sf-footer-link">معاينة الكتالوج</a>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <p class="sf-kicker">Categories</p>
                        <div class="sf-footer-links">
                            @forelse ($storefrontNavCategories as $category)
                                <a href="#foundation-categories" class="sf-footer-link">{{ $category->name }}</a>
                            @empty
                                <span class="sf-text-muted text-sm">لا توجد أقسام مفعلة بعد.</span>
                            @endforelse
                        </div>
                    </section>

                    <section class="space-y-4">
                        <p class="sf-kicker">Social</p>
                        <div class="sf-footer-links">
                            @forelse ($storefrontSocialLinks as $network => $url)
                                <a href="{{ $url }}" class="sf-footer-link" target="_blank" rel="noreferrer">
                                    {{ ucfirst($network) }}
                                </a>
                            @empty
                                <span class="sf-text-muted text-sm">لم تتم إضافة روابط اجتماعية حتى الآن.</span>
                            @endforelse
                        </div>
                    </section>
                </div>

                <div class="sf-footer-bottom">
                    <p>العناصر الحالية في السلة: {{ $storefrontCartSummary['items_count'] }}</p>
                    <p>إجمالي الشبكات الاجتماعية المتاحة: {{ $storefrontFooterData['social_count'] }}</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
