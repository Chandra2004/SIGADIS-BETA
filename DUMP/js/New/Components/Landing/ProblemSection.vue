<script setup>
import { problems } from '@/Data/landingContent';
import SectionHeading from './SectionHeading.vue';
</script>

<template>
    <section id="tentang" class="mx-auto max-w-7xl scroll-mt-24 px-4 py-20 sm:px-6 lg:px-8">
        <SectionHeading eyebrow="Kenapa SIGADIS?" title="Ketika Kondisi Darurat Terjadi, Setiap Menit Berarti" />

        <div class="mt-12 grid gap-6 md:grid-cols-2">
            <article v-for="problem in problems" :key="problem.title" class="rounded-3xl border border-brand-pink-500/40 bg-white p-6 shadow-sm">
                <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-2xl bg-risk-high-bg text-risk-high">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 3" /></svg>
                </div>
                <h3 class="text-xl font-bold text-ink-900">{{ problem.title }}</h3>
                <p class="mt-3 text-base leading-7 text-brand-navy-700">{{ problem.description }}</p>
            </article>
        </div>
    </section>
</template>
