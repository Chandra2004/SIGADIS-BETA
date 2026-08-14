<script setup>
import { useMascot } from '@/Composables/useMascot';
import { APK_URL } from '@/Data/landingContent';
import PhoneMockup from './PhoneMockup.vue';

const { src, alt } = useMascot();
</script>

<template>
    <section id="download-apk" class="scroll-mt-24 bg-brand-navy-900 py-24">
        <div class="mx-auto grid max-w-7xl items-center gap-16 px-6 lg:grid-cols-2 lg:px-8">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-brand-pink-500">Aplikasi Mobile</p>
                <h2 class="mt-4 text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Dampingi Kehamilan dari Genggaman Tangan
                </h2>
                <p class="mt-6 max-w-xl text-base leading-relaxed text-neutral-300">
                    Akses skrining, catatan riwayat, hingga tombol darurat langsung melalui smartphone Anda. Tersedia gratis untuk pengguna Android.
                </p>
                <div class="mt-10">
                    <a :href="APK_URL" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-full bg-white px-8 py-3.5 text-base font-semibold text-brand-navy-900 transition-colors hover:bg-neutral-100">
                        Download APK Sekarang
                    </a>
                </div>
            </div>

            <div class="relative flex justify-center lg:justify-end">
                <img :src="src('jempolCentang')" :alt="alt('jempolCentang')" class="absolute -left-4 bottom-8 z-20 w-28 drop-shadow-2xl sm:w-32 lg:-left-10 lg:w-36" />
                <PhoneMockup dark />
            </div>
        </div>
    </section>
</template>
