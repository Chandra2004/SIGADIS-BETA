<script setup>
import { Link } from '@inertiajs/vue3';
import { useMascot } from '@/Composables/useMascot';
import { APK_URL } from '@/Data/landingContent';

const { src, alt } = useMascot();

const testimonials = [
    { name: 'Ibu Sari', role: 'Ibu Hamil, Surabaya', initials: 'IS' },
    { name: 'Bidan Wati', role: 'Bidan Puskesmas', initials: 'BW' },
    { name: 'Ibu Rini', role: 'Ibu Hamil, Sidoarjo', initials: 'IR' },
];
</script>

<template>
    <section class="relative overflow-hidden px-6 py-24 lg:px-8">
        <!-- Background orbs -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-20 left-1/2 -translate-x-1/2 h-125 w-200 rounded-full bg-brand-navy-900/5 blur-3xl" />
        </div>

        <div class="relative mx-auto max-w-5xl">
            <!-- CTA Card -->
            <div class="relative overflow-hidden rounded-[2.5rem] bg-brand-navy-900 shadow-2xl shadow-brand-navy-900/30">
                <!-- Background decoration -->
                <div class="pointer-events-none absolute inset-0 overflow-hidden">
                    <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-brand-pink-500/10 blur-3xl" />
                    <div class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-brand-navy-700/50 blur-3xl" />
                    <div class="absolute inset-0 opacity-5"
                        style="background-image: linear-gradient(rgba(255,255,255,0.15) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.15) 1px, transparent 1px); background-size: 32px 32px;" />
                </div>

                <div class="relative flex flex-col items-center px-8 py-14 text-center sm:px-16">
                    <!-- Social proof avatars -->
                    <div class="flex -space-x-3 mb-6">
                        <div
                            v-for="t in testimonials"
                            :key="t.name"
                            class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-brand-navy-900 bg-gradient-to-br from-brand-pink-200 to-brand-pink-500 text-xs font-black text-brand-navy-900 shadow-sm"
                            :title="t.name"
                        >
                            {{ t.initials }}
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-brand-navy-900 bg-white/10 text-xs font-bold text-white">
                            +99
                        </div>
                    </div>
                    <p class="text-sm font-semibold text-brand-navy-100">
                        Bergabung dengan ratusan ibu yang sudah menggunakan SIGADIS
                    </p>

                    <!-- Mascot â€” large, central -->
                    <img
                        :src="src('merayakan')"
                        :alt="alt('merayakan')"
                        class="mt-8 w-40 drop-shadow-2xl transition-all duration-500 hover:scale-110 hover:-translate-y-2 sm:w-48"
                    />

                    <!-- Heading -->
                    <p class="mt-6 text-xs font-bold uppercase tracking-[0.2em] text-brand-pink-200">Mulai Sekarang</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-white sm:text-4xl lg:text-5xl">
                        Dampingi Kehamilan Anda
                    </h2>
                    <p class="mx-auto mt-4 max-w-lg text-base leading-relaxed text-brand-navy-100">
                        Daftarkan diri dan gunakan SIGADIS untuk pendampingan kesehatan yang lebih terhubung, kapan pun dan di mana pun.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="mt-10 flex flex-col justify-center gap-4 sm:flex-row">
                        <Link href="/register" class="group inline-flex items-center justify-center gap-2 rounded-full bg-white px-8 py-4 text-base font-bold text-brand-navy-900 shadow-lg transition-all duration-300 hover:bg-brand-pink-50 hover:-translate-y-0.5 hover:shadow-xl">
                            Daftar Sekarang
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                        </Link>
                        <a :href="APK_URL" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-full border-2 border-white/20 bg-transparent px-8 py-4 text-base font-bold text-white transition-all duration-200 hover:border-white/50 hover:bg-white/10 hover:-translate-y-0.5">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.523 15.341 13 12.839V7a1 1 0 1 0-2 0v5.839L6.477 15.34a1 1 0 1 0 1 1.732L12 14.575l4.523 2.497a1 1 0 0 0 1-1.731Z"/><path fill-rule="evenodd" d="M12 1C5.925 1 1 5.925 1 12s4.925 11 11 11 11-4.925 11-11S18.075 1 12 1ZM3 12a9 9 0 1 1 18 0 9 9 0 0 1-18 0Z" clip-rule="evenodd"/></svg>
                            Download APK
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

