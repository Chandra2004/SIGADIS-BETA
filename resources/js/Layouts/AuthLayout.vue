<script setup>
import { Link } from '@inertiajs/vue3';
import ToastNotification from '@/Components/ToastNotification.vue';

defineProps({
    panelEyebrow: { type: String, default: 'Portal Bidan & Kader' },
    panelTitle: { type: String, default: 'Sistem Gawat Darurat Ibu-Selamat' },
    panelDescription: {
        type: String,
        default: 'Portal Bidan & Kader. Akses data maternal terpusat, pantau risiko tinggi, dan berikan respon cepat untuk keselamatan ibu dan bayi.'
    },
});
</script>

<template>
    <div class="min-h-screen w-full bg-[#F2F3F5] text-[#26292E] flex items-center justify-center overflow-x-hidden md:p-6 lg:p-10 font-sans">
        <ToastNotification />
        <!-- Split Screen Container -->
        <div class="w-full max-w-[1440px] min-h-screen md:min-h-[720px] md:h-[88vh] flex flex-col md:flex-row bg-white md:rounded-3xl shadow-2xl relative overflow-hidden">
            
            <!-- Left Panel: Branding & Information (Desktop Only) -->
            <div class="hidden md:flex flex-col justify-between w-1/2 bg-[#2C4A6E] text-white p-10 lg:p-14 relative overflow-hidden select-none">
                <!-- Decorative Background Element (Medical Pattern) -->
                <div class="absolute inset-0 opacity-10 pointer-events-none">
                    <svg height="100%" width="100%" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern height="40" id="medical-pattern-auth" patternUnits="userSpaceOnUse" width="40" x="0" y="0">
                                <path d="M18 10h4v8h8v4h-8v8h-4v-8h-8v-4h8v-8z" fill="currentColor" />
                            </pattern>
                        </defs>
                        <rect fill="url(#medical-pattern-auth)" height="100%" width="100%" />
                    </svg>
                </div>

                <!-- Glow Effects -->
                <div class="absolute -top-24 -left-24 w-80 h-80 rounded-full bg-[#3D6086]/40 blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full bg-[#F3AEC0]/15 blur-3xl pointer-events-none"></div>

                <!-- Top Logo / Header -->
                <div class="relative z-10">
                    <Link :href="route('landing.home')" class="inline-flex items-center gap-3 text-white hover:opacity-90 transition-opacity">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 shadow-inner">
                            <span class="material-symbols-outlined text-3xl text-white" style="font-variation-settings: 'FILL' 1;">health_and_safety</span>
                        </div>
                        <div>
                            <span class="text-2xl font-black tracking-tight text-white block">SIGADIS</span>
                            <span class="text-[11px] font-semibold tracking-wider uppercase text-[#ABC9F3] block">Maternal Health Platform</span>
                        </div>
                    </Link>
                </div>

                <!-- Center Content -->
                <div class="relative z-10 my-auto py-8 max-w-lg space-y-6">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/15 backdrop-blur-sm text-xs font-semibold text-[#ABC9F3] tracking-wide uppercase">
                        <span class="w-2 h-2 rounded-full bg-[#ABC9F3] animate-pulse"></span>
                        {{ panelEyebrow }}
                    </div>
                    
                    <h2 class="text-3xl lg:text-4xl font-extrabold text-white leading-tight tracking-tight">
                        {{ panelTitle }}
                    </h2>
                    
                    <p class="text-base lg:text-lg text-[#ABC9F3] leading-relaxed opacity-95">
                        {{ panelDescription }}
                    </p>

                    <div class="pt-4 flex items-center gap-6 text-sm text-white/80">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-400 text-xl" style="font-variation-settings: 'FILL' 1;">verified</span>
                            <span>Akses Terenkripsi</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-400 text-xl" style="font-variation-settings: 'FILL' 1;">bolt</span>
                            <span>Respons Cepat 24/7</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Copyright & Return Link -->
                <div class="relative z-10 flex items-center justify-between text-xs text-[#ABC9F3]/80 border-t border-white/10 pt-4">
                    <span>© 2026 SIGADIS. Mendukung Kesehatan Ibu Hamil Indonesia.</span>
                    <Link :href="route('landing.home')" class="hover:text-white transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">home</span> Beranda
                    </Link>
                </div>
            </div>

            <!-- Right Panel: Form Slot -->
            <div class="w-full md:w-1/2 min-h-screen md:min-h-full flex flex-col justify-between p-6 sm:p-10 lg:p-14 bg-white relative overflow-y-auto">
                <!-- Mobile-only top header with logo -->
                <div class="md:hidden flex items-center justify-between pb-6 border-b border-neutral-100">
                    <Link :href="route('landing.home')" class="flex items-center gap-2.5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#2C4A6E] text-white">
                            <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">health_and_safety</span>
                        </div>
                        <span class="text-xl font-black text-[#2C4A6E]">SIGADIS</span>
                    </Link>
                    <Link :href="route('landing.home')" class="text-xs font-semibold text-[#2C4A6E] hover:underline flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">arrow_back</span> Beranda
                    </Link>
                </div>

                <!-- Form Content Container -->
                <div class="my-auto py-4 w-full max-w-md mx-auto">
                    <slot />
                </div>

                <!-- Mobile-only bottom footer -->
                <div class="md:hidden pt-6 text-center text-xs text-neutral-400 border-t border-neutral-100">
                    © 2026 SIGADIS. Platform Maternal Web.
                </div>
            </div>

        </div>
    </div>
</template>
