<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::query()->with('permissions')->withCount('users')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.create', [
            'role' => new Role(['guard_name' => 'web']),
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::query()->create([
            'name' => $request->validated('name'),
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($request->validated('permissions', []));

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'تم إنشاء الدور بنجاح.');
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $role->update([
            'name' => $request->validated('name'),
        ]);

        $role->syncPermissions($request->validated('permissions', []));

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'تم تحديث الدور وصلاحياته.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            return back()->with('error', 'لا يمكن حذف دور مرتبط بحسابات إدارية.');
        }

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'تم حذف الدور بنجاح.');
    }

    protected function groupedPermissions()
    {
        return Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => explode('.', $permission->name)[0]);
    }
}
