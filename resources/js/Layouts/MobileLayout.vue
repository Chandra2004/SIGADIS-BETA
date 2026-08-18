<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import ToastNotification from '@/Components/ToastNotification.vue';

const props = defineProps({
    title: {
        type: String,
        default: 'SIGADIS Mobile',
    },
    activeTab: {
        type: String,
        default: 'home', // 'home', 'history', 'facilities', 'settings'
    },
    hideNav: {
        type: Boolean,
        default: false,
    },
    hideEmergencyFab: {
        type: Boolean,
        default: false,
    },
    motherName: {
        type: String,
        default: '',
    },
    allPregnancies: {
        type: Array,
        default: () => [],
    },
    activePregnancyId: {
        type: [Number, String],
        default: null,
    },
});

const page = usePage();

// State Modal Darurat & Fallback SMS
const showEmergencyModal = ref(false);
const showSmsFallback = ref(false);
const isLongPressing = ref(false);
let longPressTimer = null;

// State Modal Switcher Profil
const showProfileModal = ref(false);

// State Modal Logout
const showLogoutModal = ref(false);

// Status Jaringan / Offline Indicator
const isOnline = ref(navigator.onLine);
const showSyncToast = ref(false);

const updateOnlineStatus = () => {
    const wasOffline = !isOnline.value;
    isOnline.value = navigator.onLine;
    if (wasOffline && isOnline.value) {
        showSyncToast.value = true;
        setTimeout(() => {
            showSyncToast.value = false;
        }, 4000);
    }
};

onMounted(() => {
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
});

onUnmounted(() => {
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
});

// Aksi Darurat
const openEmergencyModal = () => {
    showEmergencyModal.value = true;
};

const handleEmergencyLongPressStart = () => {
    isLongPressing.value = true;
    longPressTimer = setTimeout(() => {
        // Long-press 2.5s langsung trigger konfirmasi darurat
        submitEmergency();
    }, 2500);
};

const handleEmergencyLongPressEnd = () => {
    isLongPressing.value = false;
    if (longPressTimer) {
        clearTimeout(longPressTimer);
        longPressTimer = null;
    }
};

const submitEmergency = () => {
    showEmergencyModal.value = false;
    if (!navigator.onLine) {
        showSmsFallback.value = true;
        return;
    }

    router.post(route('mobile.emergency.activate'), {}, {
        preserveScroll: true,
        onError: () => {
            showSmsFallback.value = true;
        },
    });
};

const sendSmsEmergency = () => {
    const text = encodeURIComponent(`[EMERGENCY SIGADIS] Tolong saya! Pasien atas nama ${props.motherName || 'Ibu Hamil'} membutuhkan bantuan darurat maternal.`);
    window.location.href = `sms:081234567890?body=${text}`;
    showSmsFallback.value = false;
};

// Switcher Profil
const selectPregnancy = (pregnancy) => {
    showProfileModal.value = false;
    router.post(route('mobile.profile.switch', pregnancy.id));
};

// Logout
const confirmLogout = () => {
    showLogoutModal.value = false;
    router.post(route('mobile.logout'));
};
</script>

