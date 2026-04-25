<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    category: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        image: string | null;
        is_active: boolean;
        sort_order: number;
    };
}>();

const toStr = (v: string | null) => (v == null ? '' : v);

const form = useForm({
    name: props.category.name,
    slug: props.category.slug,
    description: toStr(props.category.description),
    image: toStr(props.category.image),
    is_active: props.category.is_active,
    sort_order: props.category.sort_order,
});

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            description: data.description === '' ? null : data.description,
            image: data.image === '' ? null : data.image,
        }))
        .put(`/admin/categories/${props.category.id}`, { preserveScroll: true });
};

const fieldClass =
    'w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 focus:outline-none';

const remove = () => {
    if (!confirm('Delete this category?')) {
        return;
    }
    router.delete(`/admin/categories/${props.category.id}`);
};
</script>

<template>
    <Head :title="`Edit — ${category.name}`" />
    <AdminLayout>
        <template #title>Edit category</template>
        <form class="max-w-xl space-y-6" @submit.prevent="submit">
            <div>
                <label class="text-xs font-medium text-stone-600">Name *</label>
                <input v-model="form.name" type="text" :class="fieldClass" required />
            </div>
            <div>
                <label class="text-xs font-medium text-stone-600">Slug *</label>
                <input v-model="form.slug" type="text" :class="fieldClass" required />
            </div>
            <div>
                <label class="text-xs font-medium text-stone-600">Description</label>
                <textarea v-model="form.description" rows="3" :class="fieldClass" />
            </div>
            <div>
                <label class="text-xs font-medium text-stone-600">Image URL</label>
                <input v-model="form.image" type="url" :class="fieldClass" />
            </div>
            <div>
                <label class="text-xs font-medium text-stone-600">Sort order *</label>
                <input v-model.number="form.sort_order" type="number" min="0" :class="fieldClass" required />
            </div>
            <label class="flex cursor-pointer items-center gap-2 text-sm">
                <input v-model="form.is_active" type="checkbox" class="rounded border-stone-300 text-amber-600" />
                Visible
            </label>
            <ul
                v-if="Object.keys(form.errors).length"
                class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800"
            >
                <li v-for="(msg, k) in form.errors" :key="k">{{ String(msg) }}</li>
            </ul>
            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-amber-700 disabled:opacity-50"
                >
                    Save
                </button>
                <Link href="/admin/categories" class="text-sm text-stone-600">Back</Link>
                <button type="button" class="ms-auto text-sm text-red-700 hover:underline" @click="remove">
                    Delete
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
