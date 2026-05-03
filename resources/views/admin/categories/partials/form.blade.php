@csrf
@isset($method)
    @method($method)
@endisset

<div class="grid gap-6">
    <section class="admin-subcard space-y-4">
        <h2 class="text-lg font-bold text-white">البيانات المترجمة</h2>
        <div class="grid gap-4 lg:grid-cols-2">
            <label class="space-y-2">
                <span class="text-sm font-bold text-white">اسم القسم بالعربية</span>
                <input class="admin-input" type="text" name="name[ar]" value="{{ old('name.ar', isset($category) ? $category->getTranslation('name', 'ar', false) : '') }}" required>
            </label>
            <label class="space-y-2">
                <span class="text-sm font-bold text-white">اسم القسم بالإنجليزية</span>
                <input class="admin-input" type="text" name="name[en]" value="{{ old('name.en', isset($category) ? $category->getTranslation('name', 'en', false) : '') }}" required dir="ltr">
            </label>
            <label class="space-y-2 lg:col-span-2">
                <span class="text-sm font-bold text-white">الوصف بالعربية</span>
                <textarea class="admin-textarea" name="description[ar]" rows="4">{{ old('description.ar', isset($category) ? $category->getTranslation('description', 'ar', false) : '') }}</textarea>
            </label>
            <label class="space-y-2 lg:col-span-2">
                <span class="text-sm font-bold text-white">الوصف بالإنجليزية</span>
                <textarea class="admin-textarea" name="description[en]" rows="4" dir="ltr">{{ old('description.en', isset($category) ? $category->getTranslation('description', 'en', false) : '') }}</textarea>
            </label>
        </div>
    </section>

    <section class="admin-subcard space-y-4">
        <h2 class="text-lg font-bold text-white">البيانات العامة</h2>
        <div class="grid gap-4 lg:grid-cols-2">
            <label class="space-y-2">
                <span class="text-sm font-bold text-white">الرابط</span>
                <input class="admin-input" type="text" name="slug" value="{{ old('slug', $category->slug ?? '') }}" required>
            </label>
            <label class="space-y-2">
                <span class="text-sm font-bold text-white">الترتيب</span>
                <input class="admin-input" type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0">
            </label>
            <div class="space-y-2 lg:col-span-2">
                <span class="text-sm font-bold text-white">صورة القسم</span>
                @if (! empty($category?->image))
                    <img class="h-24 w-24 border border-black/10 object-cover" src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}">
                @endif
                <input class="admin-input" type="file" name="image" data-filepond>
            </div>
            <div class="space-y-2 lg:col-span-2">
                <span class="text-sm font-bold text-white">الحالة</span>
                <label class="flex items-center gap-3 border border-white/10 bg-slate-950/40 px-4 py-3 text-slate-200">
                    <input class="admin-checkbox" type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))>
                    القسم ظاهر ونشط
                </label>
            </div>
        </div>
    </section>
</div>

<div class="mt-6 flex gap-3">
    <button class="admin-btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="admin-btn-secondary" href="{{ route('admin.categories.index') }}">عودة</a>
</div>
