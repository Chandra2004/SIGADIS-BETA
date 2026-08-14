<script setup>
import { Link } from '@inertiajs/vue3';
import { useMascot } from '@/Composables/useMascot';

defineProps({
    /** Kunci pose di useMascot(), mis. 'menyambut' untuk register, 'menyambutKembali' untuk login. */
    mascotKey: { type: String, default: 'menyambut' },
    panelEyebrow: { type: String, default: 'SIGADIS' },
    panelTitle: { type: String, required: true },
    panelDescription: { type: String, required: true },
});

const { src, alt } = useMascot();
</script>

<template>
    <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(243,174,192,0.28),transparent_22%),radial-gradient(circle_at_bottom_right,_rgba(44,74,110,0.18),transparent_30%),linear-gradient(180deg,#ffffff_0%,#fdf3f6_100%)]">
        <header class="border-b border-brand-pink-500/40 bg-white/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-2xl border border-brand-pink-500/60 bg-brand-pink-50">
                        <img :src="src('logoSistem')" :alt="alt('logoSistem')" class="h-8 w-8 object-contain" />
                    </div>
                    <p class="text-lg font-black tracking-tight text-brand-navy-900">SIGADIS</p>
                </Link>
                <Link href="/" class="text-sm font-semibold text-brand-navy-700 hover:text-brand-navy-900">
                    ← Kembali ke beranda
                </Link>
            </div>
        </header>

        <main class="mx-auto flex max-w-5xl items-center px-4 py-12 sm:px-6 lg:px-8 lg:py-20">
            <div class="grid w-full items-center gap-10 overflow-hidden rounded-[2.5rem] border border-brand-pink-500/40 bg-white shadow-[0_30px_80px_rgba(44,74,110,0.12)] lg:grid-cols-2">
                <!-- Panel brand, disembunyikan di mobile -->
                <div class="relative hidden h-full flex-col justify-between bg-brand-navy-900 p-10 text-white lg:flex">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.22em] text-brand-pink-500">{{ panelEyebrow }}</p>
                        <h2 class="mt-4 text-3xl font-black leading-tight tracking-tight">{{ panelTitle }}</h2>
                        <p class="mt-4 text-base leading-7 text-brand-navy-100">{{ panelDescription }}</p>
                    </div>
                    <img :src="src(mascotKey)" :alt="alt(mascotKey)" class="mx-auto w-40 drop-shadow-2xl" />
                </div>

                <!-- Form -->
                <div class="p-8 sm:p-10 lg:p-12">
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>
