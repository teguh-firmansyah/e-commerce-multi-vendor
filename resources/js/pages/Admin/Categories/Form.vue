<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import CategoryController from '@/actions/App/Http/Controllers/Admin/CategoryController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes/admin';
import { index } from '@/routes/admin/categories';

interface CategoryData {
    id: number;
    name: string;
    description: string | null;
    sort_order: number;
    is_active: boolean;
    parent_id: number | null;
    image_url: string | null;
}

interface ParentOption {
    id: number;
    label: string;
}

defineProps<{
    category: CategoryData | null;
    parents: ParentOption[];
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

const fieldClass =
    'flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';
</script>

<template>
    <Head :title="category ? 'Ubah Kategori' : 'Tambah Kategori'" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
        <Heading
            :title="category ? 'Ubah Kategori' : 'Tambah Kategori'"
            description="Kategori dapat bertingkat hingga 3 tingkat."
        />

        <Form
            v-bind="
                category
                    ? CategoryController.update.form(category.id)
                    : CategoryController.store.form()
            "
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Nama kategori</Label>
                <Input
                    id="name"
                    name="name"
                    :default-value="category?.name ?? ''"
                    required
                    maxlength="100"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="parent_id">Kategori induk</Label>
                <select
                    id="parent_id"
                    name="parent_id"
                    :class="[fieldClass, 'h-9']"
                >
                    <option value="">Tanpa induk (kategori utama)</option>
                    <option
                        v-for="parent in parents"
                        :key="parent.id"
                        :value="parent.id"
                        :selected="category?.parent_id === parent.id"
                    >
                        {{ parent.label }}
                    </option>
                </select>
                <InputError :message="errors.parent_id" />
            </div>

            <div class="grid gap-2">
                <Label for="description">Deskripsi</Label>
                <textarea
                    id="description"
                    name="description"
                    maxlength="1000"
                    :class="[fieldClass, 'min-h-24']"
                    :value="category?.description ?? ''"
                />
                <InputError :message="errors.description" />
            </div>

            <div class="grid gap-2 sm:max-w-48">
                <Label for="sort_order">Urutan</Label>
                <Input
                    id="sort_order"
                    name="sort_order"
                    type="number"
                    min="0"
                    :default-value="category?.sort_order ?? 0"
                />
                <InputError :message="errors.sort_order" />
            </div>

            <div class="grid gap-2">
                <Label for="image">Gambar kategori</Label>
                <img
                    v-if="category?.image_url"
                    :src="category.image_url"
                    alt=""
                    class="size-20 rounded object-cover"
                />
                <Input
                    id="image"
                    name="image"
                    type="file"
                    accept="image/png,image/jpeg,image/webp"
                />
                <InputError :message="errors.image" />
            </div>

            <div class="flex items-center gap-2">
                <input
                    id="is_active"
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="size-4"
                    :checked="category ? category.is_active : true"
                />
                <Label for="is_active">Kategori aktif</Label>
            </div>

            <div class="flex gap-2">
                <Button :disabled="processing">Simpan</Button>
                <Button variant="outline" as-child>
                    <Link :href="index()">Batal</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>