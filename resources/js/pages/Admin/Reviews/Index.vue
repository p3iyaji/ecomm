<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Pagination.vue';
const props = defineProps<{
    reviews: {
        data: {
            id: number;
            rating: number;
            title: string | null;
            content: string | null;
            is_approved: boolean;
            user?: { name: string; email: string } | null;
            product?: { id: number; name: string; slug: string } | null;
        }[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { search?: string; status?: string };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

const apply = () => {
    router.get(
        '/admin/reviews',
        { search: search.value, status: status.value || undefined },
        { preserveState: true, preserveScroll: true },
    );
};

let t: ReturnType<typeof setTimeout> | null = null;
watch(search, () => {
    if (t) clearTimeout(t);
    t = setTimeout(apply, 400);
});
watch(status, () => apply());

const setApproved = (id: number, approved: boolean) => {
    router.patch(`/admin/reviews/${id}`, { is_approved: approved }, { preserveScroll: true });
};

const remove = (id: number) => {
    if (!confirm('Remove this review?')) {
        return;
    }
    router.delete(`/admin/reviews/${id}`);
};
</script>

<template>
    <Head title="Admin — Reviews" />
    <AdminLayout>
        <template #title>Reviews</template>
        <div>
            <p class="text-sm text-stone-600">Approve or hide customer feedback before it appears on the storefront.</p>

            <div class="mt-4 flex flex-wrap gap-2">
                <input
                    v-model="search"
                    type="search"
                    placeholder="Search…"
                    class="min-w-[200px] flex-1 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm"
                />
                <select v-model="status" class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                </select>
            </div>

            <div class="mt-6 space-y-4">
                <article
                    v-for="r in reviews.data"
                    :key="r.id"
                    class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm"
                >
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-amber-700">★ {{ r.rating }}/5</span>
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        r.is_approved
                                            ? 'bg-emerald-100 text-emerald-800'
                                            : 'bg-amber-100 text-amber-900'
                                    "
                                >
                                    {{ r.is_approved ? 'Approved' : 'Pending' }}
                                </span>
                            </div>
                            <p v-if="r.title" class="mt-1 font-semibold text-stone-900">{{ r.title }}</p>
                            <p v-if="r.content" class="mt-1 text-sm text-stone-700">{{ r.content }}</p>
                            <p class="mt-2 text-xs text-stone-500">
                                {{ r.user?.name }} · {{ r.user?.email }}
                            </p>
                            <p v-if="r.product" class="text-sm">
                                <Link :href="`/products/${r.product.slug}`" class="text-amber-800 hover:underline">
                                    {{ r.product.name }}
                                </Link>
                            </p>
                        </div>
                        <div class="flex shrink-0 flex-wrap gap-2">
                            <button
                                v-if="!r.is_approved"
                                type="button"
                                class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700"
                                @click="setApproved(r.id, true)"
                            >
                                Approve
                            </button>
                            <button
                                v-if="r.is_approved"
                                type="button"
                                class="rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-medium hover:bg-stone-50"
                                @click="setApproved(r.id, false)"
                            >
                                Hide
                            </button>
                            <button
                                type="button"
                                class="text-xs font-medium text-red-700 hover:underline"
                                @click="remove(r.id)"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </article>
            </div>

            <div v-if="reviews.links.length > 3" class="mt-6">
                <Pagination :links="reviews.links" />
            </div>
        </div>
    </AdminLayout>
</template>
