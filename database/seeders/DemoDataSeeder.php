<?php

namespace Database\Seeders;

use App\Enums\AdminPermission;
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
    }
}
