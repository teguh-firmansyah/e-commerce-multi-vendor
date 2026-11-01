<?php

namespace Tests\Feature\Settings;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountDeletionRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_sellers_cannot_delete_their_account(): void
    {
        $shop = Shop::factory()->approved()->create();

        $this->actingAs($shop->user)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect(route('profile.edit'));

        $this->assertModelExists($shop->user);
        $this->assertModelExists($shop);
    }

    public function test_owners_of_pending_shops_cannot_delete_their_account(): void
    {
        $shop = Shop::factory()->create();

        $this->actingAs($shop->user)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect(route('profile.edit'));

        $this->assertModelExists($shop->user);
    }

    public function test_admins_cannot_delete_their_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect(route('profile.edit'));

        $this->assertModelExists($admin);
    }
}
