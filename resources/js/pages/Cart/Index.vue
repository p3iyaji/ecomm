<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import ShopLayout from '@/layouts/ShopLayout.vue';
import { Trash2 } from 'lucide-vue-next';

type CartItem = {
    product_id: number;
    name: string;
    price: number;
    quantity: number;
    total: number;
    image?: string | null;
};

const props = defineProps<{
    cart: { id: number; items: CartItem[] };
}>();

const subtotal = () => (props.cart.items ?? []).reduce((s, i) => s + Number(i.total), 0);

const updateQty = (productId: number, quantity: number) => {
    router.patch('/cart/update', { product_id: productId, quantity }, { preserveScroll: true });
};

const remove = (productId: number) => {
    router.delete('/cart/remove', { data: { product_id: productId }, preserveScroll: true });
};
</script>

<template>
    <Head title="Shopping cart" />
    <ShopLayout>
        <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-stone-900">Your cart</h1>
            <p class="mt-1 text-stone-600">Review items before checkout.</p>

            <div v-if="(cart.items ?? []).length" class="mt-8 space-y-4">
                <div
                    v-for="item in cart.items"
                    :key="item.product_id"
                    class="flex gap-4 rounded-xl border border-stone-200 bg-white p-4 shadow-sm"
                >
                    <img
                        :src="item.image || 'https://placehold.co/120x120/e7e5e4/57534e?text=Item'"
                        :alt="item.name"
                        class="h-24 w-24 shrink-0 rounded-lg object-cover"
                    />
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-stone-900">{{ item.name }}</p>
                        <p class="mt-1 text-sm text-stone-500">${{ Number(item.price).toFixed(2) }} each</p>
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <label class="text-sm text-stone-600">
                                Qty
                                <select
                                    :value="item.quantity"
                                    class="ml-2 rounded-md border-stone-300 text-sm"
                                    @change="
                                        updateQty(
                                            item.product_id,
                                            Number(($event.target as HTMLSelectElement).value),
                                        )
                                    "
                                >
                                    <option v-for="n in 20" :key="n" :value="n">{{ n }}</option>
                                </select>
                            </label>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 text-sm font-medium text-red-600 hover:text-red-800"
                                @click="remove(item.product_id)"
                            >
                                <Trash2 class="h-4 w-4" />
                                Remove
                            </button>
                        </div>
                    </div>
                    <p class="shrink-0 font-semibold text-stone-900">${{ Number(item.total).toFixed(2) }}</p>
                </div>

                <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <div class="flex justify-between text-lg font-semibold text-stone-900">
                        <span>Subtotal</span>
                        <span>${{ subtotal().toFixed(2) }}</span>
                    </div>
                    <p class="mt-2 text-sm text-stone-500">Taxes and shipping are calculated at checkout.</p>
                    <Link
                        v-if="$page.props.auth?.user"
                        href="/checkout"
                        class="mt-6 block w-full rounded-full bg-amber-600 py-3 text-center text-sm font-semibold text-white shadow hover:bg-amber-700"
                    >
                        Proceed to checkout
                    </Link>
                    <Link
                        v-else
                        href="/login"
                        class="mt-6 block w-full rounded-full border border-amber-600 py-3 text-center text-sm font-semibold text-amber-800 hover:bg-amber-50"
                    >
                        Sign in to checkout
                    </Link>
                </div>
            </div>

            <div v-else class="mt-12 rounded-xl border border-dashed border-stone-200 bg-white p-12 text-center">
                <p class="text-stone-600">Your cart is empty.</p>
                <Link href="/products" class="mt-4 inline-block font-semibold text-amber-800 hover:text-amber-900">
                    Continue shopping →
                </Link>
            </div>
        </div>
    </ShopLayout>
</template>
