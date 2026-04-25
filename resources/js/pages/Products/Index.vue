<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ShoppingBag } from 'lucide-vue-next';
import ShopLayout from '@/layouts/ShopLayout.vue';
import ProductCard from '@/components/ProductCard.vue';
import Pagination from '@/components/Pagination.vue';

type ProductRow = {
    id: number;
    slug: string;
    name: string;
    price: string | number;
    compare_price?: string | number | null;
    images?: string[] | null;
    average_rating?: number;
    reviews_count?: number;
    is_in_stock?: boolean;
};

const props = defineProps<{
    products: {
        data: ProductRow[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    categories: { id: number; name: string; slug: string }[];
    filters: Record<string, string | undefined>;
}>();

const filters = ref({
    search: props.filters.search ?? '',
    category: props.filters.category ?? '',
    sort: props.filters.sort ?? 'newest',
    min_price: props.filters.min_price ?? '',
    max_price: props.filters.max_price ?? '',
});

let searchTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get('/products', { ...filters.value }, { preserveState: true, preserveScroll: true });
};

const onSearchInput = () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }
    searchTimer = setTimeout(() => applyFilters(), 400);
};
</script>

<template>
    <Head title="Products" />
    <ShopLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-stone-900">All products</h1>
                <p class="mt-2 text-stone-600">Search, filter by category, and sort results.</p>
            </div>

            <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="md:col-span-2">
                    <input
                        v-model="filters.search"
                        type="search"
                        placeholder="Search products..."
                        class="w-full rounded-lg border-stone-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                        @input="onSearchInput"
                    />
                </div>
                <select
                    v-model="filters.category"
                    class="rounded-lg border-stone-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                    @change="applyFilters"
                >
                    <option value="">All categories</option>
                    <option v-for="category in categories" :key="category.id" :value="category.slug">
                        {{ category.name }}
                    </option>
                </select>
                <select
                    v-model="filters.sort"
                    class="rounded-lg border-stone-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                    @change="applyFilters"
                >
                    <option value="newest">Newest</option>
                    <option value="price_asc">Price: Low to high</option>
                    <option value="price_desc">Price: High to low</option>
                    <option value="popular">Most popular</option>
                </select>
            </div>

            <div v-if="products.data.length" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <ProductCard v-for="product in products.data" :key="product.id" :product="product" />
            </div>

            <div v-else class="py-12 text-center">
                <ShoppingBag class="mx-auto h-12 w-12 text-stone-400" :stroke-width="1.5" />
                <h3 class="mt-2 text-sm font-medium text-stone-900">No products found</h3>
                <p class="mt-1 text-sm text-stone-500">Try adjusting your search or filters.</p>
            </div>

            <div v-if="products.links.length > 3" class="mt-8">
                <Pagination :links="products.links" />
            </div>
        </div>
    </ShopLayout>
</template>
