<script setup>
import { Head, router } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const logoUrl = '/assets/images/mascot/pose-21-logo-sistem.png';

/**
 * Flows.md §1.3-1.4: status login dicek server (lihat routes/web.php,
 * pengguna dengan sesi aktif langsung diarahkan ke Beranda sebelum
 * halaman ini dirender). Kalau sampai di sini berarti belum login;
 * yang tersisa dicek di sini cuma status onboarding, disimpan lokal
 * di perangkat (bukan backend), sesuai §1.4 edge case.
 */
onMounted(() => {
    window.setTimeout(() => {
        const onboarded = window.localStorage.getItem('sigadis_onboarded') === '1';
        router.visit(onboarded ? route('auth.pregnant.phone.show') : route('onboarding'));
    }, 1500);
});
</script>

<template>
    <Head title="SIGADIS" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-brand-pink-50 px-6">
        <div class="avatar mb-6">
            <div class="w-32 rounded-full border-4 border-white bg-white shadow-lg">
                <img :src="logoUrl" alt="Maskot SIGADIS" class="h-full w-full object-cover" />
            </div>
        </div>

        <h1 class="mb-2 text-3xl font-black text-brand-navy-900">SIGADIS</h1>
        <p class="mb-8 text-center text-sm text-brand-navy-700">Sistem Deteksi Dini & Respon Cepat</p>

        <span class="loading loading-dots loading-md text-brand-navy-700"></span>
    </div>
</template>
