@php
  $pageTitle = trim((string) ($title ?? ($frontendBrand['name'] ?? config('app.name'))));
  $pageDescription = trim((string) ($metaDescription ?? $seoDescription ?? ''));
  $canonicalUrl = trim((string) ($canonicalUrl ?? url()->current()));
  $metaRobots = trim((string) ($metaRobots ?? 'index,follow'));
  $metaImage = trim((string) ($metaImage ?? $seoImage ?? ''));
  $metaImageAlt = trim((string) ($metaImageAlt ?? $pageTitle));
  $ogType = trim((string) ($ogType ?? 'website'));
  $twitterCard = trim((string) ($twitterCard ?? ($metaImage !== '' ? 'summary_large_image' : 'summary')));
  $structuredData = $structuredData ?? null;
@endphp
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $pageTitle }}</title>
@if (! empty($frontendBrand['logo_url']))
<link rel="icon" type="image/png" href="{{ $frontendBrand['logo_url'] }}">
<link rel="apple-touch-icon" href="{{ $frontendBrand['logo_url'] }}">
@endif
@if ($pageDescription !== '')
<meta name="description" content="{{ $pageDescription }}">
@endif
<meta name="robots" content="{{ $metaRobots }}">
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta property="og:site_name" content="{{ $frontendBrand['name'] ?? config('app.name') }}">
<meta property="og:locale" content="{{ app()->getLocale() === 'ar' ? 'ar_AR' : 'en_US' }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $pageTitle }}">
@if ($pageDescription !== '')
<meta property="og:description" content="{{ $pageDescription }}">
@endif
<meta property="og:url" content="{{ $canonicalUrl }}">
@if ($metaImage !== '')
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:image:alt" content="{{ $metaImageAlt }}">
@endif
<meta name="twitter:card" content="{{ $twitterCard }}">
<meta name="twitter:title" content="{{ $pageTitle }}">
@if ($pageDescription !== '')
<meta name="twitter:description" content="{{ $pageDescription }}">
@endif
@if ($metaImage !== '')
<meta name="twitter:image" content="{{ $metaImage }}">
@endif
@if ($structuredData)
<script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
@endif
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  (function () {
    const defaultTheme = @json(setting('brand.default_theme', 'dark') === 'light' ? 'light' : 'dark');
    document.documentElement.setAttribute('data-theme', defaultTheme);
  })();
</script>
@php
  $hasTopMarquee = filled(setting('brand.header_text_' . app()->getLocale()));
