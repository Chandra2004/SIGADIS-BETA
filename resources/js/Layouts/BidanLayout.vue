<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, useForm, router, usePage } from '@inertiajs/vue3';
import ToastNotification from '@/Components/ToastNotification.vue';
import ModalBox from '@/Components/ModalBox.vue';

const page = usePage();
const worker = computed(() => page.props.auth?.user || page.props.worker || {});

const isSidebarOpen = ref(false);
const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

// Real-time clock (Identik dengan Admin Navbar)
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

// State: Availability / Cuti Modal
const isAvailabilityModalOpen = ref(false);
const isSubmittingAvailability = ref(false);

const leaveForm = useForm({
    unavailable_from: new Date().toISOString().split('T')[0],
    unavailable_until: '',
    reason: '',
});

const openAvailabilityModal = () => {
    leaveForm.reset();
    leaveForm.unavailable_from = new Date().toISOString().split('T')[0];
    isAvailabilityModalOpen.value = true;
};

const deactivateDuty = () => {
    isSubmittingAvailability.value = true;
    leaveForm.post(route('bidan.availability.deactivate'), {
        onFinish: () => {
            isSubmittingAvailability.value = false;
            isAvailabilityModalOpen.value = false;
        },
    });
};

const reactivateDuty = () => {
    isSubmittingAvailability.value = true;
    router.post(route('bidan.availability.reactivate'), {}, {
        onFinish: () => {
            isSubmittingAvailability.value = false;
        },
    });
};

const logout = () => {
    router.post(route('auth.staff.logout'));
};

const activeAlertsCount = computed(() => page.props.activeAlertsCount ?? page.props.activeCount ?? 0);
const unreadNotificationCount = computed(() => page.props.unreadNotificationCount ?? 0);

const navigation = [
    {
        name: 'Dashboard Monitoring',
        href: route('bidan.dashboard'),
        icon: 'dashboard',
        active: route().current('bidan.dashboard') && (!route().params?.filter || route().params?.filter === 'semua'),
        badge: null,
    },
    {
        name: 'Pusat Kasus Darurat',
        href: route('bidan.alerts.index'),
        icon: 'emergency',
        active: route().current('bidan.alerts.*'),
        badge: activeAlertsCount,
    },
    {
        name: 'Pasien Risiko Tinggi',
        href: route('bidan.dashboard', { filter: 'tinggi' }),
        icon: 'warning',
        active: route().current('bidan.dashboard') && route().params?.filter === 'tinggi',
        badge: null,
    },
    {
        name: 'Masa Nifas (42 Hari)',
        href: route('bidan.dashboard', { filter: 'nifas' }),
        icon: 'child_care',
        active: route().current('bidan.dashboard') && route().params?.filter === 'nifas',
        badge: null,
    },
    {
        name: 'Fasilitas & Rujukan PONEK',
        href: route('bidan.referrals.index'),
        icon: 'local_hospital',
        active: route().current('bidan.referrals.*'),
        badge: null,
    },
    {
        name: 'Kontrol Ketersediaan & Cuti',
        href: route('bidan.availability.index'),
        icon: 'event_available',
        active: route().current('bidan.availability.index'),
        badge: null,
    },
    {
        name: 'Pusat Notifikasi',
        href: route('bidan.notifications.index'),
        icon: 'notifications',
        active: route().current('bidan.notifications.*'),
        badge: unreadNotificationCount,
    },
    {
        name: 'Profil & Pengaturan Akun',
        href: route('bidan.profile.index'),
        icon: 'badge',
        active: route().current('bidan.profile.*'),
        badge: null,
    },
];
</script>

