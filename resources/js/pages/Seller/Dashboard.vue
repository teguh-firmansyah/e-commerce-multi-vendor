<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes/seller';

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
        <h1 class="text-2xl font-semibold">Dashboard Toko</h1>
    </div>
</template>