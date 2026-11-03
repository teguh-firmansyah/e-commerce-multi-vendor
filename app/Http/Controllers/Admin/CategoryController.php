<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Catalog\SaveCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveCategoryRequest;
use App\Models\Category;
use App\Services\Catalog\CategoryTree;
use App\Services\Media\ImageProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(CategoryTree $tree): Response
    {
        return Inertia::render('Admin/Categories/Index', [
            'categories' => array_map(fn (array $node): array => [
                'id' => $node['category']->id,
                'name' => $node['category']->name,
                'slug' => $node['category']->slug,
                'depth' => $node['depth'],
                'sort_order' => $node['category']->sort_order,
                'is_active' => $node['category']->is_active,
                'image_url' => $node['category']->imageUrl(),
            ], $tree->flatten()),
        ]);
    }

    public function create(CategoryTree $tree): Response
    {
        return Inertia::render('Admin/Categories/Form', [
            'category' => null,
            'parents' => $this->parentOptions($tree, null),
        ]);
    }

    public function store(SaveCategoryRequest $request, SaveCategory $action): RedirectResponse
    {
        $action->execute(null, $this->payload($request), $this->image($request));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Kategori ditambahkan.'),
        ]);

        return to_route('admin.categories.index');
    }

    public function edit(Category $category, CategoryTree $tree): Response
    {
        return Inertia::render('Admin/Categories/Form', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active,
                'parent_id' => $category->parent_id,
                'image_url' => $category->imageUrl(),
            ],
            'parents' => $this->parentOptions($tree, $category),
        ]);
    }

    public function update(SaveCategoryRequest $request, Category $category, SaveCategory $action): RedirectResponse
    {
        $action->execute($category, $this->payload($request), $this->image($request));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Kategori diperbarui.'),
        ]);

        return to_route('admin.categories.index');
    }

    public function destroy(Category $category, ImageProcessor $images): RedirectResponse
    {
        if ($category->children()->exists()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('Kategori masih memiliki subkategori. Nonaktifkan saja atau hapus subkategorinya terlebih dahulu.'),
            ]);

            return back();
        }

        $imagePath = $category->image_path;

        $category->delete();
        $images->delete($imagePath);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Kategori dihapus.'),
        ]);

        return to_route('admin.categories.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(SaveCategoryRequest $request): array
    {
        return [
            ...$request->safe()->except('image'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function image(SaveCategoryRequest $request): ?UploadedFile
    {
        $image = $request->file('image');

        return $image instanceof UploadedFile ? $image : null;
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    private function parentOptions(CategoryTree $tree, ?Category $editing): array
    {
        $options = [];

        foreach ($tree->flatten($editing?->id) as $node) {
            if ($node['depth'] < Category::MAX_DEPTH) {
                $options[] = [
                    'id' => $node['category']->id,
                    'label' => str_repeat('— ', $node['depth'] - 1).$node['category']->name,
                ];
            }
        }

        return $options;
    }
}
