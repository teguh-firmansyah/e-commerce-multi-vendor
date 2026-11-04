<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Shops\UpdateShopProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdateShopProfileRequest;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ShopProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $shop = $this->ownedShop($request);

        Gate::authorize('update', $shop);

        $address = $shop->addresses()->where('is_default', true)->first();

        return Inertia::render('Shop/Edit', [
            'shop' => [
                'name' => $shop->name,
                'slug' => $shop->slug,
                'description' => $shop->description,
                'phone' => $shop->phone,
                'is_open' => $shop->is_open,
                'logo_url' => $shop->logoUrl(),
                'status' => $shop->status->value,
                'status_reason' => $shop->status_reason,
            ],
            'address' => $address === null ? null : [
                'province' => $address->province,
                'city' => $address->city,
                'postal_code' => $address->postal_code,
                'address_line' => $address->address_line,
            ],
        ]);
    }

    public function update(UpdateShopProfileRequest $request, UpdateShopProfile $action): RedirectResponse
    {
        $shop = $this->ownedShop($request);

        Gate::authorize('update', $shop);

        $logo = $request->file('logo');

        $action->execute(
            $shop,
            [
                ...$request->validated(),
                'is_open' => $request->boolean('is_open'),
            ],
            $logo instanceof UploadedFile ? $logo : null,
            $request->boolean('remove_logo'),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Data toko diperbarui.'),
        ]);

        return to_route('shop.edit');
    }

    private function ownedShop(Request $request): Shop
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        return $user->shop ?? abort(404);
    }
}
