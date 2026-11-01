<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Shops\ChangeShopStatus;
use App\Enums\ShopStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateShopStatusRequest;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ShopReviewController extends Controller
{
    public function index(Request $request): Response
    {
        $status = ShopStatus::tryFrom((string) $request->query('status', 'pending')) ?? ShopStatus::Pending;

        $paginator = Shop::query()
            ->with([
                'user:id,name,email',
                'addresses' => fn ($query) => $query->where('is_default', true),
            ])
            ->where('status', $status->value)
            ->latest()
            ->simplePaginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Shops/Index', [
            'shops' => [
                'data' => $paginator->getCollection()->map(fn (Shop $shop): array => [
                    'id' => $shop->id,
                    'name' => $shop->name,
                    'phone' => $shop->phone,
                    'status' => $shop->status->value,
                    'status_reason' => $shop->status_reason,
                    'owner' => $shop->user->name,
                    'owner_email' => $shop->user->email,
                    'city' => $shop->addresses->first()?->city,
                    'submitted_at' => $shop->created_at?->toDateString(),
                ])->values()->all(),
                'prev_page_url' => $paginator->previousPageUrl(),
                'next_page_url' => $paginator->nextPageUrl(),
            ],
            'status' => $status->value,
            'statuses' => array_map(fn (ShopStatus $case): string => $case->value, ShopStatus::cases()),
        ]);
    }

    public function updateStatus(UpdateShopStatusRequest $request, Shop $shop, ChangeShopStatus $action): RedirectResponse
    {
        Gate::authorize('review', $shop);

        $admin = $request->user();

        abort_unless($admin instanceof User, 403);

        $action->execute(
            $shop,
            ShopStatus::from((string) $request->validated('status')),
            $admin,
            $request->validated('reason'),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Status toko diperbarui.'),
        ]);

        return back();
    }
}
