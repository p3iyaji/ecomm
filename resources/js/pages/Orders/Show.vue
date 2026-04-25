<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/layouts/ShopLayout.vue';

defineProps<{
    order: {
        order_number: string;
        status: string;
        payment_status: string;
        subtotal: string | number;
        tax: string | number;
        shipping_cost: string | number;
        total: string | number;
        shipping_address: Record<string, string>;
        created_at: string;
        items: { product_name: string; sku: string; quantity: number; price: string | number; total: string | number }[];
    };
}>();
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />
    <ShopLayout>
        <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
            <Link href="/orders" class="text-sm font-medium text-amber-800 hover:text-amber-900">← Back to orders</Link>
            <h1 class="mt-4 text-3xl font-bold text-stone-900">Order {{ order.order_number }}</h1>
            <p class="mt-1 capitalize text-stone-600">
                Status: <span class="font-semibold text-stone-900">{{ order.status }}</span> · Payment:
                <span class="font-semibold text-stone-900">{{ order.payment_status }}</span>
            </p>

            <section class="mt-8 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-stone-900">Items</h2>
                <ul class="mt-4 divide-y divide-stone-100">
                    <li v-for="item in order.items" :key="item.sku + item.product_name" class="flex justify-between py-3 text-sm">
                        <div>
                            <p class="font-medium text-stone-900">{{ item.product_name }}</p>
                            <p class="text-stone-500">SKU {{ item.sku }} × {{ item.quantity }}</p>
                        </div>
                        <p class="font-semibold text-stone-900">${{ Number(item.total).toFixed(2) }}</p>
                    </li>
                </ul>
                <dl class="mt-6 space-y-2 border-t border-stone-100 pt-4 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-stone-600">Subtotal</dt>
                        <dd>${{ Number(order.subtotal).toFixed(2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-stone-600">Tax</dt>
                        <dd>${{ Number(order.tax).toFixed(2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-stone-600">Shipping</dt>
                        <dd>${{ Number(order.shipping_cost).toFixed(2) }}</dd>
                    </div>
                    <div class="flex justify-between text-base font-semibold text-stone-900">
                        <dt>Total</dt>
                        <dd>${{ Number(order.total).toFixed(2) }}</dd>
                    </div>
                </dl>
            </section>

            <section class="mt-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-stone-900">Ship to</h2>
                <p class="mt-2 text-sm text-stone-600">
                    {{ order.shipping_address.address_line1 }}<br />
                    <span v-if="order.shipping_address.address_line2">{{ order.shipping_address.address_line2 }}<br /></span>
                    {{ order.shipping_address.city }}, {{ order.shipping_address.state }}
                    {{ order.shipping_address.postal_code }}<br />
                    {{ order.shipping_address.country }}
                </p>
            </section>
        </div>
    </ShopLayout>
</template>
