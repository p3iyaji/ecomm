<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/layouts/ShopLayout.vue';
import Pagination from '@/components/Pagination.vue';

defineProps<{
    orders: {
        data: {
            id: number;
            order_number: string;
            status: string;
            payment_status: string;
            total: string | number;
            created_at: string;
            items_count: number;
        }[];
        links: { url: string | null; label: string; active: boolean }[];
    };
}>();
</script>

<template>
    <Head title="Your orders" />
    <ShopLayout>
        <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-stone-900">Your orders</h1>
            <p class="mt-1 text-stone-600">Track purchases and payment status.</p>

            <div v-if="orders.data.length" class="mt-8 space-y-4">
                <Link
                    v-for="o in orders.data"
                    :key="o.id"
                    :href="`/orders/${o.order_number}`"
                    class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-stone-200 bg-white p-4 shadow-sm transition hover:border-amber-200"
                >
                    <div>
                        <p class="font-mono text-sm font-semibold text-stone-900">{{ o.order_number }}</p>
                        <p class="text-sm text-stone-500">{{ new Date(o.created_at).toLocaleString() }}</p>
                    </div>
                    <div class="text-sm text-stone-600">
                        {{ o.items_count }} item(s) ·
                        <span class="capitalize">{{ o.status }}</span>
                    </div>
                    <p class="text-lg font-semibold text-stone-900">${{ Number(o.total).toFixed(2) }}</p>
                </Link>
            </div>
            <p v-else class="mt-12 rounded-xl border border-dashed border-stone-200 bg-white p-10 text-center text-stone-600">
                You have not placed an order yet.
                <Link href="/products" class="mt-2 block font-semibold text-amber-800">Browse products</Link>
            </p>

            <div v-if="orders.links.length > 3" class="mt-8">
                <Pagination :links="orders.links" />
            </div>
        </div>
    </ShopLayout>
</template>
