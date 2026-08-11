<script setup>
import { Head, router } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const appName = 'Testing Capacitor';
const logoUrl = '/assets/images/Pose%201%20%E2%80%94%20Menyambut%20(Onboarding_Registrasi).png';

onMounted(() => {
    window.setTimeout(() => {
        router.visit('/welcome');
    }, 5000);
});
</script>

<template>
    <Head title="Splash" />

    <div class="min-h-screen overflow-hidden bg-gradient-to-br from-sky-50 via-white to-indigo-100">
        <div class="relative flex min-h-screen items-center justify-center px-6">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(14,165,233,0.18),transparent_30%),radial-gradient(circle_at_bottom,_rgba(168,85,247,0.14),transparent_40%)]"></div>

            <main class="relative z-10 flex w-full max-w-md flex-col items-center justify-center text-center">
                <div class="avatar mb-6">
                    <div class="w-40 rounded-full border-4 border-white bg-white shadow-[0_20px_60px_rgba(14,165,233,0.18)] ring-8 ring-sky-100/80 sm:w-44">
                        <img :src="logoUrl" alt="App logo" class="h-full w-full object-cover" />
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.34rem] text-sky-600/80">Welcome</p>
                    <h1 class="text-4xl font-black tracking-tight text-slate-800 sm:text-5xl">
                        {{ appName }}
                    </h1>
                    <p class="mx-auto max-w-xs text-sm leading-6 text-slate-600 sm:text-base">
                        Mulai perjalananmu dengan pengalaman yang lebih cepat, aman, dan nyaman.
                    </p>
                </div>

                <div class="mt-8 flex flex-col items-center gap-3">
                    <span class="loading loading-dots loading-md text-primary"></span>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.32rem] text-slate-400">
                        Loading...
                    </p>
                </div>
            </main>
        </div>
    </div>
</template>
