<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { formatCurrency } from '@/lib/utils';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const currency = computed(() => String((page.props.shop as { currency?: string })?.currency ?? 'USD'));

const props = defineProps<{
    order: {
        id: number;
        order_number: string;
        status: string;
        payment_status: string;
        payment_id: string | null;
        payment_method: string;
        subtotal: string | number;
        tax: string | number;
        shipping_cost: string | number;
        discount: string | number;
        total: string | number;
        notes: string | null;
        shipping_address: Record<string, string>;
        billing_address: Record<string, string>;
        paid_at: string | null;
        shipped_at: string | null;
        delivered_at: string | null;
        user?: { name: string; email: string } | null;
        items: {
            id: number;
            product_name: string;
            sku: string;
            quantity: number;
            price: string | number;
            total: string | number;
        }[];
    };
}>();

const form = useForm({
    status: props.order.status,
    payment_status: props.order.payment_status,
    payment_id: props.order.payment_id ?? '',
    notes: props.order.notes ?? '',
    cancellation_reason: '' as string,
});

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            payment_id: data.payment_id === '' ? null : data.payment_id,
            notes: data.notes === '' ? null : data.notes,
            cancellation_reason: data.cancellation_reason === '' ? null : data.cancellation_reason,
        }))
        .patch(`/admin/orders/${props.order.id}`, { preserveScroll: true });
};

const fieldClass =
    'w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 focus:outline-none';
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />
    <AdminLayout>
        <template #title>Order {{ order.order_number }}</template>
        <div class="max-w-4xl">
            <p class="text-sm text-stone-500">
                <Link href="/admin/orders" class="text-amber-800 hover:underline">← All orders</Link>
            </p>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                    <h2 class="text-xs font-bold uppercase tracking-wide text-stone-500">Amounts</h2>
                    <dl class="mt-3 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <dt>Subtotal</dt>
                            <dd>{{ formatCurrency(Number(order.subtotal), currency) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Tax</dt>
                            <dd>{{ formatCurrency(Number(order.tax), currency) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Shipping</dt>
                            <dd>{{ formatCurrency(Number(order.shipping_cost), currency) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Discount</dt>
                            <dd>{{ formatCurrency(Number(order.discount), currency) }}</dd>
                        </div>
                        <div class="mt-2 flex justify-between border-t border-stone-200 pt-2 text-base font-bold">
                            <dt>Total</dt>
                            <dd>{{ formatCurrency(Number(order.total), currency) }}</dd>
                        </div>
                    </dl>
                </div>
                <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                    <h2 class="text-xs font-bold uppercase tracking-wide text-stone-500">Customer</h2>
                    <p v-if="order.user" class="mt-2 text-stone-900">{{ order.user.name }}</p>
                    <p v-if="order.user" class="text-sm text-stone-600">{{ order.user.email }}</p>
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                <h2 class="border-b border-stone-200 bg-stone-50 px-4 py-2 text-xs font-bold uppercase text-stone-500">
                    Items
                </h2>
                <table class="w-full min-w-0 text-sm">
                    <thead class="text-left text-xs text-stone-500">
                        <tr>
                            <th class="px-4 py-2">Product</th>
                            <th class="px-4 py-2">SKU</th>
                            <th class="px-4 py-2 text-right">Qty</th>
                            <th class="px-4 py-2 text-right">Line</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="line in order.items" :key="line.id">
                            <td class="px-4 py-2 font-medium text-stone-800">{{ line.product_name }}</td>
                            <td class="px-4 py-2 font-mono text-stone-500">{{ line.sku }}</td>
                            <td class="px-4 py-2 text-right">{{ line.quantity }}</td>
                            <td class="px-4 py-2 text-right">
                                {{ formatCurrency(Number(line.total), currency) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <form class="mt-6 space-y-4 rounded-xl border border-stone-200 bg-white p-5 shadow-sm" @submit.prevent="submit">
                <h2 class="text-xs font-bold uppercase tracking-wide text-stone-500">Update order</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-medium text-stone-600">Status</label>
                        <select v-model="form.status" :class="fieldClass" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-stone-600">Payment</label>
                        <select v-model="form.payment_status" :class="fieldClass" required>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-stone-600">Payment reference</label>
                    <input v-model="form.payment_id" type="text" :class="fieldClass" />
                </div>
                <div v-if="form.status === 'cancelled'">
                    <label class="text-xs font-medium text-stone-600">Cancellation reason (internal)</label>
                    <input v-model="form.cancellation_reason" type="text" :class="fieldClass" />
                </div>
                <div>
                    <label class="text-xs font-medium text-stone-600">Notes (internal)</label>
                    <textarea v-model="form.notes" rows="3" :class="fieldClass" />
                </div>
                <p v-if="order.paid_at" class="text-xs text-stone-500">Paid: {{ order.paid_at }}</p>
                <p v-if="order.shipped_at" class="text-xs text-stone-500">Shipped: {{ order.shipped_at }}</p>
                <p v-if="order.delivered_at" class="text-xs text-stone-500">Delivered: {{ order.delivered_at }}</p>
                <ul
                    v-if="Object.keys(form.errors).length"
                    class="rounded border border-red-200 bg-red-50 p-2 text-sm text-red-800"
                >
                    <li v-for="(msg, k) in form.errors" :key="k">{{ String(msg) }}</li>
                </ul>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-amber-700 disabled:opacity-50"
                >
                    {{ form.processing ? 'Saving…' : 'Save' }}
                </button>
            </form>

            <div class="mt-4 rounded-lg border border-stone-200 bg-stone-50 p-4 text-sm text-stone-600">
                <h3 class="text-xs font-bold uppercase text-stone-500">Shipping</h3>
                <pre class="mt-1 whitespace-pre-wrap break-words font-sans text-xs">{{
                    JSON.stringify(order.shipping_address, null, 2)
                }}</pre>
            </div>
        </div>
    </AdminLayout>
</template>
