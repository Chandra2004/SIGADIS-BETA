<script setup>
import { problems } from '@/Data/landingContent';
import SectionHeading from './SectionHeading.vue';

const problemIcons = [
    // Clock/urgency
    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 3.5"/></svg>`,
    // Chart/monitor
    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/><path d="m7 10 3 3 2-2 3 3"/></svg>`,
    // Chat/communication
    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>`,
    // Map pin
    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>`,
];

const problemColors = [
    { bg: 'bg-risk-high-bg', text: 'text-risk-high', border: 'border-risk-high/20' },
    { bg: 'bg-brand-navy-100', text: 'text-brand-navy-900', border: 'border-brand-navy-100' },
    { bg: 'bg-accent-gold-light', text: 'text-accent-amber', border: 'border-accent-amber/20' },
    { bg: 'bg-brand-pink-200', text: 'text-brand-navy-900', border: 'border-brand-pink-200' },
];
</script>

<template>
    <section id="tentang" class="relative scroll-mt-24 overflow-hidden bg-white py-24">
        <!-- Subtle pattern -->
        <div class="pointer-events-none absolute inset-0 opacity-30"
            style="background-image: radial-gradient(circle at 1px 1px, #dce6ee 1px, transparent 0); background-size: 32px 32px;" />

        <div class="relative mx-auto max-w-7xl px-6 lg:px-8">
            <SectionHeading eyebrow="Kenapa SIGADIS?" title="Setiap Menit Berharga Saat Darurat" />

            <!-- Big stat highlight -->
            <div class="mx-auto mt-10 max-w-2xl text-center">
                <p class="text-base leading-relaxed text-neutral-500">
                    Di Indonesia, kematian ibu masih menjadi tantangan besar. SIGADIS hadir untuk menutup celah komunikasi dan mempercepat respons saat keadaan kritis.
                </p>
            </div>

            <!-- Stat banner -->
            <div class="mt-10 mx-auto max-w-4xl rounded-3xl bg-linear-to-br from-brand-navy-900 to-brand-navy-700 p-1 shadow-xl shadow-brand-navy-900/20">
                <div class="rounded-[1.4rem] bg-brand-navy-900/80 px-8 py-6 backdrop-blur-sm text-center">
                    <p class="text-5xl font-black text-white sm:text-6xl">
                        72%
                    </p>
                    <p class="mt-2 text-base text-brand-navy-100">kematian ibu dapat dicegah dengan respons tepat waktu dan komunikasi yang terkoordinasi</p>
                </div>
            </div>

            <!-- Problem cards -->
            <div class="mt-14 grid gap-5 md:grid-cols-2">
                <article
                    v-for="(problem, i) in problems"
                    :key="problem.title"
                    :class="['group rounded-3xl border bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg', problemColors[i]?.border ?? 'border-neutral-100']"
                >
                    <div :class="['mb-5 flex h-12 w-12 items-center justify-center rounded-2xl [&_svg]:h-6 [&_svg]:w-6 transition-transform duration-300 group-hover:scale-110', problemColors[i]?.bg ?? 'bg-brand-pink-50', problemColors[i]?.text ?? 'text-brand-navy-900']" v-html="problemIcons[i]" />
                    <h3 class="text-lg font-black text-brand-navy-900">{{ problem.title }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-neutral-500">{{ problem.description }}</p>
                </article>
            </div>
        </div>
    </section>
</template>

