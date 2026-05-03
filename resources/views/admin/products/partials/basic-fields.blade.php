<section class="admin-panel-section space-y-6">
    <section class="admin-subcard space-y-4">
        <h2 class="text-lg font-bold text-white">البيانات المترجمة</h2>

        <div class="grid gap-4 xl:grid-cols-2">
            <label class="space-y-2">
                <span class="text-sm font-bold text-white">اسم المنتج بالعربية</span>
                <input class="admin-input" type="text" name="name[ar]" value="{{ old('name.ar', $product?->getTranslation('name', 'ar', false)) }}" required>
            </label>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">اسم المنتج بالإنجليزية</span>
                <input class="admin-input" type="text" name="name[en]" value="{{ old('name.en', $product?->getTranslation('name', 'en', false)) }}" required dir="ltr">
            </label>
        </div>

        <label class="space-y-2">
            <span class="text-sm font-bold text-white">وصف مختصر بالعربية</span>
            <textarea class="admin-textarea" name="short_description[ar]" rows="3">{{ old('short_description.ar', $product?->getTranslation('short_description', 'ar', false)) }}</textarea>
        </label>

        <label class="space-y-2">
            <span class="text-sm font-bold text-white">وصف مختصر بالإنجليزية</span>
            <textarea class="admin-textarea" name="short_description[en]" rows="3" dir="ltr">{{ old('short_description.en', $product?->getTranslation('short_description', 'en', false)) }}</textarea>
        </label>

        <label class="space-y-2">
            <span class="text-sm font-bold text-white">الوصف الكامل بالعربية</span>
            <textarea class="admin-textarea" name="description[ar]" rows="6">{{ old('description.ar', $product?->getTranslation('description', 'ar', false)) }}</textarea>
        </label>

        <label class="space-y-2">
            <span class="text-sm font-bold text-white">الوصف الكامل بالإنجليزية</span>
            <textarea class="admin-textarea" name="description[en]" rows="6" dir="ltr">{{ old('description.en', $product?->getTranslation('description', 'en', false)) }}</textarea>
        </label>

        <label class="space-y-2">
            <span class="text-sm font-bold text-white">ملاحظات بالعربية</span>
            <textarea class="admin-textarea" name="notes[ar]" rows="4">{{ old('notes.ar', $product?->getTranslation('notes', 'ar', false)) }}</textarea>
        </label>

        <label class="space-y-2">
            <span class="text-sm font-bold text-white">ملاحظات بالإنجليزية</span>
            <textarea class="admin-textarea" name="notes[en]" rows="4" dir="ltr">{{ old('notes.en', $product?->getTranslation('notes', 'en', false)) }}</textarea>
        </label>
    </section>

    <section class="admin-subcard space-y-4">
        <h2 class="text-lg font-bold text-white">البيانات العامة</h2>

        <div class="grid gap-4 xl:grid-cols-2">
            <label class="space-y-2">
                <span class="text-sm font-bold text-white">الرابط</span>
                <input class="admin-input" type="text" name="slug" value="{{ old('slug', $product?->slug) }}" required>
            </label>
        </div>

        <label class="space-y-2">
            <span class="text-sm font-bold text-white">الأقسام</span>
            <select class="admin-select" name="categories[]" multiple data-tom-select>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(collect(old('categories', $product?->categories?->pluck('id')->all() ?? []))->contains($category->id))>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <div class="grid gap-3 md:grid-cols-2">
            <label class="flex items-center gap-3 border border-white/10 bg-slate-950/40 px-4 py-3 text-slate-200">
                <input class="admin-checkbox" type="checkbox" name="is_active" value="1" @checked(old('is_active', $product?->is_active ?? true))>
                المنتج مفعل في المتجر
            </label>

            <label class="flex items-center gap-3 border border-white/10 bg-slate-950/40 px-4 py-3 text-slate-200">
                <input class="admin-checkbox" type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product?->is_featured ?? false))>
                المنتج مميز
            </label>
        </div>
    </section>
</section>
