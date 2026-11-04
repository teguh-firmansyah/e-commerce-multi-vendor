<?php

namespace Tests\Feature\Shops;

use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShopLogoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Toko Berlogo',
            'phone' => '081234567890',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'postal_code' => '40111',
            'address_line' => 'Jl. Merdeka No. 10',
        ];
    }

    public function test_owner_can_upload_a_logo(): void
    {
        Storage::fake('public');

        $shop = Shop::factory()->approved()->create();

        $this->actingAs($shop->user)
            ->patch('/shop', $this->payload(['logo' => UploadedFile::fake()->image('logo.png', 1000, 1000)]))
            ->assertRedirect(route('shop.edit'));

        $path = (string) $shop->fresh()->logo_path;

        $this->assertStringStartsWith('shops/logos/', $path);
        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);

        $size = getimagesizefromstring((string) Storage::disk('public')->get($path));

        $this->assertSame(512, $size[0]);
    }

    public function test_replacing_the_logo_deletes_the_old_file(): void
    {
        Storage::fake('public');

        $shop = Shop::factory()->approved()->create();

        $this->actingAs($shop->user)
            ->patch('/shop', $this->payload(['logo' => UploadedFile::fake()->image('a.jpg', 600, 600)]));

        $oldPath = (string) $shop->fresh()->logo_path;

        $this->actingAs($shop->user)
            ->patch('/shop', $this->payload(['logo' => UploadedFile::fake()->image('b.jpg', 600, 600)]));

        $newPath = (string) $shop->fresh()->logo_path;

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_owner_can_remove_the_logo(): void
    {
        Storage::fake('public');

        $shop = Shop::factory()->approved()->create();

        $this->actingAs($shop->user)
            ->patch('/shop', $this->payload(['logo' => UploadedFile::fake()->image('a.jpg', 600, 600)]));

        $path = (string) $shop->fresh()->logo_path;

        $this->actingAs($shop->user)
            ->patch('/shop', $this->payload(['remove_logo' => '1']));

        $this->assertNull($shop->fresh()->logo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_files_that_are_not_images_are_refused(): void
    {
        Storage::fake('public');

        $shop = Shop::factory()->approved()->create();

        $this->actingAs($shop->user)
            ->patch('/shop', $this->payload(['logo' => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf')]))
            ->assertSessionHasErrors('logo');

        $this->assertNull($shop->fresh()->logo_path);
    }

    public function test_logo_url_is_sent_to_the_edit_page(): void
    {
        Storage::fake('public');

        $shop = Shop::factory()->approved()->create();

        $this->actingAs($shop->user)
            ->patch('/shop', $this->payload(['logo' => UploadedFile::fake()->image('a.jpg', 600, 600)]));

        $this->actingAs($shop->user)
            ->get('/shop/edit')
            ->assertInertia(fn (Assert $page) => $page
                ->where('shop.logo_url', fn (string $url) => str_contains($url, 'shops/logos/')));
    }

    public function test_suspended_owner_cannot_upload_a_logo(): void
    {
        Storage::fake('public');

        $shop = Shop::factory()->suspended()->create();

        $this->actingAs($shop->user)
            ->patch('/shop', $this->payload(['logo' => UploadedFile::fake()->image('a.jpg', 600, 600)]))
            ->assertForbidden();

        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
