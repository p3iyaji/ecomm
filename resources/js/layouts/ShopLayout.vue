<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { ShoppingCart, User } from 'lucide-vue-next';

const page = usePage();
const cartCount = computed(() => Number(page.props.cartCount ?? 0));
const user = computed(() => page.props.auth?.user);
const isShopAdmin = computed(() => Boolean(page.props.isShopAdmin));
const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined);

watch(
    () => [flash.value?.success, flash.value?.error],
    () => {
        if (typeof window !== 'undefined' && (flash.value?.success || flash.value?.error)) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    },
);
</script>

<template>
    <div class="min-h-screen bg-stone-50 text-stone-900">
        <header class="sticky top-0 z-40 border-b border-stone-200 bg-white/95 shadow-sm backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex items-center gap-6">
                    <Link href="/" class="text-xl font-bold tracking-tight text-emerald-800">
                        Boa<span class="text-emerald-600">chat</span>
                    </Link>
                    <nav class="hidden items-center gap-4 text-sm font-medium text-stone-600 md:flex">
                        <Link href="/products" class="hover:text-amber-800">All products</Link>
                        <Link v-if="user" href="/orders" class="hover:text-amber-800">Your orders</Link>
                        <Link v-if="isShopAdmin" href="/admin" class="hover:text-amber-800">Shop admin</Link>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        href="/cart"
                        class="relative inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white px-3 py-2 text-sm font-medium text-stone-700 shadow-sm hover:border-amber-300 hover:text-amber-900"
                    >
                        <ShoppingCart class="h-4 w-4" />
                        <span class="hidden sm:inline">Cart</span>
                        <span
                            v-if="cartCount > 0"
                            class="absolute -top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-600 px-1 text-xs font-semibold text-white"
                        >
                            {{ cartCount > 99 ? '99+' : cartCount }}
                        </span>
                    </Link>

                    <template v-if="user">
                        <Link
                            href="/dashboard"
                            class="hidden items-center gap-1 rounded-full border border-stone-200 px-3 py-2 text-sm font-medium text-stone-700 hover:border-amber-300 sm:inline-flex"
                        >
                            <User class="h-4 w-4" />
                            Account
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            href="/login"
                            class="rounded-full px-3 py-2 text-sm font-medium text-stone-700 hover:text-amber-900"
                        >
                            Sign in
                        </Link>
                        <Link
                            href="/register"
                            class="rounded-full bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-amber-700"
                        >
                            Register
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <div
            v-if="flash?.success"
            class="border-b border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm text-emerald-900"
        >
            {{ flash.success }}
        </div>
        <div
            v-if="flash?.error"
            class="border-b border-red-200 bg-red-50 px-4 py-3 text-center text-sm text-red-900"
        >
            {{ flash.error }}
        </div>

        <main>
            <slot />
        </main>

        <footer class="mt-16 border-t border-stone-200 bg-white">
            <div class="mx-auto max-w-7xl px-4 py-10 text-sm text-stone-500 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="font-medium text-stone-700">{{ $page.props.name }}</p>
                    <p>Secure checkout with Stripe. Nigerian staples delivered to your door.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
