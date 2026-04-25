<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { formatCurrency } from '@/lib/utils';
import { computed } from 'vue';

const page = usePage();
const currency = computed(() =>
    String((page.props.shop as { currency?: string } | undefined)?.currency ?? 'USD').toUpperCase(),
);

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        email_verified_at: string | null;
        created_at: string;
        customer: {
            id: number;
            phone: string | null;
            address: string | null;
            city: string | null;
            state: string | null;
            postal_code: string | null;
            country: string | null;
            total_spent: string | number;
            total_orders: number;
        } | null;
        orders: {
            id: number;
            order_number: string;
            status: string;
            total: string | number;
            created_at: string;
        }[];
    };
}>();

const userForm = useForm({
    name: props.user.name,
    email: props.user.email,
});

const saveUser = () => {
    userForm.patch(`/admin/users/${props.user.id}`, { preserveScroll: true });
};

const createCustomerProfile = () => {
    router.post(
        '/admin/customers',
        { user_id: props.user.id },
        { preserveScroll: true },
    );
};
</script>

<template>
    <Head :title="`User — ${user.name}`" />
    <AdminLayout>
        <template #title>Account</template>
        <div class="max-w-3xl">
            <p class="text-sm text-stone-500">
                <Link href="/admin/users" class="text-amber-800 hover:underline">← All accounts</Link>
            </p>

            <form class="mt-4 space-y-4 rounded-xl border border-stone-200 bg-white p-5 shadow-sm" @submit.prevent="saveUser">
                <h2 class="text-xs font-bold uppercase tracking-wide text-stone-500">Login identity</h2>
                <div>
                    <label class="text-xs font-medium text-stone-600">Name</label>
                    <input
                        v-model="userForm.name"
                        type="text"
                        class="mt-1 w-full rounded-lg border border-stone-200 px-3 py-2 text-sm"
                        required
                    />
                </div>
                <div>
                    <label class="text-xs font-medium text-stone-600">Email</label>
                    <input
                        v-model="userForm.email"
                        type="email"
                        class="mt-1 w-full rounded-lg border border-stone-200 px-3 py-2 text-sm"
                        required
                    />
                    <p class="mt-1 text-xs text-stone-500">
                        Changing email clears verification until they confirm again.
                    </p>
                </div>
                <ul
                    v-if="Object.keys(userForm.errors).length"
                    class="rounded border border-red-200 bg-red-50 p-2 text-sm text-red-800"
                >
                    <li v-for="(msg, k) in userForm.errors" :key="k">{{ String(msg) }}</li>
                </ul>
                <button
                    type="submit"
                    :disabled="userForm.processing"
                    class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 disabled:opacity-50"
                >
                    {{ userForm.processing ? 'Saving…' : 'Save user' }}
                </button>
            </form>

            <div class="mt-6 rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="text-xs font-bold uppercase tracking-wide text-stone-500">Customer profile</h2>
                <p v-if="user.customer" class="mt-2 text-sm text-stone-600">
                    <Link :href="`/admin/customers/${user.customer.id}`" class="font-medium text-amber-800 hover:underline">
                        Open customer record
                    </Link>
                    · {{ user.customer.total_orders }} orders ·
                    {{ formatCurrency(Number(user.customer.total_spent), currency) }} lifetime
                </p>
                <div v-else class="mt-3">
                    <p class="text-sm text-stone-600">
                        No customer profile yet. Checkout normally creates one; you can also create an empty profile
                        here.
                    </p>
                    <button
                        type="button"
                        class="mt-3 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-950 hover:bg-amber-100"
                        @click="createCustomerProfile"
                    >
                        Create customer profile
                    </button>
                </div>
            </div>

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
                        <tr v-if="!user.orders.length">
                            <td colspan="3" class="px-3 py-6 text-center text-stone-500">No orders yet.</td>
                        </tr>
                        <tr v-for="o in user.orders" v-else :key="o.id">
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
