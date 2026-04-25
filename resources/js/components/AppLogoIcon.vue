<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 32 32"
        fill="none"
        :class="className"
        v-bind="$attrs"
        aria-hidden="true"
    >
        <!-- Simple market bowl + leaf (Nigerian grocery mark) -->
        <path
            fill="currentColor"
            d="M6 14h20l-1.2 9.2A3 3 0 0 1 21.8 26H10.2a3 3 0 0 1-3-2.8L6 14Z"
            opacity=".9"
        />
        <path
            fill="currentColor"
            d="M8 12c0-3.3 2.7-7 8-7s8 3.7 8 7H8Z"
            opacity=".55"
        />
        <path
            fill="currentColor"
            d="M22 6.5c2.2 1.5 3.5 3.8 3.8 6.2l-2.1.3A6.3 6.3 0 0 0 22 6.5ZM10 6.5c-1.8 1.2-3 3-3.6 5.2l2.1.4A6.3 6.3 0 0 1 10 6.5Z"
            opacity=".35"
        />
    </svg>
</template>
