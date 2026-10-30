<?php

namespace Tests\Feature\Authorization;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_seller_area(): void
    {
        $this->get('/seller/dashboard')->assertRedirect(route('login'));
    }

    public function test_customers_without_shop_cannot_access_seller_area(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/seller/dashboard')
            ->assertForbidden();
    }

    public function test_owners_of_pending_rejected_or_suspended_shops_cannot_access_seller_area(): void
    {
        foreach ([Shop::factory(), Shop::factory()->rejected(), Shop::factory()->suspended()] as $factory) {
            $this->actingAs($factory->create()->user)
                ->get('/seller/dashboard')
                ->assertForbidden();
        }
    }

    public function test_owner_of_an_approved_shop_can_access_seller_area(): void
    {
        $this->actingAs(Shop::factory()->approved()->create()->user)
            ->get('/seller/dashboard')
            ->assertOk();
    }

    public function test_restricted_owner_of_an_approved_shop_cannot_access_seller_area(): void
    {
        $user = Shop::factory()->approved()->create()->user;
        $user->forceFill(['status' => 'restricted'])->save();

        $this->actingAs($user->fresh())
            ->get('/seller/dashboard')
            ->assertForbidden();
    }

    public function test_sellers_cannot_access_admin_area(): void
    {
        $this->actingAs(Shop::factory()->approved()->create()->user)
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_admins_without_shop_cannot_access_seller_area(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/seller/dashboard')
            ->assertForbidden();
    }
}
