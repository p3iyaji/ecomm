<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    BarChart3,
    FolderTree,
    Home,
    Menu,
    Package,
    ShoppingCart,
    Star,
    Store,
    UserCircle,
    Users,
    X,
} from 'lucide-vue-next';

const page = usePage();
const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined);
const mobileOpen = ref(false);

const nav = [
    { name: 'Dashboard', href: '/admin', end: true, icon: BarChart3 },
    { name: 'Products', href: '/admin/products', end: false, icon: Package },
    { name: 'Categories', href: '/admin/categories', end: false, icon: FolderTree },
    { name: 'Orders', href: '/admin/orders', end: false, icon: ShoppingCart },
    { name: 'Customers', href: '/admin/customers', end: false, icon: Users },
    { name: 'Accounts', href: '/admin/users', end: false, icon: UserCircle },
    { name: 'Reviews', href: '/admin/reviews', end: false, icon: Star },
] as const;

const currentPath = computed(() => page.url?.split('?')[0] ?? '');

const linkActive = (href: string, end: boolean) => {
    if (end) {
        return currentPath.value === href || currentPath.value === `${href}/`;
    }
    return currentPath.value === href || currentPath.value.startsWith(`${href}/`);
};

watch(
    () => [flash.value?.success, flash.value?.error],
    () => {
        if (typeof window !== 'undefined' && (flash.value?.success || flash.value?.error)) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    },
);
</script>

<template>
    <div
        class="isolate min-h-screen bg-stone-100 text-stone-900 [color-scheme:light] dark:bg-stone-100 dark:text-stone-900"
    >
        <div
            v-if="flash?.success"
            class="border-b border-emerald-200 bg-emerald-50 px-4 py-2 text-center text-sm text-emerald-900"
        >
            {{ flash.success }}
        </div>
        <div
            v-if="flash?.error"
            class="border-b border-red-200 bg-red-50 px-4 py-2 text-center text-sm text-red-900"
        >
            {{ flash.error }}
        </div>

        <div class="flex min-h-[calc(100vh-0px)]">
            <aside
                class="hidden w-60 shrink-0 border-r border-stone-200 bg-stone-900 text-stone-100 lg:fixed lg:inset-y-0 lg:z-30 lg:flex lg:flex-col"
            >
                <div class="border-b border-stone-800 px-4 py-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Store admin</p>
                    <p class="mt-0.5 text-sm font-bold text-amber-400">{{ $page.props.name }}</p>
                </div>
                <nav class="flex-1 space-y-0.5 p-2">
                    <Link
                        v-for="item in nav"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                            linkActive(item.href, item.end)
                                ? 'bg-amber-500/20 text-amber-300'
                                : 'text-stone-300 hover:bg-stone-800 hover:text-white',
                        ]"
                    >
                        <component :is="item.icon" class="h-4 w-4 shrink-0 opacity-90" />
                        {{ item.name }}
                    </Link>
                </nav>
                <div class="border-t border-stone-800 p-2">
                    <Link
                        href="/"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-stone-300 hover:bg-stone-800 hover:text-white"
                    >
                        <Store class="h-4 w-4" />
                        View storefront
                    </Link>
                    <Link
                        href="/dashboard"
                        class="mt-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-stone-300 hover:bg-stone-800 hover:text-white"
                    >
                        <Home class="h-4 w-4" />
                        Account
                    </Link>
                </div>
            </aside>

            <div class="flex min-h-0 w-full min-w-0 flex-1 flex-col lg:pl-60">
                <header
                    class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-stone-200 bg-white/95 px-4 py-3 shadow-sm backdrop-blur lg:px-8"
                >
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-lg border border-stone-200 p-2 text-stone-700 hover:bg-stone-50 lg:hidden"
                            @click="mobileOpen = !mobileOpen"
                        >
                            <Menu v-if="!mobileOpen" class="h-5 w-5" />
                            <X v-else class="h-5 w-5" />
                        </button>
                        <h1 class="text-lg font-bold text-stone-900">
                            <slot name="title" />
                        </h1>
                    </div>
                </header>

                <div v-if="mobileOpen" class="border-b border-stone-200 bg-stone-900 p-2 lg:hidden">
                    <div class="space-y-0.5">
                        <Link
                            v-for="item in nav"
                            :key="`m-${item.href}`"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium"
                            :class="
                                linkActive(item.href, item.end)
                                    ? 'bg-amber-500/20 text-amber-200'
                                    : 'text-stone-300'
                            "
                            @click="mobileOpen = false"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            {{ item.name }}
                        </Link>
                        <Link
                            href="/"
                            class="mt-1 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-stone-400"
                            @click="mobileOpen = false"
                        >
                            <Store class="h-4 w-4" />
                            Store
                        </Link>
                    </div>
                </div>

                <div class="min-h-0 flex-1 px-4 py-6 text-stone-900 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
