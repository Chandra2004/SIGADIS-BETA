<script setup>
import { features, featureIcons } from '@/Data/landingContent';
import SectionHeading from './SectionHeading.vue';

// Background & accent colors per feature
const featureStyles = {
    '01': { bg: 'bg-emergency-pending-bg', icon: 'bg-emergency-alert text-white', border: 'border-emergency-alert/20', badge: 'bg-emergency-alert text-white', featured: true },
    '02': { bg: 'bg-brand-pink-50', icon: 'bg-brand-pink-200 text-brand-navy-900', border: 'border-brand-pink-200' },
    '03': { bg: 'bg-brand-navy-100/40', icon: 'bg-brand-navy-900 text-white', border: 'border-brand-navy-100' },
    '04': { bg: 'bg-brand-cream-100', icon: 'bg-accent-gold-light text-accent-amber', border: 'border-accent-amber/20' },
    '05': { bg: 'bg-risk-low-bg/60', icon: 'bg-risk-low-bg text-risk-low', border: 'border-risk-low/20' },
    '06': { bg: 'bg-white', icon: 'bg-brand-pink-50 text-brand-navy-900', border: 'border-neutral-100' },
};
</script>

<template>
    <section id="fitur" class="relative scroll-mt-24 overflow-hidden py-24">
        <!-- Subtle background gradient -->
        <div class="pointer-events-none absolute inset-0 bg-linear-to-b from-brand-pink-50 via-white to-brand-pink-50/30" />

        <div class="relative mx-auto max-w-7xl px-6 lg:px-8">
            <SectionHeading eyebrow="Fitur utama" title="Membantu Ibu Tetap Terpantau dan Terhubung">
                Setiap fitur dirancang khusus untuk kebutuhan ibu hamil â€” sederhana, mudah digunakan, dan responsif saat dibutuhkan.
            </SectionHeading>

            <div class="mt-14 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <!-- Featured card: Emergency Button (spans 2 cols on xl) -->
                <article
                    v-for="feature in features"
                    :key="feature.title"
                    :class="[
                        'group relative rounded-3xl border p-7 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl',
                        featureStyles[feature.number]?.bg ?? 'bg-white',
                        featureStyles[feature.number]?.border ?? 'border-neutral-100',
                        feature.number === '01' ? 'md:col-span-2 xl:col-span-1' : '',
                    ]"
                >
                    <!-- Featured badge -->
                    <span
                        v-if="featureStyles[feature.number]?.featured"
                        class="absolute right-5 top-5 rounded-full bg-emergency-alert px-2.5 py-0.5 text-[10px] font-black tracking-wider text-white uppercase"
                    >
                        Kritis
                    </span>

                    <div class="mb-6 flex items-start justify-between">
                        <div
                            :class="[
                                'flex h-12 w-12 items-center justify-center rounded-2xl [&_svg]:h-6 [&_svg]:w-6 shadow-sm transition-transform duration-300 group-hover:scale-110',
                                featureStyles[feature.number]?.icon ?? 'bg-brand-pink-50 text-brand-navy-900',
                            ]"
                            v-html="featureIcons[feature.number]"
                        />
                        <span class="text-xs font-black tracking-[0.2em] text-brand-navy-900/40">{{ feature.number }}</span>
                    </div>

                    <h3 class="text-xl font-black text-brand-navy-900">{{ feature.title }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-neutral-500">{{ feature.description }}</p>

                    <!-- Hover arrow -->
                    <div class="mt-5 flex items-center gap-1 text-xs font-bold text-brand-navy-700 opacity-0 transition-opacity group-hover:opacity-100">
                        Pelajari lebih
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>

