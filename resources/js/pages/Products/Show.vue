<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ChevronRight, Star, ShoppingCart, MessageCircle } from 'lucide-vue-next';
import ShopLayout from '@/layouts/ShopLayout.vue';
import ProductCard from '@/components/ProductCard.vue';

const props = defineProps<{
    product: {
        id: number;
        name: string;
        slug: string;
        sku?: string;
        price: string | number;
        compare_price?: string | number | null;
        discount_percentage?: number | null;
        description: string;
        quantity: number;
        track_quantity: boolean;
        is_in_stock: boolean;
        images?: string[] | null;
        average_rating: number;
        category?: { name: string };
        reviews: { id: number; rating: number; title: string | null; content: string; created_at: string; user: { name: string } }[];
    };
    relatedProducts: {
        id: number;
        slug: string;
        name: string;
        price: string | number;
        compare_price?: string | number | null;
        images?: string[] | null;
        average_rating?: number;
        reviews_count?: number;
        is_in_stock?: boolean;
    }[];
}>();

const page = usePage();
const quantity = ref(1);
const selectedImage = ref(props.product.images?.[0]);

const maxQty = computed(() => {
    if (!props.product.track_quantity) {
        return 10;
    }
    return Math.max(1, Math.min(10, props.product.quantity));
});

const reviewForm = useForm({
    product_id: props.product.id,
    rating: 5,
    title: '',
    content: '',
});

const submitReview = () => {
    reviewForm.post('/reviews', { preserveScroll: true });
};

const addToCart = () => {
    router.post('/cart/add', {
        product_id: props.product.id,
        quantity: quantity.value,
    });
};

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
</script>

