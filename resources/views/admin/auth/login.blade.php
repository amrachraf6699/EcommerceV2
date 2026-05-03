<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>دخول الإدارة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    <main class="flex min-h-screen items-center justify-center text-center bg-slate-950 px-4 py-10">
        <section class="w-full max-w-lg border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/50 sm:p-8">
            <h1 class="mt-4 text-3xl font-extrabold text-white">تسجيل الدخول</h1>
            @if ($errors->any())
                <div class="mt-6 border border-rose-400/20 bg-rose-400/10 px-4 py-4 text-sm text-rose-100">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 grid gap-4" data-loading-form>
                @csrf

                <label class="grid gap-2 text-sm text-slate-200">
                    <input type="email"
                           name="email"
                           placeholder="admin@example.com"
                           value="{{ old('email') }}"
                           class="border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-amber-300/40">
                </label>

                <label class="grid gap-2 text-sm text-slate-200">
                    <input type="password"
                           name="password"
                           placeholder="********"
                           class="border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-amber-300/40">
                </label>

                <label class="inline-flex items-center gap-3 text-sm text-slate-300">
                    <input type="checkbox" name="remember" value="1" class="size-4 border-white/10 bg-slate-950/70 text-amber-300 focus:ring-amber-300/30">
                    <span>تذكر هذا الجهاز</span>
                </label>
                <button type="submit"
                        class="mt-2 inline-flex items-center justify-center border border-black bg-amber-400 px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-amber-300"
                        data-loading-label="جاري تسجيل الدخول...">
                        دخول
                </button>
            </form>
        </section>
    </main>
</body>
</html>
