<script setup>
import { useMascot } from '@/Composables/useMascot';
import { APK_URL } from '@/Data/landingContent';
import PhoneMockup from './PhoneMockup.vue';

const { src, alt } = useMascot();
</script>

<template>
    <section id="download-apk" class="scroll-mt-24 bg-brand-navy-900 py-20 text-white">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:px-8">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.22em] text-brand-pink-500">Download APK</p>
                <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl text-white">
                    Gunakan SIGADIS di Smartphone Anda
                </h2>
                <p class="mt-6 max-w-xl text-lg leading-8 text-brand-navy-100">
                    Dapatkan pengalaman SIGADIS secara lengkap melalui aplikasi mobile.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a :href="APK_URL" target="_blank" rel="noopener noreferrer" class="btn h-12 rounded-2xl bg-brand-pink-500 px-7 text-base font-semibold text-brand-navy-900 hover:bg-brand-pink-500/90">
                        Download APK
                    </a>
                </div>
            </div>

            <div class="relative flex justify-center">
                <img :src="src('jempolCentang')" :alt="alt('jempolCentang')" class="absolute -left-2 bottom-4 z-20 w-24 drop-shadow-xl sm:w-28 lg:-left-8 lg:w-32" />
                <PhoneMockup dark />
            </div>
        </div>
    </section>
</template>
