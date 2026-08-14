<script setup>
defineProps({
    eyebrow: { type: String, required: true },
    title: { type: String, required: true },
    align: { type: String, default: 'center' }, // 'center' | 'left'
    tone: { type: String, default: 'light' }, // 'light' | 'dark' — dark = di atas background navy
});
</script>

<template>
    <div
        :class="[
            align === 'center' ? 'mx-auto max-w-2xl text-center' : 'max-w-xl text-center lg:text-left',
        ]"
    >
        <p
            :class="[
                'text-sm font-bold uppercase tracking-[0.22em]',
                tone === 'dark' ? 'text-brand-pink-500' : 'text-brand-navy-900',
            ]"
        >
            {{ eyebrow }}
        </p>
        <h2
            :class="[
                'mt-4 text-3xl font-black tracking-tight sm:text-4xl',
                tone === 'dark' ? 'text-white' : 'text-ink-900',
            ]"
        >
            {{ title }}
        </h2>
        <p v-if="$slots.default" :class="['mt-4 text-lg leading-8', tone === 'dark' ? 'text-brand-navy-100' : 'text-brand-navy-700']">
            <slot />
        </p>
    </div>
</template>
