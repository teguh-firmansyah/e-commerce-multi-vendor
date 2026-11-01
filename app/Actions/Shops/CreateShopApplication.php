<?php

namespace App\Actions\Shops;

use App\Enums\ShopStatus;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateShopApplication
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): Shop
    {
        return DB::transaction(function () use ($user, $data): Shop {
            $name = (string) $data['name'];
            $phone = (string) $data['phone'];

            $shop = $user->shop()->create([
                'name' => $name,
                'slug' => $this->uniqueSlug($name),
                'description' => isset($data['description']) ? (string) $data['description'] : null,
                'phone' => $phone,
            ]);

            $shop->addresses()->create([
                'label' => 'Alamat Utama',
                'recipient_name' => $user->name,
                'phone' => $phone,
                'province' => (string) $data['province'],
                'city' => (string) $data['city'],
                'postal_code' => (string) $data['postal_code'],
                'address_line' => (string) $data['address_line'],
                'is_default' => true,
            ]);

            $shop->statusHistories()->create([
                'actor_id' => $user->id,
                'old_status' => null,
                'new_status' => ShopStatus::Pending,
            ]);

            return $shop;
        });
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'toko';
        $slug = $base;
        $counter = 2;

        while (Shop::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
