<div class="min-w-0 space-y-5" data-product-variants-fragment>
    @php($groundTypes = \App\Enums\ProductVariantGroundType::options())
    <article class="admin-subcard min-w-0 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-white">نسخ المنتج</h2>
            <p class="text-sm text-slate-400">حرر الأسعار والمخزون وحالة كل نسخة من جدول واحد.</p>
        </div>

        <form id="variant-create-form" method="POST" action="{{ route('admin.products.variants.store', $product) }}" data-ajax-form data-ajax-fragment="variants">
            @csrf
            <div class="hidden admin-validation-errors rounded-2xl px-4 py-3 text-sm" data-ajax-errors></div>
        </form>

        @foreach ($product->variants as $variant)
            <form id="variant-update-{{ $variant->id }}" method="POST" action="{{ route('admin.products.variants.update', [$product, $variant]) }}" data-ajax-form data-ajax-fragment="variants">
                @csrf
                @method('PUT')
                <div class="hidden admin-validation-errors rounded-2xl px-4 py-3 text-sm" data-ajax-errors></div>
            </form>

            <form id="variant-delete-{{ $variant->id }}"
                  method="POST"
                  action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}"
                  data-ajax-form
                  data-ajax-fragment="variants"
                  data-confirm-title="حذف النسخة"
                  data-confirm-text="سيتم حذف هذه النسخة من المنتج.">
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        <div class="min-w-0 overflow-x-auto rounded-2xl border border-white/10">
            <table class="min-w-full divide-y divide-white/10 text-sm text-slate-200">
                <thead class="bg-white/5 text-xs uppercase tracking-[0.2em] text-slate-400">
                    <tr>
                        <th class="px-4 py-3 text-right">المقاس</th>
                        <th class="px-4 py-3 text-right">اللون</th>
                        <th class="px-4 py-3 text-right">{{ __('storefront.common.ground_type') }}</th>
                        <th class="px-4 py-3 text-right">السعر</th>
                        <th class="px-4 py-3 text-right">السعر قبل الخصم</th>
                        <th class="px-4 py-3 text-right">المخزون</th>
                        <th class="px-4 py-3 text-center">افتراضية</th>
                        <th class="px-4 py-3 text-center">مفعلة</th>
                        <th class="px-4 py-3 text-left">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <tr class="align-top">
                        <td class="px-4 py-3">
                            <input class="admin-input min-w-[140px]" type="text" name="size" placeholder="المقاس" form="variant-create-form" required>
                        </td>
                        <td class="px-4 py-3">
                            <input class="admin-input min-w-[140px]" type="text" name="color" placeholder="اللون" form="variant-create-form" required>
                        </td>
                        <td class="px-4 py-3">
                            <select class="admin-select min-w-[140px]" name="ground_type" form="variant-create-form">
                                <option value="">{{ __('storefront.common.ground_type') }}</option>
                                @foreach ($groundTypes as $groundTypeValue => $groundTypeLabel)
                                    <option value="{{ $groundTypeValue }}">{{ $groundTypeLabel }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-4 py-3">
                            <input class="admin-input min-w-[140px]" type="number" step="0.01" name="price" placeholder="السعر" form="variant-create-form" required>
                        </td>
                        <td class="px-4 py-3">
                            <input class="admin-input min-w-[160px]" type="number" step="0.01" name="compare_at_price" placeholder="السعر قبل الخصم" form="variant-create-form">
                        </td>
                        <td class="px-4 py-3">
                            <input class="admin-input min-w-[120px]" type="number" name="stock_quantity" placeholder="المخزون" form="variant-create-form" required>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <input class="admin-checkbox" type="checkbox" name="is_default" value="1" form="variant-create-form">
                        </td>
                        <td class="px-4 py-3 text-center">
                            <input class="admin-checkbox" type="checkbox" name="is_active" value="1" checked form="variant-create-form">
                        </td>
                        <td class="px-4 py-3 text-left">
                            <button class="admin-btn-primary whitespace-nowrap" type="submit" form="variant-create-form">إضافة</button>
                        </td>
                    </tr>

                    @forelse ($product->variants as $variant)
                        <tr class="align-top">
                            <td class="px-4 py-3">
                                <input class="admin-input min-w-[140px]" type="text" name="size" value="{{ $variant->size }}" form="variant-update-{{ $variant->id }}" required>
                            </td>
                            <td class="px-4 py-3">
                                <input class="admin-input min-w-[140px]" type="text" name="color" value="{{ $variant->color }}" form="variant-update-{{ $variant->id }}" required>
                            </td>
                            <td class="px-4 py-3">
                                <select class="admin-select min-w-[140px]" name="ground_type" form="variant-update-{{ $variant->id }}">
                                    <option value="">{{ __('storefront.common.ground_type') }}</option>
                                    @foreach ($groundTypes as $groundTypeValue => $groundTypeLabel)
                                        <option value="{{ $groundTypeValue }}" @selected($variant->ground_type?->value === $groundTypeValue)>{{ $groundTypeLabel }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3">
                                <input class="admin-input min-w-[140px]" type="number" step="0.01" name="price" value="{{ $variant->price }}" form="variant-update-{{ $variant->id }}" required>
                            </td>
                            <td class="px-4 py-3">
                                <input class="admin-input min-w-[160px]" type="number" step="0.01" name="compare_at_price" value="{{ $variant->compare_at_price }}" form="variant-update-{{ $variant->id }}">
                            </td>
                            <td class="px-4 py-3">
                                <input class="admin-input min-w-[120px]" type="number" name="stock_quantity" value="{{ $variant->stock_quantity }}" form="variant-update-{{ $variant->id }}" required>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input class="admin-checkbox" type="checkbox" name="is_default" value="1" @checked($variant->is_default) form="variant-update-{{ $variant->id }}">
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input class="admin-checkbox" type="checkbox" name="is_active" value="1" @checked($variant->is_active) form="variant-update-{{ $variant->id }}">
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <button class="admin-btn-secondary whitespace-nowrap" type="submit" form="variant-update-{{ $variant->id }}">حفظ</button>
                                    <button class="admin-btn-danger whitespace-nowrap" type="submit" form="variant-delete-{{ $variant->id }}" data-loading-label="جاري الحذف...">حذف</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-slate-400" colspan="9">أضف أول نسخة للبدء في التسعير والمخزون.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
</div>