@endphp
<style>
  :root {
    --black:#0a0a0a;
    --white:#f5f5f0;
    --gray-dark:#1a1a1a;
    --gray-mid:#2d2d2d;
    --gray-light:#888;
    --white-rgb:245 245 240;
    --black-rgb:10 10 10;
    --shadow-rgb:0 0 0;
    --surface-rgb:26 26 26;
    --surface-alt-rgb:45 45 45;
    --overlay-rgb:0 0 0;
    --line-soft:rgb(var(--white-rgb) / .08);
    --line-mid:rgb(var(--white-rgb) / .15);
    --line-strong:rgb(var(--white-rgb) / .3);
    --text-soft:rgb(var(--white-rgb) / .5);
    --text-faint:rgb(var(--white-rgb) / .3);
    --panel-shadow:0 30px 60px rgb(var(--shadow-rgb) / .6);
    --panel-shadow-soft:0 20px 40px rgb(var(--shadow-rgb) / .5);
    --backdrop-nav:rgb(var(--black-rgb) / .92);
    --backdrop-nav-strong:rgb(var(--black-rgb) / .98);
    --noise-opacity:.4;
    --top-marquee-offset:{{ $hasTopMarquee ? '42px' : '0px' }};
  }

  html[data-theme="light"] {
    --black:#f4f1ea;
    --white:#111111;
    --gray-dark:#ffffff;
    --gray-mid:#ece6dc;
    --gray-light:#6c6459;
    --white-rgb:17 17 17;
    --black-rgb:244 241 234;
    --shadow-rgb:17 17 17;
    --surface-rgb:255 255 255;
    --surface-alt-rgb:236 230 220;
    --overlay-rgb:244 241 234;
    --line-soft:rgb(var(--white-rgb) / .12);
    --line-mid:rgb(var(--white-rgb) / .18);
    --line-strong:rgb(var(--white-rgb) / .34);
    --text-soft:rgb(var(--white-rgb) / .62);
    --text-faint:rgb(var(--white-rgb) / .42);
    --panel-shadow:0 24px 42px rgb(var(--shadow-rgb) / .12);
    --panel-shadow-soft:0 18px 32px rgb(var(--shadow-rgb) / .10);
    --backdrop-nav:rgb(255 255 255 / .92);
    --backdrop-nav-strong:rgb(255 255 255 / .97);
    --noise-opacity:.16;
  }

  * { box-sizing:border-box; margin:0; padding:0; }
  body.frontend-template-body { font-family:'Cairo',sans-serif; background:var(--black); color:var(--white); overflow-x:hidden; transition:background-color .25s ease,color .25s ease; }
  body.frontend-template-body::before { content:''; position:fixed; inset:0; background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E"); pointer-events:none; z-index:9999; opacity:var(--noise-opacity); }
  a { color:inherit; text-decoration:none; }
  ::-webkit-scrollbar { width:4px; }
  ::-webkit-scrollbar-track { background:var(--black); }
  ::-webkit-scrollbar-thumb { background:var(--white); }
  .top-marquee { position:fixed; top:0; right:0; left:0; z-index:1001; height:42px; display:flex; align-items:center; border-bottom:1px solid var(--line-soft); background:linear-gradient(90deg,rgb(var(--white-rgb) / .08),rgb(var(--white-rgb) / .03)); backdrop-filter:blur(18px); overflow:hidden; }
  .top-marquee__label { flex:none; height:100%; display:inline-flex; align-items:center; justify-content:center; padding:0 18px; background:var(--white); color:var(--black); font-size:11px; font-weight:900; letter-spacing:.2em; text-transform:uppercase; }
  .top-marquee__track { position:relative; min-width:0; flex:1; height:100%; overflow:hidden; direction:ltr; }
  .top-marquee__content { position:absolute; top:50%; inset-inline-start:0; width:max-content; display:inline-flex; align-items:center; gap:54px; padding-inline:28px; white-space:nowrap; font-size:12px; font-weight:700; color:var(--white); will-change:transform; animation:top-marquee-scroll-rtl 14s linear infinite; transform:translate3d(100vw,-50%,0); }
  .top-marquee__content span { position:relative; display:inline-flex; align-items:center; gap:12px; }
  .top-marquee__content span::before { content:''; width:6px; height:6px; background:var(--white); border-radius:999px; opacity:.7; }
  .navbar { position:fixed; top:var(--top-marquee-offset); right:0; left:0; z-index:1000; background:var(--backdrop-nav); backdrop-filter:blur(20px); border-bottom:1px solid var(--line-soft); }
  @keyframes top-marquee-scroll-rtl { from { transform:translate3d(-100%,-50%,0); } to { transform:translate3d(100vw,-50%,0); } }
  @keyframes top-marquee-scroll-ltr { from { transform:translate3d(100vw,-50%,0); } to { transform:translate3d(-100%,-50%,0); } }
  .collection-scroller { display:flex; gap:40px; overflow-x:auto; overflow-y:hidden; padding:0 0 18px; margin-top:18px; scrollbar-width:auto; scrollbar-color:var(--white) var(--black); }
  .collection-scroller::-webkit-scrollbar:horizontal { height:18px; }
  .collection-scroller::-webkit-scrollbar-track:horizontal { background:var(--black); border:1px solid var(--line-mid); border-radius:0; }
  .collection-scroller::-webkit-scrollbar-button:horizontal,
  .collection-scroller::-webkit-scrollbar-button:horizontal:start:decrement,
  .collection-scroller::-webkit-scrollbar-button:horizontal:end:increment,
  .collection-scroller::-webkit-scrollbar-button:horizontal:single-button,
  .collection-scroller::-webkit-scrollbar-button:horizontal:double-button,
  .collection-scroller::-webkit-scrollbar-button:horizontal:single-button:start,
  .collection-scroller::-webkit-scrollbar-button:horizontal:single-button:end,
  .collection-scroller::-webkit-scrollbar-button:horizontal:double-button:start,
  .collection-scroller::-webkit-scrollbar-button:horizontal:double-button:end {
    -webkit-appearance:none;
    appearance:none;
    display:none;
    width:0 !important;
    min-width:0;
    max-width:0;
    height:0 !important;
    min-height:0;
    max-height:0;
    margin:0;
    padding:0;
    border:0;
    background:transparent;
  }
  .collection-scroller::-webkit-scrollbar-thumb:horizontal { background:var(--white); border:0; border-radius:0; }
  .collection-scroller::-webkit-scrollbar-thumb:horizontal:hover { background:rgb(var(--white-rgb) / .82); }
  .collection-scroller::-webkit-scrollbar-corner { background:transparent; }
  .collection-scroller > * { flex:0 0 auto; }
  .category-scroller__item { width:min(58vw, 190px); }
  .product-scroller__item { width:min(30vw, 116px); }
  .client-scroller__item { width:min(34vw, 148px); }
  .product-scroller__item .product-card,.product-masonry__item .product-card { height:100%; }
  .client-card { border:1px solid var(--line-soft); background:var(--gray-dark); transition:all .4s cubic-bezier(.4,0,.2,1); height:100%; }
  .client-card:hover { border-color:rgb(var(--white-rgb) / .28); transform:translateY(-6px); box-shadow:var(--panel-shadow); }
  .client-card__media { position:relative; overflow:hidden; aspect-ratio:1 / 1; background:linear-gradient(135deg,rgb(var(--surface-rgb)) 0%,rgb(var(--surface-alt-rgb)) 100%); }
  .client-card__media--compact { aspect-ratio:1 / 1; }
  .client-card__media img { width:100%; height:100%; object-fit:cover; transition:transform .6s cubic-bezier(.4,0,.2,1); }
  .client-card:hover .client-card__media img { transform:scale(1.06); }
  .client-masonry { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
  .client-masonry__item--featured { grid-column:span 2; }
  .client-masonry__item--featured .client-card__media { min-height:320px; }
  .client-masonry__item--tall .client-card__media { min-height:280px; }
  .product-masonry { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
  .product-masonry__item--featured { grid-column:span 2; }
  .product-masonry__item--featured .product-img-wrap { min-height:360px; }
  .nav-link { position:relative; font-weight:600; letter-spacing:.05em; transition:color .3s; color:var(--gray-light); }
  .nav-link::after { content:''; position:absolute; bottom:-2px; inset-inline-end:0; width:0; height:1px; background:var(--white); transition:width .3s ease; }
  .nav-link:hover::after,.nav-link.is-active::after { width:100%; }
  .nav-link:hover,.nav-link.is-active { color:var(--white); }
  .btn-primary { background:var(--white); color:var(--black); font-family:'Cairo',sans-serif; font-weight:800; letter-spacing:.05em; padding:14px 36px; border:2px solid var(--white); transition:all .3s ease; position:relative; overflow:hidden; display:inline-flex; align-items:center; justify-content:center; }
  .btn-primary::before { content:''; position:absolute; inset:0; background:var(--black); transform:translateX(101%); transition:transform .3s ease; }
  .btn-primary:hover { color:var(--white); }
  .btn-primary:hover::before { transform:translateX(0); }
  .btn-primary span { position:relative; z-index:1; }
  .btn-primary:disabled,.btn-outline:disabled,.btn-icon:disabled { opacity:.45; cursor:not-allowed; pointer-events:none; }
  .footer-meta { display:flex; flex-direction:column; gap:18px; padding-top:24px; border-top:1px solid var(--line-soft); color:var(--text-faint); font-size:14px; text-align:center; }
  .footer-meta p { margin:0; line-height:1.8; }
  .footer-meta__top,.footer-copyright,.footer-powered,.footer-payments,.footer-payment-icons { display:flex; align-items:center; justify-content:center; flex-wrap:wrap; }
  .footer-meta__top { width:100%; gap:10px 20px; }
  .footer-copyright { gap:8px 10px; }
  .footer-powered { gap:6px; color:var(--text-soft); font-size:14px; font-weight:600; }
  .footer-payments { width:100%; gap:10px 12px; justify-content:center; }
  .footer-payment-icons { gap:8px; }
  .payment-icon { width:38px; height:auto; flex:0 0 auto; border-radius:6px; box-shadow:0 0 0 1px var(--line-mid); background:#fff; }
  .footer-credit__link { color:var(--white); font-weight:800; text-decoration:underline; text-decoration-color:rgb(var(--white-rgb) / .42); text-underline-offset:4px; transition:color .2s ease,text-decoration-color .2s ease; }
  .footer-credit__link:hover,.footer-credit__link:focus-visible { color:var(--white); text-decoration-color:var(--white); }
  @media (min-width:768px) {
    .footer-meta { text-align:initial; }
    .footer-meta__top { justify-content:space-between; }
    .footer-copyright { justify-content:flex-start; }
    .footer-powered { justify-content:flex-end; }
  }
  .btn-outline { background:transparent; color:var(--white); font-family:'Cairo',sans-serif; font-weight:700; padding:12px 32px; border:1px solid var(--line-strong); transition:all .3s ease; position:relative; overflow:hidden; display:inline-flex; align-items:center; justify-content:center; }
  .btn-outline::before { content:''; position:absolute; inset:0; background:var(--white); transform:translateY(101%); transition:transform .3s ease; }
  .btn-outline:hover { color:var(--black); }
  .btn-outline:hover::before { transform:translateY(0); }
  .btn-outline span { position:relative; z-index:1; }
  .badge { position:absolute; top:12px; right:12px; background:var(--white); color:var(--black); font-size:11px; font-weight:800; padding:4px 10px; letter-spacing:.1em; z-index:2; }
  .badge--sold-out { background:#ff4d4d; color:#fff; }
  .divider { width:60px; height:2px; background:var(--white); margin-bottom:16px; }
  .product-card,.related-card,.mini-card { border:1px solid var(--line-soft); transition:all .4s cubic-bezier(.4,0,.2,1); position:relative; overflow:hidden; background:var(--gray-dark); }
  .product-card::before,.related-card::before,.mini-card::before { content:''; position:absolute; inset:0; background:linear-gradient(135deg,rgb(var(--white-rgb) / .03) 0%,transparent 60%); pointer-events:none; z-index:1; }
  .product-card:hover,.related-card:hover,.mini-card:hover { border-color:rgb(var(--white-rgb) / .28); transform:translateY(-6px); box-shadow:var(--panel-shadow); }
  .product-ground-type { display:inline-flex; align-items:center; width:max-content; max-width:100%; margin-bottom:10px; padding:5px 9px; border:1px solid var(--line-soft); color:var(--white); background:rgb(var(--white-rgb) / .05); font-size:11px; font-weight:800; line-height:1.2; }
  .product-ground-type--compact { margin-bottom:6px; padding:4px 7px; font-size:10px; }
  .product-ground-type--detail { gap:8px; margin-top:14px; margin-bottom:0; padding:8px 12px; font-size:12px; }
  .product-ground-type--detail span { color:var(--gray-light); font-weight:700; }
  .product-ground-type--detail strong { color:var(--white); font-weight:900; }
  .product-img-wrap,.related-card-img,.mini-card-media { overflow:hidden; position:relative; z-index:2; display:flex; align-items:center; justify-content:center; background:var(--gray-dark); }
  .product-img-wrap img,.related-card-img img,.mini-card-media img { width:100%; height:100%; object-fit:contain; object-position:center; transition:transform .6s cubic-bezier(.4,0,.2,1); }
  .cat-hero img,.main-img-wrap img,.thumb img { width:100%; height:100%; object-fit:cover; transition:transform .6s cubic-bezier(.4,0,.2,1); }
  .product-card:hover .product-img-wrap img,.related-card:hover .related-card-img img,.mini-card:hover .mini-card-media img,.cat-hero:hover img,.thumb:hover img { transform:scale(1.06); }
  .compact-card-media{display:flex;align-items:center;justify-content:center;padding:8px;}
  .compact-card-media__image{
    width:100%;
    height:100%;
    object-fit:contain;
    object-position:center;
    transition:transform .35s cubic-bezier(.4,0,.2,1);
  }
  .category-scroller__item:hover .compact-card-media__image,
  .product-scroller__item:hover .compact-card-media__image{transform:scale(1.03);}
  .product-overlay { position:absolute; bottom:0; right:0; left:0; background:var(--black); padding:12px; transform:translateY(100%); transition:transform .3s ease; display:flex; gap:8px; z-index:3; }
  .product-card:hover .product-overlay,.mini-card:hover .product-overlay { transform:translateY(0); }
  .category-scroller .category-compact-card { text-align:center; }
  .category-scroller .category-compact-card,
  .category-scroller .category-compact-card > div,
  .category-scroller .category-compact-card__media { border:0; background:transparent; box-shadow:none; }
  .category-scroller .category-compact-card__media { position:relative; }
  .category-scroller .category-compact-card__media::after,
  .category-scroller .category-compact-card__overlay-title { display:none; }
  .category-scroller .category-compact-card__body { display:block; padding-top:10px; text-align:center; }
  .category-scroller .category-compact-card__body h2 { font-size:13px; line-height:1.5; font-weight:800; text-align:center; margin:0; }
  .cat-hero { position:relative; overflow:hidden; border:1px solid var(--line-soft); transition:all .5s ease; }
  .cat-hero::after { content:''; position:absolute; inset:0; background:linear-gradient(to top,rgb(var(--overlay-rgb) / .95) 0%,rgb(var(--overlay-rgb) / .45) 50%,transparent 100%); }
  .cat-hero:hover { border-color:rgb(var(--white-rgb) / .35); box-shadow:var(--panel-shadow); }
  .cat-tag { position:absolute; top:20px; right:20px; z-index:10; background:var(--white); color:var(--black); font-size:11px; font-weight:900; padding:6px 14px; letter-spacing:.1em; }
  .cat-hero-content { position:absolute; bottom:0; right:0; left:0; z-index:10; padding:32px; transition:transform .4s ease; }
  .cat-hero:hover .cat-hero-content { transform:translateY(-8px); }
  .filter-btn,.sort-select,.search-input,.input-field,.size-btn,.tab-btn,.view-toggle,.theme-toggle { font-family:'Cairo',sans-serif; }
  .filter-btn { border:1px solid var(--line-mid); padding:8px 18px; font-size:13px; font-weight:600; color:var(--gray-light); transition:all .2s; background:transparent; }
  .filter-btn:hover,.filter-btn.active,.view-toggle.active,.size-btn.active,.tab-btn.active { background:var(--white); color:var(--black); border-color:var(--white); }
  .search-input,.input-field,.sort-select { background:rgb(var(--white-rgb) / .04); border:1px solid var(--line-mid); color:var(--white); outline:none; }
  .search-input,.input-field { width:100%; padding:14px 16px; direction:inherit; }
  .sort-select { padding:10px 16px; width:100%; }
  .country-select {
    appearance:none;
    -webkit-appearance:none;
    -moz-appearance:none;
    min-height:52px;
    padding-inline-end:48px;
    background-color:rgb(var(--white-rgb) / .04);
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%23f5f5f0' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 16px center;
    background-size:18px;
  }
  .country-select:focus {
    border-color:var(--white);
    box-shadow:0 0 0 3px rgb(var(--white-rgb) / .08);
  }
  .country-select option {
    background:var(--gray-dark);
    color:var(--white);
  }
  html[data-theme="light"] .country-select {
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%23111111' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  }
  html[dir="ltr"] .country-select {
    background-position:left 16px center;
  }
  .checkbox-custom { width:18px; height:18px; border:1px solid var(--line-strong); background:transparent; appearance:none; -webkit-appearance:none; transition:all .2s; flex-shrink:0; }
  .checkbox-custom:checked { background:var(--white); border-color:var(--white); }
  .view-toggle,.btn-icon,.social-icon,.theme-toggle,.locale-toggle { border:1px solid var(--line-mid); transition:all .2s; color:var(--gray-light); }
  .view-toggle { padding:8px 10px; background:transparent; }
  .social-icon,.btn-icon { width:42px; height:42px; display:flex; align-items:center; justify-content:center; }
  .social-icon:hover,.btn-icon:hover,.locale-toggle:hover { background:var(--white); color:var(--black); border-color:var(--white); }
  .modal-overlay,.zoom-overlay,.mystery-popup { position:fixed; inset:0; background:rgb(var(--overlay-rgb) / .85); backdrop-filter:blur(10px); z-index:5000; display:none; align-items:center; justify-content:center; }
  .modal-overlay.open,.zoom-overlay.open,.mystery-popup.show { display:flex; }
  .modal-box,.mystery-box { background:var(--gray-dark); border:1px solid var(--line-mid); width:90%; max-width:480px; position:relative; box-shadow:var(--panel-shadow-soft); }
  .mystery-box { max-width:560px; overflow:hidden; }
  .main-img-wrap { position:relative; overflow:hidden; background:linear-gradient(135deg,rgb(var(--surface-rgb)) 0%,rgb(var(--surface-alt-rgb)) 100%); border:1px solid var(--line-soft); }
  .thumb { border:1px solid var(--line-soft); overflow:hidden; transition:all .2s; background:rgb(var(--surface-rgb)); aspect-ratio:1; }
  .thumb.active { border-color:var(--white); }
  .size-btn { border:1px solid var(--line-mid); padding:12px 8px; font-weight:700; font-size:14px; color:var(--gray-light); transition:all .2s; background:transparent; text-align:center; }
  .size-btn.unavailable { opacity:.25; text-decoration:line-through; cursor:not-allowed; }
  .size-btn.unavailable.active { opacity:.6; border-color:var(--white); color:var(--white); }
  .color-btn { width:36px; height:36px; border:2px solid transparent; transition:all .2s; position:relative; }
  .color-btn.active { border-color:var(--white); }
  .color-btn::after { content:''; position:absolute; inset:3px; background:inherit; }
  .tab-btn { padding:14px 0; font-weight:700; font-size:14px; color:var(--gray-light); border-bottom:2px solid transparent; transition:all .2s; background:none; border-top:none; border-right:none; border-left:none; }
  .tab-content { display:none; }
  .tab-content.active { display:block; }
  .footer-link { color:var(--text-soft); transition:color .3s; font-size:14px; }
  .footer-link:hover { color:var(--white); }
  .price-display { display:flex; flex-direction:column; gap:8px; }
  .price-display__conversion { display:inline-flex; flex-direction:column; align-items:flex-start; gap:4px; width:max-content; max-width:100%; padding:8px 12px; border:1px solid rgb(var(--white-rgb) / .08); background:linear-gradient(135deg, rgb(var(--white-rgb) / .08), rgb(var(--white-rgb) / .03)); box-shadow:0 10px 22px rgb(var(--shadow-rgb) / .08); }
  .price-display__secondary { font-size:.82rem; font-weight:800; color:var(--white); line-height:1.2; }
  .product-card .price-display__conversion { padding:7px 10px; }
  .navbar-cart-menu .price-display__conversion { align-items:flex-end; margin-top:6px; padding:6px 10px; }
  .sticky-bar .price-display__conversion { padding:6px 10px; }
  html[data-theme="light"] .price-display__conversion { background:linear-gradient(135deg, rgb(var(--white-rgb) / .05), rgb(var(--white-rgb) / .02)); box-shadow:0 10px 20px rgb(var(--shadow-rgb) / .05); }
  .account-nav-link { display:flex; align-items:center; width:100%; min-height:48px; padding:0 16px; color:var(--gray-light); font-weight:700; background:transparent; border:0; transition:background-color .2s ease,color .2s ease,transform .2s ease; }
  .account-nav-link:hover,.account-nav-link.is-active { background:var(--white); color:var(--black); transform:translateX(-2px); }
  .mobile-account-dropdown { border:1px solid var(--line-soft); background:rgb(var(--white-rgb) / .03); }
  .mobile-account-trigger { width:100%; display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 18px; font-size:1.1rem; font-weight:700; color:var(--white); background:transparent; border:0; }
  .mobile-account-trigger > span { white-space:nowrap; }
  .mobile-account-trigger__icon { transition:transform .2s ease; }
  .mobile-account-trigger[aria-expanded="true"] .mobile-account-trigger__icon { transform:rotate(180deg); }
  .mobile-account-menu { display:none; border-top:1px solid var(--line-soft); }
  .mobile-account-menu.open { display:block; }
  .mobile-account-menu__link { display:flex; align-items:center; min-height:52px; padding:0 18px; color:var(--gray-light); font-weight:700; white-space:nowrap; transition:background-color .2s ease,color .2s ease; }
  .mobile-account-menu__link:hover { background:var(--white); color:var(--black); }
  .count-badge { background:var(--white); color:var(--black); font-size:10px; font-weight:900; width:18px; height:18px; display:flex; align-items:center; justify-content:center; position:absolute; top:-4px; left:-4px; }
  .navbar-cart-dropdown { position:relative; display:inline-flex; }
  .navbar-cart-trigger { width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center; border:none; background:transparent; transition:all .2s ease; }
  .navbar-cart-trigger:hover,.navbar-cart-trigger[aria-expanded="true"] { background:var(--white); color:var(--black)!important; }
  .navbar-cart-menu { position:absolute; top:calc(100% + 12px); inset-inline-end:0; width:min(320px,calc(100vw - 32px)); display:none; border:1px solid var(--line-soft); background:rgb(var(--black-rgb) / .98); box-shadow:var(--panel-shadow-soft); z-index:1100; }
  .navbar-cart-menu.open { display:block; }
  .navbar-cart-menu__content { padding:18px; display:flex; flex-direction:column; gap:14px; }
  .navbar-cart-menu__eyebrow { font-size:11px; font-weight:900; letter-spacing:.14em; color:var(--gray-light); text-transform:uppercase; }
  .navbar-cart-menu__row { display:flex; align-items:center; justify-content:space-between; gap:12px; color:var(--gray-light); font-size:14px; }
  .navbar-cart-menu__row strong { color:var(--white); font-size:15px; }
  .navbar-cart-menu__empty { color:var(--gray-light); font-size:13px; line-height:1.7; }
  .navbar-cart-menu__cta { width:100%; }
  .navbar-cart-menu__cta.is-disabled { opacity:.45; pointer-events:none; }
  .checkout-label,.checkout-eyebrow { letter-spacing:.12em; color:var(--gray-light); text-transform:uppercase; }
  .checkout-notice { border:1px solid var(--line-mid); padding:1rem 1.25rem; font-size:.95rem; line-height:1.8; background:rgb(var(--white-rgb) / .04); color:var(--white); }
  .checkout-notice--error { border-color:rgb(255 120 120 / .35); background:rgb(255 120 120 / .08); color:#ffb8b8; }
  .checkout-notice--maintenance { border-color:var(--line-mid); background:linear-gradient(135deg, rgb(var(--white-rgb) / .06), rgb(var(--white-rgb) / .025)); color:var(--white); }
  html[data-theme="light"] .checkout-notice--maintenance { border-color:rgb(var(--white-rgb) / .14); background:rgb(var(--white-rgb) / .035); color:rgb(var(--white-rgb) / .82); }
  .checkout-textarea { min-height:120px; resize:vertical; }
  .checkout-item { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding-bottom:16px; border-bottom:1px solid var(--line-soft); }
  .checkout-item:last-child { padding-bottom:0; border-bottom:0; }
  .checkout-error { color:#ffb2b2; }
  .navbar-actions-dropdown { position:relative; display:inline-flex; }
  .navbar-actions-trigger { width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--line-mid); background:transparent; color:var(--gray-light); transition:all .2s ease; }
  .navbar-actions-trigger:hover,.navbar-actions-trigger[aria-expanded="true"] { background:var(--white); color:var(--black); border-color:var(--white); }
  .navbar-actions-menu { position:absolute; top:calc(100% + 12px); inset-inline-end:0; min-width:190px; display:none; padding:10px 0; border:1px solid var(--line-soft); background:rgb(var(--black-rgb) / .98); box-shadow:var(--panel-shadow-soft); z-index:1100; }
  .navbar-actions-menu.open { display:block; }
  .navbar-actions-menu__link { width:100%; min-height:48px; padding:0 16px; display:flex; align-items:center; gap:12px; color:var(--gray-light); font-weight:700; background:transparent; border:0; text-align:start; white-space:nowrap; transition:background-color .2s ease,color .2s ease; }
  .navbar-actions-menu__link:hover { background:var(--white); color:var(--black); }
  .mobile-menu { position:fixed; top:0; right:-100%; width:85%; height:100vh; background:var(--black); border-left:1px solid var(--line-soft); z-index:2000; transition:right .4s cubic-bezier(.4,0,.2,1),left .4s cubic-bezier(.4,0,.2,1); padding:80px 32px 40px; }
  .mobile-menu.open { right:0; }
  .mobile-overlay { position:fixed; inset:0; background:rgb(var(--overlay-rgb) / .6); z-index:1999; display:none; }
  .mobile-overlay.open { display:block; }
  .reveal { opacity:0; transform:translateY(30px); }
  .sticky-bar { position:fixed; bottom:0; right:0; left:0; background:var(--gray-dark); border-top:1px solid var(--line-soft); padding:12px 16px; z-index:500; display:none; box-shadow:0 -12px 25px rgb(var(--shadow-rgb) / .14); }
  .toast { position:fixed; bottom:32px; left:50%; width:min(calc(100vw - 32px), 520px); max-width:520px; transform:translateX(-50%) translateY(100px); background:var(--white); color:var(--black); font-family:'Cairo',sans-serif; font-weight:700; padding:14px 20px; z-index:9000; transition:transform .4s cubic-bezier(.34,1.56,.64,1); white-space:normal; overflow-wrap:anywhere; word-break:break-word; text-align:center; line-height:1.6; box-shadow:0 18px 32px rgb(var(--shadow-rgb) / .24); }
  .toast.show { transform:translateX(-50%) translateY(0); }
  .whatsapp-float { position:fixed; left:24px; bottom:24px; width:58px; height:58px; display:inline-flex; align-items:center; justify-content:center; background:var(--white); color:var(--black); border:1px solid var(--line-mid); border-radius:0; box-shadow:0 16px 32px rgb(var(--shadow-rgb) / .24); z-index:1200; transition:transform .25s ease, box-shadow .25s ease, background-color .25s ease, color .25s ease, border-color .25s ease; }
  .whatsapp-float:hover { transform:translateY(-4px) scale(1.03); box-shadow:0 22px 40px rgb(var(--shadow-rgb) / .32); background:var(--black); color:var(--white); border-color:var(--line-strong); }
  .theme-toggle { display:inline-flex; align-items:center; gap:.55rem; padding:.7rem .95rem; background:transparent; font-size:.82rem; font-weight:700; }
  .theme-toggle:hover { background:var(--white); color:var(--black); border-color:var(--white); }
  .theme-toggle__icon { font-size:1rem; line-height:1; }
  .locale-toggle { display:inline-flex; align-items:center; justify-content:center; background:transparent; }
  html[dir="ltr"] .count-badge { right:-4px; left:auto; }
  html[dir="ltr"] .mobile-menu { right:auto; left:-100%; border-left:none; border-right:1px solid var(--line-soft); }
  html[dir="ltr"] .mobile-menu.open { left:0; }
  html[dir="ltr"] .whatsapp-float { left:auto; right:24px; }
  html[dir="ltr"] .cat-tag { right:auto; left:20px; }
  html[dir="ltr"] .badge { right:auto; left:12px; }
  html[dir="ltr"] .search-input,
  html[dir="ltr"] .input-field { direction:ltr; }
  html[dir="ltr"] .product-overlay,
  html[dir="ltr"] .cat-hero-content,
  html[dir="ltr"] .sticky-bar { left:0; right:0; }
  html[dir="ltr"] .top-marquee__content { animation-name:top-marquee-scroll-ltr; }
  @media(min-width:768px){ .category-scroller__item { width:220px; } .product-scroller__item { width:132px; } .client-scroller__item { width:172px; } .product-masonry { grid-template-columns:repeat(4,minmax(0,1fr)); } .product-masonry__item--featured { grid-column:span 2; grid-row:span 2; } .product-masonry__item--tall .product-img-wrap { min-height:320px; } .client-masonry { grid-template-columns:repeat(4,minmax(0,1fr)); } .client-masonry__item--featured { grid-column:span 2; grid-row:span 2; } .client-masonry__item--tall .client-card__media { min-height:320px; } }
  @media(max-width:768px){ .sticky-bar{display:flex;gap:10px;align-items:center;} .top-marquee__label{padding:0 12px;font-size:10px;} .top-marquee__content{gap:32px;padding-inline:18px;font-size:11px;} .whatsapp-float{left:16px;bottom:16px;width:52px;height:52px;} html[dir="ltr"] .whatsapp-float{left:auto;right:16px;} .category-scroller__item{width:min(64vw,190px);} .product-scroller__item{width:min(34vw,116px);} .client-scroller__item{width:min(40vw,148px);} .toast{bottom:20px;padding:12px 16px;font-size:14px;line-height:1.5;} .navbar-actions-menu{inset-inline-start:0;inset-inline-end:auto;min-width:180px;max-width:min(240px,calc(100vw - 32px));} html[dir="ltr"] .navbar-actions-menu{left:0;right:auto;} }
</style>
@stack('styles')
