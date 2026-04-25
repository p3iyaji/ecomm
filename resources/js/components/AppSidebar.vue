<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { FolderTree, LayoutGrid, Package, Shield, ShoppingCart, UserCircle } from 'lucide-vue-next';
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
import type { NavItem } from '@/types';

const page = usePage();

const mainNavItems = computed((): NavItem[] => [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
]);

const shopAdminNavItems = computed((): NavItem[] => {
    if (!page.props.isShopAdmin) {
        return [];
    }
    return [
        { title: 'Overview', href: '/admin', icon: Shield },
        { title: 'Products', href: '/admin/products', icon: Package },
        { title: 'Categories', href: '/admin/categories', icon: FolderTree },
        { title: 'Orders', href: '/admin/orders', icon: ShoppingCart },
        { title: 'Accounts', href: '/admin/users', icon: UserCircle },
    ];
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
            <NavMain v-if="shopAdminNavItems.length" group-label="Shop admin" :items="shopAdminNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
