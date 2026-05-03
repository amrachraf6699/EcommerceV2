@props([
    'title',
    'description',
])

<div {{ $attributes->class('rounded-[2rem] border border-dashed border-white/15 bg-white/5 p-6 text-center') }}>
    <div class="mx-auto max-w-xl space-y-3">
        <span class="inline-flex rounded-full border border-amber-300/30 bg-amber-300/10 px-3 py-1 text-xs font-bold text-amber-200">
            حالة فارغة
        </span>
        <h3 class="text-xl font-bold text-white">{{ $title }}</h3>
        <p class="text-sm leading-7 text-slate-300">{{ $description }}</p>
        @if (trim($slot) !== '')
            <div class="pt-2">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
