<div class="space-y-5" data-product-images-fragment>
    <article class="admin-subcard">
        <h2 class="text-lg font-bold text-white">رفع صور المنتج</h2>

        <form class="mt-4 space-y-4" method="POST" action="{{ route('admin.products.images.store', $product) }}" enctype="multipart/form-data" data-ajax-form data-ajax-fragment="images">
            @csrf

            <input class="admin-input" type="file" name="images[]" data-filepond multiple required>
            <input class="admin-input" type="text" name="alt_text" placeholder="نص بديل">

            <div class="grid gap-3 md:grid-cols-2">
                <input class="admin-input" type="number" name="sort_order" value="0" min="0" placeholder="ترتيب">
                <select class="admin-select" name="product_variant_id">
                    <option value="">لكل النسخ</option>
                    @foreach ($product->variants as $variant)
                        <option value="{{ $variant->id }}">{{ $variant->display_name }}</option>
                    @endforeach
                </select>
            </div>

            <label class="flex items-center gap-3 text-slate-200"><input class="admin-checkbox" type="checkbox" name="is_primary" value="1"> الصورة الرئيسية</label>

            <div class="hidden admin-validation-errors rounded-2xl px-4 py-3 text-sm" data-ajax-errors></div>
            <button class="admin-btn-primary" type="submit">رفع الصور</button>
        </form>
    </article>

    <div class="grid gap-3">
        @forelse ($product->images as $image)
            <div class="admin-subcard space-y-3">
                <form class="space-y-3" method="POST" action="{{ route('admin.products.images.update', [$product, $image]) }}" data-ajax-form data-ajax-fragment="images">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-4 md:grid-cols-[120px_1fr] md:items-start">
                        <img class="h-28 w-28 object-cover" src="{{ asset('storage/'.$image->path) }}" alt="{{ $image->alt_text }}">

                        <div class="grid gap-3">
                            <input class="admin-input" type="text" name="alt_text" value="{{ $image->alt_text }}" placeholder="نص بديل">

                            <div class="grid gap-3 md:grid-cols-2">
                                <input class="admin-input" type="number" name="sort_order" value="{{ $image->sort_order }}" min="0">
                                <select class="admin-select" name="product_variant_id">
                                    <option value="">لكل النسخ</option>
                                    @foreach ($product->variants as $variant)
                                        <option value="{{ $variant->id }}" @selected($image->product_variant_id === $variant->id)>{{ $variant->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <label class="flex items-center gap-3 text-slate-200"><input class="admin-checkbox" type="checkbox" name="is_primary" value="1" @checked($image->is_primary)> الصورة الرئيسية</label>
                        </div>
                    </div>

                    <div class="hidden admin-validation-errors rounded-2xl px-4 py-3 text-sm" data-ajax-errors></div>
                    <button class="admin-btn-secondary" type="submit">حفظ الصورة</button>
                </form>

                <form method="POST"
                      action="{{ route('admin.products.images.destroy', [$product, $image]) }}"
                      data-ajax-form
                      data-ajax-fragment="images"
                      data-confirm-title="حذف الصورة"
                      data-confirm-text="سيتم حذف صورة المنتج نهائيًا من الملفات.">
                    @csrf
                    @method('DELETE')
                    <button class="admin-btn-danger" type="submit" data-loading-label="جاري الحذف...">حذف الصورة</button>
                </form>
            </div>
        @empty
            <x-admin.empty-state title="لا توجد صور" description="استخدم مساحة الرفع لإضافة صور المنتج." />
        @endforelse
    </div>
</div>
