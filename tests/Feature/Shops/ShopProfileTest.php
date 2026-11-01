<?php

namespace Tests\Feature\Shops;

use App\Enums\ShopStatus;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShopProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Toko Nama Baru',
            'description' => 'Deskripsi baru',
            'phone' => '081298765432',
            'province' => 'Jawa Timur',
            'city' => 'Surabaya',
            'postal_code' => '60111',
            'address_line' => 'Jl. Pahlawan No. 5',
        ];
    }

    public function test_owner_can_open_the_edit_page(): void
    {
        $shop = Shop::factory()->create(['name' => 'Toko Uji']);

        $this->actingAs($shop->user)
            ->get('/shop/edit')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Shop/Edit')
                ->where('shop.name', 'Toko Uji')
                ->where('shop.status', 'pending'));
    }

    public function test_owner_can_update_profile_and_address(): void
    {
        $shop = Shop::factory()->approved()->create(['name' => 'Nama Lama']);

        $this->actingAs($shop->user)
            ->patch('/shop', $this->payload())
            ->assertRedirect(route('shop.edit'));

        $fresh = $shop->fresh();

        $this->assertSame('Toko Nama Baru', $fresh->name);
        $this->assertSame($shop->slug, $fresh->slug);
        $this->assertFalse($fresh->is_open);
        $this->assertDatabaseHas('shop_addresses', [
            'shop_id' => $shop->id,
            'city' => 'Surabaya',
            'is_default' => true,
        ]);
    }

    public function test_existing_default_address_is_updated_not_duplicated(): void
    {
        $shop = Shop::factory()->approved()->create();

        $this->actingAs($shop->user)->patch('/shop', $this->payload(['is_open' => '1']));
        $this->actingAs($shop->user)->patch('/shop', $this->payload(['city' => 'Malang']));

        $this->assertDatabaseCount('shop_addresses', 1);
        $this->assertDatabaseHas('shop_addresses', ['shop_id' => $shop->id, 'city' => 'Malang']);
    }

    public function test_status_and_slug_cannot_be_changed(): void
    {
        $shop = Shop::factory()->create();

        $this->actingAs($shop->user)->patch('/shop', $this->payload([
            'status' => 'approved',
            'slug' => 'slug-baru',
            'user_id' => '999',
        ]));

        $fresh = $shop->fresh();

        $this->assertSame(ShopStatus::Pending, $fresh->status);
        $this->assertSame($shop->slug, $fresh->slug);
        $this->assertSame($shop->user_id, $fresh->user_id);
    }

    public function test_shop_name_must_be_unique_except_own(): void
    {
        Shop::factory()->create(['name' => 'Toko Kembar']);
        $own = Shop::factory()->create(['name' => 'Toko Saya Sendiri']);

        $this->actingAs($own->user)
            ->patch('/shop', $this->payload(['name' => 'Toko Kembar']))
            ->assertSessionHasErrors('name');

        $this->actingAs($own->user)
            ->patch('/shop', $this->payload(['name' => 'Toko Saya Sendiri']))
            ->assertSessionHasNoErrors();
    }

    public function test_suspended_owner_cannot_edit(): void
    {
        $shop = Shop::factory()->suspended()->create();

        $this->actingAs($shop->user)->get('/shop/edit')->assertForbidden();
        $this->actingAs($shop->user)->patch('/shop', $this->payload())->assertForbidden();
    }

    public function test_users_without_a_shop_get_not_found(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/shop/edit')->assertNotFound();
        $this->actingAs($user)->patch('/shop', $this->payload())->assertNotFound();
    }

    public function test_rejected_owner_can_edit_and_status_stays_rejected(): void
    {
        $shop = Shop::factory()->rejected()->create();

        $this->actingAs($shop->user)
            ->patch('/shop', $this->payload())
            ->assertRedirect(route('shop.edit'));

        $this->assertSame(ShopStatus::Rejected, $shop->fresh()->status);
    }

    public function test_required_fields_are_validated(): void
    {
        $shop = Shop::factory()->create();

        $this->actingAs($shop->user)
            ->patch('/shop', [])
            ->assertSessionHasErrors(['name', 'phone', 'province', 'city', 'postal_code', 'address_line']);
    }
}
