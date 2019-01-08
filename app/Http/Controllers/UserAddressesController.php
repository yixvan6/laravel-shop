<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AddressRequest;
use App\Models\UserAddress;

class UserAddressesController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses;

        return view('addresses.index', compact('addresses'));
    }

    public function create(UserAddress $address)
    {
        return view('addresses.create_and_edit', compact('address'));
    }

    public function store(AddressRequest $request)
    {
        $request->user()->addresses()->create($request->only([
            'province', 'city', 'district', 'address', 'zip',
            'contact_name', 'contact_phone',
        ]));

        return redirect()->route('user.addresses.index');
    }

    public function edit(UserAddress $address)
    {
        $this->authorize('own', $address);

        return view('addresses.create_and_edit', compact('address'));
    }

    public function update(AddressRequest $request, UserAddress $address)
    {
        $this->authorize('own', $address);

        $address->update($request->only([
            'province', 'city', 'district', 'zip', 'address',
            'contact_name', 'contact_phone',
        ]));

        return redirect()->route('user.addresses.index');
    }

    public function destroy(UserAddress $address)
    {
        $this->authorize('own', $address);

        $address->delete();

        return [];
    }
}
