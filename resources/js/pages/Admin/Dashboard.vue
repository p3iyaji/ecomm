<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { formatCurrency } from '@/lib/utils';
import { AlertTriangle, BarChart3 } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();

const props = withDefaults(
    defineProps<{
        stats: { revenue: number; orders: number; products: number; pending_reviews: number };
        lowStock: { id: number; name: string; slug: string; quantity: number; security_stock: number }[];
        recentOrders: {
            id: number;
            order_number: string;
            status: string;
            total: string | number;
            user: { name: string; email: string } | null;
            created_at: string;
        }[];
    }>(),
    {
        stats: () => ({ revenue: 0, orders: 0, products: 0, pending_reviews: 0 }),
        lowStock: () => [],
        recentOrders: () => [],
    },
);

const currencyCode = computed(() => String(page.props.shop?.currency ?? 'USD').toUpperCase());

function money(amount: string | number) {
    return formatCurrency(Number(amount), currencyCode.value);
}
</script>

<template>
    <Head title="Admin — Dashboard" />
    <AdminLayout>
        <template #title>Dashboard</template>
        <div>
            <p class="text-sm text-stone-600">Overview of your shop performance and what needs attention.</p>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Revenue (paid)</p>
                    <p class="mt-2 text-2xl font-bold text-stone-900">
                        {{ money(props.stats.revenue) }}
                    </p>
                </div>
                <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Orders</p>
                    <p class="mt-2 text-2xl font-bold text-stone-900">{{ props.stats.orders }}</p>
                </div>
                <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Products</p>
                    <p class="mt-2 text-2xl font-bold text-stone-900">{{ props.stats.products }}</p>
                </div>
                <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Pending reviews</p>
                    <p class="mt-2 text-2xl font-bold text-amber-800">{{ props.stats.pending_reviews }}</p>
                </div>
            </div>

            <div class="mt-8 grid gap-8 lg:grid-cols-2">
                <div>
                    <h2 class="mb-3 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-stone-700">
                        <BarChart3 class="h-4 w-4" />
                        Recent orders
                    </h2>
                    <div class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                        <table class="w-full min-w-0 text-sm">
                            <thead class="bg-stone-50 text-left text-xs font-semibold uppercase text-stone-500">
                                <tr>
                                    <th class="px-3 py-2">Order</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                <template v-if="!props.recentOrders.length">
                                    <tr>
                                        <td colspan="3" class="px-3 py-8 text-center text-sm text-stone-500">
                                            No orders yet. They will appear here after checkout.
                                        </td>
                                    </tr>
                                </template>
                                <template v-else>
                                    <tr v-for="o in props.recentOrders" :key="o.id" class="hover:bg-stone-50/80">
                                        <td class="px-3 py-2">
                                            <Link
                                                :href="`/admin/orders/${o.id}`"
                                                class="font-mono text-amber-800 hover:underline"
                                            >
                                                {{ o.order_number }}
                                            </Link>
                                            <span class="mt-0.5 block text-xs text-stone-500">{{
                                                o.user?.email
                                            }}</span>
                                        </td>
                                        <td class="px-3 py-2 capitalize text-stone-700">{{ o.status }}</td>
                                        <td class="px-3 py-2 text-right font-medium">
                                            {{ money(o.total) }}
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div>
                    <h2 class="mb-3 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-stone-700">
                        <AlertTriangle class="h-4 w-4 text-amber-600" />
                        Low stock
                    </h2>
                    <div
                        v-if="props.lowStock.length"
                        class="space-y-2 rounded-xl border border-amber-200/80 bg-amber-50/50 p-4"
                    >
                        <div
                            v-for="p in props.lowStock"
                            :key="p.id"
                            class="flex items-center justify-between gap-2 text-sm"
                        >
                            <Link :href="`/admin/products/${p.id}/edit`" class="font-medium text-amber-900 hover:underline">
                                {{ p.name }}
                            </Link>
                            <span class="shrink-0 text-stone-600">
                                {{ p.quantity }} left (min {{ p.security_stock }})
                            </span>
                        </div>
                    </div>
                    <p
                        v-else
                        class="rounded-xl border border-stone-200 bg-white px-4 py-6 text-center text-sm text-stone-500"
                    >
                        All tracked products are above their security stock level.
                    </p>
                </div>
            </div>

            <p class="mt-8 text-center text-sm text-stone-500">
                <Link href="/admin/reviews" class="font-medium text-amber-800 hover:underline">Moderate reviews</Link>
                <span class="mx-2">·</span>
                <Link href="/admin/products/create" class="font-medium text-amber-800 hover:underline"
                    >Add a product</Link
                >
            </p>
        </div>
    </AdminLayout>
</template>
