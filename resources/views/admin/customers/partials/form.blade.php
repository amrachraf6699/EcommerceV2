@csrf
@isset($method)
    @method($method)
@endisset

<div class="grid gap-4 lg:grid-cols-2">
    <label class="space-y-2">
        <span class="text-sm font-bold text-white">الاسم</span>
        <input class="admin-input" type="text" name="name" value="{{ old('name', $customer->name ?? '') }}" required>
    </label>

    <label class="space-y-2">
        <span class="text-sm font-bold text-white">البريد الإلكتروني</span>
        <input class="admin-input" type="email" name="email" value="{{ old('email', $customer->email ?? '') }}" required>
    </label>

    <label class="space-y-2">
        <span class="text-sm font-bold text-white">الهاتف</span>
        <input class="admin-input" type="text" name="phone" value="{{ old('phone', $customer->phone ?? '') }}">
    </label>

    <label class="space-y-2">
        <span class="text-sm font-bold text-white">الدولة</span>
        <input class="admin-input" type="text" name="country" value="{{ old('country', $customer->country ?? '') }}">
    </label>

    <div class="space-y-2 lg:col-span-2">
        <span class="text-sm font-bold text-white">حالة الحساب</span>
        <label class="flex items-center gap-3 border border-white/10 bg-slate-950/40 px-4 py-3 text-slate-200">
            <input class="admin-checkbox" type="checkbox" name="is_active" value="1" @checked(old('is_active', $customer->is_active ?? true))>
            الحساب مفعل ويمكنه تسجيل الدخول
        </label>
    </div>

    <label class="space-y-2">
        <span class="text-sm font-bold text-white">كلمة المرور {{ isset($customer) && $customer->exists ? '(اختياري)' : '' }}</span>
        <input class="admin-input" type="password" name="password" {{ isset($customer) && $customer->exists ? '' : 'required' }}>
    </label>

    <label class="space-y-2">
        <span class="text-sm font-bold text-white">تأكيد كلمة المرور</span>
        <input class="admin-input" type="password" name="password_confirmation" {{ isset($customer) && $customer->exists ? '' : 'required' }}>
    </label>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button class="admin-btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="admin-btn-secondary" href="{{ route('admin.customers.index') }}">عودة</a>
</div>
