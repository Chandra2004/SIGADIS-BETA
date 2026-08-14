<script setup>
import { useMascot } from '@/Composables/useMascot';
import { steps } from '@/Data/landingContent';
import SectionHeading from './SectionHeading.vue';

const { src, alt } = useMascot();
</script>

<template>
    <section id="cara-kerja" class="mx-auto max-w-7xl scroll-mt-24 px-4 py-20 sm:px-6 lg:px-8">
        <SectionHeading eyebrow="Cara kerja" title="Bagaimana SIGADIS Bekerja?" />

        <div class="mt-12 grid gap-6 lg:grid-cols-4">
            <article v-for="step in steps" :key="step.number" class="relative overflow-hidden rounded-3xl border border-brand-pink-500/40 bg-white p-6 pt-20 shadow-sm">
                <img :src="src(step.mascotKey)" :alt="alt(step.mascotKey)" class="absolute -top-1 right-2 w-24" />
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-navy-100 text-lg font-black text-brand-navy-900">
                    {{ step.number }}
                </div>
                <h3 class="text-xl font-bold text-ink-900">{{ step.title }}</h3>
                <p class="mt-3 text-base leading-7 text-brand-navy-700">{{ step.description }}</p>
            </article>
        </div>
    </section>
</template>
