<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $items = $user->addresses()
            ->orderByDesc('is_default_shipping')
            ->orderByDesc('is_default_billing')
            ->orderByDesc('updated_at')
            ->get();

        return AddressResource::collection($items);
    }

    public function store(StoreAddressRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        $data['user_id'] = $user->id;

        return DB::transaction(function () use ($user, $data) {
            $address = Address::create($data);

            if (!empty($data['is_default_shipping'])) {
                $user->addresses()->where('id', '<>', $address->id)->update(['is_default_shipping' => false]);
                $address->is_default_shipping = true;
            }

            if (!empty($data['is_default_billing'])) {
                $user->addresses()->where('id', '<>', $address->id)->update(['is_default_billing' => false]);
                $address->is_default_billing = true;
            }

            $address->save();

            return new AddressResource($address);
        });
    }

    public function show(Request $request, Address $address)
    {
        $this->authorize('view', $address);
        return new AddressResource($address);
    }

    public function update(UpdateAddressRequest $request, Address $address)
    {
        $this->authorize('update', $address);

        $data = $request->validated();
        $user = $request->user();

        return DB::transaction(function () use ($user, $address, $data) {
            $address->update($data);

            if (array_key_exists('is_default_shipping', $data) && $data['is_default_shipping']) {
                $user->addresses()->where('id', '<>', $address->id)->update(['is_default_shipping' => false]);
                $address->is_default_shipping = true;
            }

            if (array_key_exists('is_default_billing', $data) && $data['is_default_billing']) {
                $user->addresses()->where('id', '<>', $address->id)->update(['is_default_billing' => false]);
                $address->is_default_billing = true;
            }

            $address->save();

            return new AddressResource($address);
        });
    }

    public function destroy(Request $request, Address $address)
    {
        $this->authorize('delete', $address);

        return DB::transaction(function () use ($address) {
            $user = $address->user;
            $wasDefaultShipping = $address->is_default_shipping;
            $wasDefaultBilling  = $address->is_default_billing;

            $address->delete();

            // Réassignation auto si défaut supprimé
            if ($wasDefaultShipping) {
                $candidate = $user->addresses()->orderByDesc('updated_at')->first();
                if ($candidate) $candidate->update(['is_default_shipping' => true]);
            }
            if ($wasDefaultBilling) {
                $candidate = $user->addresses()->orderByDesc('updated_at')->first();
                if ($candidate) $candidate->update(['is_default_billing' => true]);
            }

            return response()->json(['success' => true]);
        });
    }

    public function setDefaultShipping(Request $request, Address $address)
    {
        $this->authorize('setDefault', $address);

        return DB::transaction(function () use ($request, $address) {
            $request->user()->addresses()->update(['is_default_shipping' => false]);
            $address->update(['is_default_shipping' => true]);
            return new AddressResource($address);
        });
    }

    public function setDefaultBilling(Request $request, Address $address)
    {
        $this->authorize('setDefault', $address);

        return DB::transaction(function () use ($request, $address) {
            $request->user()->addresses()->update(['is_default_billing' => false]);
            $address->update(['is_default_billing' => true]);
            return new AddressResource($address);
        });
    }
}
