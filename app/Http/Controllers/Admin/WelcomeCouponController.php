<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WelcomeCoupon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class WelcomeCouponController extends Controller
{
    public function index(Request $request): View
    {
        $query = WelcomeCoupon::query()
            ->with(['customer', 'order'])
            ->latest('sent_at')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('email', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();

            if ($status === 'used') {
                $query->whereNotNull('used_at');
            } elseif ($status === 'unused') {
                $query->whereNull('used_at');
            }
        }

        if ($request->filled('sent_from')) {
            $query->whereDate('sent_at', '>=', $request->date('sent_from'));
        }

        if ($request->filled('sent_to')) {
            $query->whereDate('sent_at', '<=', $request->date('sent_to'));
        }

        return view('admin.welcome-coupons.index', [
            'coupons' => $query->paginate(15)->withQueryString(),
        ]);
    }
}
