<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Pagination.vue';

const props = defineProps<{
    users: {
        data: {
            id: number;
            name: string;
            email: string;
            email_verified_at: string | null;
            orders_count: number;
            customer?: { id: number } | null;
        }[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { search?: string };
}>();

const search = ref(props.filters.search ?? '');

const apply = () => {
    router.get('/admin/users', { search: search.value }, { preserveState: true, preserveScroll: true });
};

let t: ReturnType<typeof setTimeout> | null = null;
watch(search, () => {
    if (t) clearTimeout(t);
    t = setTimeout(apply, 400);
});
</script>

<template>
    <Head title="Admin — Accounts" />
    <AdminLayout>
        <template #title>Accounts</template>
        <div>
            <p class="text-sm text-stone-600">
                All registered users. Each account can have one customer profile used for checkout and CRM fields.
            </p>
            <input
                v-model="search"
                type="search"
                placeholder="Search by name or email…"
                class="mt-4 w-full max-w-md rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:outline-none"
            />

            <div class="mt-6 overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50 text-left text-xs font-semibold uppercase text-stone-500">
                        <tr>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Verified</th>
                            <th class="px-4 py-3 text-right">Orders</th>
                            <th class="px-4 py-3">Customer</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="u in users.data" :key="u.id" class="hover:bg-stone-50/50">
                            <td class="px-4 py-3">
                                <Link :href="`/admin/users/${u.id}`" class="font-medium text-amber-800 hover:underline">
                                    {{ u.name }}
                                </Link>
                                <span class="mt-0.5 block text-xs text-stone-500">{{ u.email }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="u.email_verified_at ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900'"
                                >
                                    {{ u.email_verified_at ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">{{ u.orders_count }}</td>
                            <td class="px-4 py-3">
                                <Link
                                    v-if="u.customer"
                                    :href="`/admin/customers/${u.customer.id}`"
                                    class="text-amber-800 hover:underline"
                                >
                                    Profile
                                </Link>
                                <span v-else class="text-stone-400">None</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="users.links.length > 3" class="mt-6">
                <Pagination :links="users.links" />
            </div>
        </div>
    </AdminLayout>
</template>
