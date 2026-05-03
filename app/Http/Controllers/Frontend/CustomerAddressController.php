<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreCustomerAddressRequest;
use App\Http\Requests\Frontend\UpdateCustomerAddressRequest;
use App\Models\CustomerAddress;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerAddressController extends Controller
{
    public function index(): View
    {
        $customer = auth('customer')->user();

        return view('frontend.account.addresses.index', [
            'addresses' => $customer->addresses()->latest()->get(),
        ]);
    }

    public function store(StoreCustomerAddressRequest $request): RedirectResponse
    {
        $customer = auth('customer')->user();
        $data = $this->normalizeDefaults($request->validated());

        if ($data['is_default_shipping']) {
            $customer->addresses()->update(['is_default_shipping' => false]);
        }

        if ($data['is_default_billing']) {
            $customer->addresses()->update(['is_default_billing' => false]);
        }

        $customer->addresses()->create($data);

        return back()->with('success', __('storefront.account.address_saved'));
    }

    public function update(UpdateCustomerAddressRequest $request, CustomerAddress $address): RedirectResponse
    {
        abort_unless($address->customer_id === auth('customer')->id(), 404);

        $customer = auth('customer')->user();
        $data = $this->normalizeDefaults($request->validated());

        if ($data['is_default_shipping']) {
            $customer->addresses()
                ->whereKeyNot($address->id)
                ->update(['is_default_shipping' => false]);
        }

        if ($data['is_default_billing']) {
            $customer->addresses()
                ->whereKeyNot($address->id)
                ->update(['is_default_billing' => false]);
        }

        $address->update($data);

        return back()->with('success', __('storefront.account.address_updated'));
    }

    public function destroy(CustomerAddress $address): RedirectResponse
    {
        abort_unless($address->customer_id === auth('customer')->id(), 404);

        $address->delete();

        return back()->with('success', __('storefront.account.address_deleted'));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeDefaults(array $data): array
    {
        $data['is_default_shipping'] = (bool) ($data['is_default_shipping'] ?? false);
        $data['is_default_billing'] = (bool) ($data['is_default_billing'] ?? false);

        return $data;
    }
}
