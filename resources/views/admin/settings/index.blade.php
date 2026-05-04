@php
    $title = 'الإعدادات';
    $pageTitle = 'الإعدادات العامة';
    $breadcrumbs = ['الإدارة', 'الإعدادات'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="grid gap-6 xl:grid-cols-[280px_1fr]">
        <aside class="admin-card">
            <h2 class="text-xl font-bold text-white">مجموعات الإعدادات</h2>
            <div class="mt-4 grid gap-2">
                @foreach ($groups as $group => $settings)
                    <a href="{{ route('admin.settings.index', ['group' => $group]) }}"
                       class="admin-nav-link {{ $currentGroup === $group ? 'is-active' : '' }}">
                        <span>{{ \App\Support\AdminArabic::settingsGroup($group) }}
                            <span class="sr-only">{{ \Illuminate\Support\Str::headline($group) }}</span>
                            <i class="bx bx-cog admin-nav-icon" aria-hidden="true"></i>
                        </span>
                    </a>
                @endforeach
            </div>
        </aside>

        <section class="admin-card">
            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="group" value="{{ $currentGroup }}">

                <div class="grid gap-4">
                    @foreach ($currentSettings as $setting)
                        <label class="space-y-2">
                            <span class="text-sm font-bold text-white">{{ \App\Support\AdminArabic::settingsLabel($setting->key, $setting->label) }}</span>
                            @if ($setting->description)
                                <p class="text-xs text-slate-400">{{ \App\Support\AdminArabic::settingsDescription($setting->key, $setting->description) }}</p>
                            @endif

                            @if ($setting->input_type === 'textarea')
                                <textarea class="admin-textarea" name="{{ $setting->key }}" rows="4">{{ old($setting->key, $setting->value) }}</textarea>
                            @elseif ($setting->input_type === 'boolean' || $setting->key === 'enable_vat')
                                <span class="flex items-center gap-3 border border-white/10 bg-slate-950/40 px-4 py-3 text-slate-200">
                                    <input class="admin-checkbox" type="checkbox" name="{{ $setting->key }}" value="1" @checked(old($setting->key, $setting->value) == '1')>
                                    تفعيل هذا الخيار
                                </span>
                            @elseif ($setting->input_type === 'select')
                                <select class="admin-select" name="{{ $setting->key }}">
                                    <option value="">اختر</option>
                                    @foreach ($setting->options ?? [] as $option)
                                        <option value="{{ $option }}" @selected(old($setting->key, $setting->value) === $option)>{{ \Illuminate\Support\Str::headline($option) }}</option>
                                    @endforeach
                                </select>
                            @elseif ($setting->input_type === 'file')
                                <div class="space-y-3">
                                    <input class="admin-input" type="file" name="{{ $setting->key }}" data-filepond>
                                    @if ($setting->value)
                                        <span class="flex items-center gap-2">
                                            <span>الملف الحالي:</span>
                                            <img
                                                src="{{ asset('storage/' . $setting->value) }}"
                                                class="inline-block"
                                                alt="logo"
                                                style="height:40px"
                                            >
                                        </span>
                                    @endif
                                </div>
                            @elseif ($setting->input_type === 'color')
                                <div class="flex items-center gap-3">
                                    <input
                                        class="h-12 w-20 cursor-pointer border border-white/10 bg-slate-950/40 p-1"
                                        type="color"
                                        name="{{ $setting->key }}"
                                        value="{{ old($setting->key, $setting->value ?: '#000000') }}"
                                        oninput="this.nextElementSibling.value = this.value"
                                    >
                                    <input
                                        class="admin-input"
                                        type="text"
                                        value="{{ old($setting->key, $setting->value ?: '#000000') }}"
                                        readonly
                                        tabindex="-1"
                                    >
                                </div>
                            @else
                                <input class="admin-input" type="{{ in_array($setting->input_type, ['email', 'number', 'password'], true) ? $setting->input_type : 'text' }}"
                                       name="{{ $setting->key }}"
                                       value="{{ $setting->input_type === 'password' ? '' : old($setting->key, $setting->value) }}">
                            @endif
                        </label>
                    @endforeach
                </div>

                <div class="mt-6">
                    <button class="admin-btn-primary" type="submit">حفظ الإعدادات</button>
                </div>
            </form>
        </section>
    </section>
@endsection
