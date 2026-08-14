<script setup>
import { useMascot } from '@/Composables/useMascot';
import { steps } from '@/Data/landingContent';
import SectionHeading from './SectionHeading.vue';

const { src, alt } = useMascot();
</script>

<template>
    <section id="cara-kerja" class="relative scroll-mt-24 overflow-hidden bg-white py-24">
        <!-- Subtle dot grid -->
        <div
            class="pointer-events-none absolute inset-0 opacity-20"
            style="background-image: radial-gradient(circle at 1px 1px, #f3aec0 1px, transparent 0); background-size: 40px 40px;"
        />

        <div class="relative mx-auto max-w-7xl px-6 lg:px-8">
            <SectionHeading eyebrow="Cara kerja" title="Bagaimana SIGADIS Bekerja?">
                Empat langkah sederhana menuju pendampingan kesehatan yang lebih terhubung dan responsif.
            </SectionHeading>

            <!-- Steps grid with connector line on desktop -->
            <div class="relative mt-16">
                <!-- Connector line (desktop only) -->
                <div class="absolute top-16 left-0 right-0 hidden xl:block">
                    <div class="mx-auto max-w-5xl">
                        <div class="h-px bg-linear-to-r from-transparent via-brand-pink-200 to-transparent mx-[12.5%]" />
                    </div>
                </div>

                <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-4">
                    <article
                        v-for="step in steps"
                        :key="step.number"
                        class="group relative flex flex-col rounded-3xl bg-white border border-neutral-100 shadow-sm transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:border-brand-pink-200/60"
                    >
                        <!-- Mascot container -->
                        <div class="relative h-40 overflow-hidden rounded-t-3xl bg-linear-to-b from-brand-pink-50 to-brand-pink-50/30 flex items-end justify-center pt-4">
                            <!-- Background glow -->
                            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 h-24 w-24 rounded-full bg-brand-pink-200/40 blur-xl" />
                            <img
                                :src="src(step.mascotKey)"
                                :alt="alt(step.mascotKey)"
                                class="relative z-10 h-32 w-32 object-contain drop-shadow-lg transition-all duration-500 group-hover:-translate-y-2 group-hover:scale-110"
                            />
                        </div>

                        <!-- Content -->
                        <div class="flex flex-1 flex-col p-6">
                            <!-- Step number badge -->
                            <div class="mb-4 flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-navy-900 text-sm font-black text-white shadow-sm">
                                    {{ step.number }}
                                </div>
                                <div class="h-px flex-1 bg-linear-to-r from-brand-navy-100 to-transparent" />
                            </div>
                            <h3 class="text-lg font-black text-brand-navy-900">{{ step.title }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-neutral-500 flex-1">{{ step.description }}</p>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
</template>

