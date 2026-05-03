@csrf
@isset($method)
    @method($method)
@endisset

<div class="grid gap-6 xl:grid-cols-2">
    <section class="admin-subcard space-y-4">
        <h2 class="text-lg font-bold text-white">البيانات الأساسية</h2>
        <label class="space-y-2">
            <span class="text-sm font-bold text-white">اسم المنتج</span>
            <input class="admin-input" type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required>
        </label>
        <label class="space-y-2">
            <span class="text-sm font-bold text-white">الرابط</span>
            <input class="admin-input" type="text" name="slug" value="{{ old('slug', $product->slug ?? '') }}" required>
        </label>
        <label class="space-y-2">
            <span class="text-sm font-bold text-white">وصف مختصر</span>
            <textarea class="admin-textarea" name="short_description" rows="3">{{ old('short_description', $product->short_description ?? '') }}</textarea>
        </label>
        <label class="space-y-2">
            <span class="text-sm font-bold text-white">الوصف الكامل</span>
            <textarea class="admin-textarea" name="description" rows="6">{{ old('description', $product->description ?? '') }}</textarea>
        </label>
        <label class="space-y-2">
            <span class="text-sm font-bold text-white">ملاحظات</span>
            <textarea class="admin-textarea" name="notes" rows="4">{{ old('notes', $product->notes ?? '') }}</textarea>
        </label>
    </section>

    <section class="admin-subcard space-y-4">
        <h2 class="text-lg font-bold text-white">الأقسام وSEO</h2>
        <label class="space-y-2">
            <span class="text-sm font-bold text-white">الأقسام</span>
            <select class="admin-select" name="categories[]" multiple data-tom-select>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(collect(old('categories', isset($product) ? $product->categories->pluck('id')->all() : []))->contains($category->id))>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </label>
        <label class="space-y-2">
            <span class="text-sm font-bold text-white">عنوان SEO</span>
            <input class="admin-input" type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title ?? '') }}">
        </label>
        <label class="space-y-2">
            <span class="text-sm font-bold text-white">وصف SEO</span>
            <textarea class="admin-textarea" name="meta_description" rows="4">{{ old('meta_description', $product->meta_description ?? '') }}</textarea>
        </label>
        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-slate-200">
            <input class="admin-checkbox" type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))>
            المنتج مفعل في المتجر
        </label>
        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-slate-200">
            <input class="admin-checkbox" type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured ?? false))>
            المنتج مميز
        </label>
    </section>
</div>

<div class="mt-6 flex gap-3">
    <button class="admin-btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="admin-btn-secondary" href="{{ route('admin.products.index') }}">عودة</a>
</div>
