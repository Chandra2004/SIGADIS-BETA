<script setup>
import { faqs } from '@/Data/landingContent';
import SectionHeading from './SectionHeading.vue';
</script>

<template>
    <section id="faq" class="mx-auto max-w-5xl scroll-mt-24 px-4 py-20 sm:px-6 lg:px-8">
        <SectionHeading eyebrow="FAQ" title="Pertanyaan yang Sering Diajukan" />

        <div class="mt-12 space-y-4">
            <div v-for="item in faqs" :key="item.question" class="collapse collapse-arrow rounded-2xl border border-brand-pink-500/40 bg-white shadow-sm">
                <input type="checkbox" />
                <div class="collapse-title text-lg font-semibold text-ink-900">{{ item.question }}</div>
                <div class="collapse-content text-base leading-7 text-brand-navy-700">
                    <p>{{ item.answer }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
