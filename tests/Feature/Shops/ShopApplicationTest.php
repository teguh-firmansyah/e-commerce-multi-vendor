<?php

namespace Tests\Feature\Shops;

use App\Enums\ShopStatus;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopApplicationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function payload(): array
    {
        return [
            'name' => 'Toko Sumber Rejeki',
            'description' => 'Menjual perlengkapan sekolah',
            'phone' => '081234567890',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'postal_code' => '40111',
            'address_line' => 'Jl. Merdeka No. 10',
        ];
    }

    public function test_customer_can_apply_for_a_shop(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/shop', $this->payload())
            ->assertRedirect(route('dashboard'));

        $shop = $user->shop()->firstOrFail();

        $this->assertSame(ShopStatus::Pending, $shop->status);
        $this->assertSame('toko-sumber-rejeki', $shop->slug);
        $this->assertDatabaseHas('shop_addresses', [
            'shop_id' => $shop->id,
            'city' => 'Bandung',
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('shop_status_histories', [
            'shop_id' => $shop->id,
            'actor_id' => $user->id,
            'old_status' => null,
            'new_status' => 'pending',
        ]);
        $this->assertFalse($user->fresh()->isSeller());
    }

    public function test_status_input_is_ignored(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/shop', $this->payload() + ['status' => 'approved']);

        $this->assertSame(ShopStatus::Pending, $user->shop()->firstOrFail()->status);
    }

    public function test_slug_is_made_unique(): void
    {
        Shop::factory()->create(['slug' => 'toko-sumber-rejeki']);

        $user = User::factory()->create();

        $this->actingAs($user)->post('/shop', ['name' => 'Toko Sumber Rejeki!'] + $this->payload());

        $this->assertSame('toko-sumber-rejeki-2', $user->shop()->firstOrFail()->slug);
    }

    public function test_users_with_a_shop_cannot_apply_again(): void
    {
        $shop = Shop::factory()->create();

        $this->actingAs($shop->user)
            ->post('/shop', ['name' => 'Toko Lain'] + $this->payload())
            ->assertForbidden();

        $this->assertDatabaseCount('shops', 1);
    }

    public function test_admins_cannot_apply_for_a_shop(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post('/shop', $this->payload())
            ->assertForbidden();
    }

    public function test_restricted_users_cannot_apply_for_a_shop(): void
    {
        $this->actingAs(User::factory()->restricted()->create())
            ->post('/shop', $this->payload())
            ->assertForbidden();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->post('/shop', $this->payload())->assertRedirect(route('login'));
    }

    public function test_required_fields_are_validated(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/shop', [])
            ->assertSessionHasErrors(['name', 'phone', 'province', 'city', 'postal_code', 'address_line']);
    }
}
