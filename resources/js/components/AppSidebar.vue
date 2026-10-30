<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, ShieldCheck, Store } from '@lucide/vue';
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

const area = computed(() => {
    if (page.url.startsWith('/admin')) {
        return 'admin';
    }

    if (page.url.startsWith('/seller')) {
        return 'seller';
    }

    return 'customer';
});

const customerItem: NavItem = {
    title: 'Dashboard',
    href: dashboard(),
    icon: LayoutGrid,
};

const accountItem: NavItem = {
    title: 'Akun Saya',
    href: dashboard(),
    icon: LayoutGrid,
};

const sellerItem: NavItem = {
    title: 'Dashboard Toko',
    href: sellerDashboard(),
    icon: Store,
};

const adminItem: NavItem = {
    title: 'Dashboard Admin',
    href: adminDashboard(),
    icon: ShieldCheck,
};

const mainNavItems = computed<NavItem[]>(() => {
    if (area.value === 'admin') {
        return [adminItem, accountItem];
    }

    if (area.value === 'seller') {
        return [sellerItem, accountItem];
    }

    const items: NavItem[] = [customerItem];

    if (access.value?.isSeller) {
        items.push(sellerItem);
    }

    if (access.value?.isAdmin) {
        items.push(adminItem);
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