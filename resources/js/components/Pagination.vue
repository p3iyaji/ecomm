<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: { url: string | null; label: string; active: boolean }[];
}>();
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center justify-center gap-1" aria-label="Pagination">
        <template v-for="link in links" :key="link.label">
            <Link
                v-if="link.url"
                :href="link.url"
                class="rounded-md px-3 py-2 text-sm font-medium transition"
                :class="
                    link.active
                        ? 'bg-amber-600 text-white'
                        : 'bg-white text-stone-700 ring-1 ring-stone-200 hover:bg-stone-50'
                "
                v-html="link.label"
            />
            <span
                v-else
                class="rounded-md px-3 py-2 text-sm text-stone-400"
                v-html="link.label"
            />
        </template>
    </nav>
</template>
