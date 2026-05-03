@php
    $title = 'العملاء';
    $pageTitle = 'إدارة العملاء';
    $pageDescription = 'متابعة حسابات العملاء وبياناتهم الأساسية والعناوين المرتبطة.';
    $breadcrumbs = ['الإدارة', 'العملاء'];
    $headerActions = view('admin.customers.partials.index-actions');
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <form method="GET" class="grid gap-3 lg:grid-cols-3">
            <input class="admin-input" type="text" name="search" placeholder="ابحث بالاسم أو البريد أو الهاتف أو الدولة" value="{{ request('search') }}">
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
                        <th>العميل</th>
                        <th>الهاتف</th>
                        <th>الدولة</th>
                        <th>العناوين</th>
                        <th>الطلبات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-white/5">
                    @forelse ($customers as $customer)
                        <tr>
                            <td>
                                <p class="font-bold text-white">{{ $customer->name }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $customer->email }}</p>
                            </td>
                            <td>{{ $customer->phone ?: 'غير متوفر' }}</td>
                            <td>{{ $customer->country ?: 'غير محدد' }}</td>
                            <td>{{ $customer->addresses_count }}</td>
                            <td>{{ $customer->orders_count }}</td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <a class="admin-btn-icon" href="{{ route('admin.customers.edit', $customer) }}" aria-label="تعديل العميل" title="تعديل العميل">
                                        <i class="bx bx-pencil" aria-hidden="true"></i>
                                    </a>
                                    @can('customers.delete')
                                        <form method="POST"
                                              action="{{ route('admin.customers.destroy', $customer) }}"
                                              data-loading-form
                                              data-confirm-title="حذف العميل"
                                              data-confirm-text="سيتم حذف حساب العميل مع الاحتفاظ بالبيانات المحفوظة داخل الطلبات السابقة.">
                                            @csrf
                                            @method('DELETE')
                                            <button class="admin-btn-icon admin-btn-icon--danger" type="submit" aria-label="حذف العميل" title="حذف العميل" data-loading-label="جاري الحذف...">
                                                <i class="bx bx-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin.empty-state title="لا يوجد عملاء" description="سيظهر العملاء هنا بعد إنشاء الحسابات أو التسجيل من الواجهة الأمامية." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $customers->links() }}</div>
    </section>
@endsection
