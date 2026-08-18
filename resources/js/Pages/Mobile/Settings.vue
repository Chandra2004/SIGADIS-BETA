<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';

const props = defineProps({
    user: {
        type: Object,
        default: () => ({}),
    },
    pregnancies: {
        type: Array,
        default: () => [],
    },
    activePregnancy: {
        type: Object,
        default: null,
    },
});

const selectedTextSize = ref(props.user?.text_size || 'normal');
const isTtsEnabled = ref(props.user?.tts_enabled ?? true);
const isNotificationEnabled = ref(props.user?.screening_reminder_enabled ?? true);

const showProfileModal = ref(false);
const showLogoutModal = ref(false);

const setTextSize = (size) => {
    selectedTextSize.value = size;
    router.post(route('mobile.settings.update'), {
        text_size: size,
    }, {
        preserveScroll: true,
    });
};

const toggleTts = () => {
    isTtsEnabled.value = !isTtsEnabled.value;
    router.post(route('mobile.settings.update'), {
        tts_enabled: isTtsEnabled.value,
    }, {
        preserveScroll: true,
    });
};

const toggleNotification = () => {
    isNotificationEnabled.value = !isNotificationEnabled.value;
    router.post(route('mobile.settings.update'), {
        screening_reminder_enabled: isNotificationEnabled.value,
    }, {
        preserveScroll: true,
    });
};

const selectPregnancy = (pregnancy) => {
    showProfileModal.value = false;
    router.post(route('mobile.profile.switch', pregnancy.id), {}, {
        preserveScroll: true,
    });
};

const confirmLogout = () => {
    showLogoutModal.value = false;
    router.post(route('mobile.logout'));
};
</script>

