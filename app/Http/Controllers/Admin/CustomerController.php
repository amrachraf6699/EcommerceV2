<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query()->withCount(['addresses', 'orders'])->latest();

        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('country', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status')->toString() === 'active');
        }

        return view('admin.customers.index', [
            'customers' => $query->paginate(12)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.customers.create', [
            'customer' => new Customer(),
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        Customer::create([
            ...$request->safe()->except('password'),
            'password' => $request->validated('password'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'تم إنشاء العميل بنجاح.');
    }

    public function edit(Customer $customer): View
    {
        $customer->loadCount(['addresses', 'orders']);

        return view('admin.customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $data = [
            ...$request->safe()->except('password'),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->validated('password');
        }

        $customer->update($data);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'تم تحديث بيانات العميل.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'تم حذف العميل.');
    }
}
