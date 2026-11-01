<?php

namespace Tests\Feature\Shops;

use App\Enums\ShopStatus;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopResubmitTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_resubmit_a_rejected_shop(): void
    {
        $shop = Shop::factory()->rejected()->create();

        $this->actingAs($shop->user)
            ->post('/shop/resubmit')
            ->assertRedirect(route('dashboard'));

        $fresh = $shop->fresh();

        $this->assertSame(ShopStatus::Pending, $fresh->status);
        $this->assertNull($fresh->status_reason);
        $this->assertDatabaseHas('shop_status_histories', [
            'shop_id' => $shop->id,
            'actor_id' => $shop->user_id,
            'old_status' => 'rejected',
            'new_status' => 'pending',
        ]);
    }

    public function test_owner_of_a_pending_shop_cannot_resubmit(): void
    {
        $shop = Shop::factory()->create();

        $this->actingAs($shop->user)
            ->post('/shop/resubmit')
            ->assertForbidden();
    }

    public function test_users_without_a_shop_cannot_resubmit(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/shop/resubmit')
            ->assertNotFound();
    }
}
