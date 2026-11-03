<?php

namespace App\Services\Catalog;

use App\Models\Category;
use Illuminate\Support\Collection;

class CategoryTree
{
    /**
     * @return array<int, array{category: Category, depth: int}>
     */
    public function flatten(?int $excludeId = null): array
    {
        /** @var Collection<int, Collection<int, Category>> $grouped */
        $grouped = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Category $category): int => $category->parent_id ?? 0);

        return $this->walk($grouped, 0, 1, $excludeId);
    }

    /**
     * @param  Collection<int, Collection<int, Category>>  $grouped
     * @return array<int, array{category: Category, depth: int}>
     */
    private function walk(Collection $grouped, int $parentId, int $depth, ?int $excludeId): array
    {
        $nodes = [];

        foreach ($grouped->get($parentId, collect()) as $category) {
            if ($category->id === $excludeId) {
                continue;
            }

            $nodes[] = ['category' => $category, 'depth' => $depth];
            $nodes = [...$nodes, ...$this->walk($grouped, $category->id, $depth + 1, $excludeId)];
        }

        return $nodes;
    }
}
