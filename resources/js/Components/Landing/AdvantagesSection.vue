<script setup>
import { advantages } from '@/Data/landingContent';
import SectionHeading from './SectionHeading.vue';

const advantageIcons = [
    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 5v6c0 5 3.4 8.7 8 11 4.6-2.3 8-6 8-11V5l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>`,
    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>`,
    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>`,
    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>`,
];

const advantageColors = [
    { icon: 'bg-risk-low-bg text-risk-low', number: '01' },
    { icon: 'bg-brand-pink-200 text-brand-navy-900', number: '02' },
    { icon: 'bg-brand-navy-100 text-brand-navy-900', number: '03' },
    { icon: 'bg-accent-gold-light text-accent-amber', number: '04' },
];

const stats = [
    { value: '< 60s', label: 'Waktu kirim alert' },
    { value: '99%', label: 'Uptime sistem' },
    { value: '4.8â˜…', label: 'Rating pengguna' },
];
</script>

<template>
    <section class="relative overflow-hidden bg-white py-24">
        <div class="pointer-events-none absolute inset-0 bg-linear-to-br from-brand-pink-50/40 via-white to-brand-navy-100/20" />

        <div class="relative mx-auto max-w-7xl px-6 lg:px-8">
            <SectionHeading eyebrow="Keunggulan" title="Lebih Terintegrasi, Lebih Responsif">
                Dibangun dengan teknologi modern dan fokus pada kemudahan â€” karena saat darurat, setiap detik berarti.
            </SectionHeading>

            <!-- Stats row -->
            <div class="mx-auto mt-12 max-w-3xl">
                <div class="grid grid-cols-3 gap-4 rounded-3xl bg-brand-navy-900 p-6 shadow-xl shadow-brand-navy-900/20">
                    <div v-for="stat in stats" :key="stat.label" class="text-center">
                        <p class="text-2xl font-black text-white sm:text-3xl">{{ stat.value }}</p>
                        <p class="mt-1 text-xs text-brand-navy-100">{{ stat.label }}</p>
                    </div>
                </div>
            </div>

            <!-- Advantage cards -->
            <div class="mt-12 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <article
                    v-for="(advantage, i) in advantages"
                    :key="advantage.title"
                    class="group relative rounded-3xl border border-neutral-100 bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-brand-pink-200/60"
                >
                    <!-- Number watermark -->
                    <span class="pointer-events-none absolute right-5 top-5 text-6xl font-black text-neutral-100 select-none">{{ advantageColors[i]?.number }}</span>

                    <div
                        :class="['mb-5 flex h-12 w-12 items-center justify-center rounded-2xl [&_svg]:h-6 [&_svg]:w-6 shadow-sm transition-transform duration-300 group-hover:scale-110', advantageColors[i]?.icon ?? 'bg-brand-pink-50 text-brand-navy-900']"
                        v-html="advantageIcons[i]"
                    />
                    <h3 class="text-lg font-black text-brand-navy-900">{{ advantage.title }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-neutral-500">{{ advantage.description }}</p>
                </article>
            </div>
        </div>
    </section>
</template>