<template>
    <MobileLayout
        title="Pengaturan & Profil — SIGADIS Mobile"
        activeTab="settings"
        :motherName="user?.full_name"
    >
        <div class="space-y-4">
            <!-- Header Halaman -->
            <div class="pt-1 pb-2">
                <h1 class="text-xl font-black text-[#123356] tracking-tight">
                    Pengaturan & Profil
                </h1>
                <p class="text-xs text-[#73777F]">
                    Kelola profil kehamilan, preferensi tampilan, dan akses akun Anda
                </p>
            </div>

            <!-- 1. KARTU PROFIL UTAMA & KEHAMILAN AKTIF -->
            <div class="bg-white rounded-3xl p-5 border border-[#F3AEC0]/40 shadow-xs space-y-4">
                <div class="flex items-center gap-3.5">
                    <!-- Avatar Foto Profil / Inisial -->
                    <div class="relative shrink-0">
                        <div
                            v-if="user?.profile_photo_url"
                            class="w-14 h-14 rounded-2xl overflow-hidden shadow-md border-2 border-white ring-2 ring-[#F3AEC0]/50"
                        >
                            <img :src="user.profile_photo_url" alt="Foto Profil" class="w-full h-full object-cover" />
                        </div>
                        <div
                            v-else
                            class="w-14 h-14 rounded-2xl bg-linear-to-br from-[#123356] to-[#2C4A6E] text-white flex items-center justify-center font-black text-xl shadow-md border-2 border-white ring-2 ring-[#F3AEC0]/50"
                        >
                            {{ user?.full_name ? user.full_name.charAt(0).toUpperCase() : 'I' }}
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <span class="inline-block px-2.5 py-0.5 rounded-full bg-pink-100 text-[#854E5E] text-[10px] font-extrabold uppercase tracking-wider mb-1">
                            Ibu Hamil
                        </span>
                        <h2 class="text-base font-extrabold text-[#123356] truncate leading-tight">
                            {{ user?.full_name || 'Ibu Hamil' }}
                        </h2>
                        <p class="text-xs text-[#73777F] font-mono mt-0.5">
                            {{ user?.phone_number || '-' }}
                        </p>
                    </div>
                </div>

                <!-- Status Kehamilan Aktif -->
                <div class="p-3.5 bg-[#FDF3F6] rounded-2xl border border-[#F3AEC0]/40 flex items-center justify-between">
                    <div class="space-y-0.5">
                        <span class="text-[10px] font-bold text-[#73777F] uppercase tracking-wider">Profil Kehamilan Aktif</span>
                        <h4 class="text-xs font-black text-[#123356]">
                            {{ activePregnancy?.mother_name || user?.full_name }}
                        </h4>
                        <p class="text-[11px] text-[#73777F]">
                            {{ activePregnancy?.gestational_age_weeks || 0 }} Minggu • HPL: {{ activePregnancy?.estimated_due_date || '-' }}
                        </p>
                    </div>

                    <!-- Tombol Ganti Profil -->
                    <button
                        type="button"
                        @click="showProfileModal = true"
                        class="py-2 px-3 rounded-xl bg-white border border-[#F3AEC0] text-[#123356] font-bold text-xs shadow-xs hover:bg-[#FDF3F6] active:scale-95 transition-all flex items-center gap-1 shrink-0 cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-sm">switch_account</span>
                        <span>Ganti</span>
                    </button>
                </div>
            </div>

            <!-- 2. AKSESIBILITAS: UKURAN TEKS -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-xs space-y-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#123356]">format_size</span>
                    <h3 class="text-xs font-extrabold text-[#123356] uppercase tracking-wider">
                        Ukuran Huruf / Teks
                    </h3>
                </div>
                <p class="text-xs text-[#73777F]">
                    Atur ukuran teks agar lebih nyaman dan mudah dibaca saat menggunakan aplikasi.
                </p>

                <!-- 2 Opsi Diskrit Sesuai Database Schema -->
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <button
                        @click="setTextSize('normal')"
                        class="py-3 px-4 rounded-2xl border text-xs font-bold transition-all text-center flex items-center justify-center gap-1.5 cursor-pointer"
                        :class="selectedTextSize === 'normal' ? 'bg-[#123356] text-white border-[#123356] shadow-xs' : 'bg-gray-50 text-[#73777F] border-gray-200'"
                    >
                        <span class="material-symbols-outlined text-sm">text_fields</span>
                        <span>Normal (Standar)</span>
                    </button>
                    <button
                        @click="setTextSize('besar')"
                        class="py-3 px-4 rounded-2xl border text-sm font-bold transition-all text-center flex items-center justify-center gap-1.5 cursor-pointer"
                        :class="selectedTextSize === 'besar' ? 'bg-[#123356] text-white border-[#123356] shadow-xs' : 'bg-gray-50 text-[#73777F] border-gray-200'"
                    >
                        <span class="material-symbols-outlined text-base">format_size</span>
                        <span>Besar (Lebih Jelas)</span>
                    </button>
                </div>

                <!-- Live Preview Text -->
                <div class="p-3.5 bg-[#FDF3F6] rounded-2xl border border-[#F3AEC0]/30 text-center">
                    <p
                        class="font-semibold text-[#123356] transition-all"
                        :class="{
                            'text-xs': selectedTextSize === 'normal',
                            'text-base': selectedTextSize === 'besar',
                        }"
                    >
                        "Apakah Ibu mengalami perdarahan banyak?"
                    </p>
                </div>
            </div>

            <!-- 3. SUARA & NOTIFIKASI -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-xs space-y-4">
                <h3 class="text-xs font-extrabold text-[#123356] uppercase tracking-wider">
                    Suara & Notifikasi
                </h3>

                <!-- Toggle Suara TTS -->
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-[#123356]">Asisten Suara Otomatis</h4>
                        <p class="text-[11px] text-[#73777F]">Bacakan pertanyaan skrining dengan suara</p>
                    </div>
                    <button
                        @click="toggleTts"
                        class="w-12 h-6 rounded-full transition-colors relative cursor-pointer"
                        :class="isTtsEnabled ? 'bg-[#123356]' : 'bg-gray-300'"
                    >
                        <span
                            class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white transition-transform shadow-xs"
                            :class="isTtsEnabled ? 'translate-x-6' : 'translate-x-0'"
                        ></span>
                    </button>
                </div>

                <!-- Toggle Notifikasi Pengingat -->
                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                    <div>
                        <h4 class="text-xs font-bold text-[#123356]">Pengingat Skrining</h4>
                        <p class="text-[11px] text-[#73777F]">Pemberitahuan jadwal skrining berkala</p>
                    </div>
                    <button
                        @click="toggleNotification"
                        class="w-12 h-6 rounded-full transition-colors relative cursor-pointer"
                        :class="isNotificationEnabled ? 'bg-[#123356]' : 'bg-gray-300'"
                    >
                        <span
                            class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white transition-transform shadow-xs"
                            :class="isNotificationEnabled ? 'translate-x-6' : 'translate-x-0'"
                        ></span>
                    </button>
                </div>
            </div>

            <!-- 4. PRIVASI & PORTABILITAS DATA -->
            <div class="bg-white rounded-3xl p-3 border border-gray-100 shadow-xs space-y-1">
                <!-- Manajemen Privasi (UU PDP) -->
                <Link
                    :href="route('mobile.privacy.index')"
                    class="p-3 rounded-2xl flex items-center justify-between hover:bg-gray-50 active:scale-98 transition-all"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#123356] flex items-center justify-center">
                            <span class="material-symbols-outlined text-base">privacy_tip</span>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-[#123356] block">Manajemen Privasi & Hak Data</span>
                            <span class="text-[10px] text-[#73777F]">Kepatuhan UU PDP No. 27/2022 & Hapus Akun</span>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-gray-400 text-base">chevron_right</span>
                </Link>

                <!-- Bahasa Aplikasi -->
                <div class="p-3 rounded-2xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-pink-50 text-[#854E5E] flex items-center justify-center">
                            <span class="material-symbols-outlined text-base">language</span>
                        </div>
                        <span class="text-xs font-bold text-[#123356]">Bahasa Aplikasi</span>
                    </div>
                    <span class="text-xs font-semibold text-[#73777F]">Bahasa Indonesia</span>
                </div>
            </div>

            <!-- 5. TOMBOL KELUAR DARI AKUN (LOGOUT) -->
            <div class="bg-white rounded-3xl p-4 border border-red-100 shadow-xs">
                <button
                    type="button"
                    @click="showLogoutModal = true"
                    class="w-full py-3 px-4 rounded-2xl bg-red-50 hover:bg-red-100 text-[#D64550] font-extrabold text-xs flex items-center justify-center gap-2 active:scale-98 transition-all border border-red-200 cursor-pointer"
                >
                    <span class="material-symbols-outlined text-lg">logout</span>
                    <span>Keluar dari Akun</span>
                </button>
            </div>

            <!-- Footer Versi Aplikasi -->
            <div class="text-center pt-2 pb-6">
                <p class="text-[11px] font-bold text-[#123356]">SIGADIS Mobile App v1.0.0</p>
                <p class="text-[10px] text-[#73777F]">GEMASTIK XIX 2026 — Tim Bidara</p>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MODAL: SWITCHER PROFIL KEHAMILAN -->
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
                class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
                @click.self="showProfileModal = false"
            >
                <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl animate-fade-in space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#123356]">switch_account</span>
                            <h3 class="text-base font-extrabold text-[#123356]">Pilih Profil Kehamilan</h3>
                        </div>
                        <button
                            @click="showProfileModal = false"
                            class="text-gray-400 hover:text-gray-600 p-1 rounded-full"
                        >
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>

                    <!-- List Profil Kehamilan -->
                    <div class="flex flex-col gap-2 max-h-60 overflow-y-auto pr-1">
                        <button
                            v-for="p in pregnancies"
                            :key="p.id"
                            type="button"
                            @click="selectPregnancy(p)"
                            class="p-3.5 rounded-2xl border text-left flex items-center justify-between transition-all cursor-pointer"
                            :class="p.is_active ? 'border-[#123356] bg-[#FDF3F6] ring-1 ring-[#123356]' : 'border-gray-200 hover:border-[#F3AEC0] bg-white'"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#123356]/10 text-[#123356] flex items-center justify-center font-bold text-sm">
                                    {{ p.mother_name ? p.mother_name.charAt(0) : 'I' }}
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-[#123356]">{{ p.mother_name }}</h4>
                                    <p class="text-[11px] text-[#73777F]">
                                        Status: <span class="capitalize font-semibold">{{ p.status }}</span> • HPL: {{ p.estimated_due_date || '-' }}
                                    </p>
                                </div>
                            </div>
                            <span
                                v-if="p.is_active"
                                class="w-6 h-6 rounded-full bg-[#123356] text-white flex items-center justify-center shrink-0"
                            >
                                <span class="material-symbols-outlined text-sm font-bold">check</span>
                            </span>
                        </button>
                    </div>

                    <!-- Tombol Tambah Profil Baru -->
                    <Link
                        :href="route('mobile.pregnancy.register.show')"
                        class="w-full py-3 px-4 border border-dashed border-[#123356] text-[#123356] font-bold rounded-xl flex items-center justify-center gap-2 hover:bg-[#FDF3F6] transition-all text-xs"
                    >
                        <span class="material-symbols-outlined text-base">add_circle</span>
                        <span>Daftarkan Kehamilan Baru</span>
                    </Link>
                </div>
            </div>
        </transition>

        <!-- ================================================================= -->
        <!-- MODAL: KONFIRMASI LOGOUT -->
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
                class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
                @click.self="showLogoutModal = false"
            >
                <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl text-center animate-fade-in">
                    <div class="w-14 h-14 rounded-full bg-red-100 text-[#D64550] mx-auto flex items-center justify-center mb-3">
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
                            class="w-full py-3 px-4 bg-[#D64550] hover:bg-red-700 text-white font-bold rounded-xl shadow-md active:scale-98 transition-all cursor-pointer text-xs"
                        >
                            Ya, Keluar
                        </button>
                        <button
                            @click="showLogoutModal = false"
                            class="w-full py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-[#26292E] font-semibold rounded-xl active:scale-98 transition-all cursor-pointer text-xs"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </MobileLayout>
</template>
