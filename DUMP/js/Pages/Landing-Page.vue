<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const mascot = (n) => `/assets/mascot/mascot-pose-${n}.webp`;
const mobileMenuOpen = ref(false);

const navItems = [
    { label: 'Beranda', href: '#beranda' },
    { label: 'Tentang', href: '#tentang' },
    { label: 'Fitur', href: '#fitur' },
    { label: 'Cara Kerja', href: '#cara-kerja' },
    { label: 'FAQ', href: '#faq' },
];

const problems = [
    {
        title: 'Sulit mendapatkan bantuan dengan cepat',
        description:
            'Informasi kondisi dan lokasi belum tersampaikan secara efektif ketika terjadi keadaan darurat.',
    },
    {
        title: 'Pemantauan kesehatan belum terintegrasi',
        description:
            'Data dan riwayat kesehatan ibu dapat tersebar dan sulit dipantau secara berkelanjutan.',
    },
    {
        title: 'Komunikasi ibu dan tenaga pendamping terbatas',
        description:
            'Ibu membutuhkan akses komunikasi dan pendampingan yang lebih terhubung.',
    },
    {
        title: 'Informasi lokasi tidak selalu tersedia',
        description:
            'Dalam keadaan darurat, mengetahui lokasi ibu dapat membantu mempercepat respons.',
    },
];

// Each icon is a small hand-picked line icon (24x24, currentColor) — no stock artwork, so it
// stays on-brand and license-free. Swap these for photography once real assets are ready.
const featureIcons = {
    '01': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 5v6c0 5 3.4 8.7 8 11 4.6-2.3 8-6 8-11V5l-8-3Z"/><path d="M12 8v5"/><path d="M12 16.5h.01"/></svg>',
    '02': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-4.6-9.5-9.1C.8 8.4 2.4 5 5.8 5c1.9 0 3.3 1 4.2 2.4C11 6 12.4 5 14.3 5c1.6 0 2.9.8 3.6 2M18 8v6M15 11h6"/></svg>',
    '03': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12a7 7 0 0 1 14 0v3a2 2 0 0 1-2 2h-1v-6h3M5 15h1v-6H3v3a2 2 0 0 0 2 2Z"/><path d="M8 19a4 4 0 0 0 4 2"/></svg>',
    '04': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h9l3 3v15H6Z"/><path d="M15 3v3h3M9 12h6M9 16h6M9 8h2"/></svg>',
    '05': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 5-2 6-2 8h16c0-2-2-3-2-8Z"/><path d="M10 21a2 2 0 0 0 4 0"/></svg>',
    '06': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.4"/><path d="M5 20c0-3.9 3.1-6 7-6s7 2.1 7 6"/></svg>',
};

const features = [
    {
        number: '01',
        title: 'Emergency Button',
        description: 'Kirim permintaan bantuan darurat dengan cepat melalui satu tombol.',
    },
    {
        number: '02',
        title: 'Location Sharing',
        description: 'Bagikan lokasi ibu untuk membantu tenaga pendamping mengetahui posisi saat keadaan darurat.',
    },
    {
        number: '03',
        title: 'Pendampingan Bidan',
        description: 'Terhubung dengan bidan atau kader untuk mendapatkan pendampingan kesehatan.',
    },
    {
        number: '04',
        title: 'Riwayat Kesehatan',
        description: 'Simpan dan akses informasi serta riwayat kesehatan ibu secara terstruktur.',
    },
    {
        number: '05',
        title: 'Notifikasi',
        description: 'Dapatkan informasi dan pemberitahuan penting terkait pendampingan kesehatan.',
    },
    {
        number: '06',
        title: 'Profil Ibu',
        description: 'Kelola data diri, informasi kehamilan, kontak suami, bidan, dan kontak darurat.',
    },
];

const emergencyFlow = [
    { label: 'DARURAT', icon: mascot(9) },
    { label: 'Ambil lokasi' },
    { label: 'Kirim laporan' },
    { label: 'Bidan / Kader' },
    { label: 'Respons bantuan' },
];

