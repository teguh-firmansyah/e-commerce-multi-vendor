<?php

namespace Tests\Feature\Authorization;

use App\Enums\AdminPermission;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SharedAccessPropsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_has_no_seller_or_admin_access(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->where('access.isAdmin', false)
                ->where('access.isSeller', false)
                ->where('access.shop', null));
    }

    public function test_seller_receives_shop_summary(): void
    {
        $shop = Shop::factory()->approved()->create(['name' => 'Toko Uji']);

        $this->actingAs($shop->user)
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->where('access.isSeller', true)
                ->where('access.shop.name', 'Toko Uji')
                ->where('access.shop.status', 'approved'));
    }

    public function test_admin_permissions_are_not_sent_to_the_browser(): void
    {
        $admin = User::factory()->admin()->withPermissions(AdminPermission::ApprovePayout)->create();

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->where('access.isAdmin', true)
                ->missing('auth.user.permissions'));
    }
}
