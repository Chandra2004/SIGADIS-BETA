<script setup>
import { useMascot } from '@/Composables/useMascot';

const { src, alt } = useMascot();

const pillars = [
    { icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 5v6c0 5 3.4 8.7 8 11 4.6-2.3 8-6 8-11V5l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>`, label: 'Pencatatan mandiri' },
    { icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>`, label: 'Komunikasi bidan' },
    { icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 5-2 6-2 8h16c0-2-2-3-2-8Z" stroke-linecap="round"/><path d="M10 21a2 2 0 0 0 4 0" stroke-linecap="round"/></svg>`, label: 'Respons darurat' },
];
</script>

<template>
    <section class="relative overflow-hidden bg-brand-cream-100/50 py-24">
        <!-- Decorative -->
        <div class="pointer-events-none absolute top-0 right-0 h-64 w-64 rounded-full bg-brand-pink-200/30 blur-3xl" />
        <div class="pointer-events-none absolute bottom-0 left-0 h-48 w-48 rounded-full bg-brand-navy-100/50 blur-3xl" />

        <div class="relative mx-auto grid max-w-7xl items-center gap-16 px-6 lg:grid-cols-[1.15fr_0.85fr] lg:px-8">
            <!-- Left: Text content -->
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-navy-100 px-3.5 py-1 text-xs font-bold tracking-[0.15em] uppercase text-brand-navy-900">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand-navy-700" />
                    Solusi
                </span>
                <h2 class="mt-5 text-3xl font-black tracking-tight text-brand-navy-900 sm:text-4xl lg:text-5xl">
                    Menghubungkan Ibu <br class="hidden sm:block"/>dengan Bantuan yang Tepat
                </h2>
                <p class="mt-5 max-w-xl text-base leading-relaxed text-neutral-500">
                    SIGADIS mengintegrasikan pencatatan medis mandiri, komunikasi dengan tenaga kesehatan, dan mekanisme respons darurat dalam satu platform yang mudah digunakan.
                </p>

                <!-- Pillar pills -->
                <div class="mt-8 flex flex-wrap gap-3">
                    <div
                        v-for="pillar in pillars"
                        :key="pillar.label"
                        class="inline-flex items-center gap-2.5 rounded-full border border-brand-navy-100 bg-white px-4 py-2.5 shadow-sm"
                    >
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-navy-100 text-brand-navy-900 [&_svg]:h-3.5 [&_svg]:w-3.5" v-html="pillar.icon" />
                        <span class="text-sm font-semibold text-brand-navy-900">{{ pillar.label }}</span>
                    </div>
                </div>

                <!-- Flow: Ibu â†’ SIGADIS â†’ Bidan -->
                <div class="mt-10 flex flex-wrap items-center gap-3">
                    <div class="flex flex-col items-center gap-1.5">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-pink-200 font-black text-brand-navy-900">Ibu</div>
                        <span class="text-[10px] font-semibold text-neutral-500 uppercase tracking-wider">Pengguna</span>
                    </div>
                    <!-- Arrow -->
                    <div class="flex flex-col items-center gap-1">
                        <svg class="h-5 w-8 text-brand-pink-500" viewBox="0 0 32 20" fill="none"><path d="M0 10h28M22 4l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span class="text-[9px] text-neutral-400">mengirim</span>
                    </div>
                    <div class="flex flex-col items-center gap-1.5">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-navy-900 font-black text-white text-xs">SIG</div>
                        <span class="text-[10px] font-semibold text-neutral-500 uppercase tracking-wider">Platform</span>
                    </div>
                    <div class="flex flex-col items-center gap-1">
                        <svg class="h-5 w-8 text-risk-low" viewBox="0 0 32 20" fill="none"><path d="M0 10h28M22 4l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span class="text-[9px] text-neutral-400">meneruskan</span>
                    </div>
                    <div class="flex flex-col items-center gap-1.5">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-risk-low-bg font-black text-risk-low text-xs">Bidan</div>
                        <span class="text-[10px] font-semibold text-neutral-500 uppercase tracking-wider">Pendamping</span>
                    </div>
                </div>
            </div>

            <!-- Right: Mascot showcase -->
            <div class="relative flex justify-center">
                <div class="relative flex h-95 w-full max-w-sm items-end justify-center overflow-hidden rounded-[2.5rem] bg-linear-to-b from-brand-pink-50 to-brand-pink-200/40 shadow-xl shadow-brand-navy-900/10">
                    <!-- Background circles -->
                    <div class="absolute top-6 left-6 h-20 w-20 rounded-full bg-white/60 blur-xl" />
                    <div class="absolute bottom-6 right-6 h-24 w-24 rounded-full bg-brand-navy-100/60 blur-xl" />

                    <!-- Floating pill tag -->
                    <div class="absolute top-6 left-1/2 -translate-x-1/2 inline-flex items-center gap-2 rounded-full bg-white/90 backdrop-blur-sm px-4 py-2 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-risk-low animate-pulse" />
                        <span class="text-xs font-bold text-brand-navy-900">Mendampingi Anda</span>
                    </div>

                    <img
                        :src="src('menjelaskan')"
                        :alt="alt('menjelaskan')"
                        class="relative z-10 w-56 drop-shadow-2xl transition-transform duration-500 hover:scale-105"
                    />
                </div>
            </div>
        </div>
    </section>
</template>

