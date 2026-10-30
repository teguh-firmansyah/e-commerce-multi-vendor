<?php

namespace Database\Seeders;

use App\Enums\AdminPermission;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->admin()
            ->withPermissions(AdminPermission::ApprovePayout, AdminPermission::ApproveRefund)
            ->create([
                'name' => 'Admin NusaMarket',
                'email' => 'admin@nusamarket.test',
            ]);

        User::factory()->create([
            'name' => 'Customer Demo',
            'email' => 'customer@nusamarket.test',
        ]);

        $seller = User::factory()->create([
            'name' => 'Seller Demo',
            'email' => 'seller@nusamarket.test',
        ]);

        Shop::factory()->approved()->for($seller)->create([
            'name' => 'Toko Demo',
            'slug' => 'toko-demo',
        ]);

        $applicant = User::factory()->create([
            'name' => 'Calon Seller',
            'email' => 'calonseller@nusamarket.test',
        ]);

        Shop::factory()->for($applicant)->create([
            'name' => 'Toko Baru',
            'slug' => 'toko-baru',
        ]);
    }
}
