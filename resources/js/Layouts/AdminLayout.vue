<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import ToastNotification from '@/Components/ToastNotification.vue';

const page = usePage();
const authAdmin = computed(() => page.props.auth?.admin || {});
const pendingCount = computed(() => page.props.pendingCount ?? 0);

const isSidebarOpen = ref(false);
const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

// Real-time clock
const currentTime = ref('');
const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
};

let timer = null;
onMounted(() => {
    updateTime();
    timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

const navigation = [
    {
        name: 'Ringkasan Utama',
        href: route('admin.dashboard'),
        icon: 'dashboard',
        active: route().current('admin.dashboard'),
        badge: null,
    },
    {
        name: 'Verifikasi Nakes',
        href: route('admin.verifikasi.index'),
        icon: 'verified_user',
        active: route().current('admin.verifikasi.*'),
        badge: pendingCount,
    },
    {
        name: 'Zonasi & Wilayah',
        href: route('admin.zonasi.index'),
        icon: 'map',
        active: route().current('admin.zonasi.*'),
        badge: null,
    },
    {
        name: 'Fasilitas Kesehatan',
        href: route('admin.fasilitas.index'),
        icon: 'local_hospital',
        active: route().current('admin.fasilitas.*'),
        badge: null,
    },
    {
        name: 'Bank Soal Skrining',
        href: route('admin.bank-soal.index'),
        icon: 'quiz',
        active: route().current('admin.bank-soal.*'),
        badge: null,
    },
    {
        name: 'Pemulihan Akun',
        href: route('admin.ganti-nomor.index'),
        icon: 'manage_accounts',
        active: route().current('admin.ganti-nomor.*'),
        badge: null,
    },
    {
        name: 'Laporan & Metrik',
        href: route('admin.reporting.index'),
        icon: 'analytics',
        active: route().current('admin.reporting.*'),
        badge: null,
    },
    {
        name: 'Pengaturan Sistem',
        href: route('admin.pengaturan.index'),
        icon: 'settings',
        active: route().current('admin.pengaturan.*'),
        badge: null,
    },
];

const logout = () => {
    router.post(route('auth.admin.logout'));
};
</script>

<template>
    <div class="min-h-screen bg-[#F4F5F8] font-sans text-[#26292E] flex flex-col antialiased">
        <!-- Floating Pop-up Toast Notifications (Pojok Kanan Atas) -->
        <ToastNotification />

        <!-- Top App Bar / Redesigned Navbar (Sticky) -->
        <header class="bg-linear-to-r from-[#0F2844] via-[#123356] to-[#1A3D63] text-white w-full h-17 flex items-center justify-between px-4 lg:px-7 shadow-lg z-40 sticky top-0 border-b border-white/10 backdrop-blur-md">
            <!-- Left Brand & Sidebar Toggle -->
            <div class="flex items-center gap-3.5">
                <!-- Hamburger Menu for Mobile / Tablet -->
                <button
                    type="button"
                    @click="toggleSidebar"
                    class="lg:hidden p-2 rounded-2xl text-white/80 hover:text-white hover:bg-white/10 transition-colors focus:outline-none"
                    aria-label="Buka Menu"
                >
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>

                <!-- Brand Logo With Official Mascot Icon -->
                <Link :href="route('admin.dashboard')" class="flex items-center gap-3 group">
                    <div class="relative flex items-center justify-center">
                        <!-- Glowing mascot container -->
                        <div class="w-11 h-11 rounded-2xl bg-linear-to-br from-[#2C4A6E] to-[#0D223A] border-2 border-[#F3AEC0]/40 p-1 flex items-center justify-center shadow-md group-hover:scale-105 group-hover:border-[#F3AEC0] transition-all overflow-hidden">
                            <img
                                src="/assets/mascot/mascot-pose-21.webp"
                                alt="Maskot SIGADIS"
                                class="w-full h-full object-contain filter drop-shadow-sm transition-transform duration-300 group-hover:scale-110"
                                loading="eager"
                            />
                        </div>
                        <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-[#123356] shadow-xs"></span>
                    </div>

                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <span class="font-black text-xl text-white tracking-wider font-display">SIGADIS</span>
                            <span class="px-2 py-0.5 rounded-full bg-[#F3AEC0]/20 border border-[#F3AEC0]/40 text-[10px] font-extrabold text-[#F3AEC0] uppercase tracking-wider">
                                ADMIN
                            </span>
                        </div>
                        <span class="text-[10px] text-white/70 font-medium tracking-tight hidden sm:block">
                            Sistem Informasi Gawat Darurat Ibu & Anak
                        </span>
                    </div>
                </Link>
            </div>

            <!-- Center: Live Status & Clock -->
            <div class="hidden xl:flex items-center gap-4">
                <div class="flex items-center gap-2.5 px-3.5 py-1.5 rounded-2xl bg-white/10 border border-white/10 text-white/90 text-xs backdrop-blur-xs shadow-inner">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-semibold text-[11px] text-emerald-300">Server Online</span>
                    <span class="text-white/40">|</span>
                    <span class="font-mono text-[11px] text-white/80">{{ currentTime }}</span>
                </div>
            </div>

            <!-- Right Actions & User Profile -->
            <div class="flex items-center gap-3 sm:gap-4">
                <!-- Institution Badge -->
                <div class="hidden md:flex items-center gap-2 px-3.5 py-1.5 rounded-2xl bg-white/10 border border-white/10 text-white/90 text-xs font-semibold backdrop-blur-xs">
                    <span class="material-symbols-outlined text-base text-[#F3AEC0]">domain</span>
                    <span class="max-w-[200px] truncate">{{ authAdmin.institution || 'Puskesmas Sungai Raya' }}</span>
                </div>

                <!-- Admin Profile Card & Logout -->
                <div class="flex items-center gap-3 border-l border-white/15 pl-3 sm:pl-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-linear-to-br from-[#F3AEC0] to-[#E0703D] text-[#123356] font-extrabold text-sm flex items-center justify-center shadow-xs border border-white/30">
                            {{ (authAdmin.full_name || 'A').charAt(0).toUpperCase() }}
                        </div>
                        <div class="hidden sm:flex flex-col text-left">
                            <span class="text-xs font-bold text-white leading-tight max-w-[140px] truncate">
                                {{ authAdmin.full_name || 'Administrator' }}
                            </span>
                            <span class="text-[10px] text-white/60 font-mono leading-tight">
                                {{ authAdmin.email || 'admin@sigadis.test' }}
                            </span>
                        </div>
                    </div>

                    <!-- Logout Button -->
                    <button
                        type="button"
                        @click="logout"
                        class="p-2 sm:px-3 sm:py-1.5 rounded-xl bg-white/10 hover:bg-rose-500/25 text-white/90 hover:text-white border border-white/15 transition-all flex items-center gap-1.5 text-xs font-bold shadow-xs active:scale-95 cursor-pointer"
                        title="Keluar dari Panel Admin"
                    >
                        <span class="material-symbols-outlined text-base text-rose-300">logout</span>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Body Container with Sticky Sidebar -->
        <div class="flex-1 flex relative">
            <!-- Sidebar Overlay for Mobile -->
            <div
                v-if="isSidebarOpen"
                @click="isSidebarOpen = false"
                class="fixed inset-0 bg-black/50 backdrop-blur-xs z-40 lg:hidden transition-opacity"
            ></div>

            <!-- Sidebar Navigation (Sticky on Desktop) -->
            <aside
                :class="[
                    'fixed lg:sticky top-17 h-[calc(100vh-4.25rem)] w-64 bg-white border-r border-[#E3E2E5] flex flex-col justify-between overflow-y-auto shrink-0 z-30 transition-transform duration-300 ease-in-out shadow-xl lg:shadow-none',
                    isSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
                ]"
            >
                <div class="p-4 space-y-1.5">
                    <div class="px-3.5 py-2 text-[11px] font-extrabold text-[#73777F] uppercase tracking-wider flex items-center justify-between">
                        <span>Menu Navigasi</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-[#123356]"></span>
                    </div>

                    <nav class="space-y-1" aria-label="Menu Admin">
                        <Link
                            v-for="item in navigation"
                            :key="item.name"
                            :href="item.href"
                            @click="isSidebarOpen = false"
                            :class="[
                                'flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition-all group',
                                item.active
                                    ? 'bg-[#123356] text-white shadow-md'
                                    : 'text-[#43474E] hover:bg-[#FAF9FC] hover:text-[#123356]'
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    :class="[
                                        'material-symbols-outlined text-xl transition-colors',
                                        item.active ? 'text-[#F3AEC0]' : 'text-[#73777F] group-hover:text-[#123356]'
                                    ]"
                                    :style="item.active ? 'font-variation-settings: \'FILL\' 1;' : ''"
                                >
                                    {{ item.icon }}
                                </span>
                                <span>{{ item.name }}</span>
                            </div>

                            <!-- Badge Counter (e.g. pending verification) -->
                            <span
                                v-if="item.badge && item.badge > 0"
                                class="px-2 py-0.5 rounded-full text-[10px] font-black bg-[#E0703D] text-white shadow-xs animate-pulse"
                            >
                                {{ item.badge }}
                            </span>
                        </Link>
                    </nav>
                </div>

                <!-- Sidebar Footer Information -->
                <div class="p-4 border-t border-[#E3E2E5] bg-[#FAF9FC]">
                    <div class="flex items-center justify-between text-xs text-[#73777F]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="font-bold text-[#26292E] text-[11px]">SIGADIS Enterprise</span>
                        </div>
                        <span class="text-[10px] font-mono text-[#8A8D96]">v2.0 Beta</span>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 min-w-0">
                <slot />
            </main>
        </div>
    </div>
</template>