<template>
    <div class="min-h-screen bg-[#F4F5F8] font-sans text-[#26292E] flex flex-col antialiased">
        <!-- Floating Pop-up Toast Notifications (Pojok Kanan Atas) -->
        <ToastNotification />

        <!-- 1. Top App Bar / Redesigned Navbar (100% Identik dengan Dashboard Admin) -->
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
                <Link :href="route('bidan.dashboard')" class="flex items-center gap-3 group">
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
                        <span
                            :class="[
                                'absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-[#123356] shadow-xs',
                                worker.is_available !== false ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'
                            ]"
                        ></span>
                    </div>

                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <span class="font-black text-xl text-white tracking-wider font-display">SIGADIS</span>
                            <span class="px-2 py-0.5 rounded-full bg-[#F3AEC0]/20 border border-[#F3AEC0]/40 text-[10px] font-extrabold text-[#F3AEC0] uppercase tracking-wider">
                                {{ worker.role === 'kader' ? 'KADER' : 'BIDAN' }}
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
                    <span
                        :class="[
                            'w-2 h-2 rounded-full',
                            worker.is_available !== false ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400'
                        ]"
                    ></span>
                    <span class="font-semibold text-[11px] text-emerald-300">
                        {{ worker.is_available !== false ? 'Sedang Bertugas' : 'Mode Cuti' }}
                    </span>
                    <span class="text-white/40">|</span>
                    <span class="font-mono text-[11px] text-white/80">{{ currentTime }}</span>
                </div>
            </div>

            <!-- Right Actions & User Profile -->
            <div class="flex items-center gap-3 sm:gap-4">
                <!-- Notification Bell -->
                <Link
                    :href="route('bidan.notifications.index')"
                    class="relative p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition-all shadow-xs"
                    title="Pusat Notifikasi"
                >
                    <span class="material-symbols-outlined text-lg">notifications</span>
                    <span
                        v-if="unreadNotificationCount > 0"
                        class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-rose-600 text-white font-black text-[9px] flex items-center justify-center border-2 border-[#123356] animate-pulse"
                    >
                        {{ unreadNotificationCount > 9 ? '9+' : unreadNotificationCount }}
                    </span>
                </Link>

                <!-- Wilayah Penugasan Badge -->
                <div class="hidden md:flex items-center gap-2 px-3.5 py-1.5 rounded-2xl bg-white/10 border border-white/10 text-white/90 text-xs font-semibold backdrop-blur-xs">
                    <span class="material-symbols-outlined text-base text-[#F3AEC0]">health_and_safety</span>
                    <span class="max-w-[180px] truncate">{{ worker.region_code || 'Puskesmas Sungai Raya' }}</span>
                </div>

                <!-- Nakes Profile Card & Logout -->
                <div class="flex items-center gap-3 border-l border-white/15 pl-3 sm:pl-4">
                    <Link :href="route('bidan.profile.index')" class="flex items-center gap-2.5 hover:opacity-90 transition-opacity">
                        <div class="w-9 h-9 rounded-xl bg-linear-to-br from-[#F3AEC0] to-[#E0703D] text-[#123356] font-extrabold text-sm flex items-center justify-center shadow-xs border border-white/30">
                            {{ (worker.full_name || 'N').charAt(0).toUpperCase() }}
                        </div>
                        <div class="hidden sm:flex flex-col text-left">
                            <span class="text-xs font-bold text-white leading-tight max-w-[140px] truncate">
                                {{ worker.full_name || 'Tenaga Medis' }}
                            </span>
                            <span class="text-[10px] text-white/60 font-mono leading-tight uppercase">
                                {{ worker.role === 'kader' ? 'Kader Posyandu' : 'Bidan Pendamping' }}
                            </span>
                        </div>
                    </Link>

                    <!-- Logout Button -->
                    <button
                        type="button"
                        @click="logout"
                        class="p-2 sm:px-3 sm:py-1.5 rounded-xl bg-white/10 hover:bg-rose-500/25 text-white/90 hover:text-white border border-white/15 transition-all flex items-center gap-1.5 text-xs font-bold shadow-xs active:scale-95 cursor-pointer"
                        title="Keluar / Logout"
                    >
                        <span class="material-symbols-outlined text-base text-rose-300">logout</span>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- 2. Body Container with Sticky Sidebar (100% Identik dengan Dashboard Admin) -->
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
                        <span>Menu Navigasi Nakes</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-[#123356]"></span>
                    </div>

                    <nav class="space-y-1" aria-label="Menu Bidan">
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

                            <!-- Badge Counter (e.g. active emergency alerts) -->
                            <span
                                v-if="item.badge && item.badge > 0"
                                class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white shadow-xs animate-pulse"
                            >
                                {{ item.badge }}
                            </span>
                        </Link>
                    </nav>
                </div>

                <!-- Sidebar Footer: Status Kesiapan & Info Sistem -->
                <div class="p-4 border-t border-[#E3E2E5] bg-[#FAF9FC] space-y-3">
                    <div class="p-3 rounded-2xl bg-white border border-[#E3E2E5] space-y-2 shadow-2xs">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-[#26292E] text-[11px]">Kesiapan Darurat</span>
                            <span
                                :class="[
                                    'w-2 h-2 rounded-full',
                                    worker.is_available !== false ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'
                                ]"
                            ></span>
                        </div>
                        <p class="text-[10px] text-[#73777F] leading-tight">
                            {{ worker.is_available !== false ? 'Siaga menerima sinyal SOS darurat wilayah ini.' : 'Status cuti: alert dialihkan ke kader lain.' }}
                        </p>
                        <button
                            v-if="worker.is_available !== false"
                            type="button"
                            @click="openAvailabilityModal"
                            class="w-full py-1.5 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-[#123356] text-[11px] font-bold transition-colors cursor-pointer border border-[#E3E2E5]"
                        >
                            Set Mode Cuti
                        </button>
                        <button
                            v-else
                            type="button"
                            @click="reactivateDuty"
                            :disabled="isSubmittingAvailability"
                            class="w-full py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold transition-colors cursor-pointer"
                        >
                            Aktifkan Tugas Kembali
                        </button>
                    </div>

                    <div class="flex items-center justify-between text-xs text-[#73777F] pt-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="font-bold text-[#26292E] text-[11px]">SIGADIS Nakes</span>
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

        <!-- ModalBox: Pengajuan Mode Cuti / Berhalangan -->
        <ModalBox
            :show="isAvailabilityModalOpen"
            type="warning"
            title="Pengaturan Mode Cuti & Ketersediaan"
            message="Saat berstatus cuti, sistem otomatis mengalihkan panggilan darurat baru ke nakes pengganti agar respon pasien tidak tertunda."
            confirm-text="Aktifkan Mode Cuti"
            :confirm-disabled="isSubmittingAvailability"
            :loading="isSubmittingAvailability"
            @close="isAvailabilityModalOpen = false"
            @cancel="isAvailabilityModalOpen = false"
            @confirm="deactivateDuty"
        >
            <form @submit.prevent="deactivateDuty" class="space-y-3.5 pt-1">
                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">Batas Tanggal Cuti (Opsional)</label>
                    <input
                        v-model="leaveForm.unavailable_until"
                        type="date"
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    />
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">Alasan Cuti / Keterangan</label>
                    <textarea
                        v-model="leaveForm.reason"
                        rows="2"
                        placeholder="Contoh: Tugas dinas luar kota / Sakit / Cuti melahirkan"
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    ></textarea>
                </div>
            </form>
        </ModalBox>
    </div>
</template>