<template>
    <Head :title="product.name" />
    <ShopLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <nav class="mb-8 flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <li>
                        <Link href="/" class="text-stone-500 hover:text-stone-800">Home</Link>
                    </li>
                    <li class="flex items-center gap-2 text-stone-400">
                        <ChevronRight class="h-4 w-4" />
                        <Link href="/products" class="text-stone-500 hover:text-stone-800">Products</Link>
                    </li>
                    <li class="flex items-center gap-2 text-stone-400">
                        <ChevronRight class="h-4 w-4" />
                        <span class="font-medium text-stone-900">{{ product.name }}</span>
                    </li>
                </ol>
            </nav>

            <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">
                <div>
                    <div class="aspect-square overflow-hidden rounded-xl bg-stone-100">
                        <img
                            :src="selectedImage || product.images?.[0] || 'https://placehold.co/600x600/e7e5e4/57534e?text=Product'"
                            :alt="product.name"
                            class="h-full w-full object-cover object-center"
                        />
                    </div>
                    <div v-if="(product.images?.length ?? 0) > 1" class="mt-4 grid grid-cols-4 gap-3">
                        <button
                            v-for="(image, index) in product.images"
                            :key="index"
                            type="button"
                            class="aspect-square overflow-hidden rounded-lg border-2 bg-stone-100"
                            :class="selectedImage === image ? 'border-amber-500' : 'border-transparent'"
                            @click="selectedImage = image"
                        >
                            <img :src="image" :alt="`${product.name} ${index + 1}`" class="h-full w-full object-cover" />
                        </button>
                    </div>
                </div>

                <div>
                    <h1 class="text-3xl font-bold text-stone-900">{{ product.name }}</h1>
                    <div class="mt-4 flex items-center gap-1">
                        <Star
                            v-for="rating in 5"
                            :key="rating"
                            class="h-5 w-5"
                            :class="rating <= product.average_rating ? 'fill-amber-400 text-amber-400' : 'text-stone-300'"
                            :stroke-width="1.5"
                        />
                        <span class="ml-2 text-sm text-stone-500">({{ product.reviews.length }} reviews)</span>
                    </div>

                    <div class="mt-6">
                        <div v-if="product.compare_price" class="flex flex-wrap items-center gap-2">
                            <span class="text-3xl font-bold text-stone-900">${{ product.price }}</span>
                            <span class="text-lg text-stone-400 line-through">${{ product.compare_price }}</span>
                            <span
                                v-if="product.discount_percentage"
                                class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800"
                            >
                                Save {{ product.discount_percentage }}%
                            </span>
                        </div>
                        <div v-else>
                            <span class="text-3xl font-bold text-stone-900">${{ product.price }}</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <span
                            class="inline-flex rounded-full px-3 py-1 text-sm font-medium"
                            :class="product.is_in_stock ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'"
                        >
                            {{ product.is_in_stock ? 'In stock' : 'Out of stock' }}
                        </span>
                        <span v-if="product.track_quantity" class="ml-2 text-sm text-stone-500">
                            {{ product.quantity }} available
                        </span>
                    </div>

                    <div class="mt-6 max-w-none space-y-3 text-sm leading-relaxed text-stone-700" v-html="product.description" />

                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <div class="w-28">
                            <label class="text-xs font-medium text-stone-600">Quantity</label>
                            <select
                                v-model.number="quantity"
                                class="mt-1 w-full rounded-lg border-stone-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                            >
                                <option v-for="n in maxQty" :key="n" :value="n">{{ n }}</option>
                            </select>
                        </div>
                        <button
                            type="button"
                            :disabled="!product.is_in_stock"
                            class="inline-flex flex-1 min-w-[200px] items-center justify-center rounded-full bg-amber-600 px-8 py-3 text-base font-semibold text-white shadow hover:bg-amber-700 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="addToCart"
                        >
                            <ShoppingCart class="mr-2 h-5 w-5" />
                            Add to cart
                        </button>
                    </div>

                    <dl class="mt-8 grid gap-4 border-t border-stone-200 pt-8 text-sm">
                        <div v-if="product.sku">
                            <dt class="font-medium text-stone-500">SKU</dt>
                            <dd class="mt-1 text-stone-900">{{ product.sku }}</dd>
                        </div>
                        <div v-if="product.category">
                            <dt class="font-medium text-stone-500">Category</dt>
                            <dd class="mt-1 text-stone-900">{{ product.category.name }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <section v-if="page.props.auth?.user" class="mt-16 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-stone-900">Write a review</h2>
                <form class="mt-4 space-y-4" @submit.prevent="submitReview">
                    <div>
                        <label class="text-sm font-medium text-stone-700">Rating</label>
                        <select
                            v-model.number="reviewForm.rating"
                            class="mt-1 w-full max-w-xs rounded-lg border-stone-300 text-sm"
                        >
                            <option v-for="n in 5" :key="n" :value="n">{{ n }} stars</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-stone-700">Title (optional)</label>
                        <input v-model="reviewForm.title" type="text" class="mt-1 w-full rounded-lg border-stone-300 text-sm" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-stone-700">Review</label>
                        <textarea
                            v-model="reviewForm.content"
                            required
                            rows="4"
                            class="mt-1 w-full rounded-lg border-stone-300 text-sm"
                        />
                    </div>
                    <p v-if="reviewForm.errors.content" class="text-sm text-red-600">{{ reviewForm.errors.content }}</p>
                    <button
                        type="submit"
                        class="rounded-full bg-stone-900 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-800 disabled:opacity-50"
                        :disabled="reviewForm.processing"
                    >
                        Submit review
                    </button>
                </form>
            </section>

            <div class="mt-16">
                <h2 class="mb-6 text-2xl font-bold text-stone-900">Customer reviews</h2>
                <div v-if="product.reviews.length" class="space-y-8">
                    <div v-for="review in product.reviews" :key="review.id" class="border-b border-stone-200 pb-8">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <Star
                                    v-for="rating in 5"
                                    :key="rating"
                                    class="h-4 w-4"
                                    :class="rating <= review.rating ? 'fill-amber-400 text-amber-400' : 'text-stone-300'"
                                    :stroke-width="1.5"
                                />
                                <span v-if="review.title" class="text-sm font-semibold text-stone-900">{{ review.title }}</span>
                            </div>
                            <span class="text-sm text-stone-500">{{ formatDate(review.created_at) }}</span>
                        </div>
                        <p class="mt-2 text-stone-700">{{ review.content }}</p>
                        <p class="mt-2 text-sm text-stone-500">— {{ review.user.name }}</p>
                    </div>
                </div>
                <div v-else class="rounded-xl bg-stone-50 py-12 text-center">
                    <MessageCircle class="mx-auto h-12 w-12 text-stone-400" :stroke-width="1.5" />
                    <h3 class="mt-2 text-sm font-medium text-stone-900">No reviews yet</h3>
                    <p class="mt-1 text-sm text-stone-500">Be the first to review this product.</p>
                </div>
            </div>

            <div v-if="relatedProducts.length" class="mt-16">
                <h2 class="mb-6 text-2xl font-bold text-stone-900">Related products</h2>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <ProductCard v-for="p in relatedProducts" :key="p.id" :product="p" />
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
