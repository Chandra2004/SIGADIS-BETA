<script setup>
defineProps({
    title: { type: String, default: 'SIGADIS' },
});

const emit = defineEmits(['menu']);
</script>

<template>
    <header class="flex items-center justify-between border-b border-brand-navy-100 bg-brand-pink-50 px-4 py-3">
        <div class="flex items-center gap-3">
            <button type="button" aria-label="Menu" class="cursor-pointer text-brand-navy-900" @click="emit('menu')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <span class="text-lg font-bold text-brand-navy-900">{{ title }}</span>
        </div>
        <div class="flex items-center gap-2">
            <slot name="actions" />
        </div>
    </header>
</template>
