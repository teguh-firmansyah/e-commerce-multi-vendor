<?php

namespace App\Actions\Catalog;

use App\Models\Category;
use App\Services\Media\ImageProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaveCategory
{
    public function __construct(private readonly ImageProcessor $images) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(?Category $category, array $data, ?UploadedFile $image = null): Category
    {
        $parent = filled($data['parent_id'] ?? null)
            ? Category::query()->find((int) $data['parent_id'])
            : null;

        $this->assertValidParent($category, $parent);

        return DB::transaction(function () use ($category, $data, $image, $parent): Category {
            $model = $category ?? new Category;
            $oldImage = $model->image_path;

            $model->fill([
                'parent_id' => $parent?->id,
                'name' => (string) $data['name'],
                'description' => filled($data['description'] ?? null) ? (string) $data['description'] : null,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => (bool) $data['is_active'],
            ]);

            if (! $model->exists) {
                $model->slug = $this->uniqueSlug((string) $data['name']);
            }

            if ($image !== null) {
                $model->image_path = $this->images->store($image, 'categories', 800);
            }

            $model->save();

            if ($image !== null) {
                $this->images->delete($oldImage);
            }

            return $model;
        });
    }

    private function assertValidParent(?Category $category, ?Category $parent): void
    {
        if ($parent === null) {
            return;
        }

        for ($node = $parent; $node !== null; $node = $node->parent) {
            if ($category !== null && $node->id === $category->id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Induk kategori tidak valid.',
                ]);
            }
        }

        $ownHeight = $category?->height() ?? 1;

        if ($parent->depth() + $ownHeight > Category::MAX_DEPTH) {
            throw ValidationException::withMessages([
                'parent_id' => 'Kedalaman kategori maksimal '.Category::MAX_DEPTH.' tingkat.',
            ]);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $counter = 2;

        while (Category::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
