<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { formatCurrency } from '@/lib/utils';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const currency = computed(() => String((page.props.shop as { currency?: string })?.currency ?? 'USD'));

const props = defineProps<{
    orders: {
        data: {
            id: number;
            order_number: string;
            status: string;
            payment_status: string;
            total: string | number;
            user?: { name: string; email: string } | null;
        }[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { search?: string; status?: string; payment_status?: string };
}>();

const statusOpts = [
    { v: '', l: 'Any status' },
    { v: 'pending', l: 'Pending' },
    { v: 'processing', l: 'Processing' },
    { v: 'confirmed', l: 'Confirmed' },
    { v: 'shipped', l: 'Shipped' },
    { v: 'delivered', l: 'Delivered' },
    { v: 'cancelled', l: 'Cancelled' },
    { v: 'refunded', l: 'Refunded' },
] as const;

const payOpts = [
    { v: '', l: 'Any payment' },
    { v: 'pending', l: 'Pending' },
    { v: 'paid', l: 'Paid' },
    { v: 'failed', l: 'Failed' },
    { v: 'refunded', l: 'Refunded' },
] as const;

const filters = ref({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
    payment_status: props.filters.payment_status ?? '',
});

const apply = () => {
    router.get('/admin/orders', { ...filters.value }, { preserveState: true, preserveScroll: true });
};

let t: ReturnType<typeof setTimeout> | null = null;
watch(
    () => filters.value.search,
    () => {
        if (t) clearTimeout(t);
        t = setTimeout(apply, 400);
    },
);
</script>

<template>
    <Head title="Admin — Orders" />
    <AdminLayout>
        <template #title>Orders</template>
        <div>
            <p class="text-sm text-stone-600">Fulfillment, payments, and order notes.</p>

            <div class="mt-4 flex flex-wrap gap-2">
                <input
                    v-model="filters.search"
                    type="search"
                    placeholder="Order #, customer…"
                    class="min-w-[200px] flex-1 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:outline-none"
                />
                <select
                    v-model="filters.status"
                    class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm"
                    @change="apply"
                >
                    <option v-for="o in statusOpts" :key="o.v" :value="o.v">{{ o.l }}</option>
                </select>
                <select
                    v-model="filters.payment_status"
                    class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm"
                    @change="apply"
                >
                    <option v-for="o in payOpts" :key="`p-${o.v}`" :value="o.v">{{ o.l }}</option>
                </select>
            </div>

            <div class="mt-6 overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                        <thead class="bg-stone-50 text-left text-xs font-semibold uppercase text-stone-500">
                            <tr>
                                <th class="px-4 py-3">Order</th>
                                <th class="px-4 py-3">Customer</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Payment</th>
                                <th class="px-4 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <tr v-for="o in orders.data" :key="o.id" class="hover:bg-stone-50/50">
                                <td class="px-4 py-3">
                                    <Link
                                        :href="`/admin/orders/${o.id}`"
                                        class="font-mono font-medium text-amber-800 hover:underline"
                                    >
                                        {{ o.order_number }}
                                    </Link>
                                </td>
                                <td class="px-4 py-3 text-stone-600">
                                    {{ o.user?.name ?? '—' }}
                                    <span class="mt-0.5 block text-xs text-stone-400">{{ o.user?.email }}</span>
                                </td>
                                <td class="px-4 py-3 capitalize text-stone-700">{{ o.status }}</td>
                                <td class="px-4 py-3 capitalize text-stone-600">{{ o.payment_status }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-stone-900">
                                    {{ formatCurrency(Number(o.total), currency) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="orders.links.length > 3" class="mt-6">
                <Pagination :links="orders.links" />
            </div>
        </div>
    </AdminLayout>
</template>
