<section class="admin-card">
    <form method="POST" action="{{ $action }}">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div class="grid gap-6">
            <div class="admin-subcard">
                <label class="grid gap-2">
                    <span class="text-sm font-bold text-white">اسم الدور</span>
                    <input class="admin-input" type="text" name="name" value="{{ old('name', $role->name) }}" placeholder="مدير المحتوى">
                </label>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                @foreach ($permissions as $group => $groupPermissions)
                    <div class="admin-subcard">
                        <h2 class="text-lg font-bold text-white">{{ \App\Support\AdminArabic::permissionGroup($group) }}</h2>
                        <div class="mt-4 grid gap-3">
                            @foreach ($groupPermissions as $permission)
                                <label class="flex items-center gap-3 rounded-2xl border border-white/10 px-4 py-3 text-slate-200">
                                    <input
                                        class="admin-checkbox"
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permission->name }}"
                                        @checked(collect(old('permissions', $role->permissions->pluck('name')->all()))->contains($permission->name))
                                    >
                                    <span>{{ \App\Support\AdminArabic::permissionName($permission->name) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button class="admin-btn-primary" type="submit">{{ $submitLabel }}</button>
            <a class="admin-btn-secondary" href="{{ route('admin.roles.index') }}">عودة</a>
        </div>
    </form>
</section>
