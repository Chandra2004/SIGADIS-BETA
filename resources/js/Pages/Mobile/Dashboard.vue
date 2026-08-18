<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';

const props = defineProps({
    motherName: {
        type: String,
        default: 'Ibu Anisa',
    },
    phoneNumber: {
        type: String,
        default: '',
    },
    pregnancy: {
        type: Object,
        default: null,
    },
    allPregnancies: {
        type: Array,
        default: () => [],
    },
    activeAlert: {
        type: Object,
        default: null,
    },
});

// State Simulator (Memungkinkan pengujian preview 4 status: 'standard', 'emergency', 'nifas', 'case_closed')
const simulatedState = ref(null); // null = ikuti data backend

const currentStatus = computed(() => {
    if (simulatedState.value) {
        return simulatedState.value;
    }
    if (props.activeAlert && props.activeAlert.status !== 'resolved') {
        return 'emergency';
    }
    if (props.pregnancy?.status === 'nifas') {
        return 'nifas';
    }
    if (props.pregnancy?.status === 'case_closed') {
        return 'case_closed';
    }
    return 'standard';
});

// Helper Data Kehamilan
const gestationalAgeWeeks = computed(() => props.pregnancy?.current_gestational_age_weeks || 24);
const trimester = computed(() => props.pregnancy?.trimester || 2);
const progressPercent = computed(() => props.pregnancy?.progress_percent || 60);
const estimatedDueDate = computed(() => props.pregnancy?.estimated_due_date || '14 November 2026');
const daysToDueDate = computed(() => props.pregnancy?.days_to_due_date || 112);
const nifasDay = computed(() => props.pregnancy?.nifas_day || 12);
const assignedMidwife = computed(() => props.pregnancy?.midwife || {
    full_name: 'Bidan Siti Rahma, S.ST',
    phone_number: '081234567890',
    str_number: 'STR-BDN-2024-9981',
});

// Speech bubble maskot
const mascotTip = computed(() => {
    switch (currentStatus.value) {
        case 'emergency':
            return 'Tetap tenang ya Bunda, bantuan medis segera tiba!';
        case 'nifas':
            return 'Jaga kesehatan luka nifas dan beri ASI eksklusif ya Bu!';
        case 'case_closed':
            return 'Hebat Bunda! Selamat telah melewati masa nifas dengan sehat!';
        default:
            return 'Jangan lupa skrining berkala minggu ini ya Bunda!';
    }
});

const mascotImage = computed(() => {
    switch (currentStatus.value) {
        case 'emergency':
            return '/assets/mascot/mascot-pose-10.webp';
        case 'nifas':
            return '/assets/mascot/mascot-pose-13.webp';
        case 'case_closed':
            return '/assets/mascot/mascot-pose-14.webp';
        default:
            return '/assets/mascot/mascot-pose-8.webp';
    }
});

// Aksi Selesaikan Darurat (Jika dalam mode emergency)
const resolveEmergency = () => {
    router.post(route('mobile.emergency.resolve'));
};
</script>

