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
    products: {
        data: {
            id: number;
            name: string;
            slug: string;
            sku: string;
            price: string | number;
            quantity: number;
            is_active: boolean;
            category?: { name: string } | null;
        }[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    categories: { id: number; name: string; slug: string }[];
    filters: { search?: string; category_id?: string; is_active?: string };
}>();

const filters = ref({
    search: props.filters.search ?? '',
    category_id: props.filters.category_id ?? '',
    is_active: props.filters.is_active ?? '',
});

const apply = () => {
    router.get('/admin/products', { ...filters.value }, { preserveState: true, preserveScroll: true });
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
    <Head title="Admin — Products" />
    <AdminLayout>
        <template #title>Products</template>
        <div>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <p class="text-sm text-stone-600">Create, edit, and remove catalog items.</p>
                <Link
                    href="/admin/products/create"
                    class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-amber-700"
                >
                    Add product
                </Link>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <input
                    v-model="filters.search"
                    type="search"
                    placeholder="Search name or SKU…"
                    class="w-full min-w-[200px] max-w-md rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 focus:outline-none sm:flex-1"
                />
                <select
                    v-model="filters.category_id"
                    class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:outline-none"
                    @change="apply"
                >
                    <option value="">All categories</option>
                    <option v-for="c in categories" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                </select>
                <select
                    v-model="filters.is_active"
                    class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:outline-none"
                    @change="apply"
                >
                    <option value="">All states</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div class="mt-6 overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                        <thead class="bg-stone-50 text-left text-xs font-semibold uppercase text-stone-500">
                            <tr>
                                <th class="px-4 py-3">Product</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">SKU</th>
                                <th class="px-4 py-3 text-right">Price</th>
                                <th class="px-4 py-3 text-right">Stock</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <tr v-for="p in products.data" :key="p.id" class="hover:bg-stone-50/50">
                                <td class="px-4 py-3">
                                    <span class="font-medium text-stone-900">{{ p.name }}</span>
                                </td>
                                <td class="px-4 py-3 text-stone-600">
                                    {{ p.category?.name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 font-mono text-stone-600">{{ p.sku }}</td>
                                <td class="px-4 py-3 text-right font-medium">
                                    {{ formatCurrency(Number(p.price), currency) }}
                                </td>
                                <td class="px-4 py-3 text-right text-stone-600">{{ p.quantity }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="
                                            p.is_active
                                                ? 'bg-emerald-100 text-emerald-800'
                                                : 'bg-stone-200 text-stone-700'
                                        "
                                    >
                                        {{ p.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        :href="`/admin/products/${p.id}/edit`"
                                        class="font-medium text-amber-800 hover:underline"
                                    >
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="products.links.length > 3" class="mt-6">
                <Pagination :links="products.links" />
            </div>
        </div>
    </AdminLayout>
</template>
