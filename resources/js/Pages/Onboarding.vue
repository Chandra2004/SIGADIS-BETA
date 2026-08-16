<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';

const slides = [
    {
        image: '/assets/mascot/mascot-pose-1.webp',
        alt: 'Maskot SIGADIS Menyambut',
        title: 'Selamat Datang, Bunda!',
        description: 'SIGADIS siap menemani perjalanan kehamilan Bunda agar tetap sehat, tenang, dan aman.',
        badge: 'Teman Setia Kehamilan',
        badgeIcon: 'favorite',
    },
    {
        image: '/assets/mascot/mascot-pose-3.webp',
        alt: 'Maskot SIGADIS Mendengarkan dan Memandu Skrining',
        title: 'Deteksi Dini & Skrining Mandiri',
        description: 'Kenali risiko komplikasi kehamilan lebih awal dengan skrining interaktif berbasis AI dan standar Kemenkes.',
        badge: 'Skrining Cerdas',
        badgeIcon: 'health_and_safety',
    },
    {
        image: '/assets/mascot/mascot-pose-10.webp',
        alt: 'Maskot SIGADIS Menemani dan Berjaga',
        title: 'Terhubung Bidan & Kader',
        description: 'Pantau kesehatan janin dan dapatkan respons tanggap darurat tercepat dari tenaga kesehatan terdekat di wilayah Bunda.',
        badge: 'Siaga 24/7',
        badgeIcon: 'support_agent',
    },
    {
        image: '/assets/mascot/mascot-pose-19.webp',
        alt: 'Maskot SIGADIS Memperkenalkan Fitur dan Aksesibilitas',
        title: 'Akses Mudah & Ramah Pengguna',
        description: 'Dilengkapi panduan suara Text-to-Speech (TTS) dan tampilan intuitif yang nyaman digunakan kapan saja.',
        badge: 'Aksesibilitas Lengkap',
        badgeIcon: 'record_voice_over',
    },
];

const currentIndex = ref(0);
const touchStartX = ref(0);
const touchEndX = ref(0);

const nextSlide = () => {
    if (currentIndex.value < slides.length - 1) {
        currentIndex.value++;
    } else {
        finishOnboarding();
    }
};

const prevSlide = () => {
    if (currentIndex.value > 0) {
        currentIndex.value--;
    }
};

const goToSlide = (index) => {
    currentIndex.value = index;
};

const skipOnboarding = () => {
    // Tombol lewati langsung membawa pengguna ke halaman 4 (slide terakhir)
    if (currentIndex.value < slides.length - 1) {
        currentIndex.value = slides.length - 1;
    } else {
        finishOnboarding();
    }
};

const finishOnboarding = () => {
    // Simpan status onboarding ke localStorage
    localStorage.setItem('sigadis_onboarding_completed', 'true');
    router.visit(route('mobile.register.show'));
};

const handleTouchStart = (e) => {
    touchStartX.value = e.changedTouches[0].screenX;
};

const handleTouchEnd = (e) => {
    touchEndX.value = e.changedTouches[0].screenX;
    handleSwipe();
};

const handleSwipe = () => {
    const threshold = 45;
    if (touchEndX.value < touchStartX.value - threshold) {
        // Swipe kiri -> Next
        nextSlide();
    } else if (touchEndX.value > touchStartX.value + threshold) {
        // Swipe kanan -> Prev
        prevSlide();
    }
};

