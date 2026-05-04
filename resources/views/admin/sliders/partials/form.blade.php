@csrf
@isset($method)
    @method($method)
@endisset

<div class="grid gap-6">
    <section class="admin-subcard space-y-4">
        <h2 class="text-lg font-bold text-white">النصوص المترجمة</h2>
        <div class="grid gap-4 lg:grid-cols-2">
            <label class="space-y-2">
                <span class="text-sm font-bold text-white">العنوان بالعربية</span>
                <input class="admin-input" type="text" name="title[ar]" value="{{ old('title.ar', isset($slider) ? $slider->getTranslation('title', 'ar', false) : '') }}">
            </label>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">العنوان بالإنجليزية</span>
                <input class="admin-input" type="text" name="title[en]" value="{{ old('title.en', isset($slider) ? $slider->getTranslation('title', 'en', false) : '') }}" dir="ltr">
            </label>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">العنوان الفرعي بالعربية</span>
                <input class="admin-input" type="text" name="subtitle[ar]" value="{{ old('subtitle.ar', isset($slider) ? $slider->getTranslation('subtitle', 'ar', false) : '') }}">
            </label>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">العنوان الفرعي بالإنجليزية</span>
                <input class="admin-input" type="text" name="subtitle[en]" value="{{ old('subtitle.en', isset($slider) ? $slider->getTranslation('subtitle', 'en', false) : '') }}" dir="ltr">
            </label>
        </div>
    </section>

    <section class="admin-subcard space-y-4">
        <h2 class="text-lg font-bold text-white">البيانات العامة</h2>
        <div class="grid gap-4 lg:grid-cols-2">
            <label class="space-y-2 lg:col-span-2">
                <span class="text-sm font-bold text-white">الرابط</span>
                <input class="admin-input" type="text" name="link" value="{{ old('link', $slider->link ?? '') }}">
            </label>

            <div class="space-y-2 lg:col-span-2">
                <span class="text-sm font-bold text-white">الصورة</span>
                @if (! empty($slider?->image))
                    <img class="h-24 w-40 border border-black/10 object-cover" src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->title }}">
                @endif
                <input class="admin-input" type="file" name="image" data-filepond {{ isset($slider) && $slider->exists ? '' : 'required' }}>
            </div>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">لون النص</span>
                <input class="admin-input h-12" type="color" name="text_color" value="{{ old('text_color', $slider->text_color ?? '#111111') }}" required>
            </label>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">لون خلفية الزر</span>
                <input class="admin-input h-12" type="color" name="button_background_color" value="{{ old('button_background_color', $slider->button_background_color ?? '#111111') }}" required>
            </label>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">لون نص الزر</span>
                <input class="admin-input h-12" type="color" name="button_text_color" value="{{ old('button_text_color', $slider->button_text_color ?? '#ffffff') }}" required>
            </label>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">شفافية الأوفرلاي في البداية</span>
                <input class="admin-input" type="number" name="overlay_opacity_start" min="0" max="1" step="0.05" value="{{ old('overlay_opacity_start', isset($slider) ? (float) ($slider->overlay_opacity_start ?? 0.90) : 0.90) }}" required>
            </label>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">شفافية الأوفرلاي في النهاية</span>
                <input class="admin-input" type="number" name="overlay_opacity_end" min="0" max="1" step="0.05" value="{{ old('overlay_opacity_end', isset($slider) ? (float) ($slider->overlay_opacity_end ?? 0.55) : 0.55) }}" required>
            </label>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">محاذاة النص أفقياً</span>
                <select class="admin-select" name="horizontal_align" required>
                    <option value="left" @selected(old('horizontal_align', $slider->horizontal_align ?? 'center') === 'left')>يسار</option>
                    <option value="center" @selected(old('horizontal_align', $slider->horizontal_align ?? 'center') === 'center')>وسط</option>
                    <option value="right" @selected(old('horizontal_align', $slider->horizontal_align ?? 'center') === 'right')>يمين</option>
                </select>
            </label>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">محاذاة النص رأسياً</span>
                <select class="admin-select" name="vertical_align" required>
                    <option value="top" @selected(old('vertical_align', $slider->vertical_align ?? 'center') === 'top')>أعلى</option>
                    <option value="center" @selected(old('vertical_align', $slider->vertical_align ?? 'center') === 'center')>وسط</option>
                    <option value="bottom" @selected(old('vertical_align', $slider->vertical_align ?? 'center') === 'bottom')>أسفل</option>
                </select>
            </label>

            <div class="space-y-2 lg:col-span-2">
                <span class="text-sm font-bold text-white">الحالة</span>
                <label class="flex items-center gap-3 border border-white/10 bg-slate-950/40 px-4 py-3 text-slate-200">
                    <input class="admin-checkbox" type="checkbox" name="is_active" value="1" @checked(old('is_active', $slider->is_active ?? true))>
                    الشريحة مفعلة وتظهر في الموقع
                </label>
            </div>
        </div>
    </section>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button class="admin-btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="admin-btn-secondary" href="{{ route('admin.sliders.index') }}">عودة</a>
</div>
