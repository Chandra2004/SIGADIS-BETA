<script setup>
import { ICONS } from '@/icons';

defineProps({
    name: { type: String, required: true },
    size: { type: String, default: 'h-5 w-5' },
    filled: { type: Boolean, default: false },
});
</script>

<template>
    <svg
        :class="size"
        viewBox="0 0 24 24"
        :fill="filled ? 'currentColor' : 'none'"
        :stroke="filled ? 'none' : 'currentColor'"
        stroke-width="1.5"
        aria-hidden="true"
    >
        <path stroke-linecap="round" stroke-linejoin="round" :d="ICONS[name]" />
    </svg>
</template>
