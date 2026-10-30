<?php

namespace Tests\Feature\Shops;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ShopPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_own_shop(): void
    {
        $shop = Shop::factory()->approved()->create();

        $this->assertTrue(Gate::forUser($shop->user)->allows('update', $shop));
    }

    public function test_other_users_cannot_update_the_shop(): void
    {
        $shop = Shop::factory()->approved()->create();

        $this->assertTrue(Gate::forUser(User::factory()->create())->denies('update', $shop));
        $this->assertTrue(Gate::forUser(User::factory()->admin()->create())->denies('update', $shop));
    }

    public function test_owner_of_a_suspended_shop_cannot_update_it(): void
    {
        $shop = Shop::factory()->suspended()->create();

        $this->assertTrue(Gate::forUser($shop->user)->denies('update', $shop));
    }

    public function test_restricted_owner_cannot_update_the_shop(): void
    {
        $shop = Shop::factory()->approved()->create();
        $shop->user->forceFill(['status' => 'restricted'])->save();

        $this->assertTrue(Gate::forUser($shop->user->fresh())->denies('update', $shop));
    }

    public function test_only_active_admins_can_review_shops(): void
    {
        $shop = Shop::factory()->create();

        $this->assertTrue(Gate::forUser(User::factory()->admin()->create())->allows('review', $shop));
        $this->assertTrue(Gate::forUser(User::factory()->admin()->restricted()->create())->denies('review', $shop));
        $this->assertTrue(Gate::forUser($shop->user)->denies('review', $shop));
    }

    public function test_owner_can_resubmit_only_when_rejected(): void
    {
        $rejected = Shop::factory()->rejected()->create();
        $pending = Shop::factory()->create();

        $this->assertTrue(Gate::forUser($rejected->user)->allows('resubmit', $rejected));
        $this->assertTrue(Gate::forUser($pending->user)->denies('resubmit', $pending));
        $this->assertTrue(Gate::forUser(User::factory()->create())->denies('resubmit', $rejected));
    }
}
