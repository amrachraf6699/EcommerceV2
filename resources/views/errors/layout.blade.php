<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ function_exists('storefront_direction') ? storefront_direction() : 'rtl' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --black:#0a0a0a;
            --white:#f5f5f0;
            --gray-dark:#1a1a1a;
            --gray-mid:#2d2d2d;
            --gray-light:#888;
            --white-rgb:245 245 240;
            --black-rgb:10 10 10;
            --line-soft:rgb(var(--white-rgb) / .08);
            --line-mid:rgb(var(--white-rgb) / .18);
            --line-strong:rgb(var(--white-rgb) / .34);
            --panel-shadow:0 30px 60px rgb(0 0 0 / .45);
        }

        * { box-sizing:border-box; }
        body {
            margin:0;
            min-height:100vh;
            font-family:'Cairo',sans-serif;
            background:
                radial-gradient(circle at top left, rgb(var(--white-rgb) / .08), transparent 34%),
                radial-gradient(circle at bottom right, rgb(var(--white-rgb) / .06), transparent 28%),
                var(--black);
            color:var(--white);
            overflow-x:hidden;
        }

        body::before {
            content:'';
            position:fixed;
            inset:0;
            background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.035'/%3E%3C/svg%3E");
            pointer-events:none;
            opacity:.45;
        }

        a { color:inherit; text-decoration:none; }

        .error-shell {
            position:relative;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:32px 20px;
        }

        .error-card {
            position:relative;
            width:min(100%, 1120px);
            border:1px solid var(--line-soft);
            background:
                linear-gradient(135deg, rgb(var(--white-rgb) / .06), transparent 58%),
                var(--gray-dark);
            box-shadow:var(--panel-shadow);
            overflow:hidden;
        }

        .error-grid {
            display:grid;
            grid-template-columns:minmax(0, 1.15fr) minmax(280px, .85fr);
        }

        .error-main,
        .error-side {
            position:relative;
            z-index:1;
            padding:40px;
        }

        .error-side {
            border-inline-start:1px solid var(--line-soft);
            background:rgb(var(--white-rgb) / .02);
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            gap:28px;
        }

        .error-code {
            display:inline-flex;
            align-items:center;
            padding:8px 14px;
            border:1px solid var(--line-mid);
            font-size:11px;
            font-weight:900;
            letter-spacing:.28em;
            text-transform:uppercase;
            color:var(--gray-light);
        }

        .error-number {
            margin:22px 0 12px;
            font-size:clamp(88px, 14vw, 180px);
            line-height:.9;
            font-weight:900;
            letter-spacing:-.06em;
        }

        .error-title {
            margin:0 0 14px;
            font-size:clamp(30px, 5vw, 58px);
            line-height:1.02;
            font-weight:900;
        }

        .error-copy {
            max-width:620px;
            color:var(--gray-light);
            font-size:16px;
            line-height:1.9;
        }

        .error-actions {
            display:flex;
            flex-wrap:wrap;
            gap:14px;
            margin-top:30px;
        }

        .btn-primary,
        .btn-outline {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            min-height:52px;
            padding:0 26px;
            font-weight:800;
            letter-spacing:.06em;
            transition:all .25s ease;
        }

        .btn-primary {
            background:var(--white);
            color:var(--black);
            border:2px solid var(--white);
        }

        .btn-primary:hover {
            transform:translateY(-2px);
            box-shadow:0 16px 28px rgb(0 0 0 / .25);
        }

        .btn-outline {
            border:1px solid var(--line-strong);
            color:var(--white);
            background:transparent;
        }

        .btn-outline:hover {
            background:var(--white);
            color:var(--black);
        }

        .error-kicker {
            font-size:12px;
            font-weight:800;
            letter-spacing:.22em;
            text-transform:uppercase;
            color:var(--gray-light);
        }

        .error-list {
            display:grid;
            gap:14px;
        }

        .error-list__item {
            border:1px solid var(--line-soft);
            background:rgb(var(--white-rgb) / .03);
            padding:16px 18px;
        }

        .error-list__item strong {
            display:block;
            margin-bottom:6px;
            font-size:14px;
        }

        .error-list__item span {
            color:var(--gray-light);
            font-size:14px;
            line-height:1.8;
        }

        .error-brand {
            display:inline-flex;
            align-items:center;
            gap:12px;
            font-size:15px;
            font-weight:900;
            letter-spacing:.08em;
            text-transform:uppercase;
        }

        .error-brand__mark {
            width:42px;
            height:42px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            border:1px solid var(--line-mid);
            background:rgb(var(--white-rgb) / .04);
            font-size:18px;
        }

        @media (max-width: 900px) {
            .error-grid { grid-template-columns:1fr; }
            .error-side { border-inline-start:0; border-top:1px solid var(--line-soft); }
            .error-main, .error-side { padding:28px; }
        }
    </style>
</head>
<body>
    @php($homeUrl = route('storefront.home', ['locale' => app()->getLocale() ?: config('storefront.default_locale', 'ar')]))
    <main class="error-shell">
        <section class="error-card">
            <div class="error-grid">
                <div class="error-main">
                    <div class="error-number">{{ $statusCode }}</div>
                    <h1 class="error-title">{{ $heading }}</h1>
                    <p class="error-copy">{{ $copy }}</p>

                    <div class="error-actions">
                        <a href="{{ $homeUrl }}" class="btn-primary">{{ $primaryAction ?? 'Back Home' }}</a>
                        <a href="javascript:history.back()" class="btn-outline">{{ $secondaryAction ?? 'Go Back' }}</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
