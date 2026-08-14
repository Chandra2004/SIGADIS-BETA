<script setup>
import { Link } from '@inertiajs/vue3';
import { useMascot } from '@/Composables/useMascot';
import { APK_URL } from '@/Data/landingContent';
import PhoneMockup from './PhoneMockup.vue';

const { src, alt } = useMascot();

const stats = [
    { value: '500+', label: 'Ibu Terdaftar' },
    { value: '50+', label: 'Bidan Aktif' },
    { value: '24/7', label: 'Siap Merespons' },
];
</script>

<template>
    <section id="beranda" class="relative scroll-mt-24 overflow-hidden">
        <!-- Background gradient orbs -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 h-150 w-150 rounded-full bg-brand-pink-200/40 blur-[100px]" />
            <div class="absolute -bottom-20 -left-20 h-100 w-100 rounded-full bg-brand-navy-100/60 blur-[80px]" />
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-75 w-75 rounded-full bg-brand-cream-100/80 blur-[60px]" />
        </div>

        <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-6 py-20 sm:px-8 lg:grid-cols-2 lg:py-32">
            <!-- Left: Copy -->
            <div class="relative z-10 flex flex-col">
                <!-- Eyebrow badge -->
                <div class="inline-flex items-center self-start gap-2 rounded-full border border-brand-pink-200 bg-white px-4 py-2 shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-brand-pink-500 animate-pulse" />
                    <span class="text-xs font-bold tracking-[0.15em] text-brand-navy-900">SIGADIS â€” Sistem Gawat Darurat Ibu-Selamat</span>
                </div>

                <!-- Heading -->
                <h1 class="mt-7 text-4xl font-black leading-[1.1] tracking-tight text-brand-navy-900 sm:text-5xl lg:text-6xl">
                    Kesehatan Ibu <br />
                    <span class="relative inline-block">
                        Lebih Terhubung,
                        <span class="absolute bottom-0.5 left-0 h-2 w-full bg-brand-pink-200/70 -z-10 rounded" />
                    </span>
                    <br />Bantuan Lebih Cepat.
                </h1>

                <p class="mt-6 max-w-lg text-lg leading-relaxed text-neutral-500">
                    Sistem pendampingan kesehatan yang menghubungkan ibu dengan bidan dan kader, serta mempercepat respons dalam kondisi darurat â€” di mana pun ibu berada.
                </p>

                <!-- CTA Buttons -->
                <div class="mt-9 flex flex-col gap-4 sm:flex-row">
                    <Link href="/register" class="group inline-flex items-center justify-center gap-2 rounded-full bg-brand-navy-900 px-8 py-4 text-base font-bold text-white shadow-lg shadow-brand-navy-900/20 transition-all duration-300 hover:bg-brand-navy-700 hover:shadow-xl hover:-translate-y-0.5">
                        Daftar Sekarang
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                    </Link>
                    <a :href="APK_URL" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-full border-2 border-brand-navy-100 bg-white px-8 py-4 text-base font-bold text-brand-navy-900 transition-all duration-200 hover:border-brand-pink-500 hover:bg-brand-pink-50 hover:-translate-y-0.5">
                        <svg class="h-5 w-5 text-brand-navy-700" viewBox="0 0 24 24" fill="currentColor"><path d="M17.523 15.341 13 12.839V7a1 1 0 1 0-2 0v5.839L6.477 15.34a1 1 0 1 0 1 1.732L12 14.575l4.523 2.497a1 1 0 0 0 1-1.731Z"/><path fill-rule="evenodd" d="M12 1C5.925 1 1 5.925 1 12s4.925 11 11 11 11-4.925 11-11S18.075 1 12 1ZM3 12a9 9 0 1 1 18 0 9 9 0 0 1-18 0Z" clip-rule="evenodd"/></svg>
                        Download APK
                    </a>
                </div>

                <!-- Stats strip -->
                <div class="mt-10 flex flex-wrap gap-x-8 gap-y-4">
                    <div v-for="stat in stats" :key="stat.label" class="flex flex-col">
                        <span class="text-2xl font-black text-brand-navy-900">{{ stat.value }}</span>
                        <span class="text-sm text-neutral-500">{{ stat.label }}</span>
                    </div>
                </div>
            </div>

            <!-- Right: Phone + Mascot -->
            <div class="relative flex items-end justify-center lg:justify-end">
                <!-- Decorative ring -->
                <div class="absolute right-8 bottom-8 h-87\.5 w-87\.5 rounded-full border border-brand-pink-200/50 lg:h-105 lg:w-105" />
                <div class="absolute right-16 bottom-16 h-62\.5 w-62\.5 rounded-full border border-brand-navy-100/60 lg:h-77\.5 lg:w-77\.5" />

                <!-- Mascot â€” large, positioned prominently -->
                <img
                    :src="src('memperkenalkanDiri')"
                    :alt="alt('memperkenalkanDiri')"
                    class="absolute -left-6 bottom-12 z-20 w-40 drop-shadow-2xl transition-all duration-500 hover:scale-105 hover:-translate-y-2 sm:w-48 lg:-left-10 lg:w-56"
                />

                <PhoneMockup class="relative z-10" />
            </div>
        </div>
    </section>
</template>

