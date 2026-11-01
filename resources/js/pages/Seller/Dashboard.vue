<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes/seller';
import { edit } from '@/routes/shop';

interface SharedAccess {
    shop: {
        name: string;
        slug: string;
        status: string;
    } | null;
}

const page = usePage();

const shop = computed(
    () => (page.props as unknown as { access: SharedAccess }).access.shop,
);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard Toko',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard Toko" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <Card class="max-w-xl">
            <CardHeader>
                <CardTitle>{{ shop?.name }}</CardTitle>
                <CardDescription>
                    Kelola profil dan produk toko Anda.
                </CardDescription>
            </CardHeader>
            <CardFooter>
                <Button variant="outline" as-child>
                    <Link :href="edit()">Ubah Data Toko</Link>
                </Button>
            </CardFooter>
        </Card>
    </div>
</template>