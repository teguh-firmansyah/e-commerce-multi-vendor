<?php

namespace App\Actions\Shops;

use App\Enums\ShopStatus;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeShopStatus
{
    public function execute(Shop $shop, ShopStatus $target, User $actor, ?string $reason = null): Shop
    {
        $reason = filled($reason) ? trim((string) $reason) : null;

        if ($target->requiresReason() && $reason === null) {
            throw ValidationException::withMessages(['reason' => 'Alasan wajib diisi.']);
        }

        return DB::transaction(function () use ($shop, $target, $actor, $reason): Shop {
            $locked = Shop::query()->lockForUpdate()->findOrFail($shop->id);
            $current = $locked->status;

            if (! $current->canTransitionTo($target)) {
                throw ValidationException::withMessages(['status' => 'Perubahan status toko tidak diizinkan.']);
            }

            $locked->status = $target;
            $locked->status_reason = $reason;

            if ($target === ShopStatus::Approved && $locked->approved_at === null) {
                $locked->approved_at = now();
            }

            $locked->save();

            $locked->statusHistories()->create([
                'actor_id' => $actor->getKey(),
                'old_status' => $current,
                'new_status' => $target,
                'reason' => $reason,
            ]);

            return $locked;
        });
    }
}
