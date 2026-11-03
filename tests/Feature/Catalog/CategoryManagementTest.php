<?php

namespace Tests\Feature\Catalog;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_can_list_categories_as_a_tree(): void
    {
        $root = Category::factory()->create(['name' => 'Elektronik']);
        Category::factory()->childOf($root)->create(['name' => 'Aksesori']);

        $this->actingAs($this->admin())
            ->get('/admin/categories')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Categories/Index')
                ->has('categories', 2)
                ->where('categories.0.depth', 1)
                ->where('categories.1.depth', 2));
    }

    public function test_non_admins_cannot_manage_categories(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/admin/categories')->assertForbidden();
        $this->actingAs($customer)->post('/admin/categories', ['name' => 'Bebas'])->assertForbidden();
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_admin_can_create_a_root_category(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/categories', ['name' => 'Elektronik', 'is_active' => '1'])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::where('name', 'Elektronik')->firstOrFail();

        $this->assertSame('elektronik', $category->slug);
        $this->assertNull($category->parent_id);
        $this->assertTrue($category->is_active);
    }

    public function test_admin_can_create_a_child_category(): void
    {
        $parent = Category::factory()->create();

        $this->actingAs($this->admin())
            ->post('/admin/categories', ['name' => 'Kabel', 'parent_id' => $parent->id, 'is_active' => '1']);

        $this->assertSame($parent->id, Category::where('name', 'Kabel')->firstOrFail()->parent_id);
    }

    public function test_sibling_names_must_be_unique_but_other_parents_may_reuse_them(): void
    {
        $first = Category::factory()->create();
        $second = Category::factory()->create();
        Category::factory()->childOf($first)->create(['name' => 'Aksesori']);

        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/categories', ['name' => 'Aksesori', 'parent_id' => $first->id])
            ->assertSessionHasErrors('name');

        $this->actingAs($admin)
            ->post('/admin/categories', ['name' => 'Aksesori', 'parent_id' => $second->id])
            ->assertSessionHasNoErrors();
    }

    public function test_depth_is_limited_to_three_levels(): void
    {
        $first = Category::factory()->create();
        $second = Category::factory()->childOf($first)->create();
        $third = Category::factory()->childOf($second)->create();

        $this->actingAs($this->admin())
            ->post('/admin/categories', ['name' => 'Level Empat', 'parent_id' => $third->id])
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseMissing('categories', ['name' => 'Level Empat']);
    }

    public function test_a_category_cannot_become_its_own_descendant(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->childOf($parent)->create();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch("/admin/categories/{$parent->id}", ['name' => $parent->name, 'parent_id' => $child->id])
            ->assertSessionHasErrors('parent_id');

        $this->actingAs($admin)
            ->patch("/admin/categories/{$parent->id}", ['name' => $parent->name, 'parent_id' => $parent->id])
            ->assertSessionHasErrors('parent_id');

        $this->assertNull($parent->fresh()->parent_id);
    }

    public function test_updating_a_category_keeps_its_slug(): void
    {
        $category = Category::factory()->create(['name' => 'Lama', 'slug' => 'lama']);

        $this->actingAs($this->admin())
            ->patch("/admin/categories/{$category->id}", ['name' => 'Baru', 'is_active' => '1'])
            ->assertRedirect(route('admin.categories.index'));

        $fresh = $category->fresh();

        $this->assertSame('Baru', $fresh->name);
        $this->assertSame('lama', $fresh->slug);
    }

    public function test_a_category_without_children_can_be_deleted(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->delete("/admin/categories/{$category->id}")
            ->assertRedirect(route('admin.categories.index'));

        $this->assertModelMissing($category);
    }

    public function test_a_category_with_children_cannot_be_deleted(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->childOf($parent)->create();

        $this->actingAs($this->admin())->delete("/admin/categories/{$parent->id}");

        $this->assertModelExists($parent);
        $this->assertModelExists($child);
    }

    public function test_uploaded_images_are_stored_as_webp_and_replaced(): void
    {
        Storage::fake('public');

        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Pakaian',
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('a.jpg', 1600, 900),
        ]);

        $category = Category::where('name', 'Pakaian')->firstOrFail();
        $oldPath = (string) $category->image_path;

        $this->assertStringEndsWith('.webp', $oldPath);
        Storage::disk('public')->assertExists($oldPath);

        $this->actingAs($admin)->patch("/admin/categories/{$category->id}", [
            'name' => 'Pakaian',
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('b.png', 800, 800),
        ]);

        $newPath = (string) $category->fresh()->image_path;

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_files_that_are_not_images_are_refused(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post('/admin/categories', [
                'name' => 'Dokumen',
                'image' => UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('categories', ['name' => 'Dokumen']);
    }

    public function test_category_is_inactive_when_the_checkbox_is_omitted(): void
    {
        $this->actingAs($this->admin())->post('/admin/categories', ['name' => 'Nonaktif']);

        $this->assertFalse(Category::where('name', 'Nonaktif')->firstOrFail()->is_active);
    }
}
