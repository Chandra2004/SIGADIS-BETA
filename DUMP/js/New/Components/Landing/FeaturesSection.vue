<script setup>
import { features, featureIcons } from '@/Data/landingContent';
import SectionHeading from './SectionHeading.vue';
</script>

<template>
    <section id="fitur" class="mx-auto max-w-7xl scroll-mt-24 px-4 py-20 sm:px-6 lg:px-8">
        <SectionHeading eyebrow="Fitur utama" title="Membantu Ibu Tetap Terpantau dan Terhubung" />

        <div class="mt-12 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <article
                v-for="feature in features"
                :key="feature.title"
                class="rounded-3xl border border-brand-pink-500/40 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
            >
                <div class="mb-5 flex items-center justify-between">
                    <span class="text-sm font-black tracking-[0.18em] text-brand-navy-900">{{ feature.number }}</span>
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-brand-pink-50 text-brand-navy-900 [&_svg]:h-5 [&_svg]:w-5"
                        v-html="featureIcons[feature.number]"
                    />
                </div>
                <h3 class="text-2xl font-bold text-ink-900">{{ feature.title }}</h3>
                <p class="mt-3 text-base leading-7 text-brand-navy-700">{{ feature.description }}</p>
            </article>
        </div>
    </section>
</template>
