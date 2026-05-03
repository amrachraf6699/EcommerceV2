@php
    $placement = $placement ?? 'head';
    $analyticsSettings = setting_group('analytics')->keyBy('key');
    $analytics = static fn (string $key): ?string => filled(data_get($analyticsSettings, "{$key}.value"))
        ? trim((string) data_get($analyticsSettings, "{$key}.value"))
        : null;

    $googleAnalyticsMeasurementId = $analytics('google_analytics_measurement_id');
    $googleTagManagerId = $analytics('google_tag_manager_id');
    $googleSearchConsoleVerificationId = $analytics('google_search_console_verification_id');
    $googleAdsConversionId = $analytics('google_ads_conversion_id');
    $googleAdsConversionLabel = $analytics('google_ads_conversion_label');
    $facebookPixelId = $analytics('facebook_pixel_id');
    $metaDomainVerificationId = $analytics('meta_domain_verification_id');
    $tiktokPixelId = $analytics('tiktok_pixel_id');
    $snapchatPixelId = $analytics('snapchat_pixel_id');
    $pinterestTagId = $analytics('pinterest_tag_id');
    $microsoftClarityProjectId = $analytics('microsoft_clarity_project_id');
    $bingUetTagId = $analytics('bing_uet_tag_id');
@endphp

@if ($placement === 'head')
    @if ($googleSearchConsoleVerificationId)
        <meta name="google-site-verification" content="{{ $googleSearchConsoleVerificationId }}">
    @endif

    @if ($metaDomainVerificationId)
        <meta name="facebook-domain-verification" content="{{ $metaDomainVerificationId }}">
    @endif

    @if ($googleTagManagerId)
        <script>
            (function (w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s),
                    dl = l !== 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', @json($googleTagManagerId));
        </script>
    @endif

    @if ($googleAnalyticsMeasurementId || $googleAdsConversionId)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $googleAnalyticsMeasurementId ?: $googleAdsConversionId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() { dataLayer.push(arguments); }
            gtag('js', new Date());
            @if ($googleAnalyticsMeasurementId)
                gtag('config', @json($googleAnalyticsMeasurementId));
            @endif
            @if ($googleAdsConversionId)
                gtag('config', @json($googleAdsConversionId));
                @if ($googleAdsConversionLabel)
                    gtag('set', 'ads_data_redaction', false);
                @endif
            @endif
        </script>
    @endif

    @if ($facebookPixelId)
        <script>
            !function (f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function () {
                    n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = true;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = true;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s);
            }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', @json($facebookPixelId));
            fbq('track', 'PageView');
        </script>
    @endif

    @if ($tiktokPixelId)
        <script>
            !function (w, d, t) {
                w.TiktokAnalyticsObject = t;
                var ttq = w[t] = w[t] || [];
                ttq.methods = ['page', 'track', 'identify', 'instances', 'debug', 'on', 'off', 'once', 'ready', 'alias', 'group', 'enableCookie', 'disableCookie'];
                ttq.setAndDefer = function (target, method) {
                    target[method] = function () {
                        target.push([method].concat([].slice.call(arguments, 0)));
                    };
                };
                for (var i = 0; i < ttq.methods.length; i++) {
                    ttq.setAndDefer(ttq, ttq.methods[i]);
                }
                ttq.load = function (id) {
                    var s = d.createElement('script');
                    s.async = true;
                    s.src = 'https://analytics.tiktok.com/i18n/pixel/events.js?sdkid=' + id + '&lib=' + t;
                    var e = d.getElementsByTagName('script')[0];
                    e.parentNode.insertBefore(s, e);
                };
                ttq.load(@json($tiktokPixelId));
                ttq.page();
            }(window, document, 'ttq');
        </script>
    @endif

    @if ($snapchatPixelId)
        <script>
            (function (e, t, n) {
                if (e.snaptr) return;
                var a = e.snaptr = function () {
                    a.handleRequest ? a.handleRequest.apply(a, arguments) : a.queue.push(arguments);
                };
                a.queue = [];
                var s = 'script';
                var r = t.createElement(s);
                r.async = true;
                r.src = n;
                var u = t.getElementsByTagName(s)[0];
                u.parentNode.insertBefore(r, u);
            })(window, document, 'https://sc-static.net/scevent.min.js');
            snaptr('init', @json($snapchatPixelId));
            snaptr('track', 'PAGE_VIEW');
        </script>
    @endif

    @if ($pinterestTagId)
        <script>
            !function (e) {
                if (!window.pintrk) {
                    window.pintrk = function () {
                        window.pintrk.queue.push(Array.prototype.slice.call(arguments));
                    };
                    var n = window.pintrk;
                    n.queue = [];
                    n.version = '3.0';
                    var t = document.createElement('script');
                    t.async = true;
                    t.src = e;
                    var r = document.getElementsByTagName('script')[0];
                    r.parentNode.insertBefore(t, r);
                }
            }('https://s.pinimg.com/ct/core.js');
            pintrk('load', @json($pinterestTagId));
            pintrk('page');
        </script>
    @endif

    @if ($microsoftClarityProjectId)
        <script>
            (function (c, l, a, r, i, t, y) {
                c[a] = c[a] || function () { (c[a].q = c[a].q || []).push(arguments); };
                t = l.createElement(r);
                t.async = 1;
                t.src = 'https://www.clarity.ms/tag/' + i;
                y = l.getElementsByTagName(r)[0];
                y.parentNode.insertBefore(t, y);
            })(window, document, 'clarity', 'script', @json($microsoftClarityProjectId));
        </script>
    @endif

    @if ($bingUetTagId)
        <script>
            (function (w, d, t, r, u) {
                var f, n, i;
                w[u] = w[u] || [];
                f = function () {
                    var o = { ti: @json($bingUetTagId) };
                    o.q = w[u];
                    w[u] = new UET(o);
                    w[u].push('pageLoad');
                };
                n = d.createElement(t);
                n.src = r;
                n.async = 1;
                n.onload = n.onreadystatechange = function () {
                    var s = this.readyState;
                    if (!s || s === 'loaded' || s === 'complete') {
                        f();
                        n.onload = n.onreadystatechange = null;
                    }
                };
                i = d.getElementsByTagName(t)[0];
                i.parentNode.insertBefore(n, i);
            })(window, document, 'script', 'https://bat.bing.com/bat.js', 'uetq');
        </script>
    @endif
@endif

@if ($placement === 'body-start' && $googleTagManagerId)
    <noscript>
        <iframe
            src="https://www.googletagmanager.com/ns.html?id={{ $googleTagManagerId }}"
            height="0"
            width="0"
            style="display:none;visibility:hidden"
        ></iframe>
    </noscript>
@endif
