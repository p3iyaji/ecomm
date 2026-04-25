<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-emerald-700 text-white dark:bg-emerald-600"
    >
        <AppLogoIcon class="size-5 fill-current" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold text-emerald-950 dark:text-emerald-50">Boachat</span>
        <span class="truncate text-[10px] leading-tight text-muted-foreground">Nigerian groceries</span>
    </div>
</template>
