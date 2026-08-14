<script setup>
import { Link } from '@inertiajs/vue3';
import { useMascot } from '@/Composables/useMascot';
import { APK_URL } from '@/Data/landingContent';
import PhoneMockup from './PhoneMockup.vue';

const { src, alt } = useMascot();
</script>

<template>
    <section
        id="beranda"
        class="relative scroll-mt-24 overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(243,174,192,0.28),transparent_22%),radial-gradient(circle_at_bottom_right,_rgba(44,74,110,0.18),transparent_30%),linear-gradient(180deg,#ffffff_0%,#fdf3f6_100%)]"
    >
        <div class="mx-auto grid max-w-7xl items-center gap-14 px-4 py-16 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-24">
            <div class="relative z-10">
                <span class="inline-flex items-center rounded-full border border-brand-pink-500 bg-brand-pink-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.22em] text-brand-navy-900">
                    SIGADIS
                </span>
                <h1 class="mt-6 max-w-xl text-4xl font-black leading-tight tracking-tight text-ink-900 sm:text-5xl lg:text-6xl">
                    Kesehatan Ibu Lebih Terhubung, Bantuan Lebih Cepat.
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-brand-navy-700">
                    SIGADIS adalah platform pendampingan kesehatan ibu yang menghubungkan ibu dengan bidan dan kader serta membantu mempercepat respons dalam kondisi darurat.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <Link href="/register" class="btn btn-primary h-12 rounded-2xl px-7 text-base font-semibold shadow-lg shadow-brand-navy-900/20">
                        Daftar Sekarang
                    </Link>
                    <a :href="APK_URL" target="_blank" rel="noopener noreferrer" class="btn h-12 rounded-2xl border border-brand-navy-900/20 bg-white px-7 text-base font-semibold text-brand-navy-900 hover:border-brand-pink-500 hover:bg-brand-pink-50">
                        Download APK
                    </a>
                </div>

                <div class="mt-8 flex items-center gap-3 rounded-[1.5rem] border border-brand-pink-500/60 bg-brand-cream-100 p-4 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-risk-low-bg text-xl text-risk-low">✓</div>
                    <p class="text-sm font-medium text-ink-900">
                        Satu aplikasi untuk pendampingan, pemantauan, dan respons darurat ibu.
                    </p>
                </div>
            </div>

            <div class="relative flex justify-center">
                <div class="absolute -inset-10 rounded-[3rem] bg-gradient-to-br from-brand-pink-500/40 via-brand-cream-100 to-brand-navy-100/60 blur-2xl" />

                <img
                    :src="src('memperkenalkanDiri')"
                    :alt="alt('memperkenalkanDiri')"
                    class="absolute -left-4 bottom-2 z-20 w-28 drop-shadow-xl sm:w-36 lg:-left-10 lg:w-44"
                />

                <PhoneMockup />
            </div>
        </div>
    </section>
</template>
