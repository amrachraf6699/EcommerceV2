<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ storefront_direction() }}">
<head>
    @include('frontend.partials.head')
    @include('frontend.partials.analytics', ['placement' => 'head'])
</head>
<body class="frontend-template-body">
    @include('frontend.partials.analytics', ['placement' => 'body-start'])

    @include('frontend.partials.top-marquee')
    @include('frontend.partials.navbar')
    @include('frontend.partials.mobile-menu')

    <div class="mt-6">
        @yield('content')
    </div>
    @if (! request()->routeIs('storefront.products.show') && ! empty($frontendBrand['whatsapp_phone']))
        @include('frontend.partials.whatsapp-float')
    @endif

    @if (! request()->routeIs('storefront.products.show') && $frontendChatbotEnabled)
        @include('frontend.partials.chatbot')
    @endif

    @include('frontend.partials.footer')
    @include('frontend.partials.login-modal')

    @if (request()->routeIs('storefront.home') && setting_bool('marketing.welcome_coupon_enabled'))
        @include('frontend.partials.welcome-popup')
    @endif

    <div class="toast" id="globalToast"></div>

    @stack('modals')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollToPlugin.min.js"></script>
    @include('frontend.partials.base-scripts')
    @stack('scripts')
</body>
</html>
