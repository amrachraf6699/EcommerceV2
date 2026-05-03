@csrf
@isset($method)
    @method($method)
@endisset

@php
    $admin = $admin ?? null;
    $selectedRole = old('role', $admin?->roles->first()?->name ?? '');
@endphp

<div class="grid gap-4 lg:grid-cols-2">
    <label class="space-y-2">
        <span class="text-sm font-bold text-white">الاسم</span>
        <input class="admin-input" type="text" name="name" value="{{ old('name', $admin?->name ?? '') }}" required>
    </label>

    <label class="space-y-2">
        <span class="text-sm font-bold text-white">البريد الإلكتروني</span>
        <input class="admin-input" type="email" name="email" value="{{ old('email', $admin?->email ?? '') }}" required>
    </label>

    <label class="space-y-2">
        <span class="text-sm font-bold text-white">الدور</span>
        <select class="admin-select" name="role" required>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}" @selected($selectedRole === $role->name)>{{ \App\Support\AdminArabic::roleName($role->name) }}</option>
            @endforeach
        </select>
    </label>

    <div class="space-y-2">
        <span class="text-sm font-bold text-white">حالة الحساب</span>
        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-slate-200">
            <input class="admin-checkbox" type="checkbox" name="is_active" value="1" @checked(old('is_active', $admin?->is_active ?? true))>
            الحساب مفعل ويمكنه تسجيل الدخول
        </label>
    </div>

    <label class="space-y-2">
        <span class="text-sm font-bold text-white">كلمة المرور {{ isset($admin) ? '(اختياري)' : '' }}</span>
        <input class="admin-input" type="password" name="password" {{ isset($admin) ? '' : 'required' }}>
    </label>

    <label class="space-y-2">
        <span class="text-sm font-bold text-white">تأكيد كلمة المرور</span>
        <input class="admin-input" type="password" name="password_confirmation" {{ isset($admin) ? '' : 'required' }}>
    </label>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button class="admin-btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="admin-btn-secondary" href="{{ route('admin.admins.index') }}">عودة</a>
</div>
