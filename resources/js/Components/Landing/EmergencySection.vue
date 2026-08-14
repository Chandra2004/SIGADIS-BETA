<script setup>
import { useMascot } from '@/Composables/useMascot';
import { emergencyFlow, emergencyInfoCards } from '@/Data/landingContent';

const { src, alt } = useMascot();

const flowIcons = [
    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4m0 4h.01"/></svg>`,
    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>`,
    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.92 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.82 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 17z"/></svg>`,
    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
];
</script>

<template>
    <section class="relative overflow-hidden bg-brand-navy-900 py-24">
        <!-- Background effects -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-32 -right-32 h-80 w-80 rounded-full bg-emergency-alert/10 blur-3xl" />
            <div class="absolute -bottom-32 -left-32 h-80 w-80 rounded-full bg-brand-pink-500/10 blur-3xl" />
            <!-- Mesh grid -->
            <div class="absolute inset-0 opacity-5"
                style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 48px 48px;" />
        </div>

        <div class="relative mx-auto max-w-7xl px-6 lg:px-8">
            <div class="grid items-center gap-16 lg:grid-cols-[0.85fr_1.15fr]">

                <!-- Left: Heading + Mascot -->
                <div class="text-center lg:text-left">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3.5 py-1 text-xs font-bold tracking-[0.15em] uppercase text-brand-pink-200 backdrop-blur-sm">
                        <span class="h-1.5 w-1.5 rounded-full bg-emergency-alert-pulse animate-pulse" />
                        Siaga Darurat
                    </span>

                    <h2 class="mt-5 text-3xl font-black tracking-tight text-white sm:text-4xl lg:text-5xl">
                        Dalam Keadaan Darurat, Satu Sentuhan Bisa Menjadi Awal Pertolongan
                    </h2>

                    <p class="mt-5 text-brand-navy-100">
                        Tombol darurat SIGADIS langsung mengirimkan lokasi dan informasi kondisi ibu kepada bidan pendamping.
                    </p>

                    <!-- Mascot siagaPenuh â€” large with pulse glow -->
                    <div class="relative mx-auto mt-10 flex h-52 w-52 items-center justify-center lg:mx-0">
                        <!-- Pulsing rings -->
                        <div class="absolute inset-0 animate-ping rounded-full bg-emergency-alert/10 [animation-duration:2.5s]" />
                        <div class="absolute inset-4 animate-ping rounded-full bg-emergency-alert/15 [animation-duration:2s] [animation-delay:0.3s]" />
                        <!-- Glow bg -->
                        <div class="absolute inset-8 rounded-full bg-emergency-alert/20 blur-xl" />
                        <img
                            :src="src('siagaPenuh')"
                            :alt="alt('siagaPenuh')"
                            class="relative z-10 w-44 drop-shadow-2xl"
                        />
                    </div>
                </div>

                <!-- Right: Flow + Info cards -->
                <div class="rounded-4xl border border-white/10 bg-white/5 p-8 backdrop-blur-sm">
                    <!-- Flow steps -->
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-pink-200 mb-6">Alur Bantuan Darurat</p>

                    <div class="space-y-3">
                        <div
                            v-for="(step, index) in emergencyFlow"
                            :key="step.label"
                            :class="[
                                'flex items-center gap-4 rounded-2xl border p-4 transition-all',
                                step.active
                                    ? 'border-emergency-alert/40 bg-emergency-alert/15 shadow-lg shadow-emergency-alert/10'
                                    : 'border-white/8 bg-white/5',
                            ]"
                        >
                            <div
                                :class="[
                                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl [&_svg]:h-5 [&_svg]:w-5',
                                    step.active ? 'bg-emergency-alert text-white' : 'bg-white/10 text-white/70',
                                ]"
                                v-html="flowIcons[index]"
                            />
                            <div class="flex-1">
                                <p :class="['text-sm font-bold', step.active ? 'text-white' : 'text-white/70']">{{ step.label }}</p>
                                <p v-if="step.active" class="text-xs text-emergency-alert-pulse mt-0.5">Dimulai</p>
                            </div>
                            <div v-if="step.active" class="h-2 w-2 rounded-full bg-emergency-alert animate-pulse" />
                            <svg v-else class="h-4 w-4 text-white/20 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                        </div>
                    </div>

                    <!-- Info cards -->
                    <div class="mt-7 border-t border-white/10 pt-6">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-pink-200 mb-4">Informasi yang dikirim otomatis</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div
                                v-for="card in emergencyInfoCards"
                                :key="card.label"
                                class="rounded-2xl border border-white/10 bg-white/5 p-4 transition-colors hover:bg-white/10"
                            >
                                <p class="text-[10px] uppercase tracking-[0.2em] text-brand-navy-100">{{ card.label }}</p>
                                <p class="mt-1.5 font-bold text-white">{{ card.value }}</p>
                            </div>
                        </div>
                        <p class="mt-5 text-xs leading-relaxed text-brand-navy-100/70">
                            SIGADIS mempercepat penyampaian informasi ke bidan/kader â€” bukan jaminan waktu tiba bantuan tertentu.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@media (prefers-reduced-motion: reduce) {
    .animate-ping,
    .animate-pulse {
        animation: none;
    }
}
</style>