<template>
    <div
        class="min-h-screen bg-[#FDF3F6] text-[#26292E] font-sans flex flex-col justify-between relative overflow-x-hidden select-none"
        :class="page.props.textSize === 'besar' ? 'text-[15px]' : 'text-xs'"
    >
        <Head :title="title" />

        <!-- Toast Notifications (Warning, Success, Error, Info) -->
        <ToastNotification />

        <!-- Offline Banner Indicator (Flows.md §20) -->
        <div
            v-if="!isOnline"
            class="sticky top-0 z-50 bg-[#C81E2C] text-white px-4 py-2 text-xs font-semibold flex items-center justify-between shadow-md animate-pulse"
        >
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-base">wifi_off</span>
                <span>Mode Offline — Data Anda tersimpan aman di perangkat.</span>
            </div>
            <button @click="showSmsFallback = true" class="underline text-[11px] font-bold">Bantuan SMS</button>
        </div>

        <!-- Sync Toast Notification saat kembali online -->
        <transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="-translate-y-full opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showSyncToast"
                class="fixed top-3 left-1/2 -translate-x-1/2 z-50 bg-[#4C9A6E] text-white px-4 py-2.5 rounded-full text-xs font-bold shadow-xl flex items-center gap-2"
            >
                <span class="material-symbols-outlined text-sm">sync</span>
                <span>Kembali Online — Menyinkronkan data skrining...</span>
            </div>
        </transition>

        <!-- Main Content Area (Immersive Native Mobile View Without Topbar) -->
        <main class="flex-1 max-w-md w-full mx-auto pb-28 px-4 pt-4">
            <slot />
        </main>

        <!-- Floating Action Button (FAB) Darurat SOS (Flows.md §8) -->
        <div
            v-if="!hideEmergencyFab"
            class="fixed bottom-22 right-4 z-40 flex flex-col items-end"
        >
            <button
                @click="openEmergencyModal"
                @touchstart="handleEmergencyLongPressStart"
                @touchend="handleEmergencyLongPressEnd"
                @mousedown="handleEmergencyLongPressStart"
                @mouseup="handleEmergencyLongPressEnd"
                class="w-16 h-16 rounded-full bg-linear-to-tr from-[#B71C1C] via-[#C81E2C] to-[#E53935] text-white shadow-[0_8px_25px_rgba(200,30,44,0.45)] border-2 border-white/40 flex flex-col items-center justify-center relative hover:scale-105 active:scale-95 transition-all focus:outline-none select-none"
                aria-label="Tombol Darurat SOS"
            >
                <!-- Pulse Ripple Rings -->
                <span class="absolute inset-0 rounded-full bg-[#FF4A57] animate-ping opacity-35 pointer-events-none"></span>
                <span class="absolute -inset-1.5 rounded-full border-2 border-[#FF4A57]/50 animate-pulse pointer-events-none"></span>

                <!-- Logo & Teks SOS Langsung (Tanpa Shield) -->
                <span class="text-xl font-black tracking-widest uppercase text-white drop-shadow-sm leading-none">SOS</span>
                <span class="text-[8px] font-extrabold tracking-wider text-red-100 uppercase opacity-95 mt-0.5">DARURAT</span>
            </button>
        </div>

        <!-- Bottom Navigation Bar (5 Menu: Beranda, Skrining, Riwayat, Faskes, Pengaturan) -->
        <nav
            v-if="!hideNav"
            class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-lg border-t border-[#F3AEC0]/40 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] px-1.5 py-1.5 flex items-center justify-between max-w-md mx-auto"
        >
            <!-- 1. Tab: Beranda -->
            <Link
                :href="route('mobile.dashboard')"
                class="flex-1 flex flex-col items-center justify-center py-1 px-1 rounded-2xl transition-all"
                :class="activeTab === 'home' ? 'text-[#123356] font-bold bg-[#FDF3F6]' : 'text-[#73777F] hover:text-[#123356]'"
            >
                <span class="material-symbols-outlined text-2xl" :style="activeTab === 'home' ? 'font-variation-settings: \'FILL\' 1;' : ''">home</span>
                <span class="text-[10px] mt-0.5 tracking-tight font-semibold">Beranda</span>
            </Link>

            <!-- 2. Tab: Skrining -->
            <Link
                :href="route('mobile.screening.index')"
                class="flex-1 flex flex-col items-center justify-center py-1 px-1 rounded-2xl transition-all"
                :class="activeTab === 'screening' ? 'text-[#123356] font-bold bg-[#FDF3F6]' : 'text-[#73777F] hover:text-[#123356]'"
            >
                <span class="material-symbols-outlined text-2xl" :style="activeTab === 'screening' ? 'font-variation-settings: \'FILL\' 1;' : ''">assignment_turned_in</span>
                <span class="text-[10px] mt-0.5 tracking-tight font-semibold">Skrining</span>
            </Link>

            <!-- 3. Tab: Riwayat -->
            <Link
                :href="route('mobile.history.index')"
                class="flex-1 flex flex-col items-center justify-center py-1 px-1 rounded-2xl transition-all"
                :class="activeTab === 'history' ? 'text-[#123356] font-bold bg-[#FDF3F6]' : 'text-[#73777F] hover:text-[#123356]'"
            >
                <span class="material-symbols-outlined text-2xl" :style="activeTab === 'history' ? 'font-variation-settings: \'FILL\' 1;' : ''">history_edu</span>
                <span class="text-[10px] mt-0.5 tracking-tight font-semibold">Riwayat</span>
            </Link>

            <!-- 4. Tab: Faskes -->
            <Link
                :href="route('mobile.facilities.index')"
                class="flex-1 flex flex-col items-center justify-center py-1 px-1 rounded-2xl transition-all"
                :class="activeTab === 'facilities' ? 'text-[#123356] font-bold bg-[#FDF3F6]' : 'text-[#73777F] hover:text-[#123356]'"
            >
                <span class="material-symbols-outlined text-2xl" :style="activeTab === 'facilities' ? 'font-variation-settings: \'FILL\' 1;' : ''">local_hospital</span>
                <span class="text-[10px] mt-0.5 tracking-tight font-semibold">Faskes</span>
            </Link>

            <!-- 5. Tab: Pengaturan -->
            <Link
                :href="route('mobile.settings.index')"
                class="flex-1 flex flex-col items-center justify-center py-1 px-1 rounded-2xl transition-all"
                :class="activeTab === 'settings' ? 'text-[#123356] font-bold bg-[#FDF3F6]' : 'text-[#73777F] hover:text-[#123356]'"
            >
                <span class="material-symbols-outlined text-2xl" :style="activeTab === 'settings' ? 'font-variation-settings: \'FILL\' 1;' : ''">settings</span>
                <span class="text-[10px] mt-0.5 tracking-tight font-semibold">Pengaturan</span>
            </Link>
        </nav>

        <!-- ================================================================= -->
        <!-- MODAL 1: KONFIRMASI DARURAT (Flows.md §8.2, emergency-confirmation-modal.html) -->
        <!-- ================================================================= -->
        <transition
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showEmergencyModal"
                class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
                @click.self="showEmergencyModal = false"
            >
                <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-red-100 text-center animate-fade-in relative overflow-hidden">
                    <div class="w-16 h-16 rounded-full bg-red-100 text-[#C81E2C] mx-auto flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-3xl font-bold animate-bounce">warning</span>
                    </div>

                    <h3 class="text-lg font-extrabold text-[#123356] mb-2">
                        Kirim Peringatan Darurat?
                    </h3>
                    <p class="text-xs text-[#73777F] mb-6 leading-relaxed">
                        Ibu yakin ingin mengirim sinyal darurat ke <span class="font-bold text-[#123356]">Bidan Pendamping & Kader Wilayah</span> sekarang?
                    </p>

                    <div class="flex flex-col gap-2.5">
                        <button
                            @click="submitEmergency"
                            class="w-full py-3.5 px-4 bg-[#C81E2C] hover:bg-red-700 text-white font-bold rounded-xl shadow-lg shadow-red-200 active:scale-98 transition-all flex items-center justify-center gap-2"
                        >
                            <span class="material-symbols-outlined text-lg">notifications_active</span>
                            <span>Ya, Kirim Bantuan Sekarang</span>
                        </button>
                        <button
                            @click="showEmergencyModal = false"
                            class="w-full py-3 px-4 bg-gray-100 hover:bg-gray-200 text-[#26292E] font-semibold rounded-xl active:scale-98 transition-all"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- ================================================================= -->
        <!-- MODAL 2: FALLBACK SMS GATEWAY (Flows.md §22, fallback-sms-gateway-screen.html) -->
        <!-- ================================================================= -->
        <transition
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showSmsFallback"
                class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
                @click.self="showSmsFallback = false"
            >
                <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-amber-100 text-center animate-fade-in">
                    <div class="w-16 h-16 rounded-full bg-amber-100 text-[#E0703D] mx-auto flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-3xl font-bold">sms</span>
                    </div>

                    <h3 class="text-lg font-extrabold text-[#123356] mb-2">
                        Koneksi Internet Terputus
                    </h3>
                    <p class="text-xs text-[#73777F] mb-6 leading-relaxed">
                        Sistem mendeteksi Anda sedang offline. Anda dapat mengirim pesan darurat otomatis lewat SMS langsung ke Posko Bidan / Puskesmas.
                    </p>

                    <div class="flex flex-col gap-2.5">
                        <button
                            @click="sendSmsEmergency"
                            class="w-full py-3.5 px-4 bg-[#E0703D] hover:bg-[#c95b28] text-white font-bold rounded-xl shadow-lg active:scale-98 transition-all flex items-center justify-center gap-2"
                        >
                            <span class="material-symbols-outlined text-lg">send</span>
                            <span>Kirim Peringatan via SMS</span>
                        </button>
                        <button
                            @click="showSmsFallback = false"
                            class="w-full py-3 px-4 bg-gray-100 text-[#26292E] font-semibold rounded-xl active:scale-98 transition-all"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- ================================================================= -->
        <!-- MODAL 3: SWITCHER PROFIL KEHAMILAN (Flows.md §16.2, profile-switcher-dropdown-modal.html) -->
        <!-- ================================================================= -->
        <transition
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showProfileModal"
                class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4"
                @click.self="showProfileModal = false"
            >
                <div class="bg-white rounded-t-3xl sm:rounded-3xl p-6 max-w-md w-full shadow-2xl border border-[#F3AEC0]/30 animate-fade-in max-h-[85vh] overflow-y-auto">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#123356]">switch_account</span>
                            <h3 class="text-base font-extrabold text-[#123356]">
                                Pilih Profil Kehamilan
                            </h3>
                        </div>
                        <button @click="showProfileModal = false" class="text-gray-400 hover:text-gray-600">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <p class="text-xs text-[#73777F] mb-4">
                        Satu nomor HP dapat memantau beberapa kehamilan (misal kehamilan berikutnya atau anggota keluarga).
                    </p>

                    <!-- List Profil -->
                    <div class="flex flex-col gap-2 mb-4">
                        <button
                            v-for="p in allPregnancies"
                            :key="p.id"
                            @click="selectPregnancy(p)"
                            class="p-3.5 rounded-2xl border text-left flex items-center justify-between transition-all"
                            :class="p.id === activePregnancyId ? 'border-[#123356] bg-[#FDF3F6] ring-1 ring-[#123356]' : 'border-gray-200 hover:border-[#F3AEC0] bg-white'"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#123356]/10 text-[#123356] flex items-center justify-center font-bold text-sm">
                                    {{ p.mother_name ? p.mother_name.charAt(0) : 'I' }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-[#123356]">{{ p.mother_name }}</h4>
                                    <p class="text-xs text-[#73777F]">
                                        Status: <span class="capitalize font-semibold">{{ p.status }}</span> • HPL: {{ p.estimated_due_date || '-' }}
                                    </p>
                                </div>
                            </div>
                            <span
                                v-if="p.id === activePregnancyId"
                                class="w-6 h-6 rounded-full bg-[#123356] text-white flex items-center justify-center"
                            >
                                <span class="material-symbols-outlined text-sm font-bold">check</span>
                            </span>
                        </button>
                    </div>

                    <!-- Tombol Tambah Profil Baru -->
                    <Link
                        :href="route('mobile.pregnancy.register.show')"
                        class="w-full py-3 px-4 border border-dashed border-[#123356] text-[#123356] font-bold rounded-xl flex items-center justify-center gap-2 hover:bg-[#FDF3F6] transition-all"
                    >
                        <span class="material-symbols-outlined text-lg">add_circle</span>
                        <span>Daftarkan Kehamilan Baru</span>
                    </Link>
                </div>
            </div>
        </transition>

        <!-- ================================================================= -->
        <!-- MODAL 4: KONFIRMASI LOGOUT (Flows.md §30, logout-confirmation.html) -->
        <!-- ================================================================= -->
        <transition
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showLogoutModal"
                class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
                @click.self="showLogoutModal = false"
            >
                <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl text-center animate-fade-in">
                    <div class="w-14 h-14 rounded-full bg-red-50 text-[#D64550] mx-auto flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-2xl font-bold">logout</span>
                    </div>

                    <h3 class="text-base font-extrabold text-[#123356] mb-1">
                        Keluar dari Akun?
                    </h3>
                    <p class="text-xs text-[#73777F] mb-5 leading-relaxed">
                        Data dan riwayat kehamilan Ibu tetap tersimpan aman. Ibu dapat masuk kembali kapan saja dengan nomor HP terdaftar.
                    </p>

                    <div class="flex flex-col gap-2">
                        <button
                            @click="confirmLogout"
                            class="w-full py-3 px-4 bg-[#D64550] hover:bg-red-700 text-white font-bold rounded-xl shadow-md active:scale-98 transition-all"
                        >
                            Ya, Keluar
                        </button>
                        <button
                            @click="showLogoutModal = false"
                            class="w-full py-2.5 px-4 bg-gray-100 text-[#26292E] font-semibold rounded-xl active:scale-98 transition-all"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.96);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
.animate-fade-in {
    animation: fadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
