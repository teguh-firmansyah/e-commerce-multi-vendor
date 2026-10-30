<?php

namespace Database\Factories;

use App\Enums\ShopStatus;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('####'),
            'description' => fake()->sentence(),
            'phone' => fake()->phoneNumber(),
            'status' => ShopStatus::Pending,
            'status_reason' => null,
            'approved_at' => null,
            'is_open' => true,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ShopStatus::Approved,
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ShopStatus::Rejected,
            'status_reason' => 'Dokumen belum lengkap',
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ShopStatus::Suspended,
            'status_reason' => 'Pelanggaran kebijakan',
            'approved_at' => now(),
        ]);
    }
}
