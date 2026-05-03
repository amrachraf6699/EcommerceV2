<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $query = Cart::query()->latest('last_activity_at')->latest();

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();

            if ($status === 'expired') {
                $query->whereNotNull('expires_at')->where('expires_at', '<', now());
            }

            if ($status === 'active') {
                $query->where(function ($builder): void {
                    $builder->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                });
            }
        }

        if ($request->filled('search')) {
            $query->where('session_id', 'like', '%'.$request->string('search')->toString().'%');
        }

        return view('admin.carts.index', [
            'carts' => $query->paginate(12)->withQueryString(),
        ]);
    }

    public function show(Cart $cart): View
    {
        return view('admin.carts.show', [
            'cart' => $cart->load('items'),
        ]);
    }
}
