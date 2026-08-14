<script setup>
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const slides = [
    {
        title: 'Selamat Datang, Bunda!',
        body: 'SIGADIS siap menemani perjalanan kehamilan Bunda agar tetap sehat dan aman.',
        mascot: '/assets/images/mascot/pose-01-menyambut.png',
    },
    {
        title: 'Deteksi Dini Mudah & Cepat',
        body: 'Pantau kesehatan Bunda dan janin secara mandiri melalui skrining yang praktis.',
        mascot: '/assets/images/mascot/pose-19-memperkenalkan-diri.png',
    },
    {
        title: 'Terhubung Langsung dengan Bidan',
        body: 'Dapatkan pendampingan langsung dari bidan terdekat untuk keamanan ekstra Bunda.',
        mascot: '/assets/images/mascot/pose-10-menemani-berjaga.png',
    },
    {
        // Flows.md §29: pengaturan TTS bisa diubah lagi kapan saja lewat menu Pengaturan Aplikasi.
        title: 'Mudah Digunakan dengan Suara',
        body: 'Gunakan fitur teks-ke-suara untuk memudahkan Bunda mengisi data skrining.',
        mascot: '/assets/images/mascot/pose-20-jempol-centang.png',
    },
];

const step = ref(0);
const isLast = computed(() => step.value === slides.length - 1);

function finish() {
    window.localStorage.setItem('sigadis_onboarded', '1');
    router.visit(route('auth.pregnant.phone.show'));
}

function next() {
    if (isLast.value) {
        finish();
        return;
    }

    step.value += 1;
}
</script>

<template>
    <Head title="Selamat Datang" />

    <div class="flex min-h-screen flex-col bg-brand-pink-50 px-6 py-10">
        <div class="flex justify-end">
            <button type="button" class="btn btn-ghost btn-sm text-brand-navy-700" @click="finish">Lewati</button>
        </div>

        <div class="flex flex-1 flex-col items-center justify-center text-center">
            <img :src="slides[step].mascot" alt="" class="mb-6 h-40 w-40 object-contain" />
            <h1 class="mb-4 text-2xl font-black text-brand-navy-900">{{ slides[step].title }}</h1>
            <p class="max-w-sm text-sm text-brand-navy-700">{{ slides[step].body }}</p>
        </div>

        <div class="mb-6 flex justify-center gap-2">
            <span
                v-for="(s, i) in slides"
                :key="i"
                class="h-2 rounded-full transition-all"
                :class="i === step ? 'w-6 bg-brand-navy-900' : 'w-2 bg-brand-navy-100'"
            ></span>
        </div>

        <button type="button" class="btn w-full border-none bg-brand-navy-900 text-white" @click="next">
            {{ isLast ? 'Mulai Sekarang' : 'Lanjut' }}
        </button>
    </div>
</template>
