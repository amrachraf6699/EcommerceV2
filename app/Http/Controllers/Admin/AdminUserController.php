<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use App\Services\Admin\AdminUserGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function __construct(private readonly AdminUserGuard $guard)
    {
    }

    public function index(Request $request): View
    {
        $query = User::query()->whereKeyNot(auth()->id())->with('roles');

        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status')->toString() === 'active');
        }

        if ($request->filled('role')) {
            $query->role($request->string('role')->toString());
        }

        return view('admin.admins.index', [
            'admins' => $query->latest()->paginate(12)->withQueryString(),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.admins.create', [
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
        ]);

        $admin->syncRoles([$validated['role']]);

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'تم إنشاء الحساب الإداري بنجاح.');
    }

    public function edit(User $admin): View
    {
        $admin->load('roles');

        return view('admin.admins.edit', [
            'admin' => $admin,
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateAdminUserRequest $request, User $admin): RedirectResponse
    {
        $validated = $request->validated();
        $isActive = $request->boolean('is_active');

        $this->guard->ensureCanApplyRoleChange($admin, $validated['role'], $isActive);

        $admin->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $isActive,
        ]);

        if (! empty($validated['password'])) {
            $admin->password = Hash::make($validated['password']);
        }

        $admin->save();
        $admin->syncRoles([$validated['role']]);

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'تم تحديث الحساب الإداري.');
    }

    public function destroy(User $admin): RedirectResponse
    {
        $this->guard->ensureCanDeactivate($admin);

        $admin->update(['is_active' => false]);

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'تم تعطيل الحساب الإداري.');
    }
}
