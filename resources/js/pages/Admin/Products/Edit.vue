<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { computed } from 'vue';

const props = defineProps<{
    product: {
        id: number;
        name: string;
        slug: string;
        description: string;
        short_description: string | null;
        price: string | number;
        compare_price: string | number | null;
        cost_per_item: string | number | null;
        sku: string;
        barcode: string | null;
        quantity: number;
        security_stock: number;
        track_quantity: boolean;
        allow_backorder: boolean;
        weight: string | number | null;
        height: string | number | null;
        width: string | number | null;
        length: string | number | null;
        is_active: boolean;
        is_featured: boolean;
        category_id: number | null;
    };
    categories: { id: number; name: string; slug: string }[];
    imageUrlsText: string;
}>();

const toStr = (v: string | number | null) => (v == null || v === '' ? '' : String(v));

const form = useForm({
    name: props.product.name,
    slug: props.product.slug,
    description: props.product.description,
    short_description: toStr(props.product.short_description),
    price: toStr(props.product.price),
    compare_price: toStr(props.product.compare_price),
    cost_per_item: toStr(props.product.cost_per_item),
    sku: props.product.sku,
    barcode: toStr(props.product.barcode),
    quantity: props.product.quantity,
    security_stock: props.product.security_stock,
    track_quantity: props.product.track_quantity,
    allow_backorder: props.product.allow_backorder,
    weight: toStr(props.product.weight),
    height: toStr(props.product.height),
    width: toStr(props.product.width),
    length: toStr(props.product.length),
    is_active: props.product.is_active,
    is_featured: props.product.is_featured,
    category_id: (props.product.category_id ?? '') as string | number,
    image_urls: props.imageUrlsText,
    product_images: [] as File[],
});

function onProductImagesChange(e: Event) {
    const el = e.target as HTMLInputElement;
    form.product_images = el.files ? Array.from(el.files) : [];
}

const submit = () => {
    const n = (v: string | number) => (v === '' || v === null ? null : v);
    const build = (data: Record<string, unknown>) => ({
        ...data,
        slug: data.slug === '' ? null : data.slug,
        short_description: data.short_description === '' ? null : data.short_description,
        compare_price: n(data.compare_price as string & number),
        cost_per_item: n(data.cost_per_item as string & number),
        barcode: data.barcode === '' ? null : data.barcode,
        weight: n(data.weight as string & number),
        height: n(data.height as string & number),
        width: n(data.width as string & number),
        length: n(data.length as string & number),
        category_id: data.category_id === '' || data.category_id === null ? null : data.category_id,
    });

    const url = `/admin/products/${props.product.id}`;
    if (form.product_images.length > 0) {
        form
            .transform((data) => ({ ...build(data), _method: 'put' as const }))
            .post(url, { preserveScroll: true });
    } else {
        form.transform((data) => build(data)).put(url, { preserveScroll: true });
    }
};

const fieldClass =
    'w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 focus:outline-none';

const deleteProduct = () => {
    if (!confirm('Delete this product? You cannot remove products that have been sold.')) {
        return;
    }
    router.delete(`/admin/products/${props.product.id}`);
};

const hasErr = computed(() => Object.keys(form.errors).length > 0);
</script>

