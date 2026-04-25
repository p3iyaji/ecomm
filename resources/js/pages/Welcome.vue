<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Welcome — Boachat" />
    <div class="flex min-h-screen flex-col bg-stone-50 text-stone-900">
        <header class="border-b border-stone-200 bg-white">
            <div class="mx-auto flex max-w-4xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                <span class="text-lg font-bold tracking-tight text-emerald-800">Boa<span class="text-emerald-600">chat</span></span>
                <nav class="flex items-center gap-3 text-sm font-medium">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="rounded-md px-3 py-1.5 text-stone-700 hover:bg-stone-100"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link :href="login()" class="text-stone-600 hover:text-emerald-800">Log in</Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="rounded-md bg-emerald-700 px-3 py-1.5 text-white shadow hover:bg-emerald-800"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-4xl flex-1 flex-col items-center justify-center gap-8 px-4 py-12 sm:px-6">
            <div class="text-center">
                <h1 class="text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl">Nigerian groceries, one cart away</h1>
                <p class="mt-3 max-w-lg text-balance text-stone-600">
                    Shop garri, ofada rice, palm oil, egusi, plantain, yam, and more. Create an account to track orders, or
                    browse the store as a guest.
                </p>
            </div>
            <div class="flex w-full max-w-md flex-col gap-3 sm:flex-row sm:justify-center">
                <Link
                    href="/"
                    class="inline-flex items-center justify-center rounded-lg border border-stone-300 bg-white px-5 py-2.5 text-sm font-semibold text-stone-800 shadow-sm hover:border-emerald-400 hover:text-emerald-900"
                >
                    Go to store
                </Link>
                <Link
                    v-if="!$page.props.auth.user && canRegister"
                    :href="register()"
                    class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-800"
                >
                    Create account
                </Link>
            </div>
        </main>
    </div>
</template>
