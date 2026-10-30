<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes/admin';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard Admin',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard Admin" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <h1 class="text-2xl font-semibold">Dashboard Admin</h1>
    </div>
</template>