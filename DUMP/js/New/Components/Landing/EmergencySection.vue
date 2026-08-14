<script setup>
import { useMascot } from '@/Composables/useMascot';
import { emergencyFlow, emergencyInfoCards } from '@/Data/landingContent';

const { src, alt } = useMascot();
</script>

<template>
    <section class="relative overflow-hidden bg-brand-navy-900 py-20">
        <div class="pointer-events-none absolute -right-16 -top-16 h-72 w-72 rounded-full bg-emergency/20 blur-3xl" />
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-[0.9fr_1.1fr]">
                <div class="text-center lg:text-left">
                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-brand-pink-500">Emergency</p>
                    <h2 class="mt-4 text-3xl font-black tracking-tight text-white sm:text-4xl">
                        Dalam Keadaan Darurat, Satu Sentuhan Bisa Menjadi Awal Pertolongan
                    </h2>
                    <div class="relative mx-auto mt-8 flex h-40 w-40 items-center justify-center lg:mx-0">
                        <span class="pulse-dot absolute inset-0" />
                        <img :src="src('siagaPenuh')" :alt="alt('siagaPenuh')" class="relative z-10 w-36 drop-shadow-2xl" />
                    </div>
                </div>

                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-8 backdrop-blur-sm">
                    <div class="grid gap-4 sm:grid-cols-5 sm:items-center">
                        <template v-for="(step, index) in emergencyFlow" :key="step.label">
                            <div class="flex items-center gap-4 sm:flex-col sm:text-center">
                                <div
                                    :class="[
                                        'flex h-16 w-16 items-center justify-center rounded-2xl text-xs font-black shadow-sm',
                                        step.active ? 'bg-emergency text-white' : 'bg-white/10 text-white',
                                    ]"
                                >
                                    {{ step.label }}
                                </div>
                                <div v-if="index < emergencyFlow.length - 1" class="hidden h-px w-10 bg-white/20 sm:block" />
                            </div>
                        </template>
                    </div>

                    <div class="mt-8 border-t border-white/10 pt-6">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-pink-500">Informasi yang dibawa laporan</p>
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div v-for="card in emergencyInfoCards" :key="card.label" class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-brand-navy-100">{{ card.label }}</p>
                                <p class="mt-1 font-semibold text-white">{{ card.value }}</p>
                            </div>
                        </div>
                        <p class="mt-5 text-sm leading-6 text-brand-navy-100">
                            SIGADIS mempercepat penyampaian informasi ke bidan/kader — bukan jaminan waktu tiba bantuan tertentu.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes pulse-ring {
    0% {
        transform: scale(0.95);
        opacity: 0.9;
    }
    70% {
        transform: scale(1.4);
        opacity: 0;
    }
    100% {
        transform: scale(1.4);
        opacity: 0;
    }
}
.pulse-dot::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 9999px;
    background: theme('colors.emergency.pulse');
    animation: pulse-ring 1.8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@media (prefers-reduced-motion: reduce) {
    .pulse-dot::before {
        animation: none;
    }
}
</style>
