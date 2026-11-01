<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ClipboardList, LayoutGrid, ShieldCheck, Store } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as adminShops } from '@/routes/admin/shops';
import { dashboard as sellerDashboard } from '@/routes/seller';
import type { NavItem } from '@/types';

interface Access {
    isAdmin: boolean;
    isSeller: boolean;
}

const page = usePage();

const access = computed(
    () => (page.props as Record<string, unknown>).access as Access | undefined,
);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    if (access.value?.isSeller) {
        items.push({
            title: 'Dashboard Toko',
            href: sellerDashboard(),
            icon: Store,
        });
    }

    if (access.value?.isAdmin) {
        items.push(
            {
                title: 'Dashboard Admin',
                href: adminDashboard(),
                icon: ShieldCheck,
            },
            {
                title: 'Pengajuan Toko',
                href: adminShops(),
                icon: ClipboardList,
            },
        );
    }

    return items;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>