<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_DEFAULT_EMAIL', 'admin@example.com');
        $password = env('ADMIN_DEFAULT_PASSWORD', 'password');
        $name = env('ADMIN_DEFAULT_NAME', 'Super Admin');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $role = Role::findByName('super-admin', 'web');
        $user->syncRoles([$role]);
    }
}
