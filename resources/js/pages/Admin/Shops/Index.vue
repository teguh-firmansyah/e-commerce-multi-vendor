<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import ShopReviewController from '@/actions/App/Http/Controllers/Admin/ShopReviewController';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { dashboard } from '@/routes/admin';
import { index } from '@/routes/admin/shops';

interface ShopRow {
    id: number;
    name: string;
    phone: string | null;
    status: string;
    status_reason: string | null;
    owner: string;
    owner_email: string;
    city: string | null;
    submitted_at: string | null;
}

interface ShopPage {
    data: ShopRow[];
    prev_page_url: string | null;
    next_page_url: string | null;
}

defineProps<{
    shops: ShopPage;
    status: string;
    statuses: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard Admin',
                href: dashboard(),
            },
            {
                title: 'Pengajuan Toko',
                href: index(),
            },
        ],
    },
});

const labels: Record<string, string> = {
    pending: 'Menunggu',
    approved: 'Disetujui',
    rejected: 'Ditolak',
    suspended: 'Ditangguhkan',
};
</script>

<template>
    <Head title="Pengajuan Toko" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <h1 class="text-2xl font-semibold">Pengajuan Toko</h1>

        <div class="flex flex-wrap gap-2">
            <Button
                v-for="item in statuses"
                :key="item"
                :variant="item === status ? 'default' : 'outline'"
                size="sm"
                as-child
            >
                <Link :href="index({ query: { status: item } })">
                    {{ labels[item] ?? item }}
                </Link>
            </Button>
        </div>

        <p
            v-if="shops.data.length === 0"
            class="text-sm text-muted-foreground"
        >
            Tidak ada toko dengan status ini.
        </p>

        <div v-else class="overflow-x-auto rounded-xl border">
            <table class="w-full text-left text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-medium">Toko</th>
                        <th class="px-4 py-3 font-medium">Pemilik</th>
                        <th class="px-4 py-3 font-medium">Kota</th>
                        <th class="px-4 py-3 font-medium">Diajukan</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="shop in shops.data"
                        :key="shop.id"
                        class="border-t align-top"
                    >
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ shop.name }}</div>
                            <div class="text-muted-foreground">
                                {{ shop.phone }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div>{{ shop.owner }}</div>
                            <div class="text-muted-foreground">
                                {{ shop.owner_email }}
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ shop.city }}</td>
                        <td class="px-4 py-3">{{ shop.submitted_at }}</td>
                        <td class="px-4 py-3">
                            <Badge variant="secondary">
                                {{ labels[shop.status] ?? shop.status }}
                            </Badge>
                            <p
                                v-if="shop.status_reason"
                                class="mt-1 max-w-48 text-muted-foreground"
                            >
                                {{ shop.status_reason }}
                            </p>
                        </td>
                        <td class="space-y-2 px-4 py-3">
                            <Form
                                v-if="
                                    shop.status === 'pending' ||
                                    shop.status === 'suspended'
                                "
                                v-bind="
                                    ShopReviewController.updateStatus.form(
                                        shop.id,
                                    )
                                "
                                v-slot="{ errors, processing }"
                            >
                                <input
                                    type="hidden"
                                    name="status"
                                    value="approved"
                                />
                                <Button size="sm" :disabled="processing">
                                    {{
                                        shop.status === 'suspended'
                                            ? 'Aktifkan kembali'
                                            : 'Setujui'
                                    }}
                                </Button>
                                <InputError :message="errors.status" />
                            </Form>

                            <Form
                                v-if="shop.status === 'pending'"
                                v-bind="
                                    ShopReviewController.updateStatus.form(
                                        shop.id,
                                    )
                                "
                                class="flex items-start gap-2"
                                v-slot="{ errors, processing }"
                            >
                                <input
                                    type="hidden"
                                    name="status"
                                    value="rejected"
                                />
                                <div class="grid gap-1">
                                    <Input
                                        name="reason"
                                        placeholder="Alasan penolakan"
                                        class="h-8 w-48"
                                    />
                                    <InputError :message="errors.reason" />
                                    <InputError :message="errors.status" />
                                </div>
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    :disabled="processing"
                                >
                                    Tolak
                                </Button>
                            </Form>

                            <Form
                                v-if="shop.status === 'approved'"
                                v-bind="
                                    ShopReviewController.updateStatus.form(
                                        shop.id,
                                    )
                                "
                                class="flex items-start gap-2"
                                v-slot="{ errors, processing }"
                            >
                                <input
                                    type="hidden"
                                    name="status"
                                    value="suspended"
                                />
                                <div class="grid gap-1">
                                    <Input
                                        name="reason"
                                        placeholder="Alasan penangguhan"
                                        class="h-8 w-48"
                                    />
                                    <InputError :message="errors.reason" />
                                    <InputError :message="errors.status" />
                                </div>
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    :disabled="processing"
                                >
                                    Tangguhkan
                                </Button>
                            </Form>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex gap-2">
            <Button v-if="shops.prev_page_url" variant="outline" size="sm" as-child>
                <Link :href="shops.prev_page_url">Sebelumnya</Link>
            </Button>
            <Button v-if="shops.next_page_url" variant="outline" size="sm" as-child>
                <Link :href="shops.next_page_url">Berikutnya</Link>
            </Button>
        </div>
    </div>
</template>