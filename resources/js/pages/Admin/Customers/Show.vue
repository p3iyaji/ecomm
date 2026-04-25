<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { formatCurrency } from '@/lib/utils';
import { computed } from 'vue';

const page = usePage();
const currency = computed(() =>
    String((page.props.shop as { currency?: string } | undefined)?.currency ?? 'USD').toUpperCase(),
);

const props = defineProps<{
    customer: {
        id: number;
        phone: string | null;
        city: string | null;
        state: string | null;
        postal_code: string | null;
        country: string | null;
        address: string | null;
        total_spent: string | number;
        total_orders: number;
        last_purchase_at: string | null;
        user?: { id: number; name: string; email: string } | null;
        orders: {
            id: number;
            order_number: string;
            status: string;
            total: string | number;
            created_at: string;
        }[];
    };
}>();

const profileForm = useForm({
    phone: props.customer.phone ?? '',
    address: props.customer.address ?? '',
    city: props.customer.city ?? '',
    state: props.customer.state ?? '',
    postal_code: props.customer.postal_code ?? '',
    country: props.customer.country ?? '',
});

const saveProfile = () => {
    profileForm.patch(`/admin/customers/${props.customer.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Customer detail" />
    <AdminLayout>
        <template #title>Customer</template>
        <div class="max-w-3xl">
            <p class="text-sm text-stone-500">
                <Link href="/admin/customers" class="text-amber-800 hover:underline">← Customers</Link>
            </p>
            <div class="mt-4 rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 v-if="customer.user" class="text-lg font-bold text-stone-900">{{ customer.user.name }}</h2>
                        <p v-if="customer.user" class="text-stone-600">{{ customer.user.email }}</p>
                        <Link
                            v-if="customer.user?.id"
                            :href="`/admin/users/${customer.user.id}`"
                            class="mt-2 inline-block text-sm font-medium text-amber-800 hover:underline"
                        >
                            Open account
                        </Link>
                    </div>
                    <dl class="flex flex-wrap gap-6 text-sm">
                        <div>
                            <dt class="text-stone-500">Orders</dt>
                            <dd class="font-semibold">{{ customer.total_orders }}</dd>
                        </div>
                        <div>
                            <dt class="text-stone-500">Lifetime value</dt>
                            <dd class="font-semibold">{{ formatCurrency(Number(customer.total_spent), currency) }}</dd>
                        </div>
                    </dl>
                </div>
                <p v-if="customer.phone" class="mt-2 text-sm text-stone-600">
                    Phone: <span class="font-mono">{{ customer.phone }}</span>
                </p>
                <div v-if="customer.address || customer.city" class="mt-3 text-sm text-stone-600">
                    <p>{{ customer.address }}</p>
                    <p>
                        {{ customer.city }}<span v-if="customer.state">, {{ customer.state }}</span>
                        {{ customer.postal_code }}
                    </p>
                    <p>{{ customer.country }}</p>
                </div>
            </div>

            <form
                class="mt-6 space-y-4 rounded-xl border border-stone-200 bg-white p-5 shadow-sm"
                @submit.prevent="saveProfile"
            >
                <h3 class="text-xs font-bold uppercase tracking-wide text-stone-500">Edit profile</h3>
                <div>
                    <label class="text-xs font-medium text-stone-600">Phone</label>
                    <input v-model="profileForm.phone" type="text" class="mt-1 w-full rounded-lg border border-stone-200 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="text-xs font-medium text-stone-600">Address</label>
                    <textarea v-model="profileForm.address" rows="2" class="mt-1 w-full rounded-lg border border-stone-200 px-3 py-2 text-sm" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-medium text-stone-600">City</label>
                        <input v-model="profileForm.city" type="text" class="mt-1 w-full rounded-lg border border-stone-200 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="text-xs font-medium text-stone-600">State / region</label>
                        <input v-model="profileForm.state" type="text" class="mt-1 w-full rounded-lg border border-stone-200 px-3 py-2 text-sm" />
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-medium text-stone-600">Postal code</label>
                        <input
                            v-model="profileForm.postal_code"
                            type="text"
                            class="mt-1 w-full rounded-lg border border-stone-200 px-3 py-2 text-sm"
                        />
                    </div>
                    <div>
                        <label class="text-xs font-medium text-stone-600">Country</label>
                        <input v-model="profileForm.country" type="text" class="mt-1 w-full rounded-lg border border-stone-200 px-3 py-2 text-sm" />
                    </div>
                </div>
                <ul
                    v-if="Object.keys(profileForm.errors).length"
                    class="rounded border border-red-200 bg-red-50 p-2 text-sm text-red-800"
                >
                    <li v-for="(msg, k) in profileForm.errors" :key="k">{{ String(msg) }}</li>
                </ul>
                <button
                    type="submit"
                    :disabled="profileForm.processing"
                    class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 disabled:opacity-50"
                >
                    {{ profileForm.processing ? 'Saving…' : 'Save profile' }}
                </button>
            </form>

            <h3 class="mt-8 text-sm font-bold uppercase tracking-wide text-stone-700">Recent orders</h3>
            <div class="mt-2 overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                <table class="w-full text-sm">
                    <thead class="bg-stone-50 text-left text-xs text-stone-500">
                        <tr>
                            <th class="px-3 py-2">Order</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-if="!customer.orders.length">
                            <td colspan="3" class="px-3 py-6 text-center text-stone-500">No orders yet.</td>
                        </tr>
                        <tr v-for="o in customer.orders" v-else :key="o.id">
                            <td class="px-3 py-2">
                                <Link :href="`/admin/orders/${o.id}`" class="font-mono text-amber-800 hover:underline">
                                    {{ o.order_number }}
                                </Link>
                            </td>
                            <td class="px-3 py-2 capitalize">{{ o.status }}</td>
                            <td class="px-3 py-2 text-right">{{ formatCurrency(Number(o.total), currency) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
