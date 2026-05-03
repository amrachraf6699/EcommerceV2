@csrf
@isset($method)
    @method($method)
@endisset

<div class="grid gap-6">
    <section class="admin-subcard space-y-4">
        <h2 class="text-lg font-bold text-white">البيانات المترجمة</h2>
        <div class="grid gap-4">
            <div class="grid gap-4 lg:grid-cols-2">
                <label class="space-y-2">
                    <span class="text-sm font-bold text-white">عنوان الصفحة بالعربية</span>
                    <input class="admin-input" type="text" name="title[ar]" value="{{ old('title.ar', isset($page) ? $page->getTranslation('title', 'ar', false) : '') }}" required>
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-bold text-white">عنوان الصفحة بالإنجليزية</span>
                    <input class="admin-input" type="text" name="title[en]" value="{{ old('title.en', isset($page) ? $page->getTranslation('title', 'en', false) : '') }}" required dir="ltr">
                </label>
            </div>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">المحتوى بالعربية</span>
                <textarea class="admin-textarea" name="content[ar]" rows="16" data-rich-text>{{ old('content.ar', isset($page) ? $page->getTranslation('content', 'ar', false) : '') }}</textarea>
            </label>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">المحتوى بالإنجليزية</span>
                <textarea class="admin-textarea" name="content[en]" rows="16" data-rich-text dir="ltr">{{ old('content.en', isset($page) ? $page->getTranslation('content', 'en', false) : '') }}</textarea>
            </label>
        </div>
    </section>

    <section class="admin-subcard space-y-4">
        <h2 class="text-lg font-bold text-white">البيانات العامة</h2>
        <label class="space-y-2">
            <span class="text-sm font-bold text-white">الرابط</span>
            <input class="admin-input" type="text" name="slug" value="{{ old('slug', $page->slug ?? '') }}" placeholder="يتم توليده تلقائياً من العنوان العربي">
        </label>
    </section>
</div>

<div class="mt-6 flex gap-3">
    <button class="admin-btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="admin-btn-secondary" href="{{ route('admin.pages.index') }}">عودة</a>
</div>
