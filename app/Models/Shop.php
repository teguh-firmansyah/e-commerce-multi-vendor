<?php

namespace App\Models;

use App\Enums\ShopStatus;
use Carbon\CarbonInterface;
use Database\Factories\ShopFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $phone
 * @property string|null $logo_path
 * @property string|null $banner_path
 * @property ShopStatus $status
 * @property string|null $status_reason
 * @property CarbonInterface|null $approved_at
 * @property bool $is_open
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
#[Fillable(['name', 'slug', 'description', 'phone', 'logo_path', 'banner_path', 'is_open'])]
class Shop extends Model
{
    /** @use HasFactory<ShopFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'pending',
        'is_open' => true,
    ];

    protected function casts(): array
    {
        return [
            'status' => ShopStatus::class,
            'approved_at' => 'datetime',
            'is_open' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ShopAddress, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(ShopAddress::class);
    }

    /**
     * @return HasMany<ShopStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(ShopStatusHistory::class);
    }

    public function isApproved(): bool
    {
        return $this->status === ShopStatus::Approved;
    }
}
