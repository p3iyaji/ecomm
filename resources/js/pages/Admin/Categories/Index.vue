<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Pagination.vue';

defineProps<{
    categories: {
        data: {
            id: number;
            name: string;
            slug: string;
            is_active: boolean;
            sort_order: number;
            products_count: number;
        }[];
        links: { url: string | null; label: string; active: boolean }[];
    };
}>();

const remove = (id: number) => {
    if (!confirm('Delete this category? It must have no products.')) {
        return;
    }
    router.delete(`/admin/categories/${id}`);
};
</script>

<template>
    <Head title="Admin — Categories" />
    <AdminLayout>
        <template #title>Categories</template>
        <div>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <p class="text-sm text-stone-600">Organize your catalog into browsable groups.</p>
                <Link
                    href="/admin/categories/create"
                    class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-amber-700"
                >
                    Add category
                </Link>
            </div>

            <div class="mt-6 overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50 text-left text-xs font-semibold uppercase text-stone-500">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Slug</th>
                            <th class="px-4 py-3 text-right">Sort</th>
                            <th class="px-4 py-3 text-right">Products</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="c in categories.data" :key="c.id">
                            <td class="px-4 py-3 font-medium text-stone-900">{{ c.name }}</td>
                            <td class="px-4 py-3 font-mono text-stone-600">{{ c.slug }}</td>
                            <td class="px-4 py-3 text-right">{{ c.sort_order }}</td>
                            <td class="px-4 py-3 text-right">{{ c.products_count }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        c.is_active
                                            ? 'bg-emerald-100 text-emerald-800'
                                            : 'bg-stone-200 text-stone-700'
                                    "
                                >
                                    {{ c.is_active ? 'Active' : 'Hidden' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Link
                                    :href="`/admin/categories/${c.id}/edit`"
                                    class="font-medium text-amber-800 hover:underline"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="ms-3 text-sm font-medium text-red-700 hover:underline"
                                    @click="remove(c.id)"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="categories.links.length > 3" class="mt-6">
                <Pagination :links="categories.links" />
            </div>
        </div>
    </AdminLayout>
</template>
