<script setup>
defineProps({
    eyebrow: { type: String, required: true },
    title: { type: String, required: true },
    align: { type: String, default: 'center' },
    tone: { type: String, default: 'light' },
});
</script>

<template>
    <div :class="[align === 'center' ? 'mx-auto max-w-2xl text-center' : 'max-w-xl text-center lg:text-left']">
        <!-- Eyebrow pill badge -->
        <span
            :class="[
                'inline-flex items-center gap-1.5 rounded-full px-3.5 py-1 text-xs font-bold tracking-[0.15em] uppercase',
                tone === 'dark'
                    ? 'bg-white/10 text-brand-pink-200'
                    : 'bg-brand-navy-100 text-brand-navy-900',
            ]"
        >
            <span
                :class="[
                    'inline-block h-1.5 w-1.5 rounded-full',
                    tone === 'dark' ? 'bg-brand-pink-500' : 'bg-brand-navy-700',
                ]"
            />
            {{ eyebrow }}
        </span>

        <h2
            :class="[
                'mt-4 text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl',
                tone === 'dark' ? 'text-white' : 'text-brand-navy-900',
            ]"
        >
            {{ title }}
        </h2>

        <p
            v-if="$slots.default"
            :class="[
                'mt-5 text-base leading-relaxed',
                tone === 'dark' ? 'text-brand-navy-100' : 'text-neutral-500',
            ]"
        >
            <slot />
        </p>
    </div>
</template>