// Steps map 1:1 onto real mascot poses already defined for the mobile app, so the landing
// page previews the exact character the user will meet inside the product at each stage.
const steps = [
    {
        number: '01',
        title: 'Daftar',
        description: 'Ibu membuat akun melalui website atau aplikasi.',
        mascot: mascot(1),
        mascotAlt: 'Maskot SIGADIS menyambut pengguna baru',
    },
    {
        number: '02',
        title: 'Lengkapi Profil',
        description: 'Masukkan informasi penting seperti data diri dan kontak pendamping/darurat.',
        mascot: mascot(2),
        mascotAlt: 'Maskot SIGADIS menjelaskan pengisian data',
    },
    {
        number: '03',
        title: 'Gunakan SIGADIS',
        description: 'Pantau informasi kesehatan dan tetap terhubung dengan bidan/kader melalui aplikasi.',
        mascot: mascot(3),
        mascotAlt: 'Maskot SIGADIS mendengarkan skrining berjalan',
    },
    {
        number: '04',
        title: 'Aktifkan Bantuan Saat Darurat',
        description: 'Tekan tombol darurat dan sistem mengirimkan informasi yang diperlukan kepada pihak terkait.',
        mascot: mascot(9),
        mascotAlt: 'Maskot SIGADIS siaga penuh saat darurat diaktifkan',
    },
];

const roles = [
    {
        name: 'Ibu',
        description: 'Mendapatkan akses pendampingan dan bantuan ketika membutuhkan.',
        mascot: mascot(5),
        mascotAlt: 'Maskot SIGADIS lega dan gembira mendampingi ibu',
    },
    {
        name: 'Bidan / Kader',
        description: 'Membantu memantau dan merespons kondisi ibu yang berada dalam pendampingan.',
        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3v6a4 4 0 0 0 8 0V3M10 9v3a5 5 0 0 0 5 5h0a5 5 0 0 0 5-5"/><circle cx="19" cy="6" r="2"/></svg>',
    },
    {
        name: 'Admin',
        description: 'Mengelola pengguna, data, dan operasional sistem.',
        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 5v6c0 5 3.4 8.7 8 11 4.6-2.3 8-6 8-11V5l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>',
    },
];

const advantages = [
    {
        title: 'Terintegrasi',
        description: 'Informasi ibu, pendampingan, dan laporan darurat berada dalam satu sistem.',
    },
    {
        title: 'Berbasis Lokasi',
        description: 'Informasi lokasi dapat digunakan dalam proses respons keadaan darurat.',
    },
    {
        title: 'Terhubung',
        description: 'Ibu dapat terhubung dengan bidan/kader yang mendampingi.',
    },
    {
        title: 'Responsif',
        description: 'Mempermudah penyampaian informasi ketika kondisi membutuhkan perhatian segera.',
    },
];

const faqs = [
    {
        question: 'Apa itu SIGADIS?',
        answer:
            'SIGADIS adalah Sistem Gawat Darurat Ibu-Selamat yang dirancang untuk mendukung pendampingan dan respons kesehatan ibu.',
    },
    {
        question: 'Siapa yang dapat menggunakan SIGADIS?',
        answer: 'Ibu/pasien, bidan/kader, dan admin sesuai dengan hak akses masing-masing.',
    },
    {
        question: 'Apakah SIGADIS dapat digunakan melalui website?',
        answer:
            'Website digunakan untuk informasi, registrasi, dan akses publik. Fitur utama SIGADIS tersedia melalui aplikasi mobile.',
    },
    {
        question: 'Bagaimana cara menggunakan fitur darurat?',
        answer:
            'Pengguna dapat menggunakan tombol darurat pada aplikasi untuk mengirimkan laporan sesuai mekanisme yang tersedia.',
    },
    {
        question: 'Apakah SIGADIS membutuhkan lokasi?',
        answer:
            'Fitur tertentu, khususnya mekanisme bantuan darurat, dapat menggunakan informasi lokasi pengguna.',
    },
    {
        question: 'Bagaimana cara mendapatkan aplikasi SIGADIS?',
        answer: 'Pengguna dapat mengunduh aplikasi melalui tombol download yang tersedia pada website.',
    },
];

const logoUrl = mascot(21);
</script>

<style scoped>
:root {
    --navy: #2c4a6e;
    --navy-2: #3d6086;
    --pink: #f3aec0;
    --pink-soft: #fdf3f6;
    --cream: #f7efe4;
    --dark: #26292e;
    --light: #f2f3f5;
    --white: #ffffff;
    --low: #4c9a6e;
    --low-bg: #e6f4ec;
    --medium: #e0a030;
    --medium-bg: #fbf0dc;
    --high: #d64550;
    --high-bg: #fbe4e5;
    --emergency: #c81e2c;
    --pulse: #ff4a57;
}

@keyframes pulse-ring {
    0% { transform: scale(0.95); opacity: 0.9; }
    70% { transform: scale(1.4); opacity: 0; }
    100% { transform: scale(1.4); opacity: 0; }
}
.pulse-dot::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 9999px;
    background: var(--pulse);
    animation: pulse-ring 1.8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@media (prefers-reduced-motion: reduce) {
    .pulse-dot::before { animation: none; }
}
</style>

