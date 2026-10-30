<?php

namespace Tests\Feature\Shops;

use App\Actions\Shops\ChangeShopStatus;
use App\Enums\ShopStatus;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ChangeShopStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_approving_a_pending_shop_records_history(): void
    {
        $admin = User::factory()->admin()->create();
        $shop = Shop::factory()->create();

        $result = app(ChangeShopStatus::class)->execute($shop, ShopStatus::Approved, $admin);

        $this->assertSame(ShopStatus::Approved, $result->status);
        $this->assertNotNull($result->approved_at);
        $this->assertDatabaseHas('shop_status_histories', [
            'shop_id' => $shop->id,
            'actor_id' => $admin->id,
            'old_status' => 'pending',
            'new_status' => 'approved',
        ]);
    }

    public function test_rejection_requires_a_reason(): void
    {
        $this->expectException(ValidationException::class);

        app(ChangeShopStatus::class)->execute(
            Shop::factory()->create(),
            ShopStatus::Rejected,
            User::factory()->admin()->create(),
        );
    }

    public function test_rejection_stores_the_reason(): void
    {
        $shop = Shop::factory()->create();

        $result = app(ChangeShopStatus::class)->execute(
            $shop,
            ShopStatus::Rejected,
            User::factory()->admin()->create(),
            'Foto toko tidak jelas',
        );

        $this->assertSame('Foto toko tidak jelas', $result->status_reason);
        $this->assertDatabaseHas('shop_status_histories', [
            'shop_id' => $shop->id,
            'new_status' => 'rejected',
            'reason' => 'Foto toko tidak jelas',
        ]);
    }

    public function test_invalid_transition_is_refused_without_history(): void
    {
        $shop = Shop::factory()->rejected()->create();

        try {
            app(ChangeShopStatus::class)->execute(
                $shop,
                ShopStatus::Approved,
                User::factory()->admin()->create(),
            );
            $this->fail('Transition should have been refused.');
        } catch (ValidationException) {
            $this->assertSame(ShopStatus::Rejected, $shop->fresh()->status);
            $this->assertDatabaseCount('shop_status_histories', 0);
        }
    }

    public function test_only_users_with_approved_shops_are_sellers(): void
    {
        $approved = Shop::factory()->approved()->create();
        $pending = Shop::factory()->create();
        $suspended = Shop::factory()->suspended()->create();

        $this->assertTrue($approved->user->isSeller());
        $this->assertFalse($pending->user->isSeller());
        $this->assertFalse($suspended->user->isSeller());
        $this->assertFalse(User::factory()->create()->isSeller());
    }

    public function test_status_cannot_be_mass_assigned(): void
    {
        $user = User::factory()->create();

        $shop = $user->shop()->create([
            'name' => 'Toko X',
            'slug' => 'toko-x',
            'status' => 'approved',
            'user_id' => 999,
        ]);

        $shop->refresh();

        $this->assertSame(ShopStatus::Pending, $shop->status);
        $this->assertSame($user->id, $shop->user_id);
    }
}