<template>
    <Head :title="`Edit — ${product.name}`" />
    <AdminLayout>
        <template #title>Edit product</template>
        <form class="max-w-4xl space-y-8" @submit.prevent="submit">
            <div class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-stone-800">Basic</h2>
                <div>
                    <label class="text-xs font-medium text-stone-600">Name *</label>
                    <input v-model="form.name" type="text" :class="fieldClass" required />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-medium text-stone-600">Slug (optional)</label>
                        <input v-model="form.slug" type="text" :class="fieldClass" />
                    </div>
                    <div>
                        <label class="text-xs font-medium text-stone-600">Category</label>
                        <select v-model="form.category_id" :class="fieldClass">
                            <option value="">— None —</option>
                            <option v-for="c in props.categories" :key="c.id" :value="c.id">
                                {{ c.name }}
                            </option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-stone-600">Description *</label>
                    <textarea v-model="form.description" rows="4" :class="fieldClass" required />
                </div>
                <div>
                    <label class="text-xs font-medium text-stone-600">Short description</label>
                    <input v-model="form.short_description" type="text" :class="fieldClass" />
                </div>
            </div>

            <div class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-stone-800">Pricing &amp; identity</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-medium text-stone-600">Price *</label>
                        <input v-model="form.price" type="number" min="0" step="0.01" :class="fieldClass" required />
                    </div>
                    <div>
                        <label class="text-xs font-medium text-stone-600">Compare at</label>
                        <input v-model="form.compare_price" type="number" min="0" step="0.01" :class="fieldClass" />
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-medium text-stone-600">Cost per item</label>
                        <input v-model="form.cost_per_item" type="number" min="0" step="0.01" :class="fieldClass" />
                    </div>
                    <div>
                        <label class="text-xs font-medium text-stone-600">SKU *</label>
                        <input v-model="form.sku" type="text" :class="fieldClass" required />
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-stone-600">Barcode</label>
                    <input v-model="form.barcode" type="text" :class="fieldClass" />
                </div>
            </div>

            <div class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-stone-800">Inventory</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-medium text-stone-600">Quantity *</label>
                        <input v-model.number="form.quantity" type="number" min="0" :class="fieldClass" required />
                    </div>
                    <div>
                        <label class="text-xs font-medium text-stone-600">Security stock *</label>
                        <input
                            v-model.number="form.security_stock"
                            type="number"
                            min="0"
                            :class="fieldClass"
                            required
                        />
                    </div>
                </div>
                <div class="flex flex-wrap gap-6">
                    <label class="flex cursor-pointer items-center gap-2 text-sm">
                        <input v-model="form.track_quantity" type="checkbox" class="rounded border-stone-300 text-amber-600" />
                        Track quantity
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 text-sm">
                        <input
                            v-model="form.allow_backorder"
                            type="checkbox"
                            class="rounded border-stone-300 text-amber-600"
                        />
                        Allow backorder
                    </label>
                </div>
            </div>

            <div class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-stone-800">Media</h2>
                <p class="text-xs text-stone-500">
                    One URL per line. New uploads are appended to this list. Use
                    <code class="rounded bg-stone-100 px-1">php artisan storage:link</code>
                    for local disk URLs.
                </p>
                <textarea v-model="form.image_urls" rows="4" :class="fieldClass" />
                <div>
                    <label class="text-xs font-medium text-stone-600">Add images (upload)</label>
                    <input
                        type="file"
                        accept="image/*"
                        multiple
                        :class="fieldClass + ' py-2 file:mr-3 file:rounded file:border-0 file:bg-amber-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-amber-900'"
                        @change="onProductImagesChange"
                    />
                    <p v-if="form.product_images.length" class="mt-1 text-xs text-stone-600">
                        {{ form.product_images.length }} new file(s) — saved on submit
                    </p>
                </div>
            </div>

            <div class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-stone-800">Dimensions (optional)</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-medium text-stone-600">Weight</label>
                        <input v-model="form.weight" type="number" min="0" step="0.01" :class="fieldClass" />
                    </div>
                    <div>
                        <label class="text-xs font-medium text-stone-600">Height</label>
                        <input v-model="form.height" type="number" min="0" step="0.01" :class="fieldClass" />
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-medium text-stone-600">Width</label>
                        <input v-model="form.width" type="number" min="0" step="0.01" :class="fieldClass" />
                    </div>
                    <div>
                        <label class="text-xs font-medium text-stone-600">Length</label>
                        <input v-model="form.length" type="number" min="0" step="0.01" :class="fieldClass" />
                    </div>
                </div>
            </div>

            <div class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-stone-800">Visibility</h2>
                <div class="flex flex-wrap gap-6">
                    <label class="flex cursor-pointer items-center gap-2 text-sm">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-stone-300 text-amber-600" />
                        Active
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 text-sm">
                        <input
                            v-model="form.is_featured"
                            type="checkbox"
                            class="rounded border-stone-300 text-amber-600"
                        />
                        Featured
                    </label>
                </div>
            </div>

            <ul v-if="hasErr" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                <li v-for="(msg, k) in form.errors" :key="k">{{ String(msg) }}</li>
            </ul>

            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-amber-700 disabled:opacity-50"
                >
                    {{ form.processing ? 'Saving…' : 'Save changes' }}
                </button>
                <Link href="/admin/products" class="text-sm font-medium text-stone-600 hover:text-stone-900">Back</Link>
                <button
                    type="button"
                    class="ms-auto text-sm font-medium text-red-700 hover:underline"
                    :disabled="form.processing"
                    @click="deleteProduct"
                >
                    Delete product
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
