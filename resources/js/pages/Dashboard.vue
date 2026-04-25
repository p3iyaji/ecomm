<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Package, Settings, Shield, ShoppingBag } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isShopAdmin = computed(() => Boolean(page.props.isShopAdmin));

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div>
                <h2 class="text-2xl font-semibold text-foreground">Welcome back{{ user?.name ? `, ${user.name}` : '' }}</h2>
                <p class="mt-1 text-sm text-muted-foreground">Jump back into the store or manage your account.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    href="/products"
                    class="group flex flex-col gap-3 rounded-xl border border-border bg-card p-5 text-card-foreground shadow-sm transition hover:border-primary/30 hover:shadow-md"
                >
                    <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <Package class="size-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold">Browse products</h3>
                        <p class="mt-1 text-sm text-muted-foreground">Explore the catalog and add items to your cart.</p>
                    </div>
                </Link>

                <Link
                    href="/orders"
                    class="group flex flex-col gap-3 rounded-xl border border-border bg-card p-5 text-card-foreground shadow-sm transition hover:border-primary/30 hover:shadow-md"
                >
                    <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <ShoppingBag class="size-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold">Your orders</h3>
                        <p class="mt-1 text-sm text-muted-foreground">Track shipments and download receipts.</p>
                    </div>
                </Link>

                <Link
                    v-if="isShopAdmin"
                    href="/admin"
                    class="group flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 p-5 text-amber-950 shadow-sm transition hover:border-amber-400 hover:shadow-md dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-50"
                >
                    <div
                        class="flex size-10 items-center justify-center rounded-lg bg-amber-600/15 text-amber-800 dark:text-amber-200"
                    >
                        <Shield class="size-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold">Store admin</h3>
                        <p class="mt-1 text-sm text-amber-900/80 dark:text-amber-100/80">
                            Products, orders, customers, and reviews.
                        </p>
                    </div>
                </Link>

                <Link
                    href="/settings/profile"
                    class="group flex flex-col gap-3 rounded-xl border border-border bg-card p-5 text-card-foreground shadow-sm transition hover:border-primary/30 hover:shadow-md"
                >
                    <div class="flex size-10 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                        <Settings class="size-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold">Account settings</h3>
                        <p class="mt-1 text-sm text-muted-foreground">Profile, password, and security.</p>
                    </div>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
