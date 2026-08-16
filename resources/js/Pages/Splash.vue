<script setup>
import { onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';

const navigateNext = () => {
    try {
        const isCompleted = localStorage.getItem('sigadis_onboarding_completed');
        if (isCompleted === 'true') {
            // Pengguna lama / sudah pernah onboarding -> langsung ke halaman login mobile
            router.visit(route('mobile.login.show'));
        } else {
            // Pertama kali install & buka aplikasi -> masuk ke Onboarding
            router.visit(route('onboarding'));
        }
    } catch (e) {
        router.visit(route('onboarding'));
    }
};

onMounted(() => {
    const timer = setTimeout(() => {
        navigateNext();
    }, 2200);

    return () => clearTimeout(timer);
});
</script>

<template>
    <Head title="SIGADIS Mobile" />

    <div
        @click="goToOnboarding"
        class="min-h-screen bg-[#FDF3F6] flex flex-col items-center justify-center p-4 cursor-pointer select-none relative overflow-hidden"
    >
        <!-- Background Glowing Circles -->
        <div class="absolute -top-24 -right-24 w-80 h-80 bg-[#F3AEC0]/30 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-[#ABC9F3]/30 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>

        <!-- Center Brand Container -->
        <div class="relative z-10 flex flex-col items-center gap-6 animate-fade-in">
            <!-- Icon/Mascot Image -->
            <div class="relative flex items-center justify-center">
                <div class="absolute w-44 h-44 bg-white/70 rounded-full blur-xl"></div>
                <img
                    src="/assets/mobile/splash.webp"
                    alt="Logo SIGADIS"
                    class="relative z-10 w-36 h-36 sm:w-44 sm:h-44 object-contain drop-shadow-2xl hover:scale-105 transition-transform duration-500"
                    onerror="this.onerror=null; this.src='/icon-mobile.png';"
                />
            </div>

            <!-- App Title & Tagline -->
            <div class="text-center space-y-1.5">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-[#123356] tracking-tight">
                    SIGADIS
                </h1>
                <p class="text-xs sm:text-sm font-semibold text-[#73777F] tracking-wide uppercase">
                    Sistem Informasi Kesehatan Ibu Hamil & Nifas
                </p>
            </div>

            <!-- Loading Spinner / Pulse indicator -->
            <div class="mt-4 flex items-center gap-1.5">
                <div class="w-2.5 h-2.5 rounded-full bg-[#2C4A6E] animate-bounce" style="animation-delay: 0ms;"></div>
                <div class="w-2.5 h-2.5 rounded-full bg-[#F3AEC0] animate-bounce" style="animation-delay: 150ms;"></div>
                <div class="w-2.5 h-2.5 rounded-full bg-[#2C4A6E] animate-bounce" style="animation-delay: 300ms;"></div>
            </div>
        </div>

        <!-- Bottom Footer -->
        <footer class="absolute bottom-6 text-center text-xs text-[#8A8D96]">
            <span>Ketuk layar untuk melanjutkan</span>
        </footer>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(16px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
.animate-fade-in {
    animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
