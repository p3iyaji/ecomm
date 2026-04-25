<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Star, ShoppingCart } from 'lucide-vue-next';

const props = defineProps<{
    product: {
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
}>();

const quickAddToCart = (e: Event) => {
    e.preventDefault();
    e.stopPropagation();
    router.post(
        '/cart/add',
        {
            product_id: props.product.id,
            quantity: 1,
        },
        { preserveScroll: true },
    );
};
</script>

<template>
    <Link :href="`/products/${product.slug}`" class="group">
        <div
            class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm transition hover:border-amber-200 hover:shadow-md"
        >
            <div class="aspect-square bg-stone-100">
                <img
                    :src="product.images?.[0] || 'https://placehold.co/400x400/e7e5e4/57534e?text=Product'"
                    :alt="product.name"
                    class="h-full w-full object-cover object-center transition duration-300 group-hover:scale-105"
                />
            </div>
            <div class="p-4">
                <h3 class="truncate text-sm font-semibold text-stone-900 group-hover:text-amber-800">
                    {{ product.name }}
                </h3>
                <div class="mt-1 flex items-center gap-1">
                    <Star
                        v-for="rating in 5"
                        :key="rating"
                        class="h-4 w-4"
                        :class="rating <= (product.average_rating || 0) ? 'fill-amber-400 text-amber-400' : 'text-stone-300'"
                        :stroke-width="1.5"
                    />
                    <span class="ml-1 text-xs text-stone-500">({{ product.reviews_count ?? 0 }})</span>
                </div>
                <div class="mt-2 flex items-center justify-between">
                    <div>
                        <span class="text-lg font-bold text-stone-900">${{ product.price }}</span>
                        <span v-if="product.compare_price" class="ml-2 text-sm text-stone-400 line-through">
                            ${{ product.compare_price }}
                        </span>
                    </div>
                    <span
                        v-if="product.is_in_stock === false"
                        class="rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800"
                    >
                        Out of stock
                    </span>
                </div>
                <button
                    type="button"
                    :disabled="product.is_in_stock === false"
                    class="mt-3 flex w-full items-center justify-center rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100 disabled:cursor-not-allowed disabled:opacity-50"
                    @click="quickAddToCart"
                >
                    <ShoppingCart class="mr-2 h-4 w-4" />
                    Add to cart
                </button>
            </div>
        </div>
    </Link>
</template>
