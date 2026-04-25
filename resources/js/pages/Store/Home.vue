<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/layouts/ShopLayout.vue';
import ProductCard from '@/components/ProductCard.vue';

type Featured = {
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

defineProps<{
    featuredProducts: Featured[];
    categories: { id: number; name: string; slug: string; image: string | null }[];
}>();
</script>

<template>
    <Head title="Home — Boachat" />
    <ShopLayout>
        <section class="border-b border-emerald-100 bg-gradient-to-b from-emerald-50 to-stone-50">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:flex lg:items-center lg:gap-12 lg:px-8 lg:py-20">
                <div class="max-w-xl">
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">
                        Nigerian groceries
                    </p>
                    <h1 class="mt-2 text-4xl font-bold tracking-tight text-stone-900 sm:text-5xl">
                        Garri, rice, palm oil &amp; more — delivered with care.
                    </h1>
                    <p class="mt-4 text-lg text-stone-600">
                        Browse staples and fresh produce, read reviews, and pay securely with Stripe at checkout.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link
                            href="/products"
                            class="inline-flex items-center rounded-full bg-emerald-700 px-6 py-3 text-sm font-semibold text-white shadow hover:bg-emerald-800"
                        >
                            Shop all products
                        </Link>
                        <Link
                            href="/register"
                            class="inline-flex items-center rounded-full border border-stone-300 bg-white px-6 py-3 text-sm font-semibold text-stone-800 hover:border-emerald-400"
                        >
                            Create an account
                        </Link>
                    </div>
                </div>
                <div
                    class="mt-10 hidden flex-1 rounded-2xl border border-stone-200 bg-white p-6 shadow-lg lg:mt-0 lg:block"
                >
                    <p class="text-sm font-medium text-stone-500">Popular categories</p>
                    <ul class="mt-4 grid grid-cols-2 gap-3">
                        <li v-for="cat in categories" :key="cat.id">
                            <Link
                                :href="`/products?category=${cat.slug}`"
                                class="block rounded-xl border border-stone-100 bg-stone-50 px-4 py-3 text-sm font-semibold text-stone-800 hover:border-amber-200 hover:bg-amber-50"
                            >
                                {{ cat.name }}
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-stone-900">Featured picks</h2>
                    <p class="mt-1 text-stone-600">Staff favourites and seasonal highlights.</p>
                </div>
                <Link href="/products" class="text-sm font-semibold text-amber-800 hover:text-amber-900">
                    View all →
                </Link>
            </div>
            <div
                v-if="featuredProducts.length"
                class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
            >
                <ProductCard v-for="p in featuredProducts" :key="p.id" :product="p" />
            </div>
            <p v-else class="mt-8 rounded-xl border border-dashed border-stone-200 bg-white p-10 text-center text-stone-600">
                No featured products yet. Run
                <code class="rounded bg-stone-100 px-1 py-0.5 text-sm">php artisan db:seed</code>
                to load demo data.
            </p>
        </section>
    </ShopLayout>
</template>
