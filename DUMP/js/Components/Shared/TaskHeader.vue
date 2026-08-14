<script setup>
defineProps({
    title: { type: String, required: true },
    backHref: { type: String, default: null },
    closeHref: { type: String, default: null },
    showClose: { type: Boolean, default: true },
});

const emit = defineEmits(['back', 'close']);
</script>

<template>
    <header class="flex items-center justify-between bg-brand-pink-50 px-4 py-3">
        <component
            :is="backHref ? 'a' : 'button'"
            :href="backHref ?? undefined"
            :type="backHref ? undefined : 'button'"
            aria-label="Kembali"
            class="btn btn-circle btn-sm border-none bg-white text-brand-navy-900 shadow-sm"
            @click="!backHref && emit('back')"
        >
            &larr;
        </component>

        <h1 class="text-base font-bold text-brand-navy-900">{{ title }}</h1>

        <component
            :is="closeHref ? 'a' : 'button'"
            v-if="showClose"
            :href="closeHref ?? undefined"
            :type="closeHref ? undefined : 'button'"
            aria-label="Tutup"
            class="btn btn-circle btn-sm border-none bg-white text-brand-navy-900 shadow-sm"
            @click="!closeHref && emit('close')"
        >
            &#10005;
        </component>
        <span v-else class="w-8"></span>
    </header>
</template>