const handleKeyDown = (e) => {
    if (e.key === 'ArrowRight') nextSlide();
    if (e.key === 'ArrowLeft') prevSlide();
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);

    // Preload semua gambar maskot slide ke cache browser agar transisi slide instan
    slides.forEach((slide) => {
        const img = new Image();
        img.src = slide.image;
    });
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <Head title="Selamat Datang di SIGADIS" />

    <div class="min-h-screen bg-[#F8EBF0] text-[#26292E] flex flex-col items-center justify-center p-0 sm:p-4 font-sans selection:bg-[#F3AEC0] selection:text-[#2C4A6E]">
        <!-- Mobile Viewport Container -->
        <main
            class="w-full max-w-md h-[100dvh] sm:h-[860px] sm:max-h-[95vh] sm:rounded-[36px] sm:shadow-2xl overflow-hidden bg-[#FDF3F6] relative flex flex-col sm:border sm:border-[#F3AEC0]/30 select-none"
            @touchstart="handleTouchStart"
            @touchend="handleTouchEnd"
        >
            <!-- Background Decorative Glows -->
            <div class="absolute -top-16 -right-16 w-64 h-64 bg-[#F3AEC0]/25 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/3 -left-20 w-60 h-60 bg-[#ABC9F3]/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-10 right-1/4 w-72 h-72 bg-[#F7EFE4]/60 rounded-full blur-2xl pointer-events-none"></div>

            <!-- Top Bar: Step Counter & Skip Button -->
            <header class="relative z-20 flex items-center justify-between px-6 pt-6 pb-2">
                <!-- Back Button (if not first slide) -->
                <button
                    v-if="currentIndex > 0"
                    type="button"
                    @click="prevSlide"
                    class="w-10 h-10 rounded-full bg-white/70 backdrop-blur-sm border border-[#F3AEC0]/40 text-[#2C4A6E] flex items-center justify-center shadow-sm hover:bg-white active:scale-95 transition-all"
                    aria-label="Slide sebelumnya"
                >
                    <span class="material-symbols-outlined text-xl">arrow_back</span>
                </button>
                <div v-else class="w-10 h-10"></div>

                <!-- Step Badge -->
                <div class="flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-white/60 backdrop-blur-md border border-[#F3AEC0]/30 shadow-xs">
                    <span class="text-xs font-bold text-[#2C4A6E] tracking-wider">{{ currentIndex + 1 }}</span>
                    <span class="text-xs text-[#8A8D96]">/</span>
                    <span class="text-xs font-medium text-[#8A8D96]">{{ slides.length }}</span>
                </div>

                <!-- Skip Button -->
                <button
                    v-if="currentIndex < slides.length - 1"
                    type="button"
                    @click="skipOnboarding"
                    class="text-xs font-bold text-[#73777F] hover:text-[#2C4A6E] px-3 py-1.5 rounded-full hover:bg-white/60 active:scale-95 transition-all flex items-center gap-0.5"
                >
                    <span>Lewati</span>
                    <span class="material-symbols-outlined text-base">chevron_right</span>
                </button>
                <div v-else class="w-16"></div>
            </header>

            <!-- Carousel Slide Viewport -->
            <div class="relative z-10 flex-1 flex flex-col justify-between px-6 py-4 overflow-hidden">
                <!-- Mascot & Illustration Container -->
                <div class="flex-1 flex items-center justify-center relative my-auto min-h-[260px] max-h-[360px]">
                    <transition name="scale-fade" mode="out-in">
                        <div
                            :key="currentIndex"
                            class="relative flex flex-col items-center justify-center w-full h-full"
                        >
                            <!-- Ambient Glow Circle -->
                            <div class="absolute w-52 h-52 sm:w-60 sm:h-60 bg-gradient-to-tr from-[#F3AEC0]/30 to-[#ABC9F3]/30 rounded-full blur-2xl z-0"></div>
                            
                            <!-- Feature Tag Pill -->
                            <div class="absolute top-0 z-20 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/90 backdrop-blur-md border border-[#F3AEC0]/40 text-[#2C4A6E] shadow-sm text-xs font-semibold animate-bounce-subtle">
                                <span class="material-symbols-outlined text-sm text-[#E0703D]">{{ slides[currentIndex].badgeIcon }}</span>
                                <span>{{ slides[currentIndex].badge }}</span>
                            </div>

                            <!-- Illustration Image with Eager Loading & Preload Optimization -->
                            <img
                                :src="slides[currentIndex].image"
                                :alt="slides[currentIndex].title"
                                loading="eager"
                                decoding="async"
                                fetchpriority="high"
                                class="relative z-10 w-auto max-w-[270px] sm:max-w-[300px] max-h-[250px] sm:max-h-[280px] object-contain drop-shadow-xl transition-transform duration-500 hover:scale-105"
                            />
                        </div>
                    </transition>
                </div>

                <!-- Text Content Area -->
                <div class="text-center w-full py-3">
                    <transition name="slide-fade" mode="out-in">
                        <div :key="currentIndex" class="space-y-2.5">
                            <h1 class="text-2xl sm:text-[26px] font-bold text-[#123356] leading-tight tracking-tight px-2">
                                {{ slides[currentIndex].title }}
                            </h1>
                            <p class="text-sm sm:text-base text-[#43474E] leading-relaxed font-normal px-3 max-w-[340px] mx-auto">
                                {{ slides[currentIndex].description }}
                            </p>
                        </div>
                    </transition>
                </div>

                <!-- Bottom Controls: Indicators & CTA Buttons -->
                <div class="pt-4 pb-2 w-full flex flex-col items-center gap-5">
                    <!-- Progress Pill Indicators -->
                    <div class="flex items-center justify-center gap-2" role="tablist" aria-label="Navigasi Onboarding">
                        <button
                            v-for="(_, index) in slides"
                            :key="index"
                            type="button"
                            @click="goToSlide(index)"
                            :class="[
                                'h-2 rounded-full transition-all duration-300 focus:outline-none',
                                currentIndex === index
                                    ? 'w-8 bg-[#2C4A6E] shadow-sm'
                                    : 'w-2 bg-[#C3C6CF] hover:bg-[#8A8D96]/60'
                            ]"
                            :aria-label="`Menuju slide ${index + 1}`"
                            :aria-selected="currentIndex === index"
                        />
                    </div>

                    <!-- Action Buttons -->
                    <div class="w-full flex flex-col gap-2.5">
                        <button
                            type="button"
                            @click="nextSlide"
                            class="w-full h-14 bg-[#2C4A6E] hover:bg-[#1E334D] active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg shadow-[#2C4A6E]/20 transition-all flex items-center justify-center gap-2 text-base group"
                        >
                            <span>{{ currentIndex === slides.length - 1 ? 'Mulai Sekarang' : 'Lanjut' }}</span>
                            <span class="material-symbols-outlined text-xl transition-transform duration-200 group-hover:translate-x-1">
                                {{ currentIndex === slides.length - 1 ? 'check_circle' : 'arrow_forward' }}
                            </span>
                        </button>

                        <!-- Alternative login link if on last slide -->
                        <div v-if="currentIndex === slides.length - 1" class="text-center pt-1">
                            <p class="text-xs text-[#73777F]">
                                Sudah memiliki akun?
                                <button
                                    type="button"
                                    @click="router.visit(route('mobile.login.show'))"
                                    class="font-bold text-[#2C4A6E] hover:underline"
                                >
                                    Masuk di sini
                                </button>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Transisi Slide Teks */
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.35s ease-out;
}
.slide-fade-enter-from {
    opacity: 0;
    transform: translateY(12px);
}
.slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-12px);
}

/* Transisi Skala Gambar */
.scale-fade-enter-active,
.scale-fade-leave-active {
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.scale-fade-enter-from {
    opacity: 0;
    transform: scale(0.88);
}
.scale-fade-leave-to {
    opacity: 0;
    transform: scale(1.06);
}

@keyframes bounceSubtle {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}
.animate-bounce-subtle {
    animation: bounceSubtle 3s ease-in-out infinite;
}
</style>