<template>
    <MobileLayout
        :title="`Beranda Ibu Hamil — SIGADIS`"
        activeTab="home"
        :motherName="pregnancy?.mother_name || motherName"
        :allPregnancies="allPregnancies"
        :activePregnancyId="pregnancy?.id"
    >
        <!-- Header Branding & Sapaan Hangat -->
        <div class="flex items-center justify-between pt-1 pb-2">
            <div>
                <span class="text-[10px] font-bold text-[#854E5E] uppercase tracking-wider block">Selamat Datang</span>
                <h1 class="text-xl font-black text-[#123356] tracking-tight">
                    {{ pregnancy?.mother_name || motherName || 'Bunda' }} 👋
                </h1>
            </div>
            <div class="flex items-center gap-1 px-3 py-1.5 rounded-full bg-white/80 border border-[#F3AEC0]/40 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-[#4C9A6E] animate-pulse"></span>
                <span class="text-xs font-black text-[#123356]">SIGADIS</span>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- STATE SWITCHER BAR (Simulasi Demo Komprehensif) -->
        <!-- ============================================================= -->
        <div class="mb-4 bg-white/80 backdrop-blur-md rounded-2xl p-2.5 border border-[#F3AEC0]/40 shadow-xs flex items-center justify-between">
            <span class="text-[11px] font-bold text-[#123356] flex items-center gap-1">
                <span class="material-symbols-outlined text-sm text-[#E0703D]">tune</span>
                Simulasi Status:
            </span>
            <div class="flex items-center gap-1">
                <button
                    @click="simulatedState = 'standard'"
                    class="px-2.5 py-1 rounded-xl text-[10px] font-bold transition-all"
                    :class="currentStatus === 'standard' ? 'bg-[#123356] text-white shadow-xs' : 'bg-gray-100 text-[#73777F] hover:bg-gray-200'"
                >
                    Hamil Aktif
                </button>
                <button
                    @click="simulatedState = 'emergency'"
                    class="px-2.5 py-1 rounded-xl text-[10px] font-bold transition-all"
                    :class="currentStatus === 'emergency' ? 'bg-[#C81E2C] text-white shadow-xs' : 'bg-gray-100 text-[#73777F] hover:bg-gray-200'"
                >
                    Darurat (SOS)
                </button>
                <button
                    @click="simulatedState = 'nifas'"
                    class="px-2.5 py-1 rounded-xl text-[10px] font-bold transition-all"
                    :class="currentStatus === 'nifas' ? 'bg-[#854E5E] text-white shadow-xs' : 'bg-gray-100 text-[#73777F] hover:bg-gray-200'"
                >
                    Nifas (42 Hari)
                </button>
                <button
                    @click="simulatedState = 'case_closed'"
                    class="px-2.5 py-1 rounded-xl text-[10px] font-bold transition-all"
                    :class="currentStatus === 'case_closed' ? 'bg-[#4C9A6E] text-white shadow-xs' : 'bg-gray-100 text-[#73777F] hover:bg-gray-200'"
                >
                    Selesai
                </button>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- 1. KONDISI DARURAT AKTIF (emergency-active-home.html) -->
        <!-- ============================================================= -->
        <section v-if="currentStatus === 'emergency'" class="space-y-4 animate-fade-in">
            <!-- Pulsing Emergency Banner -->
            <div class="bg-[#C81E2C] rounded-3xl p-5 text-white shadow-xl flex items-center gap-4 relative overflow-hidden">
                <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-3xl font-bold animate-bounce">warning</span>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-black tracking-wide uppercase">Peringatan Darurat Aktif!</h3>
                    <p class="text-xs text-red-100 leading-snug mt-0.5">
                        Bidan dan kader Ibu telah diberi tahu secara otomatis melalui sistem koordinasi.
                    </p>
                </div>
            </div>

            <!-- Kartu Status Penanganan -->
            <div class="bg-white rounded-3xl p-5 border border-red-100 shadow-md">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#E0703D] animate-ping"></span>
                        <span class="text-xs font-bold text-[#123356]">Status Respons</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-[#E0703D] text-[11px] font-bold">
                        Sedang Ditangani
                    </span>
                </div>

                <div class="py-4 space-y-2">
                    <h4 class="text-base font-extrabold text-[#123356]">Bantuan Sedang Menuju</h4>
                    <p class="text-xs text-[#73777F] leading-relaxed">
                        Harap tetap tenang, berbaring miring ke kiri, dan siapkan Buku KIA / dokumen kesehatan keluarga.
                    </p>
                </div>

                <!-- Action Buttons: Direct Call -->
                <div class="grid grid-cols-2 gap-2.5 pt-2">
                    <a
                        :href="`tel:${assignedMidwife.phone_number}`"
                        class="py-3 px-3 rounded-2xl bg-[#123356] text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-md active:scale-95 transition-all"
                    >
                        <span class="material-symbols-outlined text-base">call</span>
                        <span>Telepon Bidan</span>
                    </a>
                    <Link
                        :href="route('mobile.facilities.index')"
                        class="py-3 px-3 rounded-2xl bg-white border border-[#123356] text-[#123356] text-xs font-bold flex items-center justify-center gap-1.5 active:scale-95 transition-all"
                    >
                        <span class="material-symbols-outlined text-base">directions</span>
                        <span>Rute ke IGD</span>
                    </Link>
                </div>

                <!-- Tombol Tandai Selesai (Jika alert sudah tertangani) -->
                <button
                    @click="resolveEmergency"
                    class="w-full mt-3 py-2.5 text-center text-xs font-semibold text-gray-500 hover:text-gray-700 underline"
                >
                    Tandai Status Darurat Selesai
                </button>
            </div>
        </section>

        <!-- ============================================================= -->
        <!-- 2. KONDISI MASA NIFAS 42 HARI (postpartum-nifas-home.html) -->
        <!-- ============================================================= -->
        <section v-else-if="currentStatus === 'nifas'" class="space-y-4 animate-fade-in">
            <!-- Header Nifas Tracker -->
            <div class="bg-gradient-to-br from-[#854E5E] to-[#2C4A6E] rounded-3xl p-5 text-white shadow-lg relative overflow-hidden">
                <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

                <div class="flex items-center justify-between mb-3">
                    <span class="px-3 py-1 rounded-full bg-white/20 text-[11px] font-bold tracking-wide uppercase">
                        Masa Nifas (Postpartum)
                    </span>
                    <span class="text-xs font-semibold text-pink-200">Total 42 Hari</span>
                </div>

                <h3 class="text-xl font-extrabold mb-1">
                    Hari ke-{{ nifasDay }} <span class="text-sm font-normal text-pink-100">/ 42 Hari</span>
                </h3>
                <p class="text-xs text-pink-100 leading-relaxed mb-4">
                    Selamat atas kelahiran buah hati Ibu! SIGADIS terus mendampingi kesehatan Ibu hingga 42 hari pascapersalinan.
                </p>

                <!-- Progress Bar Nifas -->
                <div class="w-full bg-black/20 rounded-full h-2.5 overflow-hidden">
                    <div
                        class="bg-gradient-to-r from-[#F3AEC0] to-[#E0703D] h-full rounded-full transition-all duration-500"
                        :style="`width: ${(nifasDay / 42) * 100}%`"
                    ></div>
                </div>
            </div>

            <!-- Card Skrining Nifas Berkala -->
            <div class="bg-white rounded-3xl p-5 border border-[#F3AEC0]/40 shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-[#854E5E]">health_and_safety</span>
                    <span class="text-xs font-bold text-[#854E5E] uppercase tracking-wider">Skrining Gejala Nifas</span>
                </div>
                <h4 class="text-base font-extrabold text-[#123356] mb-1">
                    Periksa Tanda Bahaya Pascapersalinan
                </h4>
                <p class="text-xs text-[#73777F] mb-4 leading-relaxed">
                    Deteksi dini perdarahan hebat nifas, demam tinggi, infeksi luka jahitan, atau keluhan menyusui.
                </p>
                <Link
                    :href="route('mobile.screening.index')"
                    class="w-full py-3.5 px-4 rounded-2xl bg-[#854E5E] hover:bg-[#6e3e4d] text-white text-xs font-bold flex items-center justify-center gap-2 shadow-md active:scale-98 transition-all"
                >
                    <span>Mulai Skrining Nifas</span>
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                </Link>
            </div>
        </section>

        <!-- ============================================================= -->
        <!-- 3. KONDISI KASUS SELESAI SELAMAT (case-closed-home.html) -->
        <!-- ============================================================= -->
        <section v-else-if="currentStatus === 'case_closed'" class="space-y-4 animate-fade-in">
            <div class="bg-gradient-to-br from-[#4C9A6E] to-[#123356] rounded-3xl p-6 text-white text-center shadow-xl relative overflow-hidden">
                <div class="w-16 h-16 rounded-full bg-white/20 mx-auto flex items-center justify-center mb-3">
                    <span class="material-symbols-outlined text-4xl text-amber-300">verified</span>
                </div>
                <h3 class="text-xl font-extrabold mb-1">Selamat Bunda!</h3>
                <p class="text-xs text-emerald-100 leading-relaxed max-w-xs mx-auto mb-4">
                    Ibu dan buah hati telah berhasil menyelesaikan seluruh masa pemantauan kehamilan dan 42 hari nifas dengan sehat dan selamat.
                </p>
                <span class="inline-block px-4 py-1.5 rounded-full bg-white text-[#4C9A6E] text-xs font-black tracking-wider uppercase shadow-md">
                    Status: Kasus Selesai Selamat
                </span>
            </div>

            <!-- Akses Arsip Riwayat -->
            <div class="bg-white rounded-3xl p-5 border border-gray-200 shadow-sm">
                <h4 class="text-sm font-extrabold text-[#123356] mb-1">Arsip Rekam Kesehatan Anda</h4>
                <p class="text-xs text-[#73777F] mb-4">
                    Seluruh riwayat skrining, tensi, dan catatan medis dari Bidan tetap tersimpan aman dan dapat Anda lihat kapan saja.
                </p>
                <Link
                    :href="route('mobile.history.index')"
                    class="w-full py-3 px-4 rounded-2xl bg-[#123356] text-white text-xs font-bold flex items-center justify-center gap-2 shadow-sm"
                >
                    <span class="material-symbols-outlined text-base">history_edu</span>
                    <span>Lihat Riwayat Lengkap</span>
                </Link>
            </div>
        </section>

        <!-- ============================================================= -->
        <!-- 4. KONDISI STANDAR KEHAMILAN AKTIF (standard-active-home.html) -->
        <!-- ============================================================= -->
        <section v-else class="space-y-4 animate-fade-in">
            <!-- Welcome Card Kehamilan -->
            <div class="bg-gradient-to-br from-[#123356] to-[#2C4A6E] rounded-3xl p-5 text-white shadow-xl relative overflow-hidden">
                <!-- Background decorative shape -->
                <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-[#F3AEC0]/20 rounded-full blur-2xl pointer-events-none"></div>

                <div class="flex items-center justify-between mb-3">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md">
                        <span class="w-2 h-2 rounded-full bg-[#4C9A6E]"></span>
                        <span class="text-[11px] font-bold tracking-wide">Trimester {{ trimester }}</span>
                    </div>
                    <span class="text-xs font-semibold text-blue-200">
                        {{ daysToDueDate }} Hari Menuju HPL
                    </span>
                </div>

                <div class="mb-4">
                    <h2 class="text-2xl font-black tracking-tight">
                        {{ gestationalAgeWeeks }} <span class="text-base font-medium text-blue-200">Minggu</span>
                    </h2>
                    <p class="text-xs text-blue-100 mt-0.5">
                        Hari Perkiraan Lahir: <span class="font-bold text-white">{{ estimatedDueDate }}</span>
                    </p>
                </div>

                <!-- Progress Bar Trimester -->
                <div class="space-y-1.5">
                    <div class="flex justify-between text-[10px] text-blue-200 font-semibold">
                        <span>Progres Kehamilan</span>
                        <span>{{ progressPercent }}%</span>
                    </div>
                    <div class="w-full bg-black/25 rounded-full h-2.5 overflow-hidden">
                        <div
                            class="bg-gradient-to-r from-[#F3AEC0] to-[#E0703D] h-full rounded-full transition-all duration-700"
                            :style="`width: ${progressPercent}%`"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Card: Skrining Berkala Berikutnya (standard-active-home.html) -->
            <div class="bg-white rounded-3xl p-5 border-l-4 border-[#4C9A6E] border-y border-r border-gray-100 shadow-md relative overflow-hidden">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-[#4C9A6E] text-lg">calendar_today</span>
                    <span class="text-[11px] font-bold text-[#4C9A6E] uppercase tracking-wider">Jadwal Terdekat</span>
                </div>
                <h3 class="text-base font-extrabold text-[#123356] mb-1">
                    Skrining Berkala Mandiri
                </h3>
                <p class="text-xs text-[#73777F] mb-4 leading-relaxed">
                    Luangkan 1 menit untuk menjawab beberapa pertanyaan sederhana guna memastikan perkembangan janin tetap sehat.
                </p>
                <Link
                    :href="route('mobile.screening.index')"
                    class="w-full py-3.5 px-4 rounded-2xl bg-[#123356] hover:bg-[#2C4A6E] text-white text-xs font-bold flex items-center justify-center gap-2 shadow-md active:scale-98 transition-all"
                >
                    <span>Mulai Skrining Sekarang</span>
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                </Link>
            </div>

            <!-- Kartu Bidan Pendamping Resmi -->
            <div class="bg-white rounded-3xl p-4 border border-[#F3AEC0]/40 shadow-xs flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-[#FDF3F6] border border-[#F3AEC0]/60 text-[#123356] flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-2xl text-[#123356]">medical_services</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-[#73777F] uppercase tracking-wider">Bidan Pendamping</span>
                        <h4 class="text-xs font-extrabold text-[#123356]">{{ assignedMidwife.full_name }}</h4>
                        <p class="text-[10px] text-[#73777F]">{{ assignedMidwife.str_number || 'Wilayah Puskesmas Binaan' }}</p>
                    </div>
                </div>
                <a
                    :href="`tel:${assignedMidwife.phone_number}`"
                    class="w-9 h-9 rounded-full bg-[#123356] text-white flex items-center justify-center shadow-xs active:scale-90 transition-all"
                    aria-label="Hubungi Bidan"
                >
                    <span class="material-symbols-outlined text-base">call</span>
                </a>
            </div>

            <!-- Bento Shortcuts Grid -->
            <div class="grid grid-cols-2 gap-3">
                <!-- Shortcut 1: Faskes Terdekat -->
                <Link
                    :href="route('mobile.facilities.index')"
                    class="bg-white rounded-3xl p-4 border border-gray-100 shadow-xs hover:border-[#F3AEC0] active:scale-98 transition-all flex flex-col justify-between"
                >
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#123356] flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-xl">local_hospital</span>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-[#123356]">Faskes Terdekat</h4>
                        <p class="text-[10px] text-[#73777F] mt-0.5">Puskesmas & RS PONEK</p>
                    </div>
                </Link>

                <!-- Shortcut 2: Riwayat Kehamilan -->
                <Link
                    :href="route('mobile.history.index')"
                    class="bg-white rounded-3xl p-4 border border-gray-100 shadow-xs hover:border-[#F3AEC0] active:scale-98 transition-all flex flex-col justify-between"
                >
                    <div class="w-10 h-10 rounded-2xl bg-pink-50 text-[#854E5E] flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-xl">history_edu</span>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-[#123356]">Riwayat Saya</h4>
                        <p class="text-[10px] text-[#73777F] mt-0.5">Skrining & Catatan ANC</p>
                    </div>
                </Link>
            </div>
        </section>

        <!-- ============================================================= -->
        <!-- MASKOT PENDAMPING & SPEECH BUBBLE (Design.md §4.3) -->
        <!-- ============================================================= -->
        <div class="mt-6 mb-2 flex items-end justify-between">
            <div class="bg-white p-3.5 rounded-3xl rounded-bl-xs shadow-md border border-[#F3AEC0]/40 max-w-[210px] animate-fade-in">
                <p class="text-[11px] font-semibold text-[#123356] leading-snug">
                    {{ mascotTip }}
                </p>
            </div>

            <div class="relative">
                <img
                    :src="mascotImage"
                    alt="Maskot SIGADIS"
                    class="w-28 h-28 object-contain drop-shadow-xl hover:scale-105 transition-transform"
                    onerror="this.onerror=null; this.src='/assets/mascot/mascot-pose-8.webp';"
                />
            </div>
        </div>
    </MobileLayout>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in {
    animation: fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
