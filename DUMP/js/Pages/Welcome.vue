<script setup>
import { Head, Link } from '@inertiajs/vue3';

// Menerima data (props) yang dikirimkan dari WelcomeController
defineProps({
    appName: String,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <!-- Komponen Head untuk mengubah title tab browser dinamis -->
    <Head title="Selamat Datang" />

    <div class="min-h-screen bg-slate-900 text-slate-100 flex flex-col justify-center items-center p-6 relative overflow-hidden font-sans">
        <!-- Efekt Background Glow dekoratif (Sangat cantik di layar HP) -->
        <div class="absolute top-0 -left-4 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
        <div class="absolute bottom-0 -right-4 w-72 h-72 bg-violet-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>

        <!-- Card Container Utama -->
        <div class="bg-slate-800/60 backdrop-blur-xl border border-slate-700/50 rounded-3xl shadow-2xl p-8 max-w-md w-full text-center relative z-10">

            <!-- Lingkaran Logo / Icon -->
            <div class="bg-gradient-to-tr from-blue-600 to-violet-600 w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-blue-500/20 transform rotate-3 hover:rotate-0 transition duration-300">
                <svg class="w-10 h-10 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>

            <!-- Judul & Deskripsi -->
            <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent mb-3">
                {{ appName || 'Laravel App' }}
            </h1>
            <p class="text-slate-400 mb-8 text-sm leading-relaxed max-w-sm mx-auto">
                Aplikasi <span class="text-blue-400 font-medium">Laravel</span> + <span class="text-emerald-400 font-medium">Vue 3</span> + <span class="text-purple-400 font-medium">Inertia</span> kamu telah dikemas sempurna oleh <span class="text-indigo-400 font-medium">Capacitor</span>.
            </p>

            <!-- Info Status Lingkungan (Grid Horisontal) -->
            <div class="grid grid-cols-2 gap-3 mb-8">
                <div class="bg-slate-900/50 border border-slate-700/30 rounded-2xl p-3.5 text-center">
                    <span class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Laravel</span>
                    <span class="text-sm font-bold font-mono text-blue-400">v{{ laravelVersion }}</span>
                </div>
                <div class="bg-slate-900/50 border border-slate-700/30 rounded-2xl p-3.5 text-center">
                    <span class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">PHP Engine</span>
                    <span class="text-sm font-bold font-mono text-violet-400">v{{ phpVersion }}</span>
                </div>
            </div>

            <!-- Kelompok Tombol Aksi (Sangat ergonomis untuk jempol di HP) -->
            <div class="flex flex-col gap-3">
                <button class="bg-gradient-to-r from-blue-600 to-violet-600 hover:from-blue-500 hover:to-violet-500 active:scale-[0.98] text-white font-semibold py-3.5 px-6 rounded-2xl transition-all duration-200 w-full shadow-lg shadow-blue-500/25 tracking-wide">
                    Mulai Eksplorasi
                </button>

                <a href="/webartisan" class="inline-flex items-center justify-center border border-slate-700 hover:bg-slate-700/50 active:scale-[0.98] text-slate-300 hover:text-white font-semibold py-3.5 px-6 rounded-2xl transition-all duration-200 w-full tracking-wide">
                    <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Buka WebArtisan
                </a>
            </div>
        </div>
    </div>
</template>
