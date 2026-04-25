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
    customers: {
        data: {
            id: number;
            phone: string | null;
            city: string | null;
            country: string | null;
            total_spent: string | number;
            total_orders: number;
            user?: { id: number; name: string; email: string } | null;
        }[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { search?: string };
}>();

const search = ref(props.filters.search ?? '');

const apply = () => {
    router.get('/admin/customers', { search: search.value }, { preserveState: true, preserveScroll: true });
};

let t: ReturnType<typeof setTimeout> | null = null;
watch(search, () => {
    if (t) clearTimeout(t);
    t = setTimeout(apply, 400);
});
</script>

<template>
    <Head title="Admin — Customers" />
    <AdminLayout>
        <template #title>Customers</template>
        <div>
            <p class="text-sm text-stone-600">Profiles linked to registered accounts.</p>
            <input
                v-model="search"
                type="search"
                placeholder="Name, email, city…"
                class="mt-4 w-full max-w-md rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:outline-none"
            />

            <div class="mt-6 overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50 text-left text-xs font-semibold uppercase text-stone-500">
                        <tr>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Location</th>
                            <th class="px-4 py-3 text-right">Orders</th>
                            <th class="px-4 py-3 text-right">Spent</th>
                            <th class="px-4 py-3 text-right">Account</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="c in customers.data" :key="c.id" class="hover:bg-stone-50/50">
                            <td class="px-4 py-3">
                                <Link
                                    :href="`/admin/customers/${c.id}`"
                                    class="font-medium text-amber-800 hover:underline"
                                >
                                    {{ c.user?.name ?? '—' }}
                                </Link>
                                <span class="mt-0.5 block text-xs text-stone-500">{{ c.user?.email }}</span>
                            </td>
                            <td class="px-4 py-3 text-stone-600">
                                <span v-if="c.city">{{ c.city }}</span>
                                <span v-if="c.country">, {{ c.country }}</span>
                                <span v-if="!c.city && !c.country">—</span>
                            </td>
                            <td class="px-4 py-3 text-right">{{ c.total_orders }}</td>
                            <td class="px-4 py-3 text-right font-medium">
                                {{ formatCurrency(Number(c.total_spent), currency) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Link
                                    v-if="c.user?.id"
                                    :href="`/admin/users/${c.user.id}`"
                                    class="text-amber-800 hover:underline"
                                >
                                    View
                                </Link>
                                <span v-else class="text-stone-400">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="customers.links.length > 3" class="mt-6">
                <Pagination :links="customers.links" />
            </div>
        </div>
    </AdminLayout>
</template>
