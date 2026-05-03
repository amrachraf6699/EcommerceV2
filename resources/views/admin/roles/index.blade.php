@php
    $title = 'الأدوار';
    $pageTitle = 'الأدوار والصلاحيات';
    $pageDescription = 'إدارة الأدوار والصلاحيات من مكان واحد.';
    $breadcrumbs = ['الإدارة', 'الأدوار'];
    $headerActions = view('admin.roles.partials.index-actions');
@endphp

@extends('layouts.admin')

@section('content')
    <section class="grid gap-6 xl:grid-cols-2">
        @foreach ($roles as $role)
            <article class="admin-card">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-white">{{ \App\Support\AdminArabic::roleName($role->name) }}</h2>
                        <p class="mt-2 text-sm text-slate-300">{{ $role->permissions->count() }} صلاحية مرتبطة</p>
                        <p class="mt-1 text-sm text-slate-300">{{ $role->users_count }} حسابات مرتبطة</p>
                    </div>

                    <div class="flex gap-2">
                        @can('roles.update')
                            <a class="admin-btn-icon" href="{{ route('admin.roles.edit', $role) }}" aria-label="تعديل الدور" title="تعديل الدور">
                                <i class="bx bx-pencil" aria-hidden="true"></i>
                            </a>
                        @endcan

                        @can('roles.delete')
                            <form
                                method="POST"
                                action="{{ route('admin.roles.destroy', $role) }}"
                                data-confirm-title="حذف الدور"
                                data-confirm-text="هل تريد حذف هذا الدور؟"
                            >
                                @csrf
                                @method('DELETE')
                                <button class="admin-btn-icon admin-btn-icon--danger" type="submit" aria-label="حذف الدور" title="حذف الدور">
                                    <i class="bx bx-trash" aria-hidden="true"></i>
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($role->permissions as $permission)
                        <span class="border border-white/10 bg-white/5 px-3 py-2 text-xs text-slate-200">{{ \App\Support\AdminArabic::permissionName($permission->name) }}</span>
                    @endforeach
                </div>
            </article>
        @endforeach
    </section>
@endsection
