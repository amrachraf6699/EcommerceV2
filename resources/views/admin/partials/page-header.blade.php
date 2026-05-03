<div class="mb-6 flex flex-col gap-4 rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-slate-950/40">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-2">
            <div>
                <h1 class="text-2xl font-extrabold text-white sm:text-3xl">{{ $pageTitle ?? 'لوحة التحكم' }}</h1>
                @if (! empty($pageDescription))
                    <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-300">{{ $pageDescription }}</p>
                @endif
            </div>
        </div>

        @isset($headerActions)
            <div class="flex flex-wrap items-center gap-3">
                {!! $headerActions !!}
            </div>
        @endisset
    </div>
</div>
