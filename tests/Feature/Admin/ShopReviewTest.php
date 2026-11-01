<?php

namespace Tests\Feature\Admin;

use App\Enums\ShopStatus;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShopReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_pending_shops(): void
    {
        Shop::factory()->count(2)->create();
        Shop::factory()->approved()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/shops')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Shops/Index')
                ->where('status', 'pending')
                ->has('shops.data', 2));
    }

    public function test_status_filter_lists_other_statuses(): void
    {
        Shop::factory()->create();
        Shop::factory()->approved()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/shops?status=approved')
            ->assertInertia(fn (Assert $page) => $page
                ->where('status', 'approved')
                ->has('shops.data', 1));
    }

    public function test_customers_cannot_open_the_review_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/shops')
            ->assertForbidden();
    }

    public function test_admin_can_approve_a_pending_shop(): void
    {
        $admin = User::factory()->admin()->create();
        $shop = Shop::factory()->create();

        $this->actingAs($admin)
            ->patch("/admin/shops/{$shop->id}/status", ['status' => 'approved'])
            ->assertRedirect();

        $this->assertSame(ShopStatus::Approved, $shop->fresh()->status);
        $this->assertDatabaseHas('shop_status_histories', [
            'shop_id' => $shop->id,
            'actor_id' => $admin->id,
            'old_status' => 'pending',
            'new_status' => 'approved',
        ]);
        $this->assertTrue($shop->user->fresh()->isSeller());
    }

    public function test_admin_can_reject_with_a_reason(): void
    {
        $shop = Shop::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->patch("/admin/shops/{$shop->id}/status", [
                'status' => 'rejected',
                'reason' => 'Alamat tidak lengkap',
            ])
            ->assertRedirect();

        $this->assertSame(ShopStatus::Rejected, $shop->fresh()->status);
        $this->assertSame('Alamat tidak lengkap', $shop->fresh()->status_reason);
    }

    public function test_rejection_without_a_reason_is_refused(): void
    {
        $shop = Shop::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->patch("/admin/shops/{$shop->id}/status", ['status' => 'rejected'])
            ->assertSessionHasErrors('reason');

        $this->assertSame(ShopStatus::Pending, $shop->fresh()->status);
    }

    public function test_admin_can_suspend_and_reactivate_a_shop(): void
    {
        $admin = User::factory()->admin()->create();
        $shop = Shop::factory()->approved()->create();

        $this->actingAs($admin)
            ->patch("/admin/shops/{$shop->id}/status", [
                'status' => 'suspended',
                'reason' => 'Produk melanggar kebijakan',
            ]);

        $this->assertSame(ShopStatus::Suspended, $shop->fresh()->status);
        $this->assertFalse($shop->user->fresh()->isSeller());

        $this->actingAs($admin)
            ->patch("/admin/shops/{$shop->id}/status", ['status' => 'approved']);

        $this->assertSame(ShopStatus::Approved, $shop->fresh()->status);
        $this->assertTrue($shop->user->fresh()->isSeller());
    }

    public function test_invalid_transition_is_refused(): void
    {
        $shop = Shop::factory()->rejected()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->patch("/admin/shops/{$shop->id}/status", ['status' => 'approved'])
            ->assertSessionHasErrors('status');

        $this->assertSame(ShopStatus::Rejected, $shop->fresh()->status);
    }

    public function test_non_admins_cannot_change_shop_status(): void
    {
        $target = Shop::factory()->create();

        $users = [
            User::factory()->create(),
            Shop::factory()->approved()->create()->user,
            $target->user,
        ];

        foreach ($users as $user) {
            $this->actingAs($user)
                ->patch("/admin/shops/{$target->id}/status", ['status' => 'approved'])
                ->assertForbidden();
        }

        $this->assertSame(ShopStatus::Pending, $target->fresh()->status);
    }
}
