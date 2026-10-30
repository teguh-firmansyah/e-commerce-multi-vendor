<?php

namespace App\Policies;

use App\Enums\ShopStatus;
use App\Models\Shop;
use App\Models\User;

class ShopPolicy
{
    public function update(User $user, Shop $shop): bool
    {
        return $user->isActive()
            && $shop->user_id === $user->id
            && $shop->status !== ShopStatus::Suspended;
    }

    public function resubmit(User $user, Shop $shop): bool
    {
        return $user->isActive()
            && $shop->user_id === $user->id
            && $shop->status === ShopStatus::Rejected;
    }

    public function review(User $user, Shop $shop): bool
    {
        return $user->isAdmin() && $user->isActive();
    }
}
