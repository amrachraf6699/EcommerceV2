@csrf
@isset($method)
    @method($method)
@endisset

<div class="grid gap-6 xl:grid-cols-2">
    <section class="admin-subcard space-y-4">
        <h2 class="text-lg font-bold text-white">إعدادات الكوبون</h2>

        <label class="space-y-2">
            <span class="text-sm font-bold text-white">الكود</span>
            <input class="admin-input" type="text" name="code" value="{{ old('code', $coupon->code ?? '') }}" required dir="ltr">
        </label>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="space-y-2">
                <span class="text-sm font-bold text-white">نوع الخصم</span>
                <select class="admin-select" name="discount_type" required>
                    <option value="percent" @selected(old('discount_type', $coupon->discount_type ?? 'percent') === 'percent')>نسبة مئوية</option>
                    <option value="amount" @selected(old('discount_type', $coupon->discount_type ?? 'percent') === 'amount')>مبلغ ثابت</option>
                </select>
            </label>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">قيمة الخصم</span>
                <input class="admin-input" type="number" name="discount_value" step="0.01" min="0.01" value="{{ old('discount_value', isset($coupon) ? number_format((float) $coupon->discount_value, 2, '.', '') : '') }}" required>
            </label>
        </div>

        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-slate-200">
            <input class="admin-checkbox" type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->is_active ?? true))>
            الكوبون مفعل
        </label>
    </section>

    <section class="admin-subcard space-y-4">
        <h2 class="text-lg font-bold text-white">القيود</h2>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="space-y-2">
                <span class="text-sm font-bold text-white">بداية التفعيل</span>
                <input class="admin-input" type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($coupon) && $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}">
            </label>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">نهاية التفعيل</span>
                <input class="admin-input" type="datetime-local" name="ends_at" value="{{ old('ends_at', isset($coupon) && $coupon->ends_at ? $coupon->ends_at->format('Y-m-d\TH:i') : '') }}">
            </label>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="space-y-2">
                <span class="text-sm font-bold text-white">أقل إجمالي قبل الخصم</span>
                <input class="admin-input" type="number" name="min_order_subtotal" step="0.01" min="0" value="{{ old('min_order_subtotal', isset($coupon) && $coupon->min_order_subtotal !== null ? number_format((float) $coupon->min_order_subtotal, 2, '.', '') : '') }}">
            </label>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">أعلى إجمالي قبل الخصم</span>
                <input class="admin-input" type="number" name="max_order_subtotal" step="0.01" min="0" value="{{ old('max_order_subtotal', isset($coupon) && $coupon->max_order_subtotal !== null ? number_format((float) $coupon->max_order_subtotal, 2, '.', '') : '') }}">
            </label>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="space-y-2">
                <span class="text-sm font-bold text-white">حد الاستخدام الكلي</span>
                <input class="admin-input" type="number" name="usage_limit" min="1" value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}">
            </label>

            <label class="space-y-2">
                <span class="text-sm font-bold text-white">حد الاستخدام لكل عميل</span>
                <input class="admin-input" type="number" name="usage_limit_per_customer" min="1" value="{{ old('usage_limit_per_customer', $coupon->usage_limit_per_customer ?? '') }}">
            </label>
        </div>

        <label class="space-y-2">
            <span class="text-sm font-bold text-white">الدول المسموح بها</span>
            <select class="admin-select" name="allowed_countries[]" multiple data-tom-select>
                @include('frontend.partials.country-options', [
                    'selectedCountries' => old('allowed_countries', $coupon->allowed_countries ?? []),
                ])
            </select>
            <small class="text-xs text-slate-400">اتركها فارغة لتفعيل الكوبون في كل الدول.</small>
        </label>
    </section>
</div>

<div class="mt-6 flex gap-3">
    <button class="admin-btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="admin-btn-secondary" href="{{ route('admin.coupons.index') }}">عودة</a>
</div>
