<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(AdminLoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'بيانات الدخول غير صحيحة.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'هذا الحساب الإداري غير مفعل حالياً.'])
                ->onlyInput('email');
        }

        if (! ($user->hasAnyRole(['super-admin', 'admin']) || $user->can('dashboard.view'))) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'هذا الحساب لا يملك صلاحية دخول لوحة الإدارة.'])
                ->onlyInput('email');
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'تم تسجيل الدخول بنجاح.');
    }

    public function destroy(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()
            ->route('admin.login')
            ->with('success', 'تم تسجيل الخروج بنجاح.');
    }
}
