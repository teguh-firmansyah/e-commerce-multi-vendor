<?php

namespace App\Actions\Shops;

use App\Models\Shop;
use Illuminate\Support\Facades\DB;

class UpdateShopProfile
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Shop $shop, array $data): Shop
    {
        return DB::transaction(function () use ($shop, $data): Shop {
            $shop->fill([
                'name' => (string) $data['name'],
                'description' => filled($data['description'] ?? null) ? (string) $data['description'] : null,
                'phone' => (string) $data['phone'],
                'is_open' => (bool) $data['is_open'],
            ])->save();

            $address = $shop->addresses()->where('is_default', true)->first()
                ?? $shop->addresses()->make([
                    'label' => 'Alamat Utama',
                    'recipient_name' => $shop->user->name,
                    'is_default' => true,
                ]);

            $address->fill([
                'phone' => (string) $data['phone'],
                'province' => (string) $data['province'],
                'city' => (string) $data['city'],
                'postal_code' => (string) $data['postal_code'],
                'address_line' => (string) $data['address_line'],
            ])->save();

            return $shop;
        });
    }
}
