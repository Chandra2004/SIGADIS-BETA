<script setup>
import { advantages } from '@/Data/landingContent';
import SectionHeading from './SectionHeading.vue';
</script>

<template>
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <SectionHeading eyebrow="Keunggulan" title="Solusi yang Lebih Terintegrasi dan Responsif" />

        <div class="mt-12 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            <article v-for="advantage in advantages" :key="advantage.title" class="rounded-3xl border border-brand-pink-500/40 bg-white p-6 shadow-sm">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-risk-low-bg text-2xl text-risk-low">✓</div>
                <h3 class="text-xl font-bold text-ink-900">{{ advantage.title }}</h3>
                <p class="mt-3 text-base leading-7 text-brand-navy-700">{{ advantage.description }}</p>
            </article>
        </div>
    </section>
</template>
