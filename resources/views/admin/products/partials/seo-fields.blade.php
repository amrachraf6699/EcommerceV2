<section class="admin-panel-section space-y-6">
    <section class="admin-subcard space-y-4">
        <h2 class="text-lg font-bold text-white">SEO</h2>

        <div class="grid gap-4 xl:grid-cols-2">
            <label class="space-y-2">
                <span class="text-sm font-bold text-white">عنوان SEO بالعربية</span>
                <input class="admin-input" type="text" name="meta_title[ar]" value="{{ old('meta_title.ar', $product?->getTranslation('meta_title', 'ar', false)) }}">
            </label>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">عنوان SEO بالإنجليزية</span>
                <input class="admin-input" type="text" name="meta_title[en]" value="{{ old('meta_title.en', $product?->getTranslation('meta_title', 'en', false)) }}" dir="ltr">
            </label>
        </div>

        <label class="space-y-2">
            <span class="text-sm font-bold text-white">وصف SEO بالعربية</span>
            <textarea class="admin-textarea" name="meta_description[ar]" rows="5">{{ old('meta_description.ar', $product?->getTranslation('meta_description', 'ar', false)) }}</textarea>
        </label>

        <label class="space-y-2">
            <span class="text-sm font-bold text-white">وصف SEO بالإنجليزية</span>
            <textarea class="admin-textarea" name="meta_description[en]" rows="5" dir="ltr">{{ old('meta_description.en', $product?->getTranslation('meta_description', 'en', false)) }}</textarea>
        </label>
    </section>
</section>
