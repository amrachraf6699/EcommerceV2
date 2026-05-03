@php
    $title = 'العملاء المميزون';
    $pageTitle = 'إدارة العملاء المميزين';
    $pageDescription = 'إدارة شعارات أو صور العملاء والمناصب الوظيفية الخاصة بهم.';
    $breadcrumbs = ['الإدارة', 'العملاء المميزون'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" class="flex-1">
                <div class="grid gap-3 lg:grid-cols-[1fr_auto]">
                    <input class="admin-input" type="text" name="search" placeholder="ابحث بالاسم أو المسمى الوظيفي" value="{{ request('search') }}">
                    <button class="admin-btn-secondary" type="submit">بحث</button>
                </div>
            </form>

            @can('clients.create')
                <a class="admin-btn-primary" href="{{ route('admin.clients.create') }}">إضافة عميل مميز</a>
            @endcan
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="bg-slate-950/50">
                    <tr>
                        <th>الصورة</th>
                        <th>الاسم</th>
                        <th>المسمى الوظيفي</th>
                        <th>آخر تحديث</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-white/5">
                    @forelse ($clients as $client)
                        <tr>
                            <td>
                                <img class="h-16 w-16 border border-black/10 object-cover" src="{{ asset('storage/' . $client->photo) }}" alt="{{ $client->name }}">
                            </td>
                            <td class="font-bold text-white">{{ $client->name }}</td>
                            <td>{{ $client->position ?: 'بدون مسمى وظيفي' }}</td>
                            <td>{{ $client->updated_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <a class="admin-btn-icon" href="{{ route('admin.clients.edit', $client) }}" aria-label="تعديل العميل" title="تعديل العميل">
                                        <i class="bx bx-pencil" aria-hidden="true"></i>
                                    </a>
                                    @can('clients.delete')
                                        <form method="POST"
                                              action="{{ route('admin.clients.destroy', $client) }}"
                                              data-loading-form
                                              data-confirm-title="حذف العميل"
                                              data-confirm-text="سيتم حذف هذا العميل وصورته من لوحة الإدارة.">
                                            @csrf
                                            @method('DELETE')
                                            <button class="admin-btn-icon admin-btn-icon--danger" type="submit" aria-label="حذف العميل" title="حذف العميل" data-loading-label="جارٍ الحذف...">
                                                <i class="bx bx-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin.empty-state title="لا يوجد عملاء مميزون بعد" description="أضف أول عميل مميز ليظهر في الواجهة الرئيسية للمتجر." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $clients->links() }}</div>
    </section>
@endsection
