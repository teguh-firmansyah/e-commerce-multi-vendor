<?php

namespace Tests\Feature\Customer;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardShopPropsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_without_a_shop_receives_null(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('shop', null));
    }

    public function test_pending_shop_status_is_shown(): void
    {
        $shop = Shop::factory()->create(['name' => 'Toko Uji']);

        $this->actingAs($shop->user)
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->where('shop.name', 'Toko Uji')
                ->where('shop.status', 'pending')
                ->where('shop.reason', null));
    }

    public function test_rejection_reason_is_shown(): void
    {
        $shop = Shop::factory()->rejected()->create();

        $this->actingAs($shop->user)
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->where('shop.status', 'rejected')
                ->where('shop.reason', 'Dokumen belum lengkap'));
    }
}
