<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Elektronik' => ['Aksesori' => ['Kabel', 'Charger'], 'Komputer' => []],
            'Pakaian' => ['Pria' => [], 'Wanita' => []],
            'Perlengkapan Sekolah' => ['Alat Tulis' => [], 'Tas' => []],
        ];

        $order = 0;

        foreach ($tree as $rootName => $children) {
            $root = $this->make($rootName, null, $order++);

            foreach ($children as $childName => $grandchildren) {
                $child = $this->make($childName, $root, 0);

                foreach ($grandchildren as $grandchildName) {
                    $this->make($grandchildName, $child, 0);
                }
            }
        }
    }

    private function make(string $name, ?Category $parent, int $order): Category
    {
        return Category::query()->firstOrCreate(
            ['slug' => Str::slug($name)],
            [
                'name' => $name,
                'parent_id' => $parent?->id,
                'sort_order' => $order,
                'is_active' => true,
            ],
        );
    }
}
