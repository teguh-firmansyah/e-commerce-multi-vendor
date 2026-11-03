<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import CategoryController from '@/actions/App/Http/Controllers/Admin/CategoryController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes/admin';
import { create, edit, index } from '@/routes/admin/categories';

interface CategoryRow {
    id: number;
    name: string;
    slug: string;
    depth: number;
    sort_order: number;
    is_active: boolean;
    image_url: string | null;
}

defineProps<{
    categories: CategoryRow[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard Admin',
                href: dashboard(),
            },
            {
                title: 'Kategori',
                href: index(),
            },
        ],
    },
});

function remove(id: number): void {
    if (window.confirm('Hapus kategori ini?')) {
        router.delete(CategoryController.destroy.url(id));
    }
}
</script>

<template>
    <Head title="Kategori" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Kategori</h1>
            <Button as-child>
                <Link :href="create()">Tambah Kategori</Link>
            </Button>
        </div>

        <p
            v-if="categories.length === 0"
            class="text-sm text-muted-foreground"
        >
            Belum ada kategori.
        </p>

        <div v-else class="overflow-x-auto rounded-xl border">
            <table class="w-full text-left text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nama</th>
                        <th class="px-4 py-3 font-medium">Slug</th>
                        <th class="px-4 py-3 font-medium">Urutan</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="category in categories"
                        :key="category.id"
                        class="border-t"
                    >
                        <td class="px-4 py-3">
                            <div
                                class="flex items-center gap-3"
                                :style="{
                                    paddingLeft: `${(category.depth - 1) * 1.5}rem`,
                                }"
                            >
                                <img
                                    v-if="category.image_url"
                                    :src="category.image_url"
                                    alt=""
                                    class="size-8 rounded object-cover"
                                />
                                <span class="font-medium">
                                    {{ category.name }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ category.slug }}
                        </td>
                        <td class="px-4 py-3">{{ category.sort_order }}</td>
                        <td class="px-4 py-3">
                            <Badge
                                :variant="
                                    category.is_active
                                        ? 'default'
                                        : 'secondary'
                                "
                            >
                                {{ category.is_active ? 'Aktif' : 'Nonaktif' }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="edit(category.id)">Ubah</Link>
                                </Button>
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    @click="remove(category.id)"
                                >
                                    Hapus
                                </Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>