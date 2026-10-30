<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $shop_id
 * @property string $label
 * @property string $recipient_name
 * @property string $phone
 * @property string $province
 * @property string $city
 * @property string $postal_code
 * @property string $address_line
 * @property bool $is_default
 */
#[Fillable(['label', 'recipient_name', 'phone', 'province', 'city', 'postal_code', 'address_line', 'is_default'])]
class ShopAddress extends Model
{
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Shop, $this>
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
