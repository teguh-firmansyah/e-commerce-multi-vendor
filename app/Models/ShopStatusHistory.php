<?php

namespace App\Models;

use App\Enums\ShopStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $shop_id
 * @property int|null $actor_id
 * @property ShopStatus|null $old_status
 * @property ShopStatus $new_status
 * @property string|null $reason
 */
#[Fillable(['actor_id', 'old_status', 'new_status', 'reason'])]
class ShopStatusHistory extends Model
{
    protected function casts(): array
    {
        return [
            'old_status' => ShopStatus::class,
            'new_status' => ShopStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Shop, $this>
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
