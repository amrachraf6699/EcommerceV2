@php
    $brandSettings = \App\Models\Setting::where('group', 'brand')
        ->get()
        ->keyBy('key');
@endphp

<aside data-admin-sidebar class="admin-sidebar border-b border-white/10 bg-slate-950/80 px-4 py-4 backdrop-blur xl:min-h-screen xl:w-80 xl:border-b-0 xl:border-l xl:px-6 xl:py-8">
    <div class="flex items-center justify-between gap-4 xl:block">
        <div class="space-y-2">
            <div class="mt-2 flex min-w-0 items-center gap-2 overflow-hidden">
                @if (! empty($brandSettings['logo']->value))
                    <img
                        src="{{ asset('storage/' . $brandSettings['logo']->value) }}"
                        alt="??????"
                        class="h-6 w-6 shrink-0 object-contain"
                    >
                @endif

                <h1 class="min-w-0 truncate text-lg font-extrabold text-white">
                    {{ $brandSettings['name']->value ?? 'FlexBoots' }}
                </h1>
            </div>
        </div>

        <button
            type="button"
            class="inline-flex items-center justify-center border border-black bg-white px-3 py-3 text-black xl:hidden"
            data-sidebar-close
            aria-label="????? ??????? ????????"
        >
            <i class="bx bx-x text-xl"></i>
        </button>
    </div>

    <nav class="mt-6 grid gap-2" aria-label="?????? ???????">
        <a href="{{ route('admin.dashboard') }}"
           class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
            <i class="bx bx-home admin-nav-icon" aria-hidden="true"></i>
            <span>لوحة التحكم</span>
            <small>نظرة عامة على المتجر</small>
        </a>

        @can('admins.view')
            <a href="{{ route('admin.admins.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.admins.*') ? 'is-active' : '' }}">
                <i class="bx bx-user admin-nav-icon" aria-hidden="true"></i>
                <span>المسؤولون</span>
                <small>إدارة حسابات الإدارة</small>
            </a>
        @endcan

        @can('roles.view')
            <a href="{{ route('admin.roles.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.roles.*') ? 'is-active' : '' }}">
                <i class="bx bx-shield admin-nav-icon" aria-hidden="true"></i>
                <span>الأدوار والصلاحيات</span>
                <small>التحكم في الوصول</small>
            </a>
        @endcan

        @can('settings.view')
            <a href="{{ route('admin.settings.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">
                <i class="bx bx-cog admin-nav-icon" aria-hidden="true"></i>
                <span>الإعدادات</span>
                <small>إعدادات المتجر العامة</small>
            </a>
        @endcan

        @can('pages.view')
            <a href="{{ route('admin.pages.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.pages.*') ? 'is-active' : '' }}">
                <i class="bx bx-file admin-nav-icon" aria-hidden="true"></i>
                <span>الصفحات</span>
                <small>محتوى الصفحات الثابتة</small>
            </a>
        @endcan

        @can('sliders.view')
            <a href="{{ route('admin.sliders.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.sliders.*') ? 'is-active' : '' }}">
                <i class="bx bx-slideshow admin-nav-icon" aria-hidden="true"></i>
                <span>السلايدر</span>
                <small>شرائح الصفحة الرئيسية</small>
            </a>
        @endcan

        @can('clients.view')
            <a href="{{ route('admin.clients.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.clients.*') ? 'is-active' : '' }}">
                <i class="bx bx-briefcase admin-nav-icon" aria-hidden="true"></i>
                <span>العملاء المميزون</span>
                <small>شعارات وآراء العملاء</small>
            </a>
        @endcan

        @can('categories.view')
            <a href="{{ route('admin.categories.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.categories.*') ? 'is-active' : '' }}">
                <i class="bx bx-category admin-nav-icon" aria-hidden="true"></i>
                <span>الأقسام</span>
                <small>تصنيفات المتجر</small>
            </a>
        @endcan

        @can('products.view')
            <a href="{{ route('admin.products.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.products.*') ? 'is-active' : '' }}">
                <i class="bx bx-package admin-nav-icon" aria-hidden="true"></i>
                <span>المنتجات</span>
                <small>إدارة المنتجات والمخزون</small>
            </a>
        @endcan

        @can('customers.view')
            <a href="{{ route('admin.customers.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.customers.*') ? 'is-active' : '' }}">
                <i class="bx bx-group admin-nav-icon" aria-hidden="true"></i>
                <span>العملاء</span>
                <small>حسابات العملاء والعناوين</small>
            </a>
        @endcan

        @can('orders.view')
            <a href="{{ route('admin.orders.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.orders.*') ? 'is-active' : '' }}">
                <i class="bx bx-receipt admin-nav-icon" aria-hidden="true"></i>
                <span>الطلبات</span>
                <small>متابعة الطلبات والدفع</small>
            </a>
        @endcan

        @can('carts.view')
            <a href="{{ route('admin.carts.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.carts.*') ? 'is-active' : '' }}">
                <i class="bx bx-cart admin-nav-icon" aria-hidden="true"></i>
                <span>السلات</span>
                <small>السلات النشطة والمتروكة</small>
            </a>
        @endcan

        @can('welcome_coupons.view')
            <a href="{{ route('admin.welcome-coupons.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.welcome-coupons.*') ? 'is-active' : '' }}">
                <i class="bx bx-purchase-tag admin-nav-icon" aria-hidden="true"></i>
                <span>كوبونات الترحيب</span>
                <small>العروض الترحيبية</small>
            </a>
        @endcan

        @can('coupons.view')
            <a href="{{ route('admin.coupons.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.coupons.*') ? 'is-active' : '' }}">
                <i class="bx bx-purchase-tag-alt admin-nav-icon" aria-hidden="true"></i>
                <span>الكوبونات</span>
                <small>أكواد الخصم العادية</small>
            </a>
        @endcan

        @can('contact_messages.view')
            <a href="{{ route('admin.contact-messages.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.contact-messages.*') ? 'is-active' : '' }}">
                <i class="bx bx-message-rounded-dots admin-nav-icon" aria-hidden="true"></i>
                <span>رسائل التواصل</span>
                <small>استفسارات ورسائل الزوار</small>
            </a>
        @endcan

        <a href="{{ route('admin.profile.edit') }}"
           class="admin-nav-link {{ request()->routeIs('admin.profile.*') ? 'is-active' : '' }}">
            <i class="bx bx-id-card admin-nav-icon" aria-hidden="true"></i>
            <span>الملف الشخصي</span>
            <small>بيانات الحساب وكلمة المرور</small>
        </a>
    </nav>
</aside>
