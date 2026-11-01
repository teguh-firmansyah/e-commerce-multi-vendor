<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import ShopProfileController from '@/actions/App/Http/Controllers/Customer/ShopProfileController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { edit } from '@/routes/shop';

interface ShopData {
    name: string;
    slug: string;
    description: string | null;
    phone: string | null;
    is_open: boolean;
    status: string;
    status_reason: string | null;
}

interface AddressData {
    province: string;
    city: string;
    postal_code: string;
    address_line: string;
}

defineProps<{
    shop: ShopData;
    address: AddressData | null;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Data Toko',
                href: edit(),
            },
        ],
    },
});

const textareaClass =
    'flex min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';
</script>

<template>
    <Head title="Data Toko" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
        <Heading
            title="Data Toko"
            :description="`Alamat unik toko: ${shop.slug} (tidak dapat diubah)`"
        />

        <Alert v-if="shop.status === 'rejected'">
            <AlertTitle>Pengajuan ditolak</AlertTitle>
            <AlertDescription>
                {{ shop.status_reason }}. Perbaiki data di bawah, lalu ajukan
                ulang dari halaman dashboard.
            </AlertDescription>
        </Alert>

        <Form
            v-bind="ShopProfileController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Nama toko</Label>
                <Input
                    id="name"
                    name="name"
                    :default-value="shop.name"
                    required
                    maxlength="100"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="description">Deskripsi toko</Label>
                <textarea
                    id="description"
                    name="description"
                    maxlength="1000"
                    :class="textareaClass"
                    :value="shop.description ?? ''"
                />
                <InputError :message="errors.description" />
            </div>

            <div class="grid gap-2">
                <Label for="phone">Nomor telepon</Label>
                <Input
                    id="phone"
                    name="phone"
                    type="tel"
                    :default-value="shop.phone ?? ''"
                    required
                />
                <InputError :message="errors.phone" />
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="province">Provinsi</Label>
                    <Input
                        id="province"
                        name="province"
                        :default-value="address?.province ?? ''"
                        required
                    />
                    <InputError :message="errors.province" />
                </div>

                <div class="grid gap-2">
                    <Label for="city">Kota / Kabupaten</Label>
                    <Input
                        id="city"
                        name="city"
                        :default-value="address?.city ?? ''"
                        required
                    />
                    <InputError :message="errors.city" />
                </div>
            </div>

            <div class="grid gap-2 sm:max-w-48">
                <Label for="postal_code">Kode pos</Label>
                <Input
                    id="postal_code"
                    name="postal_code"
                    inputmode="numeric"
                    maxlength="5"
                    :default-value="address?.postal_code ?? ''"
                    required
                />
                <InputError :message="errors.postal_code" />
            </div>

            <div class="grid gap-2">
                <Label for="address_line">Alamat lengkap</Label>
                <textarea
                    id="address_line"
                    name="address_line"
                    required
                    maxlength="500"
                    :class="textareaClass"
                    :value="address?.address_line ?? ''"
                />
                <InputError :message="errors.address_line" />
            </div>

            <div class="flex items-center gap-2">
                <input
                    id="is_open"
                    type="checkbox"
                    name="is_open"
                    value="1"
                    class="size-4"
                    :checked="shop.is_open"
                />
                <Label for="is_open">Toko sedang buka</Label>
            </div>
            <InputError :message="errors.is_open" />

            <Button :disabled="processing">Simpan</Button>
        </Form>
    </div>
</template>