<script setup>
import { ref } from 'vue';
import { faqs } from '@/Data/landingContent';
import SectionHeading from './SectionHeading.vue';

const openIndex = ref(null);

const toggle = (i) => {
    openIndex.value = openIndex.value === i ? null : i;
};
</script>

<template>
    <section id="faq" class="relative scroll-mt-24 overflow-hidden bg-brand-cream-100/30 py-24">
        <div class="pointer-events-none absolute top-0 right-0 h-72 w-72 rounded-full bg-brand-pink-200/30 blur-3xl" />
        <div class="pointer-events-none absolute bottom-0 left-0 h-48 w-48 rounded-full bg-brand-navy-100/40 blur-3xl" />

        <div class="relative mx-auto max-w-3xl px-6 lg:px-8">
            <SectionHeading eyebrow="FAQ" title="Pertanyaan yang Sering Diajukan">
                Semua yang perlu Anda ketahui tentang SIGADIS — singkat dan jelas.
            </SectionHeading>

            <div class="mt-12 space-y-3">
                <div
                    v-for="(item, i) in faqs"
                    :key="item.question"
                    class="group overflow-hidden rounded-2xl border border-neutral-200/80 bg-white shadow-sm transition-all duration-200"
                    :class="openIndex === i ? 'border-brand-navy-100 shadow-md' : 'hover:border-brand-pink-200'"
                >
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left transition-colors"
                        @click="toggle(i)"
                        :aria-expanded="openIndex === i"
                    >
                        <span class="text-base font-bold text-brand-navy-900 leading-snug">{{ item.question }}</span>
                        <span
                            :class="[
                                'flex h-8 w-8 shrink-0 items-center justify-center rounded-full transition-all duration-300',
                                openIndex === i
                                    ? 'bg-brand-navy-900 text-white rotate-45'
                                    : 'bg-brand-navy-100 text-brand-navy-900 group-hover:bg-brand-pink-200'
                            ]"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                            </svg>
                        </span>
                    </button>

                    <transition
                        enter-active-class="transition-all duration-300 ease-out overflow-hidden"
                        enter-from-class="max-h-0 opacity-0"
                        enter-to-class="max-h-96 opacity-100"
                        leave-active-class="transition-all duration-200 ease-in overflow-hidden"
                        leave-from-class="max-h-96 opacity-100"
                        leave-to-class="max-h-0 opacity-0"
                    >
                        <div v-if="openIndex === i" class="px-6 pb-5">
                            <div class="border-t border-neutral-100 pt-4">
                                <p class="text-sm leading-relaxed text-neutral-500">{{ item.answer }}</p>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </div>
    </section>
</template>
