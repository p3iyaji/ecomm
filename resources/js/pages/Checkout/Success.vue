<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/layouts/ShopLayout.vue';

defineProps<{
    order: {
        order_number: string;
        total: string | number;
        status: string;
        items: { product_name: string; quantity: number; total: string | number }[];
    };
}>();
</script>

<template>
    <Head title="Order confirmed" />
    <ShopLayout>
        <div class="mx-auto max-w-2xl px-4 py-16 text-center sm:px-6">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                ✓
            </div>
            <h1 class="mt-6 text-3xl font-bold text-stone-900">Thank you for your order</h1>
            <p class="mt-2 text-stone-600">
                Order <span class="font-mono font-semibold">{{ order.order_number }}</span> is confirmed. You will receive
                an email when it ships.
            </p>
            <p class="mt-4 text-lg font-semibold text-stone-900">Total paid: ${{ Number(order.total).toFixed(2) }}</p>
            <div class="mt-10 rounded-xl border border-stone-200 bg-white p-6 text-left shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Items</h2>
                <ul class="mt-3 divide-y divide-stone-100 text-sm">
                    <li v-for="item in order.items" :key="item.product_name" class="flex justify-between py-2">
                        <span>{{ item.product_name }} × {{ item.quantity }}</span>
                        <span class="font-medium">${{ Number(item.total).toFixed(2) }}</span>
                    </li>
                </ul>
            </div>
            <div class="mt-10 flex flex-wrap justify-center gap-3">
                <Link
                    href="/orders"
                    class="inline-flex rounded-full bg-amber-600 px-6 py-3 text-sm font-semibold text-white hover:bg-amber-700"
                >
                    View your orders
                </Link>
                <Link
                    href="/products"
                    class="inline-flex rounded-full border border-stone-300 px-6 py-3 text-sm font-semibold text-stone-800 hover:bg-stone-50"
                >
                    Keep shopping
                </Link>
            </div>
        </div>
    </ShopLayout>
</template>
