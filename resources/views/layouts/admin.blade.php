<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'لوحة التحكم' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="admin-body">
    <div class="min-h-screen bg-slate-950/95 text-slate-50">
        <div class="mx-auto flex min-h-screen max-w-screen-2xl flex-col xl:flex-row">
            @include('admin.partials.sidebar')

            <div class="flex min-h-screen flex-1 flex-col">
                @include('admin.partials.topbar')

                <main class="flex-1 px-4 py-6 sm:px-6 xl:px-10 xl:py-8">
                    @include('admin.partials.alerts')
                    @include('admin.partials.page-header')

                    <section class="space-y-6">
                        @yield('content')
                    </section>
                </main>
            </div>
        </div>
    </div>

    <div data-sidebar-overlay class="admin-sidebar-overlay xl:hidden"></div>
    <div id="confirm-modal-root"></div>
    @stack('scripts')
</body>
</html>
