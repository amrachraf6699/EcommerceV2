<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreCustomerAddressRequest;
use App\Http\Requests\Frontend\UpdateCustomerAddressRequest;
use App\Http\Resources\Api\V1\CustomerAddressResource;
use App\Models\CustomerAddress;
use Illuminate\Http\JsonResponse;

class AddressController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'addresses' => CustomerAddressResource::collection(
                request()->user()->addresses()->latest()->get()
            ),
        ]);
    }

    public function store(StoreCustomerAddressRequest $request): JsonResponse
    {
        $customer = $request->user();
        $data = $this->normalizeDefaults($request->validated());

        if ($data['is_default_shipping']) {
            $customer->addresses()->update(['is_default_shipping' => false]);
        }

        if ($data['is_default_billing']) {
            $customer->addresses()->update(['is_default_billing' => false]);
        }

        $address = $customer->addresses()->create($data);

        return response()->json([
            'message' => __('storefront.account.address_saved'),
            'address' => new CustomerAddressResource($address),
        ], 201);
    }

    public function update(UpdateCustomerAddressRequest $request, CustomerAddress $address): JsonResponse
    {
        abort_unless($address->customer_id === $request->user()->id, 404);

        $customer = $request->user();
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

        return response()->json([
            'message' => __('storefront.account.address_updated'),
            'address' => new CustomerAddressResource($address->fresh()),
        ]);
    }

    public function destroy(CustomerAddress $address): JsonResponse
    {
        abort_unless($address->customer_id === request()->user()->id, 404);
        $address->delete();

        return response()->json([
            'message' => __('storefront.account.address_deleted'),
        ]);
    }

    private function normalizeDefaults(array $data): array
    {
        $data['is_default_shipping'] = (bool) ($data['is_default_shipping'] ?? false);
        $data['is_default_billing'] = (bool) ($data['is_default_billing'] ?? false);

        return $data;
    }
}
