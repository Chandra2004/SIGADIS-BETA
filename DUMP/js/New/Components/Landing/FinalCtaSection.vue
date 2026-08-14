<script setup>
import { Link } from '@inertiajs/vue3';
import { useMascot } from '@/Composables/useMascot';
import { APK_URL } from '@/Data/landingContent';

const { src, alt } = useMascot();
</script>

<template>
    <section class="px-4 pb-20 sm:px-6 lg:px-8">
        <div class="relative mx-auto max-w-5xl overflow-hidden rounded-[2.5rem] bg-gradient-to-r from-brand-navy-900 via-brand-navy-700 to-brand-pink-500 p-8 text-center text-white shadow-[0_30px_80px_rgba(44,74,110,0.25)] sm:p-12">
            <img :src="src('merayakan')" :alt="alt('merayakan')" class="mx-auto w-28 drop-shadow-xl sm:w-32" />
            <p class="mt-4 text-sm font-bold uppercase tracking-[0.22em] text-brand-cream-100">Mulai sekarang</p>
            <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl text-white">
                Mulai Gunakan SIGADIS
            </h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg leading-8 text-brand-navy-100">
                Daftarkan diri Anda dan gunakan aplikasi SIGADIS untuk mendapatkan pendampingan yang lebih terhubung.
            </p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <Link href="/register" class="btn h-12 rounded-2xl bg-white px-7 text-base font-semibold text-brand-navy-900 hover:bg-ink-100">
                    Daftar Sekarang
                </Link>
                <a :href="APK_URL" target="_blank" rel="noopener noreferrer" class="btn h-12 rounded-2xl border border-white/40 bg-transparent px-7 text-base font-semibold text-white hover:bg-white/10">
                    Download APK
                </a>
            </div>
        </div>
    </section>
</template>
