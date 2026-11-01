<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import ShopApplicationController from '@/actions/App/Http/Controllers/Customer/ShopApplicationController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { apply } from '@/routes/shop';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Buka Toko',
                href: apply(),
            },
        ],
    },
});

const textareaClass =
    'flex min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';
</script>

<template>
    <Head title="Buka Toko" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
        <Heading
            title="Buka Toko"
            description="Lengkapi data toko Anda. Pengajuan akan ditinjau oleh admin sebelum toko bisa berjualan."
        />

        <Form
            v-bind="ShopApplicationController.store.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Nama toko</Label>
                <Input
                    id="name"
                    name="name"
                    required
                    maxlength="100"
                    placeholder="Contoh: Toko Sumber Rejeki"
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
                    placeholder="Ceritakan singkat tentang toko Anda"
                />
                <InputError :message="errors.description" />
            </div>

            <div class="grid gap-2">
                <Label for="phone">Nomor telepon</Label>
                <Input
                    id="phone"
                    name="phone"
                    type="tel"
                    required
                    autocomplete="tel"
                    placeholder="081234567890"
                />
                <InputError :message="errors.phone" />
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="province">Provinsi</Label>
                    <Input id="province" name="province" required />
                    <InputError :message="errors.province" />
                </div>

                <div class="grid gap-2">
                    <Label for="city">Kota / Kabupaten</Label>
                    <Input id="city" name="city" required />
                    <InputError :message="errors.city" />
                </div>
            </div>

            <div class="grid gap-2 sm:max-w-48">
                <Label for="postal_code">Kode pos</Label>
                <Input
                    id="postal_code"
                    name="postal_code"
                    required
                    inputmode="numeric"
                    maxlength="5"
                    placeholder="40111"
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
                    placeholder="Nama jalan, nomor, RT/RW, kelurahan, kecamatan"
                />
                <InputError :message="errors.address_line" />
            </div>

            <Button :disabled="processing">Kirim Pengajuan</Button>
        </Form>
    </div>
</template>