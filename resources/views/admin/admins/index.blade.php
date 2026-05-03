@php
    $title = 'إدارة المسؤولين';
    $pageTitle = 'حسابات الإدارة';
    $pageDescription = 'إنشاء الحسابات الإدارية وتوزيع الأدوار بطريقة واضحة وسهلة لفريق العمل.';
    $breadcrumbs = ['الإدارة', 'المسؤولون'];
    $headerActions = view('admin.admins.partials.index-actions');
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="GET" class="grid gap-3 lg:grid-cols-4">
            <input class="admin-input" type="text" name="search" placeholder="ابحث بالاسم أو البريد" value="{{ request('search') }}">
            <select class="admin-select" name="role">
                <option value="">كل الأدوار</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ \App\Support\AdminArabic::roleName($role->name) }}</option>
                @endforeach
            </select>
            <select class="admin-select" name="status">
                <option value="">كل الحالات</option>
                <option value="active" @selected(request('status') === 'active')>مفعل</option>
                <option value="inactive" @selected(request('status') === 'inactive')>غير مفعل</option>
            </select>
            <button class="admin-btn-secondary" type="submit">تطبيق الفلاتر</button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="bg-slate-950/50">
                    <tr>
                        <th>الاسم</th>
                        <th>البريد</th>
                        <th>الدور</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-white/5">
                    @forelse ($admins as $admin)
                        <tr>
                            <td class="font-bold text-white">{{ $admin->name }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>{{ $admin->roles->map(fn ($role) => \App\Support\AdminArabic::roleName($role->name))->join(' / ') ?: 'بدون دور' }}</td>
                            <td>
                                <span class="px-3 py-1 text-xs {{ $admin->is_active ? 'bg-emerald-400/10 text-emerald-200' : 'bg-slate-400/10 text-slate-300' }}">
                                    {{ $admin->is_active ? 'مفعل' : 'غير مفعل' }}
                                </span>
                            </td>
                            <td>
                                <a class="admin-btn-icon" href="{{ route('admin.admins.edit', $admin) }}" aria-label="تعديل الحساب الإداري" title="تعديل الحساب الإداري">
                                    <i class="bx bx-pencil" aria-hidden="true"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin.empty-state title="لا توجد حسابات مطابقة" description="جرّب إزالة بعض الفلاتر أو أضف مسؤولا جديدا." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $admins->links() }}</div>
    </section>
@endsection
