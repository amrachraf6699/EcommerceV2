<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class AdminUserGuard
{
    public function ensureCanApplyRoleChange(User $admin, string $roleName, bool $isActive): void
    {
        if ($admin->hasRole('super-admin') && (! $isActive || $roleName !== 'super-admin')) {
            $this->ensureAnotherActiveSuperAdminExists($admin);
        }
    }

    public function ensureCanDeactivate(User $admin): void
    {
        if ($admin->hasRole('super-admin')) {
            $this->ensureAnotherActiveSuperAdminExists($admin);
        }
    }

    private function ensureAnotherActiveSuperAdminExists(User $admin): void
    {
        $otherSuperAdmins = User::query()
            ->whereKeyNot($admin->id)
            ->where('is_active', true)
            ->role('super-admin')
            ->count();

        if ($otherSuperAdmins === 0) {
            throw ValidationException::withMessages([
                'role' => 'لا يمكن تعطيل أو تغيير آخر مشرف عام في النظام.',
            ]);
        }
    }
}