<template>
    <Head title="SIGADIS | Pendampingan Kesehatan Ibu" />

    <div class="min-h-screen bg-[#F2F3F5] text-[#26292E]">
        <header class="sticky top-0 z-50 border-b border-[#F3AEC0]/50 bg-white/90 backdrop-blur-md">
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="#beranda" class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl border border-[#F3AEC0]/60 bg-[#FDF3F6]">
                        <img :src="logoUrl" alt="Logo SIGADIS" class="h-9 w-9 object-contain" />
                    </div>
                    <p class="text-lg font-black tracking-tight text-[#2C4A6E]">SIGADIS</p>
                </a>

                <div class="hidden items-center gap-6 text-sm font-medium text-[#3D6086] lg:flex">
                    <a v-for="item in navItems" :key="item.href" :href="item.href" class="transition hover:text-[#2C4A6E]">
                        {{ item.label }}
                    </a>
                </div>

                <div class="hidden items-center gap-3 sm:flex">
                    <Link href="/login" class="btn border border-[#2C4A6E]/20 bg-white text-[#2C4A6E] hover:border-[#F3AEC0] hover:bg-[#FDF3F6] hover:text-[#2C4A6E]">
                        Masuk
                    </Link>
                    <Link href="/register" class="btn border border-[#2C4A6E]/20 bg-white text-[#2C4A6E] hover:border-[#F3AEC0] hover:bg-[#FDF3F6] hover:text-[#2C4A6E]">
                        Daftar
                    </Link>
                    <a href="https://dev-sigadis-beta.rf.gd/application/SIGADIS.apk" target="_blank" rel="noopener noreferrer" class="btn bg-[#2C4A6E] text-white hover:bg-[#3D6086]">
                        Download APK
                    </a>
                </div>

                <!-- Mobile hamburger -->
                <button
                    type="button"
                    class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#F3AEC0]/60 text-[#2C4A6E] sm:hidden"
                    :aria-expanded="mobileMenuOpen"
                    aria-label="Buka menu navigasi"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <svg v-if="!mobileMenuOpen" viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                    <svg v-else viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </nav>

            <transition
                enter-active-class="transition ease-out duration-150"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-100"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="mobileMenuOpen" class="border-t border-[#F3AEC0]/50 bg-white px-4 py-4 sm:hidden">
                    <div class="flex flex-col gap-1 text-base font-medium text-[#3D6086]">
                        <a
                            v-for="item in navItems"
                            :key="item.href"
                            :href="item.href"
                            class="rounded-xl px-3 py-2.5 hover:bg-[#FDF3F6] hover:text-[#2C4A6E]"
                            @click="mobileMenuOpen = false"
                        >
                            {{ item.label }}
                        </a>
                    </div>
                    <div class="mt-3 flex flex-col gap-2 border-t border-[#F3AEC0]/40 pt-3">
                        <Link href="/login" class="btn h-11 rounded-xl border border-[#2C4A6E]/20 bg-white text-[#2C4A6E]" @click="mobileMenuOpen = false">
                            Masuk
                        </Link>
                        <Link href="/register" class="btn h-11 rounded-xl border border-[#2C4A6E]/20 bg-white text-[#2C4A6E]" @click="mobileMenuOpen = false">
                            Daftar
                        </Link>
                        <a href="https://dev-sigadis-beta.rf.gd/application/SIGADIS.apk" target="_blank" rel="noopener noreferrer" class="btn h-11 rounded-xl bg-[#2C4A6E] text-white" @click="mobileMenuOpen = false">
                            Download APK
                        </a>
                    </div>
                </div>
            </transition>
        </header>

        <main>
            <!-- HERO -->
            <section id="beranda" class="relative overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(243,174,192,0.28),transparent_22%),radial-gradient(circle_at_bottom_right,_rgba(44,74,110,0.18),transparent_30%),linear-gradient(180deg,#ffffff_0%,#fdf3f6_100%)]">
                <div class="mx-auto grid max-w-7xl items-center gap-14 px-4 py-16 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-24">
                    <div class="relative z-10">
                        <span class="inline-flex items-center rounded-full border border-[#F3AEC0] bg-[#FDF3F6] px-3 py-1 text-[11px] font-bold uppercase tracking-[0.22em] text-[#2C4A6E]">
                            SIGADIS
                        </span>
                        <h1 class="mt-6 max-w-xl text-4xl font-black leading-tight tracking-tight text-[#26292E] sm:text-5xl lg:text-6xl">
                            Kesehatan Ibu Lebih Terhubung, Bantuan Lebih Cepat.
                        </h1>
                        <p class="mt-6 max-w-xl text-lg leading-8 text-[#3D6086]">
                            SIGADIS adalah platform pendampingan kesehatan ibu yang menghubungkan ibu dengan bidan dan kader serta membantu mempercepat respons dalam kondisi darurat.
                        </p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <Link href="/register" class="btn h-12 rounded-2xl bg-[#2C4A6E] px-7 text-base font-semibold text-white shadow-lg shadow-[#2C4A6E]/20 hover:bg-[#3D6086]">
                                Daftar Sekarang
                            </Link>
                            <a href="https://dev-sigadis-beta.rf.gd/application/SIGADIS.apk" target="_blank" rel="noopener noreferrer" class="btn h-12 rounded-2xl border border-[#2C4A6E]/20 bg-white px-7 text-base font-semibold text-[#2C4A6E] hover:border-[#F3AEC0] hover:bg-[#FDF3F6]">
                                Download APK
                            </a>
                        </div>

                        <div class="mt-8 flex items-center gap-3 rounded-[1.5rem] border border-[#F3AEC0]/60 bg-[#F7EFE4] p-4 shadow-sm">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#E6F4EC] text-xl text-[#4C9A6E]">✓</div>
                            <p class="text-sm font-medium text-[#26292E]">
                                Satu aplikasi untuk pendampingan, pemantauan, dan respons darurat ibu.
                            </p>
                        </div>
                    </div>

                    <div class="relative flex justify-center">
                        <div class="absolute -inset-10 rounded-[3rem] bg-gradient-to-br from-[#F3AEC0]/40 via-[#F7EFE4] to-[#DDEAF8]/60 blur-2xl"></div>

                        <!-- Mascot: pose 19, "memperkenalkan diri" — introduces the product on first contact -->
                        <img
                            :src="mascot(19)"
                            alt="Maskot SIGADIS memperkenalkan diri"
                            class="absolute -left-4 bottom-2 z-20 w-28 drop-shadow-xl sm:w-36 lg:-left-10 lg:w-44"
                        />

                        <div class="relative w-full max-w-md rounded-[2.2rem] border border-[#2C4A6E]/10 bg-[#2C4A6E] p-3 shadow-[0_30px_80px_rgba(44,74,110,0.18)]">
                            <div class="rounded-[1.7rem] bg-gradient-to-br from-[#2C4A6E] via-[#335A82] to-[#1E3150] p-5">
                                <div class="mb-4 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="h-3 w-3 rounded-full bg-[#F3AEC0]"></span>
                                        <span class="h-3 w-3 rounded-full bg-[#E0A030]"></span>
                                        <span class="h-3 w-3 rounded-full bg-[#4C9A6E]"></span>
                                    </div>
                                    <span class="rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.22em] text-white">SIGADIS</span>
                                </div>

                                <div class="rounded-[1.5rem] bg-white p-4 shadow-inner">
                                    <div class="mb-4 flex items-center justify-between">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#3D6086]">Dashboard</p>
                                            <h3 class="mt-1 text-xl font-black text-[#26292E]">Ibu Sehat</h3>
                                        </div>
                                        <div class="rounded-full bg-[#FBE4E5] px-2.5 py-1 text-xs font-bold text-[#D64550]">Darurat</div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="rounded-2xl bg-[#EAF4FF] p-3">
                                            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#3D6086]">Lokasi</p>
                                            <p class="mt-2 text-sm font-bold text-[#26292E]">Aktif</p>
                                        </div>
                                        <div class="rounded-2xl bg-[#E6F4EC] p-3">
                                            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#4C9A6E]">Status</p>
                                            <p class="mt-2 text-sm font-bold text-[#26292E]">Terpantau</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 rounded-2xl bg-[#F2F3F5] p-3">
                                        <div class="mb-2 flex items-center justify-between text-xs font-semibold uppercase tracking-[0.16em] text-[#3D6086]">
                                            <span>Hari Ini</span>
                                            <span>4/5</span>
                                        </div>
                                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-[#E5E7EB]">
                                            <div class="h-full w-[80%] rounded-full bg-gradient-to-r from-[#2C4A6E] via-[#F3AEC0] to-[#4C9A6E]"></div>
                                        </div>
                                    </div>

                                    <button class="mt-5 flex h-12 w-full items-center justify-center rounded-2xl bg-[#C81E2C] text-base font-black text-white shadow-lg shadow-[#C81E2C]/25">
                                        DARURAT
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- PROBLEM -->
            <section id="tentang" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-[#2C4A6E]">Kenapa SIGADIS?</p>
                    <h2 class="mt-4 text-3xl font-black tracking-tight text-[#26292E] sm:text-4xl">
                        Ketika Kondisi Darurat Terjadi, Setiap Menit Berarti
                    </h2>
                </div>
                <div class="mt-12 grid gap-6 md:grid-cols-2">
                    <article v-for="problem in problems" :key="problem.title" class="rounded-3xl border border-[#F3AEC0]/40 bg-white p-6 shadow-sm">
                        <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-2xl bg-[#FBE4E5] text-[#D64550]">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-[#26292E]">{{ problem.title }}</h3>
                        <p class="mt-3 text-base leading-7 text-[#3D6086]">{{ problem.description }}</p>
                    </article>
                </div>
            </section>

            <!-- SOLUTION -->
            <section class="bg-[#F7EFE4] py-20">
                <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-[1fr_0.85fr] lg:px-8">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.22em] text-[#2C4A6E]">Solusi</p>
                        <h2 class="mt-4 text-3xl font-black tracking-tight text-[#26292E] sm:text-4xl">
                            SIGADIS Menghubungkan Ibu dengan Bantuan yang Dibutuhkan
                        </h2>
                        <p class="mt-6 max-w-xl text-lg leading-8 text-[#3D6086]">
                            SIGADIS (Sistem Gawat Darurat Ibu-Selamat) merupakan platform digital yang dirancang untuk mendukung pendampingan kesehatan ibu melalui integrasi informasi, komunikasi dengan bidan/kader, pemantauan kondisi, serta mekanisme respons darurat berbasis lokasi.
                        </p>
                    </div>

                    <div class="relative flex justify-center">
                        <!-- Theme photo slot: drop a real photo of an ibu with her bidan/kader here.
                             Path below is a placeholder — until the file exists the soft gradient card
                             still looks intentional, so nothing breaks. -->
                        <div
                            class="relative flex h-72 w-full max-w-sm items-end justify-center overflow-hidden rounded-[2rem] border border-[#F3AEC0]/50 bg-gradient-to-br from-[#FDF3F6] via-[#F3AEC0]/30 to-[#DDEAF8] bg-cover bg-center shadow-sm"
                            style="background-image: url('/assets/images/theme-ibu-bidan.jpg')"
                        >
                            <img :src="mascot(2)" alt="Maskot SIGADIS menjelaskan alur layanan" class="relative z-10 mb-4 w-32 drop-shadow-lg" />
                        </div>
                    </div>
                </div>

                <div class="mx-auto mt-14 max-w-4xl px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col items-center gap-3 rounded-[2rem] border border-[#F3AEC0]/40 bg-white p-8 shadow-sm sm:flex-row sm:justify-between sm:gap-2">
                        <div class="rounded-2xl bg-[#FDF3F6] px-5 py-3 text-lg font-bold text-[#2C4A6E]">Ibu</div>
                        <span class="text-2xl text-[#F3AEC0]">→</span>
                        <div class="rounded-2xl bg-[#2C4A6E] px-5 py-3 text-lg font-bold text-white">SIGADIS</div>
                        <span class="text-2xl text-[#F3AEC0]">→</span>
                        <div class="rounded-2xl bg-[#E6F4EC] px-5 py-3 text-lg font-bold text-[#4C9A6E]">Bidan / Kader</div>
                        <span class="text-2xl text-[#F3AEC0]">→</span>
                        <div class="rounded-2xl bg-[#F7EFE4] px-5 py-3 text-lg font-bold text-[#2C4A6E]">Bantuan</div>
                    </div>
                </div>
            </section>

            <!-- FEATURES -->
            <section id="fitur" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-[#2C4A6E]">Fitur utama</p>
                    <h2 class="mt-4 text-3xl font-black tracking-tight text-[#26292E] sm:text-4xl">
                        Membantu Ibu Tetap Terpantau dan Terhubung
                    </h2>
                </div>
                <div class="mt-12 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <article v-for="feature in features" :key="feature.title" class="rounded-3xl border border-[#F3AEC0]/40 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                        <div class="mb-5 flex items-center justify-between">
                            <span class="text-sm font-black tracking-[0.18em] text-[#2C4A6E]">{{ feature.number }}</span>
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#FDF3F6] text-[#2C4A6E] [&_svg]:h-5 [&_svg]:w-5"
                                v-html="featureIcons[feature.number]"
                            ></span>
                        </div>
                        <h3 class="text-2xl font-bold text-[#26292E]">{{ feature.title }}</h3>
                        <p class="mt-3 text-base leading-7 text-[#3D6086]">{{ feature.description }}</p>
                    </article>
                </div>
            </section>

            <!-- EMERGENCY (signature section) -->
            <section class="relative overflow-hidden bg-[#2C4A6E] py-20">
                <div class="pointer-events-none absolute -right-16 -top-16 h-72 w-72 rounded-full bg-[#C81E2C]/20 blur-3xl"></div>
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid items-center gap-12 lg:grid-cols-[0.9fr_1.1fr]">
                        <div class="text-center lg:text-left">
                            <p class="text-sm font-bold uppercase tracking-[0.22em] text-[#F3AEC0]">Emergency</p>
                            <h2 class="mt-4 text-3xl font-black tracking-tight text-white sm:text-4xl">
                                Dalam Keadaan Darurat, Satu Sentuhan Bisa Menjadi Awal Pertolongan
                            </h2>
                            <div class="relative mx-auto mt-8 flex h-40 w-40 items-center justify-center lg:mx-0">
                                <span class="pulse-dot absolute inset-0"></span>
                                <img :src="mascot(9)" alt="Maskot SIGADIS siaga penuh saat darurat" class="relative z-10 w-36 drop-shadow-2xl" />
                            </div>
                        </div>

                        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-8 backdrop-blur-sm">
                            <div class="grid gap-4 sm:grid-cols-5 sm:items-center">
                                <template v-for="(step, index) in emergencyFlow" :key="step.label">
                                    <div class="flex items-center gap-4 sm:flex-col sm:text-center">
                                        <div :class="[
                                            'flex h-16 w-16 items-center justify-center rounded-2xl text-xs font-black shadow-sm',
                                            index === 0 ? 'bg-[#C81E2C] text-white' : 'bg-white/10 text-white'
                                        ]">
                                            {{ step.label }}
                                        </div>
                                        <div v-if="index < emergencyFlow.length - 1" class="hidden h-px w-10 bg-white/20 sm:block"></div>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-8 border-t border-white/10 pt-6">
                                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#F3AEC0]">Informasi yang dibawa laporan</p>
                                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <p class="text-xs uppercase tracking-[0.2em] text-[#DDEAF8]">Identitas</p>
                                        <p class="mt-1 font-semibold text-white">Identitas pengguna</p>
                                    </div>
                                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <p class="text-xs uppercase tracking-[0.2em] text-[#DDEAF8]">Waktu</p>
                                        <p class="mt-1 font-semibold text-white">Waktu kejadian</p>
                                    </div>
                                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <p class="text-xs uppercase tracking-[0.2em] text-[#DDEAF8]">Lokasi</p>
                                        <p class="mt-1 font-semibold text-white">Lokasi</p>
                                    </div>
                                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <p class="text-xs uppercase tracking-[0.2em] text-[#DDEAF8]">Status</p>
                                        <p class="mt-1 font-semibold text-white">Status laporan</p>
                                    </div>
                                </div>
                                <p class="mt-5 text-sm leading-6 text-[#C9D8E8]">
                                    SIGADIS mempercepat penyampaian informasi ke bidan/kader — bukan jaminan waktu tiba bantuan tertentu.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- HOW IT WORKS -->
            <section id="cara-kerja" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-[#2C4A6E]">Cara kerja</p>
                    <h2 class="mt-4 text-3xl font-black tracking-tight text-[#26292E] sm:text-4xl">
                        Bagaimana SIGADIS Bekerja?
                    </h2>
                </div>
                <div class="mt-12 grid gap-6 lg:grid-cols-4">
                    <article v-for="step in steps" :key="step.number" class="relative overflow-hidden rounded-3xl border border-[#F3AEC0]/40 bg-white p-6 pt-20 shadow-sm">
                        <img :src="step.mascot" :alt="step.mascotAlt" class="absolute -top-1 right-2 w-24" />
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#EAF4FF] text-lg font-black text-[#2C4A6E]">
                            {{ step.number }}
                        </div>
                        <h3 class="text-xl font-bold text-[#26292E]">{{ step.title }}</h3>
                        <p class="mt-3 text-base leading-7 text-[#3D6086]">{{ step.description }}</p>
                    </article>
                </div>
            </section>

            <!-- ROLES -->
            <section class="bg-[#F7EFE4] py-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-sm font-bold uppercase tracking-[0.22em] text-[#2C4A6E]">Untuk siapa?</p>
                        <h2 class="mt-4 text-3xl font-black tracking-tight text-[#26292E] sm:text-4xl">
                            SIGADIS Dirancang untuk Kebutuhan Bersama
                        </h2>
                    </div>

                    <div class="mt-12 grid gap-8 lg:grid-cols-3">
                        <div v-for="role in roles" :key="role.name" class="rounded-[2rem] border border-[#F3AEC0]/40 bg-white p-8 text-center shadow-sm">
                            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-[#2C4A6E] to-[#F3AEC0] shadow-lg shadow-[#F3AEC0]/20">
                                <img v-if="role.mascot" :src="role.mascot" :alt="role.mascotAlt" class="h-16 w-16 object-contain" />
                                <span v-else class="text-white [&_svg]:h-8 [&_svg]:w-8" v-html="role.icon"></span>
                            </div>
                            <h3 class="mt-6 text-2xl font-black text-[#26292E]">{{ role.name }}</h3>
                            <p class="mt-4 text-base leading-7 text-[#3D6086]">{{ role.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ADVANTAGES -->
            <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-[#2C4A6E]">Keunggulan</p>
                    <h2 class="mt-4 text-3xl font-black tracking-tight text-[#26292E] sm:text-4xl">
                        Solusi yang Lebih Terintegrasi dan Responsif
                    </h2>
                </div>
                <div class="mt-12 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                    <article v-for="advantage in advantages" :key="advantage.title" class="rounded-3xl border border-[#F3AEC0]/40 bg-white p-6 shadow-sm">
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#E6F4EC] text-2xl text-[#4C9A6E]">✓</div>
                        <h3 class="text-xl font-bold text-[#26292E]">{{ advantage.title }}</h3>
                        <p class="mt-3 text-base leading-7 text-[#3D6086]">{{ advantage.description }}</p>
                    </article>
                </div>
            </section>

            <!-- DOWNLOAD APK -->
            <section id="download-apk" class="bg-[#2C4A6E] py-20 text-white">
                <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:px-8">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.22em] text-[#F3AEC0]">Download APK</p>
                        <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl text-white">
                            Gunakan SIGADIS di Smartphone Anda
                        </h2>
                        <p class="mt-6 max-w-xl text-lg leading-8 text-[#E5EEF8]">
                            Dapatkan pengalaman SIGADIS secara lengkap melalui aplikasi mobile.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="https://dev-sigadis-beta.rf.gd/application/SIGADIS.apk" target="_blank" rel="noopener noreferrer" class="btn h-12 rounded-2xl bg-[#F3AEC0] px-7 text-base font-semibold text-[#2C4A6E] hover:bg-[#ECA7BA]">
                                Download APK
                            </a>
                        </div>
                    </div>

                    <div class="relative flex justify-center">
                        <img :src="mascot(20)" alt="Maskot SIGADIS memberi jempol" class="absolute -left-2 bottom-4 z-20 w-24 drop-shadow-xl sm:w-28 lg:-left-8 lg:w-32" />
                        <div class="w-full max-w-sm rounded-[2.5rem] border border-white/10 bg-[#1F3551] p-4 shadow-[0_30px_80px_rgba(22,36,55,0.45)]">
                            <div class="rounded-[2rem] bg-gradient-to-br from-[#2C4A6E] to-[#17304F] p-6">
                                <div class="mb-6 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="h-3 w-3 rounded-full bg-[#F3AEC0]"></span>
                                        <span class="h-3 w-3 rounded-full bg-[#E0A030]"></span>
                                        <span class="h-3 w-3 rounded-full bg-[#4C9A6E]"></span>
                                    </div>
                                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#DDEAF8]">SIGADIS</span>
                                </div>

                                <div class="rounded-[1.7rem] bg-white p-5 text-[#26292E]">
                                    <div class="mb-4 flex items-center justify-between">
                                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-[#3D6086]">Dashboard</span>
                                        <span class="rounded-full bg-[#E6F4EC] px-2 py-1 text-[10px] font-bold text-[#4C9A6E]">Aktif</span>
                                    </div>
                                    <div class="rounded-2xl bg-[#F2F3F5] p-4">
                                        <p class="text-sm font-bold text-[#3D6086]">Selamat datang</p>
                                        <p class="mt-2 text-3xl font-black text-[#26292E]">Ibu</p>
                                    </div>
                                    <button class="mt-5 flex h-14 w-full items-center justify-center rounded-2xl bg-[#C81E2C] text-lg font-black text-white shadow-lg shadow-[#C81E2C]/25">
                                        DARURAT
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ -->
            <section id="faq" class="mx-auto max-w-5xl px-4 py-20 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-[#2C4A6E]">FAQ</p>
                    <h2 class="mt-4 text-3xl font-black tracking-tight text-[#26292E] sm:text-4xl">
                        Pertanyaan yang Sering Diajukan
                    </h2>
                </div>

                <div class="mt-12 space-y-4">
                    <div v-for="item in faqs" :key="item.question" class="collapse collapse-arrow rounded-2xl border border-[#F3AEC0]/40 bg-white shadow-sm">
                        <input type="checkbox" />
                        <div class="collapse-title text-lg font-semibold text-[#26292E]">{{ item.question }}</div>
                        <div class="collapse-content text-base leading-7 text-[#3D6086]">
                            <p>{{ item.answer }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FINAL CTA -->
            <section class="px-4 pb-20 sm:px-6 lg:px-8">
                <div class="relative mx-auto max-w-5xl overflow-hidden rounded-[2.5rem] bg-gradient-to-r from-[#2C4A6E] via-[#3D6086] to-[#F3AEC0] p-8 text-center text-white shadow-[0_30px_80px_rgba(44,74,110,0.25)] sm:p-12">
                    <img :src="mascot(14)" alt="Maskot SIGADIS merayakan" class="mx-auto w-28 drop-shadow-xl sm:w-32" />
                    <p class="mt-4 text-sm font-bold uppercase tracking-[0.22em] text-[#F7EFE4]">Mulai sekarang</p>
                    <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl text-white">
                        Mulai Gunakan SIGADIS
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg leading-8 text-[#F3F6FB]">
                        Daftarkan diri Anda dan gunakan aplikasi SIGADIS untuk mendapatkan pendampingan yang lebih terhubung.
                    </p>
                    <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                        <Link href="/register" class="btn h-12 rounded-2xl bg-white px-7 text-base font-semibold text-[#2C4A6E] hover:bg-[#F2F3F5]">
                            Daftar Sekarang
                        </Link>
                        <a href="https://dev-sigadis-beta.rf.gd/application/SIGADIS.apk" target="_blank" rel="noopener noreferrer" class="btn h-12 rounded-2xl border border-white/40 bg-transparent px-7 text-base font-semibold text-white hover:bg-white/10">
                            Download APK
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-[#F3AEC0]/40 bg-[#F7EFE4]">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1.2fr_0.8fr_0.8fr_1fr] lg:px-8">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl border border-[#F3AEC0]/60 bg-[#FDF3F6]">
                            <img :src="logoUrl" alt="Logo SIGADIS" class="h-8 w-8 object-contain" />
                        </div>
                        <p class="text-xl font-black tracking-tight text-[#2C4A6E]">SIGADIS</p>
                    </div>
                    <p class="mt-4 text-base leading-7 text-[#3D6086]">Sistem Gawat Darurat Ibu-Selamat</p>
                </div>

                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-[#3D6086]">Navigasi</p>
                    <ul class="mt-4 space-y-3 text-[#2C4A6E]">
                        <li><a href="#tentang" class="hover:text-[#3D6086]">Tentang</a></li>
                        <li><a href="#fitur" class="hover:text-[#3D6086]">Fitur</a></li>
                        <li><a href="#cara-kerja" class="hover:text-[#3D6086]">Cara Kerja</a></li>
                        <li><a href="#faq" class="hover:text-[#3D6086]">FAQ</a></li>
                        <li><a href="#download-apk" class="hover:text-[#3D6086]">Download APK</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-[#3D6086]">Kontak</p>
                    <ul class="mt-4 space-y-3 text-[#2C4A6E]">
                        <li>Email</li>
                        <li>Telepon</li>
                        <li>Alamat</li>
                    </ul>
                </div>

                <div class="lg:text-right">
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-[#3D6086]">Ikuti</p>
                    <div class="mt-4 flex gap-3 lg:justify-end">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-[#F3AEC0]/60 bg-white text-[#2C4A6E]">f</div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-[#F3AEC0]/60 bg-white text-[#2C4A6E]">in</div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-[#F3AEC0]/60 bg-white text-[#2C4A6E]">◎</div>
                    </div>
                </div>
            </div>

            <div class="border-t border-[#F3AEC0]/40">
                <div class="mx-auto max-w-7xl px-4 py-6 text-center text-sm text-[#3D6086] sm:px-6 lg:px-8">
                    © 2026 SIGADIS. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>
