<?php

namespace App\Actions\Shops;

use App\Models\Shop;
use App\Services\Media\ImageProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateShopProfile
{
    public function __construct(private readonly ImageProcessor $images) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Shop $shop, array $data, ?UploadedFile $logo = null, bool $removeLogo = false): Shop
    {
        $oldLogo = $shop->logo_path;
        $newLogo = $oldLogo;
        $stored = null;

        if ($logo !== null) {
            $stored = $this->images->store($logo, 'shops/logos', 512);
            $newLogo = $stored;
        } elseif ($removeLogo) {
            $newLogo = null;
        }

        try {
            DB::transaction(function () use ($shop, $data, $newLogo): void {
                $shop->fill([
                    'name' => (string) $data['name'],
                    'description' => filled($data['description'] ?? null) ? (string) $data['description'] : null,
                    'phone' => (string) $data['phone'],
                    'is_open' => (bool) $data['is_open'],
                    'logo_path' => $newLogo,
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
            });
        } catch (Throwable $exception) {
            $this->images->delete($stored);

            throw $exception;
        }

        if ($newLogo !== $oldLogo) {
            $this->images->delete($oldLogo);
        }

        return $shop;
    }
}
