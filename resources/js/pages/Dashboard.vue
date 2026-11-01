<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import ShopApplicationController from '@/actions/App/Http/Controllers/Customer/ShopApplicationController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { dashboard as sellerDashboard } from '@/routes/seller';
import { apply, edit as editShop } from '@/routes/shop';

type ShopStatus = 'pending' | 'approved' | 'rejected' | 'suspended';

interface ShopSummary {
    name: string;
    status: ShopStatus;
    reason: string | null;
}

defineProps<{
    shop: ShopSummary | null;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const statusLabel: Record<ShopStatus, string> = {
    pending: 'Menunggu persetujuan',
    approved: 'Disetujui',
    rejected: 'Ditolak',
    suspended: 'Ditangguhkan',
};

const statusVariant: Record<
    ShopStatus,
    'default' | 'secondary' | 'destructive'
> = {
    pending: 'secondary',
    approved: 'default',
    rejected: 'destructive',
    suspended: 'destructive',
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <Card class="max-w-xl">
            <CardHeader>
                <CardTitle>Toko Saya</CardTitle>
                <CardDescription v-if="shop">{{ shop.name }}</CardDescription>
                <CardDescription v-else>
                    Mulai berjualan di NusaMarket dengan membuka toko.
                </CardDescription>
            </CardHeader>

            <CardContent v-if="shop" class="space-y-2">
                <Badge :variant="statusVariant[shop.status]">
                    {{ statusLabel[shop.status] }}
                </Badge>
                <p
                    v-if="shop.status === 'pending'"
                    class="text-sm text-muted-foreground"
                >
                    Pengajuan Anda sedang ditinjau oleh admin.
                </p>
                <p v-if="shop.reason" class="text-sm text-muted-foreground">
                    Alasan: {{ shop.reason }}
                </p>
                <p
                    v-if="shop.status === 'rejected'"
                    class="text-sm text-muted-foreground"
                >
                    Perbaiki data toko terlebih dahulu, lalu ajukan ulang.
                </p>
            </CardContent>

            <CardFooter class="flex-wrap gap-2">
                <Button v-if="!shop" as-child>
                    <Link :href="apply()">Buka Toko</Link>
                </Button>

                <Button v-if="shop?.status === 'approved'" as-child>
                    <Link :href="sellerDashboard()">Ke Dashboard Toko</Link>
                </Button>

                <Button
                    v-if="shop && shop.status !== 'suspended'"
                    variant="outline"
                    as-child
                >
                    <Link :href="editShop()">Ubah Data Toko</Link>
                </Button>

                <Form
                    v-if="shop?.status === 'rejected'"
                    v-bind="ShopApplicationController.resubmit.form()"
                    v-slot="{ processing }"
                >
                    <Button :disabled="processing">Ajukan Ulang</Button>
                </Form>
            </CardFooter>
        </Card>
    </div>
</template>