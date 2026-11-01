<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Shops\ChangeShopStatus;
use App\Actions\Shops\CreateShopApplication;
use App\Enums\ShopStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreShopRequest;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ShopApplicationController extends Controller
{
    public function create(): Response
    {
        Gate::authorize('create', Shop::class);

        return Inertia::render('Shop/Apply');
    }

    public function store(StoreShopRequest $request, CreateShopApplication $action): RedirectResponse
    {
        Gate::authorize('create', Shop::class);

        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $action->execute($user, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Pengajuan toko terkirim. Mohon tunggu persetujuan admin.'),
        ]);

        return to_route('dashboard');
    }

    public function resubmit(Request $request, ChangeShopStatus $action): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $shop = $user->shop ?? abort(404);

        Gate::authorize('resubmit', $shop);

        $action->execute($shop, ShopStatus::Pending, $user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Pengajuan toko diajukan ulang.'),
        ]);

        return to_route('dashboard');
    }
}
