<script setup>
import { useMascot } from '@/Composables/useMascot';

const { src, alt } = useMascot();
</script>

<template>
    <section class="bg-brand-cream-100 py-20">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-[1fr_0.85fr] lg:px-8">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.22em] text-brand-navy-900">Solusi</p>
                <h2 class="mt-4 text-3xl font-black tracking-tight text-ink-900 sm:text-4xl">
                    SIGADIS Menghubungkan Ibu dengan Bantuan yang Dibutuhkan
                </h2>
                <p class="mt-6 max-w-xl text-lg leading-8 text-brand-navy-700">
                    SIGADIS (Sistem Gawat Darurat Ibu-Selamat) merupakan platform digital yang dirancang untuk mendukung pendampingan kesehatan ibu melalui integrasi informasi, komunikasi dengan bidan/kader, pemantauan kondisi, serta mekanisme respons darurat berbasis lokasi.
                </p>
            </div>

            <div class="relative flex justify-center">
                <!-- Slot foto tematik: taruh foto asli ibu bersama bidan/kader di sini.
                     Path di bawah adalah placeholder — selama file belum ada, gradient
                     lembut ini tetap terlihat rapi, jadi tidak ada yang rusak. -->
                <div
                    class="relative flex h-72 w-full max-w-sm items-end justify-center overflow-hidden rounded-[2rem] border border-brand-pink-500/50 bg-gradient-to-br from-brand-pink-50 via-brand-pink-500/30 to-brand-navy-100 bg-cover bg-center shadow-sm"
                    style="background-image: url('/assets/images/theme-ibu-bidan.jpg')"
                >
                    <img :src="src('menjelaskan')" :alt="alt('menjelaskan')" class="relative z-10 mb-4 w-32 drop-shadow-lg" />
                </div>
            </div>
        </div>

        <div class="mx-auto mt-14 max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center gap-3 rounded-[2rem] border border-brand-pink-500/40 bg-white p-8 shadow-sm sm:flex-row sm:justify-between sm:gap-2">
                <div class="rounded-2xl bg-brand-pink-50 px-5 py-3 text-lg font-bold text-brand-navy-900">Ibu</div>
                <span class="text-2xl text-brand-pink-500">→</span>
                <div class="rounded-2xl bg-brand-navy-900 px-5 py-3 text-lg font-bold text-white">SIGADIS</div>
                <span class="text-2xl text-brand-pink-500">→</span>
                <div class="rounded-2xl bg-risk-low-bg px-5 py-3 text-lg font-bold text-risk-low">Bidan / Kader</div>
                <span class="text-2xl text-brand-pink-500">→</span>
                <div class="rounded-2xl bg-brand-cream-100 px-5 py-3 text-lg font-bold text-brand-navy-900">Bantuan</div>
            </div>
        </div>
    </section>
</template>
