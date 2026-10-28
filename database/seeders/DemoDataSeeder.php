<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin NusaMarket',
            'email' => 'admin@nusamarket.test',
        ]);

        User::factory()->create([
            'name' => 'Customer Demo',
            'email' => 'customer@nusamarket.test',
        ]);
    }
}
